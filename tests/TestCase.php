<?php

namespace Tests;

use App\Support\InstallationManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $testingDatabasePath = database_path('testing.sqlite');

        File::ensureDirectoryExists(dirname($testingDatabasePath));

        if (! File::exists($testingDatabasePath)) {
            File::put($testingDatabasePath, '');
        }

        Config::set('database.connections.tenant', array_merge(
            config('database.connections.sqlite', []),
            ['database' => $testingDatabasePath],
        ));

        DB::purge('tenant');
        DB::connection('tenant')->setPdo(DB::connection()->getPdo());
        DB::connection('tenant')->setReadPdo(DB::connection()->getReadPdo());

        app(InstallationManager::class)->markInstalled('ERP System');
    }

    protected function tearDown(): void
    {
        DB::disconnect('tenant');

        app(InstallationManager::class)->clearState();

        parent::tearDown();
    }
}
