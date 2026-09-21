<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'care_instructions')) {
                $table->text('care_instructions')->nullable()->after('highlights');
            }
            if (! Schema::hasColumn('products', 'shipping_information')) {
                $table->text('shipping_information')->nullable()->after('estimated_delivery');
            }
            if (! Schema::hasColumn('products', 'return_policy')) {
                $table->text('return_policy')->nullable()->after('shipping_information');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('products', 'care_instructions') ? 'care_instructions' : null,
                Schema::hasColumn('products', 'shipping_information') ? 'shipping_information' : null,
                Schema::hasColumn('products', 'return_policy') ? 'return_policy' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
