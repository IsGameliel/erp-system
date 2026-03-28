<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $productCategoriesAvailable = ProductCategory::schemaIsReady();

        $products = Product::query()
            ->when($productCategoriesAvailable, fn ($query) => $query->with('category'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($productCategoriesAvailable && $request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'statuses' => Product::STATUSES,
            'categories' => $productCategoriesAvailable ? ProductCategory::query()->orderBy('name')->get() : collect(),
            'productCategoriesAvailable' => $productCategoriesAvailable,
        ]);
    }

    public function create()
    {
        $productCategoriesAvailable = ProductCategory::schemaIsReady();

        return view('products.create', [
            'product' => new Product(),
            'statuses' => Product::STATUSES,
            'categories' => $productCategoriesAvailable ? ProductCategory::query()->orderBy('name')->get() : collect(),
            'productCategoriesAvailable' => $productCategoriesAvailable,
            'stores' => Store::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $product = Product::create($validated);
            $this->syncStoreQuantities($product, $validated['store_quantities'] ?? []);

            return $product;
        });

        $this->activityLogService->log($request->user()->id, 'created', 'products', "Created product {$product->name}.", $product);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $relations = ['salesOrderItems.salesOrder', 'purchaseOrderItems.purchaseOrder'];

        if (ProductCategory::schemaIsReady()) {
            array_unshift($relations, 'category');
        }

        $product->load($relations);

        return view('products.show', [
            'product' => $product->load('storeQuantities.store'),
        ]);
    }

    public function edit(Product $product)
    {
        $productCategoriesAvailable = ProductCategory::schemaIsReady();

        return view('products.edit', [
            'product' => $product->load('storeQuantities'),
            'statuses' => Product::STATUSES,
            'categories' => $productCategoriesAvailable ? ProductCategory::query()->orderBy('name')->get() : collect(),
            'productCategoriesAvailable' => $productCategoriesAvailable,
            'stores' => Store::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $before = $this->productSnapshot($product);

        DB::transaction(function () use ($request, $product) {
            $validated = $request->validated();
            $product->update($validated);
            $this->syncStoreQuantities($product, $validated['store_quantities'] ?? []);
        });

        $this->activityLogService->log(
            $request->user()->id,
            'updated',
            'products',
            "Updated product {$product->name}.",
            $product,
            $before,
            $this->productSnapshot($product->fresh(['category', 'storeQuantities.store']))
        );

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product)
    {
        $name = $product->name;
        try {
            $product->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'This product cannot be deleted because it is already used by an order.',
            ]);
        }

        $this->activityLogService->log($request->user()->id, 'deleted', 'products', "Deleted product {$name}.");

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    private function syncStoreQuantities(Product $product, array $storeQuantities): void
    {
        $payload = collect($storeQuantities)
            ->mapWithKeys(fn ($entry) => [
                (int) $entry['store_id'] => ['quantity' => (int) ($entry['quantity'] ?? 0)],
            ])
            ->all();

        $product->storeQuantities()->delete();

        if ($payload === []) {
            return;
        }

        $product->storeQuantities()->createMany(
            collect($payload)
                ->filter(fn ($entry) => $entry['quantity'] > 0)
                ->map(fn ($entry, $storeId) => [
                    'store_id' => $storeId,
                    'quantity' => $entry['quantity'],
                ])
                ->values()
                ->all()
        );
    }

    private function productSnapshot(Product $product): array
    {
        $product->loadMissing(['category', 'storeQuantities.store']);

        return [
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => $product->description,
            'category' => $product->category?->name,
            'selling_price' => (float) $product->selling_price,
            'purchase_price' => (float) $product->purchase_price,
            'stock_quantity' => (int) $product->stock_quantity,
            'status' => $product->status,
            'store_allocations' => $product->storeQuantities
                ->map(fn ($allocation) => [
                    'store' => $allocation->store?->name ?? "Store #{$allocation->store_id}",
                    'quantity' => (int) $allocation->quantity,
                ])
                ->sortBy('store')
                ->values()
                ->all(),
        ];
    }
}
