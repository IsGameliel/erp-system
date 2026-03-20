<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $stores = Store::query()
            ->with(['salesOfficers', 'procurementOfficers'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->withCount('users')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('stores.index', [
            'stores' => $stores,
        ]);
    }

    public function create()
    {
        return view('stores.create', [
            'store' => new Store(),
            'salesOfficers' => User::query()->where('role', User::ROLE_SALES_OFFICER)->orderBy('name')->get(),
            'procurementOfficers' => User::query()->where('role', User::ROLE_PROCUREMENT_OFFICER)->orderBy('name')->get(),
            'selectedSalesOfficerIds' => [],
            'selectedProcurementOfficerIds' => [],
        ]);
    }

    public function store(StoreStoreRequest $request)
    {
        $store = DB::transaction(function () use ($request) {
            $store = Store::create($request->safe()->except(['sales_officer_id', 'procurement_officer_id']));

            $this->syncAssignments(
                $store,
                $request->integer('sales_officer_id') ?: null,
                $request->integer('procurement_officer_id') ?: null
            );

            return $store;
        });

        $this->activityLogService->log($request->user()->id, 'created', 'stores', "Created store {$store->name}.", $store);

        return redirect()->route('stores.index')->with('success', 'Store created successfully.');
    }

    public function edit(Store $store)
    {
        $store->load(['salesOfficers', 'procurementOfficers']);

        return view('stores.edit', [
            'store' => $store,
            'salesOfficers' => User::query()->where('role', User::ROLE_SALES_OFFICER)->orderBy('name')->get(),
            'procurementOfficers' => User::query()->where('role', User::ROLE_PROCUREMENT_OFFICER)->orderBy('name')->get(),
            'selectedSalesOfficerIds' => $store->salesOfficers->pluck('id')->all(),
            'selectedProcurementOfficerIds' => $store->procurementOfficers->pluck('id')->all(),
        ]);
    }

    public function update(UpdateStoreRequest $request, Store $store)
    {
        DB::transaction(function () use ($request, $store): void {
            $store->update($request->safe()->except(['sales_officer_id', 'procurement_officer_id']));

            $this->syncAssignments(
                $store,
                $request->integer('sales_officer_id') ?: null,
                $request->integer('procurement_officer_id') ?: null
            );
        });

        $this->activityLogService->log($request->user()->id, 'updated', 'stores', "Updated store {$store->name}.", $store);

        return redirect()->route('stores.index')->with('success', 'Store updated successfully.');
    }

    public function destroy(Request $request, Store $store)
    {
        $name = $store->name;

        try {
            DB::transaction(function () use ($store): void {
                $store->users()->update(['store_id' => null]);
                $store->delete();
            });
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'This store cannot be deleted because it is linked to existing records.',
            ]);
        }

        $this->activityLogService->log($request->user()->id, 'deleted', 'stores', "Deleted store {$name}.");

        return redirect()->route('stores.index')->with('success', 'Store deleted successfully.');
    }

    private function syncAssignments(Store $store, ?int $salesOfficerId, ?int $procurementOfficerId): void
    {
        User::query()
            ->where('store_id', $store->id)
            ->whereIn('role', [User::ROLE_SALES_OFFICER, User::ROLE_PROCUREMENT_OFFICER])
            ->whereNotIn('id', array_filter([$salesOfficerId, $procurementOfficerId]))
            ->update(['store_id' => null]);

        if ($salesOfficerId) {
            User::query()
                ->whereKey($salesOfficerId)
                ->where('role', User::ROLE_SALES_OFFICER)
                ->update(['store_id' => $store->id]);
        }

        if ($procurementOfficerId) {
            User::query()
                ->whereKey($procurementOfficerId)
                ->where('role', User::ROLE_PROCUREMENT_OFFICER)
                ->update(['store_id' => $store->id]);
        }
    }
}
