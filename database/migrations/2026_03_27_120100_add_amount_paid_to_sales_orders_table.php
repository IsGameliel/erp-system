<?php

use App\Models\SalesOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('total');
        });

        DB::table('sales_orders')
            ->where('payment_status', SalesOrder::PAYMENT_STATUS_PAID)
            ->update(['amount_paid' => DB::raw('total')]);
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};
