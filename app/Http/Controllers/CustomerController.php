<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
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
            'discountAmounts' => Customer::DISCOUNT_AMOUNTS,
        ]);
    }

    public function create()
    {
        return view('customers.create', [
            'customer' => new Customer(),
            'statuses' => Customer::STATUSES,
            'discountAmounts' => Customer::DISCOUNT_AMOUNTS,
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated() + ['created_by' => $request->user()->id]);

        $this->activityLogService->log($request->user()->id, 'created', 'customers', "Created customer {$customer->full_name}.", $customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'creator',
            'salesOrders.user',
            'activityLogs.user',
        ]);

        return view('customers.show', [
            'customer' => $customer,
            'totalOrders' => $customer->salesOrders->count(),
            'totalAmountSpent' => $customer->salesOrders->sum('total'),
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', [
            'customer' => $customer,
            'statuses' => Customer::STATUSES,
            'discountAmounts' => Customer::DISCOUNT_AMOUNTS,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        $this->activityLogService->log($request->user()->id, 'updated', 'customers', "Updated customer {$customer->full_name}.", $customer);

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
}
