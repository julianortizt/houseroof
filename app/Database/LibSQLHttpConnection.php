<?php

namespace App\Database;

use Turso\Http\Laravel\Database\LibSQLConnection;

/**
 * Fixes upstream libSQL HTTP connection issues under Laravel 11.
 *
 * - getServerVersion() in the package is declared as string but the HTTP
 *   client returns an array, which breaks queries using groupLimit.
 * - select() in the package returns the raw HTTP envelope instead of rows,
 *   which breaks database-backed cache/session stores.
 */
class LibSQLHttpConnection extends LibSQLConnection
{
    public function getServerVersion(): string
    {
        return '3.45.0';
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return [];
            }

            $result = $this->getRawPdo()->prepare($query)->query($bindings);

            return $this->normaliseResult($result);
        });
    }

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        if ($this->pretending()) {
            return [];
        }

        foreach ($this->select($query, $bindings, $useReadPdo) as $row) {
            yield $row;
        }
    }

    protected function getDefaultPostProcessor(): \Turso\Http\Laravel\Database\LibSQLQueryProcessor
    {
        return new LibSQLHttpQueryProcessor();
    }

    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }

    protected function normaliseResult(mixed $result): array
    {
        if (!is_array($result) || !isset($result['cols'], $result['rows'])) {
            return [];
        }

        $columns = array_column($result['cols'], 'name');

        return array_map(static function (array $row) use ($columns): array {
            $values = array_map(static function (array $cell): mixed {
                return ($cell['type'] ?? null) === 'null' ? null : ($cell['value'] ?? null);
            }, $row);

            return array_combine($columns, $values);
        }, $result['rows']);
    }
}
