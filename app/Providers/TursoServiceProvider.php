<?php

namespace App\Providers;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Libsql\Laravel\LibsqlConnection;
use Libsql\PDO as LibsqlPDO;

/**
 * Registers the libSQL connection without replacing Laravel's global PDO
 * factory (the upstream provider does that unconditionally, which breaks
 * every other driver). Only loaded when DB_CONNECTION=libsql.
 */
class TursoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('db', function (DatabaseManager $db): void {
            $db->extend('libsql', function (array $config, string $name): LibsqlConnection {
                $config = array_merge(config('database.connections.libsql', []), $config);
                $config['name'] = $name;

                return new LibsqlConnection(
                    static function () use ($config): LibsqlPDO {
                        return new LibsqlPDO(
                            $config['database'] ?? null,
                            password: $config['password'] ?? null,
                            options: $config,
                        );
                    },
                    database: $config['database'] ?? '',
                    config: $config,
                );
            });
        });
    }
}
