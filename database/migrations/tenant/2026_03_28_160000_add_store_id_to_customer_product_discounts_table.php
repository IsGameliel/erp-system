<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('tenant')->hasColumn('customer_product_discounts', 'store_id')) {
            Schema::connection('tenant')->table('customer_product_discounts', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('product_id')->constrained('stores')->nullOnDelete();
            });
        }

        Schema::connection('tenant')->table('customer_product_discounts', function (Blueprint $table) {
            $table->index('customer_id', 'customer_product_discounts_customer_id_idx');
        });

        DB::connection('tenant')->statement(
            'ALTER TABLE customer_product_discounts DROP INDEX customer_product_discounts_customer_id_product_id_unique'
        );

        Schema::connection('tenant')->table('customer_product_discounts', function (Blueprint $table) {
            $table->unique(['customer_id', 'product_id', 'store_id'], 'customer_product_discounts_customer_product_store_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('customer_product_discounts', function (Blueprint $table) {
            $table->dropUnique('customer_product_discounts_customer_product_store_unique');
            $table->dropIndex('customer_product_discounts_customer_id_idx');
            $table->dropConstrainedForeignId('store_id');
            $table->unique(['customer_id', 'product_id']);
        });
    }
};
