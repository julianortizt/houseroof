<?php

namespace App\Database;

use Turso\Http\Laravel\Database\LibSQLDatabase;

/**
 * The libSQL HTTP API is stateless between requests: a BEGIN sent in one HTTP
 * call is not visible to the COMMIT sent in the next one, so transactions
 * always fail with "cannot commit - no transaction is active" (this breaks
 * Filament logins, which wrap page actions in DB::beginTransaction()).
 *
 * Laravel's Connection still tracks the transaction nesting level and fires
 * its events, so keeping the PDO-level transaction methods as no-ops preserves
 * the expected control flow without emitting statements the API cannot honour.
 */
class LibSQLHttpDatabase extends LibSQLDatabase
{
    public function beginTransaction(): bool
    {
        $this->inTransaction = true;

        return true;
    }

    public function commit(): bool
    {
        $this->inTransaction = false;

        return true;
    }

    public function rollBack(): bool
    {
        $this->inTransaction = false;

        return true;
    }
}
