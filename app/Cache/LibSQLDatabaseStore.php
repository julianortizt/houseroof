<?php

namespace App\Cache;

use Illuminate\Cache\DatabaseStore;

/**
 * Database cache store that is safe with the HTTP libSQL driver.
 *
 * Laravel only base64-encodes serialized cache values for SQLite connections.
 * The HTTP driver is not an SQLiteConnection, so strings containing NUL bytes
 * were truncated by the JSON API. Encoding every value with NUL bytes keeps
 * cache entries intact and still reads entries written by the old FFI driver.
 */
class LibSQLDatabaseStore extends DatabaseStore
{
    protected function serialize($value)
    {
        $result = serialize($value);

        if (str_contains($result, "\0")) {
            return base64_encode($result);
        }

        return $result;
    }

    protected function unserialize($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $decoded = base64_decode((string) $value, true);

        if ($decoded !== false && $decoded !== '') {
            $result = @unserialize($decoded);

            if ($result !== false || $decoded === serialize(false)) {
                return $result;
            }
        }

        return @unserialize((string) $value);
    }
}
