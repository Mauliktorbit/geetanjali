<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('coupons', 'included_collections')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->json('included_collections')->nullable()->after('included_categories');
            });
        }

        if (! Schema::hasColumn('coupons', 'exclude_sale_items')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->boolean('exclude_sale_items')->default(false)->after('included_collections');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'exclude_sale_items')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('exclude_sale_items');
            });
        }

        if (Schema::hasColumn('coupons', 'included_collections')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('included_collections');
            });
        }
    }
};
