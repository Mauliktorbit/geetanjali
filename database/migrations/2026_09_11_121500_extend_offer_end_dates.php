<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dates = [
            'GEET10' => '2026-12-31',
            'GOLD15' => '2026-12-31',
            'KUNDAN5' => '2026-12-31',
            'PREPAID10' => '2026-12-31',
            'BANK5' => '2026-12-31',
            'GOLD8' => '2027-01-31',
            'SOLITAIRE12' => '2027-01-15',
            'FEST20' => '2026-12-31',
        ];

        foreach ($dates as $code => $date) {
            DB::table('offers')->where('promo_code', $code)->update([
                'ends_at' => Carbon::parse($date)->endOfDay(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Dates were extended so current offers stay visible; no rollback needed.
    }
};
