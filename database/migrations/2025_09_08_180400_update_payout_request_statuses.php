<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payout_requests')) {
            return;
        }

        DB::table('payout_requests')
            ->where('status', 'pending')
            ->update(['status' => 'requested']);

        DB::table('payout_requests')
            ->where('status', 'approved')
            ->update(['status' => 'paid']);

        DB::table('payout_requests')
            ->where('status', 'paid')
            ->whereNull('processed_at')
            ->update(['processed_at' => now()]);

        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE payout_requests MODIFY status VARCHAR(255) NOT NULL DEFAULT 'requested'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('payout_requests')) {
            return;
        }

        DB::table('payout_requests')
            ->where('status', 'requested')
            ->update(['status' => 'pending']);

        DB::table('payout_requests')
            ->where('status', 'paid')
            ->update(['status' => 'approved']);

        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE payout_requests MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
        }
    }
};
