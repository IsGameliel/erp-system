<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $procurementOfficerId = User::where('role', User::ROLE_PROCUREMENT_OFFICER)->value('id')
            ?? User::where('role', User::ROLE_ADMIN)->value('id');

        $vendors = Vendor::limit(10)->get();
        $products = Product::where('status', Product::STATUS_ACTIVE)->limit(10)->get();

        if ($vendors->isEmpty() || $products->isEmpty()) {
            return;
        }

        PurchaseOrder::factory(5)->create([
            'user_id' => $procurementOfficerId,
        ])->each(function (PurchaseOrder $order) use ($vendors, $products) {
            $subtotal = 0;

            $order->update([
                'vendor_id' => $vendors->random()->id,
            ]);

            foreach ($products->random(rand(1, min(3, $products->count()))) as $product) {
                $quantity = rand(1, 6);
                $lineTotal = $quantity * $product->purchase_price;
                $subtotal += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $product->purchase_price,
                    'total_cost' => $lineTotal,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'tax' => 0,
                'discount' => 0,
                'total' => $subtotal,
            ]);
        });
    }
}
