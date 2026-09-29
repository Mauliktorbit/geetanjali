<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('coupons', 'existing_customers_only')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->boolean('existing_customers_only')->default(false)->after('new_customers_only');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'existing_customers_only')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('existing_customers_only');
            });
        }
    }
};
