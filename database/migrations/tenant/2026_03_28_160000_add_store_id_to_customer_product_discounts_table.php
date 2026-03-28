<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('customer_product_discounts', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('product_id')->constrained('stores')->nullOnDelete();
            $table->dropUnique('customer_product_discounts_customer_id_product_id_unique');
            $table->unique(['customer_id', 'product_id', 'store_id'], 'customer_product_discounts_customer_product_store_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('customer_product_discounts', function (Blueprint $table) {
            $table->dropUnique('customer_product_discounts_customer_product_store_unique');
            $table->dropConstrainedForeignId('store_id');
            $table->unique(['customer_id', 'product_id']);
        });
    }
};
