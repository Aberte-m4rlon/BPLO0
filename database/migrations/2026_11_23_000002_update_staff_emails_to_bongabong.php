<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STAFF_PREFIXES = ['admin', 'bplo', 'treasurer', 'inspector'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::STAFF_PREFIXES as $prefix) {
            $old = $prefix.'@bansud.gov.ph';
            $new = $prefix.'@bongabong.gov.ph';

            if (DB::table('users')->where('email', $old)->exists()
                && ! DB::table('users')->where('email', $new)->exists()) {
                DB::table('users')->where('email', $old)->update([
                    'email' => $new,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::STAFF_PREFIXES as $prefix) {
            $old = $prefix.'@bansud.gov.ph';
            $new = $prefix.'@bongabong.gov.ph';

            if (DB::table('users')->where('email', $new)->exists()
                && ! DB::table('users')->where('email', $old)->exists()) {
                DB::table('users')->where('email', $new)->update([
                    'email' => $old,
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
