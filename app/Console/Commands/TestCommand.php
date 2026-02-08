<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    protected $signature = 'test
        {--filter= : Filter tests by name or pattern}
        {--testsuite= : Run a specific test suite}
        {--stop-on-failure : Stop on first failure}';

    protected $description = 'Run the test suite via PHPUnit';

    public function handle(): int
    {
        $phpUnitBinary = base_path('vendor/bin/phpunit');
        $phpUnitConfiguration = base_path('phpunit.xml');

        if (! file_exists($phpUnitBinary)) {
            $this->error('PHPUnit is not installed. Run: composer install');

            return self::FAILURE;
        }

        $arguments = [PHP_BINARY, $phpUnitBinary];

        if (file_exists($phpUnitConfiguration)) {
            $arguments[] = '--configuration';
            $arguments[] = $phpUnitConfiguration;
        }

        if ($filter = $this->option('filter')) {
            $arguments[] = '--filter';
            $arguments[] = $filter;
        }

        if ($suite = $this->option('testsuite')) {
            $arguments[] = '--testsuite';
            $arguments[] = $suite;
        }

        if ($this->option('stop-on-failure')) {
            $arguments[] = '--stop-on-failure';
        }

        $processEnvironment = array_merge($_ENV, [
            'APP_ENV' => 'testing',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
        ]);

        $process = new Process($arguments, base_path(), $processEnvironment);
        $process->setTty(Process::isTtySupported());
        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        return $process->getExitCode() ?? self::FAILURE;
    }
}
