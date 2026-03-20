<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ActivityLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'statuses' => Product::STATUSES,
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'product' => new Product(),
            'statuses' => Product::STATUSES,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        $this->activityLogService->log($request->user()->id, 'created', 'products', "Created product {$product->name}.", $product);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['salesOrderItems.salesOrder', 'purchaseOrderItems.purchaseOrder']);

        return view('products.show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'statuses' => Product::STATUSES,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        $this->activityLogService->log($request->user()->id, 'updated', 'products', "Updated product {$product->name}.", $product);

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
}
