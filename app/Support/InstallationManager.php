<?php

namespace App\Support;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallationManager
{
    public function installed(): bool
    {
        return File::exists($this->statePath());
    }

    public function brandName(): string
    {
        return Arr::get($this->state(), 'brand_name', config('app.name', 'ERP System'));
    }

    public function state(): array
    {
        if (! $this->installed()) {
            return [];
        }

        $state = json_decode((string) File::get($this->statePath()), true);

        return is_array($state) ? $state : [];
    }

    public function install(array $data): void
    {
        $this->configureDatabaseConnection($data);

        DB::connection('mysql')->getPdo();

        Artisan::call('migrate', ['--force' => true]);

        User::query()->updateOrCreate(
            ['email' => $data['owner_email']],
            [
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'role' => User::ROLE_SUPER_ADMIN,
                'access_enabled' => true,
                'access_expires_at' => null,
                'email_verified_at' => now(),
                'password' => Hash::make($data['owner_password']),
            ],
        );

        SubscriptionPlan::query()->firstOrCreate(
            ['slug' => 'annual-access'],
            [
                'name' => 'Annual Access',
                'description' => 'Full application access for one year after payment approval.',
                'price' => 0,
                'duration_months' => 12,
                'is_active' => true,
            ],
        );

        $this->markInstalled($data['brand_name'], [
            'connection' => 'mysql',
            'host' => $data['db_host'],
            'port' => $data['db_port'],
            'database' => $data['db_database'],
            'username' => $data['db_username'],
            'password' => $data['db_password'],
        ]);
    }

    public function clearState(): void
    {
        File::delete($this->statePath());
    }

    public function markInstalled(string $brandName, array $database = []): void
    {
        File::ensureDirectoryExists(dirname($this->statePath()));
        File::put($this->statePath(), json_encode([
            'brand_name' => $brandName,
            'database' => $database,
            'installed_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function configureFromState(): void
    {
        $state = $this->state();

        if ($state === []) {
            return;
        }

        $database = Arr::get($state, 'database');

        Config::set('app.name', Arr::get($state, 'brand_name', config('app.name', 'ERP System')));

        if (! is_array($database) || $database === []) {
            return;
        }

        if (app()->runningUnitTests()) {
            return;
        }

        Config::set('database.default', Arr::get($database, 'connection', 'mysql'));
        Config::set('database.connections.mysql', array_merge(config('database.connections.mysql', []), [
            'host' => Arr::get($database, 'host'),
            'port' => Arr::get($database, 'port'),
            'database' => Arr::get($database, 'database'),
            'username' => Arr::get($database, 'username'),
            'password' => Arr::get($database, 'password'),
        ]));
    }

    private function statePath(): string
    {
        return storage_path('app/installation.json');
    }

    private function configureDatabaseConnection(array $data): void
    {
        Config::set('app.name', $data['brand_name']);
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql', array_merge(config('database.connections.mysql', []), [
            'host' => $data['db_host'],
            'port' => $data['db_port'],
            'database' => $data['db_database'],
            'username' => $data['db_username'],
            'password' => $data['db_password'],
        ]));

        DB::purge('mysql');
        DB::reconnect('mysql');
    }
}
