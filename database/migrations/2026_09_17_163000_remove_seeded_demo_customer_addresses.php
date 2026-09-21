<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('customer_addresses')
            ->where(function ($query) {
                $query->where(function ($home) {
                    $home->where('address_line1', '123, Green Park')
                        ->where('city', 'New Delhi')
                        ->where('pincode', '110016');
                })->orWhere(function ($office) {
                    $office->where('address_line1', 'C - 1209/1210, PNTC Tower, Times of India Press Road')
                        ->where('city', 'Ahmedabad')
                        ->where('pincode', '380015')
                        ->where('label', 'office');
                });
            })
            ->delete();
    }

    public function down(): void
    {
        // Demo addresses should not be restored.
    }
};
