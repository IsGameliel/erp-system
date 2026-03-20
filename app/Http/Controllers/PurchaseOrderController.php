<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Services\ActivityLogService;
use App\Services\InventoryService;
use App\Services\OrderNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly OrderNumberService $orderNumberService,
        private readonly InventoryService $inventoryService,
    ) {
    }

    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with(['vendor', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('po_number', 'like', "%{$search}%")
                        ->orWhereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('order_date')
            ->paginate(10)
            ->withQueryString();

        return view('purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'statuses' => PurchaseOrder::STATUSES,
        ]);
    }

    public function create()
    {
        return view('purchase-orders.create', $this->formOptions(new PurchaseOrder([
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'tax' => 0,
            'discount' => 0,
        ])));
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $purchaseOrder = DB::transaction(function () use ($request) {
            [$subtotal, $tax, $discount, $total, $items] = $this->normalizePurchaseOrderPayload($request->validated());

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->orderNumberService->purchaseOrderNumber(),
                'vendor_id' => $request->integer('vendor_id'),
                'user_id' => $request->user()->id,
                'order_date' => $request->date('order_date'),
                'status' => $request->string('status'),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'notes' => $request->input('notes'),
            ]);

            $purchaseOrder->items()->createMany($items);
            $purchaseOrder->load('items.product');
            $this->inventoryService->applyPurchaseOrder($purchaseOrder);

            return $purchaseOrder;
        });

        $this->activityLogService->log($request->user()->id, 'created', 'purchase_orders', "Created purchase order {$purchaseOrder->po_number}.", $purchaseOrder);

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'user', 'items.product', 'activityLogs.user']);

        return view('purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items');

        return view('purchase-orders.edit', $this->formOptions($purchaseOrder));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        DB::transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->load('items.product');
            $this->inventoryService->revertPurchaseOrder($purchaseOrder);

            [$subtotal, $tax, $discount, $total, $items] = $this->normalizePurchaseOrderPayload($request->validated());

            $purchaseOrder->update([
                'vendor_id' => $request->integer('vendor_id'),
                'user_id' => $request->user()->id,
                'order_date' => $request->date('order_date'),
                'status' => $request->string('status'),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'notes' => $request->input('notes'),
            ]);

            $purchaseOrder->items()->delete();
            $purchaseOrder->items()->createMany($items);
            $purchaseOrder->load('items.product');
            $this->inventoryService->applyPurchaseOrder($purchaseOrder);
        });

        $purchaseOrder->refresh();

        $this->activityLogService->log($request->user()->id, 'updated', 'purchase_orders', "Updated purchase order {$purchaseOrder->po_number}.", $purchaseOrder);

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order updated successfully.');
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder)
    {
        $poNumber = $purchaseOrder->po_number;

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->load('items.product');
            $this->inventoryService->revertPurchaseOrder($purchaseOrder);
            $purchaseOrder->delete();
        });

        $this->activityLogService->log($request->user()->id, 'deleted', 'purchase_orders', "Deleted purchase order {$poNumber}.");

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted successfully.');
    }

    private function formOptions(PurchaseOrder $purchaseOrder): array
    {
        return [
            'purchaseOrder' => $purchaseOrder,
            'vendors' => Vendor::orderBy('company_name')->get(),
            'products' => Product::where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
            'statuses' => PurchaseOrder::STATUSES,
        ];
    }

    private function normalizePurchaseOrderPayload(array $validated): array
    {
        $tax = (float) ($validated['tax'] ?? 0);
        $discount = (float) ($validated['discount'] ?? 0);
        $subtotal = 0;
        $items = [];

        foreach ($validated['items'] as $item) {
            $lineTotal = (float) $item['quantity'] * (float) $item['unit_cost'];
            $subtotal += $lineTotal;
            $items[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'total_cost' => $lineTotal,
            ];
        }

        $total = max(0, $subtotal + $tax - $discount);

        return [$subtotal, $tax, $discount, $total, $items];
    }
}
