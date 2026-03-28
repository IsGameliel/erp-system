<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Services\ActivityLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withCount('salesOrders')
            ->withCount('productDiscounts')
            ->withSum('salesOrders as total_amount_spent', 'total')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('business_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('customer_type'), fn ($query) => $query->where('customer_type', $request->string('customer_type')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'statuses' => Customer::STATUSES,
            'customerTypes' => Customer::query()->whereNotNull('customer_type')->distinct()->pluck('customer_type')->filter()->values(),
        ]);
    }

    public function create()
    {
        return view('customers.create', [
            'customer' => new Customer(),
            'statuses' => Customer::STATUSES,
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'selling_price']),
            'stores' => Store::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated() + [
            'created_by' => $request->user()->id,
            'account_balance' => 0,
        ]);
        $this->syncProductDiscounts($customer, $request->validated('product_discounts', []));

        $this->activityLogService->log($request->user()->id, 'created', 'customers', "Created customer {$customer->full_name}.", $customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $relations = [
            'creator',
            'productDiscounts.product',
            'productDiscounts.store',
            'salesOrders.user',
        ];

        if (\App\Models\ActivityLog::schemaIsReady()) {
            $relations[] = 'activityLogs.user';
        }

        $customer->load($relations);

        return view('customers.show', [
            'customer' => $customer,
            'totalOrders' => $customer->salesOrders->count(),
            'totalAmountSpent' => $customer->salesOrders->sum('total'),
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', [
            'customer' => $customer->load('productDiscounts.store'),
            'statuses' => Customer::STATUSES,
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'selling_price']),
            'stores' => Store::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $before = $this->customerSnapshot($customer);

        $customer->update($request->validated());
        $this->syncProductDiscounts($customer, $request->validated('product_discounts', []));

        $this->activityLogService->log(
            $request->user()->id,
            'updated',
            'customers',
            "Updated customer {$customer->full_name}.",
            $customer,
            $before,
            $this->customerSnapshot($customer->fresh(['productDiscounts.product', 'productDiscounts.store']))
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, Customer $customer)
    {
        $name = $customer->full_name;
        try {
            $customer->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'This customer cannot be deleted because it is linked to existing sales orders.',
            ]);
        }

        $this->activityLogService->log($request->user()->id, 'deleted', 'customers', "Deleted customer {$name}.");

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    private function syncProductDiscounts(Customer $customer, array $productDiscounts): void
    {
        $customer->productDiscounts()->delete();

        if ($productDiscounts === []) {
            return;
        }

        $customer->productDiscounts()->createMany($productDiscounts);
    }

    private function customerSnapshot(Customer $customer): array
    {
        $customer->loadMissing(['productDiscounts.product', 'productDiscounts.store']);

        return [
            'full_name' => $customer->full_name,
            'business_name' => $customer->business_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'customer_type' => $customer->customer_type,
            'status' => $customer->status,
            'notes' => $customer->notes,
            'product_discounts' => $customer->productDiscounts
                ->map(fn ($discount) => [
                    'product' => $discount->product?->name ?? "Product #{$discount->product_id}",
                    'store' => $discount->store?->name ?? "Store #{$discount->store_id}",
                    'discount_amount' => (float) $discount->discount_amount,
                ])
                ->sortBy(fn ($discount) => $discount['product'].' '.$discount['store'])
                ->values()
                ->all(),
        ];
    }
}
