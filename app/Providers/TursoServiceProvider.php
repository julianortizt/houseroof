<?php

namespace App\Providers;

use App\Database\LibSQLHttpConnection;
use App\Database\LibSQLHttpDatabase;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the libSQL HTTP connection only when Turso is the configured
 * database. The upstream package replaces Laravel's global db.factory for
 * every driver, so discovery is disabled and this provider is used instead.
 *
 * Using HTTP avoids the native FFI client, which segfaults under FrankenPHP.
 */
class TursoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('db', function (DatabaseManager $db): void {
            $db->extend('libsql', function (array $config, string $name): LibSQLHttpConnection {
                $config = array_merge(config('database.connections.libsql', []), $config);
                $config['name'] = $name;

                $database = new LibSQLHttpDatabase($config);

                return new LibSQLHttpConnection(
                    $database,
                    (string) ($config['database'] ?? ':memory:'),
                    (string) ($config['prefix'] ?? ''),
                    $config,
                );
            });
        });
    }
}
