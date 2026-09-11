<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->index();
            $table->string('theme')->default('dark');
            $table->string('label')->nullable();
            $table->string('discount_display');
            $table->string('discount_suffix')->default('Off');
            $table->string('title');
            $table->string('promo_code')->nullable()->index();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();
        $rows = [
            ['diamond', 'dark', 'Flat', '10%', 'Off', "On Diamond Jewellery", 'GEET10', 'public/assets/images/offers/diamond.jpg', 'Diamond jewellery offer', '2026-12-31', 1],
            ['gold', 'light', 'Up To', '15%', 'Off', "On Making Charges\nof Gold Jewellery", 'GOLD15', 'public/assets/images/offers/gold.jpg', 'Gold jewellery making charges offer', '2026-12-31', 2],
            ['festival', 'dark', 'Flat', '5%', 'Off', "On Kundan\nCollections", 'KUNDAN5', 'public/assets/images/offers/kundan.jpg', 'Kundan jewellery offer', '2026-12-31', 3],
            ['seasonal', 'light', 'Extra', '10%', 'Off', "On Prepaid\nOrders", 'PREPAID10', 'public/assets/images/offers/prepaid.jpg', 'Prepaid order gift offer', '2026-12-31', 4],
            ['bank', 'dark', 'Extra', '5%', 'Off', "With Select\nBank Cards", 'BANK5', 'public/assets/images/categories/rings.jpg', 'Bank offer on jewellery', '2026-12-31', 5],
            ['gold', 'light', 'Flat', '8%', 'Off', "On 22K Gold\nJewellery", 'GOLD8', 'public/assets/images/products/traditional-gold-necklace.jpg', '22K gold jewellery offer', '2027-01-31', 6],
            ['diamond', 'dark', 'Up To', '12%', 'Off', "On Diamond\nSolitaires", 'SOLITAIRE12', 'public/assets/images/categories/rings.jpg', 'Diamond solitaire offer', '2027-01-15', 7],
            ['festival', 'light', 'Special', '20%', 'Off', "Festival Making\nCharges Offer", 'FEST20', 'public/assets/images/occasions/festival.jpg', 'Festival jewellery offer', '2026-12-31', 8],
        ];

        foreach ($rows as $row) {
            DB::table('offers')->insert([
                'category' => $row[0],
                'theme' => $row[1],
                'label' => $row[2],
                'discount_display' => $row[3],
                'discount_suffix' => $row[4],
                'title' => $row[5],
                'promo_code' => $row[6],
                'image' => $row[7],
                'image_alt' => $row[8],
                'starts_at' => Carbon::parse('2026-01-01')->startOfDay(),
                'ends_at' => Carbon::parse($row[9])->endOfDay(),
                'sort_order' => $row[10],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
