<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->json('payload')->nullable()->after('quantity');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->json('payload')->nullable()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('payload');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn('payload');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
