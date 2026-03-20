<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $salesOfficerId = User::where('role', User::ROLE_SALES_OFFICER)->value('id')
            ?? User::where('role', User::ROLE_ADMIN)->value('id');

        $customers = Customer::limit(10)->get();
        $products = Product::where('status', Product::STATUS_ACTIVE)->limit(10)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        SalesOrder::factory(5)->create([
            'user_id' => $salesOfficerId,
        ])->each(function (SalesOrder $order) use ($customers, $products) {
            $subtotal = 0;

            $order->update([
                'customer_id' => $customers->random()->id,
            ]);

            foreach ($products->random(rand(1, min(3, $products->count()))) as $product) {
                $quantity = rand(1, 4);
                $lineTotal = $quantity * $product->selling_price;
                $subtotal += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'total_price' => $lineTotal,
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
