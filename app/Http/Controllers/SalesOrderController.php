<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Mail\OrderReceiptMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\StoreProductQuantity;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\InventoryService;
use App\Services\OrderNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SalesOrderController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly OrderNumberService $orderNumberService,
        private readonly InventoryService $inventoryService,
    ) {
    }

    public function index(Request $request)
    {
        $salesOrders = SalesOrder::query()
            ->with(['customer', 'user', 'store'])
            ->when($request->user()->hasRole(User::ROLE_SALES_OFFICER), function ($query) use ($request) {
                $query->where('store_id', $request->user()->store_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('store', fn ($storeQuery) => $storeQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('store_id') && $request->user()->hasRole(User::ROLE_ADMIN), fn ($query) => $query->where('store_id', $request->integer('store_id')))
            ->latest('order_date')
            ->paginate(10)
            ->withQueryString();

        return view('sales-orders.index', [
            'salesOrders' => $salesOrders,
            'statuses' => SalesOrder::STATUSES,
            'stores' => Store::query()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('sales-orders.create', $this->formOptions(new SalesOrder([
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_DELIVERED,
            'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
            'tax' => 0,
            'discount' => 0,
            'store_id' => auth()->user()?->store_id,
        ])));
    }

    public function store(StoreSalesOrderRequest $request)
    {
        $salesOrder = DB::transaction(function () use ($request) {
            $customer = $this->resolveCustomer($request);
            $storeId = $this->resolveStoreId($request);
            [$subtotal, $tax, $discount, $total, $amountPaid, $items] = $this->normalizeSalesOrderPayload($request->validated(), $customer, $request->user(), $storeId);

            $salesOrder = SalesOrder::create([
                'order_number' => $this->orderNumberService->salesOrderNumber(),
                'customer_id' => $customer->id,
                'user_id' => $request->user()->id,
                'store_id' => $storeId,
                'order_date' => $request->date('order_date'),
                'status' => $request->input('status'),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'payment_method' => $request->input('payment_method'),
                'payment_status' => $request->input('payment_status'),
                'due_date' => $request->date('due_date'),
                'total' => $total,
                'amount_paid' => $amountPaid,
                'notes' => $request->input('notes'),
            ]);

            $salesOrder->items()->createMany($items);
            $salesOrder->load(['items.product', 'customer', 'user', 'store']);

            $this->ensureSufficientStock($salesOrder);
            $this->inventoryService->applySalesOrder($salesOrder);
            $this->refreshCustomerBalance($customer);

            return $salesOrder;
        });

        $this->activityLogService->log($request->user()->id, 'created', 'sales_orders', "Created sales order {$salesOrder->order_number}.", $salesOrder);
        $this->sendReceiptEmail($salesOrder);

        return redirect()->route('sales-orders.receipt', $salesOrder)->with('success', 'Order checked out successfully.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $this->authorizeSalesOrderAccess(request()->user(), $salesOrder);
        $relations = ['customer', 'user', 'store', 'items.product'];

        if (ActivityLog::schemaIsReady()) {
            $relations[] = 'activityLogs.user';
        }

        $salesOrder->load($relations);

        return view('sales-orders.show', [
            'salesOrder' => $salesOrder,
        ]);
    }

    public function edit(SalesOrder $salesOrder)
    {
        $this->authorizeSalesOrderAccess(request()->user(), $salesOrder);
        $salesOrder->load('items');

        return view('sales-orders.edit', $this->formOptions($salesOrder));
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder)
    {
        $this->authorizeSalesOrderAccess($request->user(), $salesOrder);
        $before = $this->salesOrderSnapshot($salesOrder);

        if ($request->user()->hasRole(User::ROLE_SALES_OFFICER)) {
            DB::transaction(function () use ($request, $salesOrder) {
                $salesOrder->load('items.product');
                $this->inventoryService->revertSalesOrder($salesOrder);

                $paymentStatus = $request->input('payment_status');

                $salesOrder->update([
                    'status' => $request->input('status'),
                    'payment_status' => $paymentStatus,
                    'payment_method' => $paymentStatus === SalesOrder::PAYMENT_STATUS_PAID ? $request->input('payment_method') : null,
                    'due_date' => $paymentStatus === SalesOrder::PAYMENT_STATUS_PENDING ? ($salesOrder->due_date ?: $salesOrder->order_date) : null,
                    'amount_paid' => $paymentStatus === SalesOrder::PAYMENT_STATUS_PAID ? $salesOrder->total : 0,
                ]);

                $this->ensureSufficientStock($salesOrder->refresh()->load('items.product'));
                $this->inventoryService->applySalesOrder($salesOrder);
                $this->refreshCustomerBalance($salesOrder->customer);
            });

            $salesOrder->refresh();

            $this->activityLogService->log(
                $request->user()->id,
                'updated',
                'sales_orders',
                "Updated sales order {$salesOrder->order_number}.",
                $salesOrder,
                $before,
                $this->salesOrderSnapshot($salesOrder->fresh(['customer', 'store', 'items.product']))
            );

            return redirect()->route('sales-orders.receipt', $salesOrder)->with('success', 'Sales order updated successfully.');
        }

        DB::transaction(function () use ($request, $salesOrder) {
            $salesOrder->load('items.product');
            $this->inventoryService->revertSalesOrder($salesOrder);
            $originalCustomerId = $salesOrder->customer_id;

            $customer = $this->resolveCustomer($request);
            $storeId = $this->resolveStoreId($request);
            [$subtotal, $tax, $discount, $total, $amountPaid, $items] = $this->normalizeSalesOrderPayload($request->validated(), $customer, $request->user(), $storeId);

            $salesOrder->update([
                'customer_id' => $customer->id,
                'user_id' => $request->user()->id,
                'store_id' => $storeId,
                'order_date' => $request->date('order_date'),
                'status' => $request->input('status'),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'payment_method' => $request->input('payment_method'),
                'payment_status' => $request->input('payment_status'),
                'due_date' => $request->date('due_date'),
                'total' => $total,
                'amount_paid' => $amountPaid,
                'notes' => $request->input('notes'),
            ]);

            $salesOrder->items()->delete();
            $salesOrder->items()->createMany($items);
            $salesOrder->load(['items.product', 'customer', 'user', 'store']);

            $this->ensureSufficientStock($salesOrder);
            $this->inventoryService->applySalesOrder($salesOrder);
            $this->refreshCustomerBalance($customer);

            if ($originalCustomerId !== $customer->id) {
                $this->refreshCustomerBalance(Customer::findOrFail($originalCustomerId));
            }
        });

        $salesOrder->refresh();

        $this->activityLogService->log(
            $request->user()->id,
            'updated',
            'sales_orders',
            "Updated sales order {$salesOrder->order_number}.",
            $salesOrder,
            $before,
            $this->salesOrderSnapshot($salesOrder->fresh(['customer', 'store', 'items.product']))
        );
        $this->sendReceiptEmail($salesOrder->load(['customer', 'user', 'store', 'items.product']));

        return redirect()->route('sales-orders.receipt', $salesOrder)->with('success', 'Sales order updated successfully.');
    }

    public function receipt(SalesOrder $salesOrder)
    {
        $this->authorizeSalesOrderAccess(request()->user(), $salesOrder);
        $salesOrder->load(['customer', 'user', 'store', 'items.product']);

        return view('sales-orders.receipt', [
            'salesOrder' => $salesOrder,
        ]);
    }

    public function destroy(Request $request, SalesOrder $salesOrder)
    {
        $this->authorizeSalesOrderAccess($request->user(), $salesOrder);
        abort_unless($request->user()->hasRole(User::ROLE_ADMIN), 403);
        $orderNumber = $salesOrder->order_number;

        DB::transaction(function () use ($salesOrder) {
            $salesOrder->load('items.product');
            $this->inventoryService->revertSalesOrder($salesOrder);
            $customer = $salesOrder->customer;
            $salesOrder->delete();
            $this->refreshCustomerBalance($customer);
        });

        $this->activityLogService->log($request->user()->id, 'deleted', 'sales_orders', "Deleted sales order {$orderNumber}.");

        return redirect()->route('sales-orders.index')->with('success', 'Sales order deleted successfully.');
    }

    private function formOptions(SalesOrder $salesOrder): array
    {
        return [
            'salesOrder' => $salesOrder,
            'customers' => Customer::query()
                ->with(['productDiscounts:customer_id,product_id,store_id,discount_amount'])
                ->orderBy('full_name')
                ->get(['id', 'full_name']),
            'products' => Product::where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
            'statuses' => SalesOrder::STATUSES,
            'paymentMethods' => SalesOrder::PAYMENT_METHODS,
            'paymentStatuses' => SalesOrder::PAYMENT_STATUSES,
            'stores' => Store::query()->orderBy('name')->get(),
        ];
    }

    private function normalizeSalesOrderPayload(array $validated, Customer $customer, User $user, ?int $storeId): array
    {
        $tax = (float) ($validated['tax'] ?? 0);
        $savedDiscount = 0;
        $manualDiscount = $user->hasRole(User::ROLE_ADMIN) ? (float) ($validated['discount'] ?? 0) : 0;
        $subtotal = 0;
        $items = [];

        foreach ($validated['items'] as $item) {
            $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];
            $subtotal += $lineTotal;
            $savedDiscount += $this->discountForCustomerProduct($customer, (int) $item['product_id'], $storeId) * (int) $item['quantity'];
            $items[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $lineTotal,
            ];
        }

        $discount = $user->hasRole(User::ROLE_ADMIN) ? $manualDiscount : $savedDiscount;
        $total = max(0, $subtotal + $tax - $discount);
        $amountPaid = ($validated['payment_status'] ?? SalesOrder::PAYMENT_STATUS_PAID) === SalesOrder::PAYMENT_STATUS_PENDING
            ? 0
            : (float) ($validated['amount_paid'] ?? $total);

        return [$subtotal, $tax, $discount, $total, $amountPaid, $items];
    }

    private function ensureSufficientStock(SalesOrder $salesOrder): void
    {
        if ($salesOrder->status !== SalesOrder::STATUS_DELIVERED) {
            return;
        }

        foreach ($salesOrder->items as $item) {
            if ($item->quantity > $item->product->stock_quantity) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$item->product->name}.",
                ]);
            }

            if (! $salesOrder->store_id) {
                continue;
            }

            $storeQuantity = StoreProductQuantity::query()
                ->where('store_id', $salesOrder->store_id)
                ->where('product_id', $item->product_id)
                ->first();

            $hasStoreAllocations = StoreProductQuantity::query()
                ->where('product_id', $item->product_id)
                ->exists();

            if ($hasStoreAllocations && ! $storeQuantity) {
                throw ValidationException::withMessages([
                    'items' => "{$item->product->name} is not allocated to the selected store.",
                ]);
            }

            if ($storeQuantity && $item->quantity > $storeQuantity->quantity) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$item->product->name} in the selected store.",
                ]);
            }
        }
    }

    private function resolveCustomer(StoreSalesOrderRequest|UpdateSalesOrderRequest $request): Customer
    {
        if ($request->input('customer_mode') !== 'new' && $request->filled('customer_id')) {
            return Customer::findOrFail($request->integer('customer_id'));
        }

        return Customer::create([
            'full_name' => $request->input('customer.full_name'),
            'business_name' => $request->input('customer.business_name'),
            'email' => $request->input('customer.email'),
            'phone' => $request->input('customer.phone'),
            'address' => $request->input('customer.address'),
            'customer_type' => $request->input('customer.customer_type'),
            'account_balance' => 0,
            'status' => Customer::STATUS_ACTIVE,
            'notes' => $request->input('customer.notes'),
            'created_by' => $request->user()->id,
        ]);
    }

    private function resolveStoreId(StoreSalesOrderRequest|UpdateSalesOrderRequest $request): ?int
    {
        if ($request->user()->hasRole(User::ROLE_ADMIN)) {
            return $request->integer('store_id') ?: null;
        }

        return $request->user()->store_id;
    }

    private function discountForCustomerProduct(Customer $customer, int $productId, ?int $storeId): float
    {
        if (! $customer->relationLoaded('productDiscounts')) {
            $customer->load('productDiscounts');
        }

        return (float) ($customer->productDiscounts
            ->first(fn ($discount) => (int) $discount->product_id === $productId && (int) $discount->store_id === (int) $storeId)?->discount_amount ?? 0);
    }

    private function authorizeSalesOrderAccess(User $user, SalesOrder $salesOrder): void
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        abort_if($salesOrder->store_id !== $user->store_id, 403);
    }

    private function sendReceiptEmail(SalesOrder $salesOrder): void
    {
        if (! $salesOrder->customer?->email) {
            return;
        }

        try {
            Mail::to($salesOrder->customer->email)->send(new OrderReceiptMail($salesOrder));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function refreshCustomerBalance(Customer $customer): void
    {
        $balance = (float) ($customer->salesOrders()
            ->selectRaw('COALESCE(SUM(amount_paid - total), 0) as balance')
            ->value('balance') ?? 0);

        $customer->update(['account_balance' => $balance]);
    }

    private function salesOrderSnapshot(SalesOrder $salesOrder): array
    {
        $salesOrder->loadMissing(['customer', 'store', 'items.product']);

        return [
            'customer' => $salesOrder->customer?->full_name,
            'store' => $salesOrder->store?->name,
            'order_date' => optional($salesOrder->order_date)->format('Y-m-d'),
            'status' => $salesOrder->status,
            'payment_status' => $salesOrder->payment_status,
            'payment_method' => $salesOrder->payment_method,
            'due_date' => optional($salesOrder->due_date)->format('Y-m-d'),
            'subtotal' => (float) $salesOrder->subtotal,
            'tax' => (float) $salesOrder->tax,
            'discount' => (float) $salesOrder->discount,
            'total' => (float) $salesOrder->total,
            'amount_paid' => (float) $salesOrder->amount_paid,
            'notes' => $salesOrder->notes,
            'items' => $salesOrder->items
                ->map(fn ($item) => [
                    'product' => $item->product?->name ?? "Product #{$item->product_id}",
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ])
                ->sortBy('product')
                ->values()
                ->all(),
        ];
    }
}
