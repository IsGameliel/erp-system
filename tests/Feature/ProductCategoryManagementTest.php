<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->post(route('product-categories.store'), [
                'name' => 'Electronics',
                'description' => 'Devices and accessories',
            ]);

        $response->assertRedirect(route('product-categories.index'));

        $this->assertDatabaseHas('product_categories', [
            'name' => 'Electronics',
        ], 'tenant');
    }

    public function test_admin_can_assign_product_to_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ProductCategory::create([
            'name' => 'Office Supplies',
            'description' => 'Everyday office items',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('products.store'), [
                'name' => 'Stapler',
                'sku' => 'SKU-STAPLER-001',
                'description' => 'Heavy duty stapler',
                'category_id' => $category->id,
                'selling_price' => 25.00,
                'purchase_price' => 18.00,
                'stock_quantity' => 30,
                'status' => Product::STATUS_ACTIVE,
            ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Stapler',
            'category_id' => $category->id,
        ], 'tenant');
    }

    public function test_admin_can_assign_product_quantity_to_stores(): void
    {
        $admin = User::factory()->admin()->create();
        $lagosStore = Store::create([
            'name' => 'Lagos Store',
            'code' => 'LAG',
            'location' => 'Lagos',
        ]);
        $abujaStore = Store::create([
            'name' => 'Abuja Store',
            'code' => 'ABJ',
            'location' => 'Abuja',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('products.store'), [
                'name' => 'Notebook',
                'sku' => 'SKU-NOTEBOOK-001',
                'description' => 'Ruled notebook',
                'selling_price' => 1500,
                'purchase_price' => 1200,
                'stock_quantity' => 40,
                'status' => Product::STATUS_ACTIVE,
                'store_quantities' => [
                    ['store_id' => $lagosStore->id, 'quantity' => 25],
                    ['store_id' => $abujaStore->id, 'quantity' => 15],
                ],
            ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::query()->where('sku', 'SKU-NOTEBOOK-001')->firstOrFail();

        $this->assertDatabaseHas('store_product_quantities', [
            'product_id' => $product->id,
            'store_id' => $lagosStore->id,
            'quantity' => 25,
        ], 'tenant');
        $this->assertDatabaseHas('store_product_quantities', [
            'product_id' => $product->id,
            'store_id' => $abujaStore->id,
            'quantity' => 15,
        ], 'tenant');
    }
}
