<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use App\Services\ActivityLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $vendors = Vendor::query()
            ->withCount('purchaseOrders')
            ->withSum('purchaseOrders as total_procurement_value', 'total')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('vendors.index', [
            'vendors' => $vendors,
            'statuses' => Vendor::STATUSES,
            'categories' => Vendor::query()->whereNotNull('category')->distinct()->pluck('category')->filter()->values(),
        ]);
    }

    public function create()
    {
        return view('vendors.create', [
            'vendor' => new Vendor(),
            'statuses' => Vendor::STATUSES,
        ]);
    }

    public function store(StoreVendorRequest $request)
    {
        $vendor = Vendor::create($request->validated() + ['created_by' => $request->user()->id]);

        $this->activityLogService->log($request->user()->id, 'created', 'vendors', "Created vendor {$vendor->company_name}.", $vendor);

        return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load([
            'creator',
            'purchaseOrders.user',
            'activityLogs.user',
        ]);

        return view('vendors.show', [
            'vendor' => $vendor,
            'totalPurchaseOrders' => $vendor->purchaseOrders->count(),
            'totalProcurementValue' => $vendor->purchaseOrders->sum('total'),
        ]);
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', [
            'vendor' => $vendor,
            'statuses' => Vendor::STATUSES,
        ]);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        $this->activityLogService->log($request->user()->id, 'updated', 'vendors', "Updated vendor {$vendor->company_name}.", $vendor);

        return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Request $request, Vendor $vendor)
    {
        $name = $vendor->company_name;
        try {
            $vendor->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'This vendor cannot be deleted because it is linked to existing purchase orders.',
            ]);
        }

        $this->activityLogService->log($request->user()->id, 'deleted', 'vendors', "Deleted vendor {$name}.");

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
