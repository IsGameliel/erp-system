<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('discount');
            $table->string('payment_status')->default('paid')->after('payment_method')->index();
            $table->date('due_date')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'due_date']);
        });
    }
};
