<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_addresses', 'label')) {
                $table->string('label', 40)->default('home')->after('type')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('customer_addresses', 'label')) {
                $table->dropColumn('label');
            }
        });
    }
};
