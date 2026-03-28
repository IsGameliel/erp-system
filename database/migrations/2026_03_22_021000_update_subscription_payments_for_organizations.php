<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscription_payments')) {
            return;
        }

        Schema::table('subscription_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_payments', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('subscription_payments', 'submitted_by')) {
                $table->foreignId('submitted_by')->nullable()->after('organization_id')->constrained('users')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('subscription_payments', 'user_id')) {
            DB::table('subscription_payments')
                ->leftJoin('users', 'subscription_payments.user_id', '=', 'users.id')
                ->whereNull('subscription_payments.organization_id')
                ->update([
                    'subscription_payments.organization_id' => DB::raw('users.organization_id'),
                    'subscription_payments.submitted_by' => DB::raw('subscription_payments.user_id'),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('subscription_payments')) {
            return;
        }

        Schema::table('subscription_payments', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_payments', 'submitted_by')) {
                $table->dropConstrainedForeignId('submitted_by');
            }

            if (Schema::hasColumn('subscription_payments', 'organization_id')) {
                $table->dropConstrainedForeignId('organization_id');
            }
        });
    }
};
