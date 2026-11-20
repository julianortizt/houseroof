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
                        // libSQL expects the remote URL in options['url']; passing
                        // it as the DSN makes the native client treat it as a
                        // local file path. Keep a plain path only for local files.
                        $dsn = $config['database'] ?? null;
                        if (is_string($dsn) && preg_match('#^(libsql|https?)://#i', $dsn)) {
                            $dsn = null;
                        }

                        return new LibsqlPDO(
                            $dsn,
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
