<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private const STATUS_CHECK_CONSTRAINT = 'event_nights_status_check';

    public function up(): void
    {
        $this->dropStatusCheckConstraint();
        DB::table('event_nights')->where('status', 'scheduled')->update(['status' => 'draft']);
        DB::table('event_nights')->where('status', 'live')->update(['status' => 'active']);
        $this->addStatusCheckConstraint("status in ('draft','active','closed')");
    }

    public function down(): void
    {
        $this->dropStatusCheckConstraint();
        DB::table('event_nights')->where('status', 'draft')->update(['status' => 'scheduled']);
        DB::table('event_nights')->where('status', 'active')->update(['status' => 'live']);
        $this->addStatusCheckConstraint("status in ('scheduled','live','closed')");
    }

    private function dropStatusCheckConstraint(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if (! $this->statusCheckConstraintExists()) {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement(sprintf(
                'ALTER TABLE event_nights DROP CONSTRAINT IF EXISTS %s',
                self::STATUS_CHECK_CONSTRAINT
            ));

            return;
        }

        if ($driver === 'mysql') {
            DB::statement(sprintf(
                'ALTER TABLE event_nights DROP CHECK %s',
                self::STATUS_CHECK_CONSTRAINT
            ));
        }
    }

    private function addStatusCheckConstraint(string $checkExpression): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE event_nights ADD CONSTRAINT %s CHECK (%s)',
            self::STATUS_CHECK_CONSTRAINT,
            $checkExpression
        ));
    }

    private function statusCheckConstraintExists(): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $result = DB::selectOne(
                'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
                ['event_nights', self::STATUS_CHECK_CONSTRAINT]
            );

            return $result !== null;
        }

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT conname FROM pg_constraint WHERE conname = ?',
                [self::STATUS_CHECK_CONSTRAINT]
            );

            return $result !== null;
        }

        return false;
    }
};
