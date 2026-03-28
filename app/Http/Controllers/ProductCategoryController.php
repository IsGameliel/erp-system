<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index()
    {
        if ($response = $this->unavailableResponse('products.index')) {
            return $response;
        }

        return view('product-categories.index', [
            'categories' => ProductCategory::query()
                ->withCount('products')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function create()
    {
        if ($response = $this->unavailableResponse('products.index')) {
            return $response;
        }

        return view('product-categories.create', [
            'category' => new ProductCategory(),
        ]);
    }

    public function store(StoreProductCategoryRequest $request)
    {
        if ($response = $this->unavailableResponse('products.index')) {
            return $response;
        }

        $category = ProductCategory::create($request->validated());

        $this->activityLogService->log($request->user()->id, 'created', 'product_categories', "Created product category {$category->name}.", $category);

        return redirect()->route('product-categories.index')->with('success', 'Product category created successfully.');
    }

    public function edit(ProductCategory $productCategory)
    {
        if ($response = $this->unavailableResponse('products.index')) {
            return $response;
        }

        return view('product-categories.edit', [
            'category' => $productCategory,
        ]);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        if ($response = $this->unavailableResponse('products.index')) {
            return $response;
        }

        $before = $this->productCategorySnapshot($productCategory);
        $productCategory->update($request->validated());

        $this->activityLogService->log(
            $request->user()->id,
            'updated',
            'product_categories',
            "Updated product category {$productCategory->name}.",
            $productCategory,
            $before,
            $this->productCategorySnapshot($productCategory->fresh())
        );

        return redirect()->route('product-categories.index')->with('success', 'Product category updated successfully.');
    }

    public function destroy(Request $request, ProductCategory $productCategory)
    {
        if ($response = $this->unavailableResponse('products.index')) {
            return $response;
        }

        $name = $productCategory->name;
        $productCategory->delete();

        $this->activityLogService->log($request->user()->id, 'deleted', 'product_categories', "Deleted product category {$name}.");

        return redirect()->route('product-categories.index')->with('success', 'Product category deleted successfully.');
    }

    private function unavailableResponse(string $route)
    {
        if (ProductCategory::schemaIsReady()) {
            return null;
        }

        return redirect()->route($route)->withErrors([
            'catalog' => 'Product categories are unavailable for this organization until the latest tenant database migration is applied.',
        ]);
    }

    private function productCategorySnapshot(ProductCategory $productCategory): array
    {
        return [
            'name' => $productCategory->name,
            'description' => $productCategory->description,
        ];
    }
}
