<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('badge')->nullable()->after('warranty');
            $table->string('metal')->nullable()->after('badge');
            $table->string('purity')->nullable()->after('metal');
            $table->string('stone')->nullable()->after('purity');
            $table->string('style')->nullable()->after('stone');
            $table->string('occasion')->nullable()->after('style');
            $table->string('certification')->nullable()->after('occasion');
            $table->string('dimensions_text')->nullable()->after('certification');
            $table->string('tax_note')->nullable()->after('dimensions_text');
            $table->json('highlights')->nullable()->after('tax_note');
            $table->unsignedInteger('sold_count')->default(0)->after('review_count');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'badge',
                'metal',
                'purity',
                'stone',
                'style',
                'occasion',
                'certification',
                'dimensions_text',
                'tax_note',
                'highlights',
                'sold_count',
            ]);
        });
    }
};
