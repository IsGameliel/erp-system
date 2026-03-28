<?php

namespace App\Support;

use App\Models\Organization;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantDatabaseManager
{
    public function hasConfiguration(?Organization $organization): bool
    {
        return $organization !== null
            && filled($organization->db_host)
            && filled($organization->db_database)
            && filled($organization->db_username);
    }

    public function configure(?Organization $organization): void
    {
        if (! $this->hasConfiguration($organization)) {
            return;
        }

        Config::set('database.connections.tenant', array_merge(
            config('database.connections.mysql', []),
            [
                'driver' => $organization->db_connection ?: 'mysql',
                'host' => $organization->db_host,
                'port' => $organization->db_port ?: 3306,
                'database' => $organization->db_database,
                'username' => $organization->db_username,
                'password' => $organization->db_password,
            ],
        ));

        DB::purge('tenant');
    }

    public function validateConnection(array $data): void
    {
        Config::set('database.connections.organization_onboarding_probe', array_merge(
            config('database.connections.mysql', []),
            [
                'driver' => $data['db_connection'],
                'host' => $data['db_host'],
                'port' => $data['db_port'],
                'database' => $data['db_database'],
                'username' => $data['db_username'],
                'password' => $data['db_password'],
            ],
        ));

        try {
            DB::purge('organization_onboarding_probe');
            DB::connection('organization_onboarding_probe')->getPdo();
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'db_host' => 'Could not connect to the organization database with the details provided.',
            ]);
        } finally {
            DB::purge('organization_onboarding_probe');
        }
    }

    public function migrate(array $data): void
    {
        Config::set('database.connections.tenant', array_merge(
            config('database.connections.mysql', []),
            [
                'driver' => $data['db_connection'],
                'host' => $data['db_host'],
                'port' => $data['db_port'],
                'database' => $data['db_database'],
                'username' => $data['db_username'],
                'password' => $data['db_password'],
            ],
        ));

        try {
            DB::purge('tenant');
            DB::reconnect('tenant');

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => $this->tenantMigrationsPath(),
                '--realpath' => true,
                '--force' => true,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'db_database' => 'The organization database was reachable, but the app could not prepare its tables there.',
            ]);
        }
    }

    private function tenantMigrationsPath(): string
    {
        return database_path('migrations/tenant');
    }
}
