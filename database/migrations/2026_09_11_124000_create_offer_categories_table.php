<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        foreach ([
            ['Bank Offers', 'bank', 1],
            ['Gold Offers', 'gold', 2],
            ['Diamond Offers', 'diamond', 3],
            ['Festival Offers', 'festival', 4],
            ['Seasonal Offers', 'seasonal', 5],
        ] as [$name, $slug, $order]) {
            DB::table('offer_categories')->insert([
                'name' => $name,
                'slug' => $slug,
                'sort_order' => $order,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_categories');
    }
};
