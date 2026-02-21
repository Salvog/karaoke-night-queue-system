<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteTable("state in ('idle','playing','break','paused')");

            return;
        }

        DB::statement('ALTER TABLE playback_states DROP CONSTRAINT IF EXISTS playback_states_state_check');
        DB::statement("ALTER TABLE playback_states ADD CONSTRAINT playback_states_state_check CHECK (state in ('idle','playing','break','paused'))");
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteTable("state in ('idle','playing','paused')");

            return;
        }

        DB::statement('ALTER TABLE playback_states DROP CONSTRAINT IF EXISTS playback_states_state_check');
        DB::statement("ALTER TABLE playback_states ADD CONSTRAINT playback_states_state_check CHECK (state in ('idle','playing','paused'))");
    }

    private function rebuildSqliteTable(string $stateCheck): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement("CREATE TABLE playback_states_tmp (
            id integer primary key autoincrement not null,
            event_night_id integer not null unique,
            current_request_id integer null,
            state varchar(20) not null,
            started_at datetime null,
            expected_end_at datetime null,
            created_at datetime null,
            updated_at datetime null,
            paused_at datetime null,
            FOREIGN KEY (event_night_id) REFERENCES event_nights(id) ON DELETE CASCADE,
            FOREIGN KEY (current_request_id) REFERENCES song_requests(id) ON DELETE SET NULL,
            CHECK ({$stateCheck})
        )");

        DB::statement('INSERT INTO playback_states_tmp (id, event_night_id, current_request_id, state, started_at, expected_end_at, created_at, updated_at, paused_at)
            SELECT id, event_night_id, current_request_id, state, started_at, expected_end_at, created_at, updated_at, paused_at
            FROM playback_states');

        DB::statement('DROP TABLE playback_states');
        DB::statement('ALTER TABLE playback_states_tmp RENAME TO playback_states');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
