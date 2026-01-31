<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('event_nights')->where('status', 'scheduled')->update(['status' => 'draft']);
        DB::table('event_nights')->where('status', 'live')->update(['status' => 'active']);
    }

    public function down(): void
    {
        DB::table('event_nights')->where('status', 'draft')->update(['status' => 'scheduled']);
        DB::table('event_nights')->where('status', 'active')->update(['status' => 'live']);
    }
};
