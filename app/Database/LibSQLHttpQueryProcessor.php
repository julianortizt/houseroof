<?php

namespace App\Database;

use Turso\Http\Laravel\Database\LibSQLQueryProcessor;

/**
 * The connection already converts libSQL HTTP results into associative rows,
 * so Laravel's processor must not try to reinterpret them a second time.
 */
class LibSQLHttpQueryProcessor extends LibSQLQueryProcessor
{
    public function processSelect($query, $results)
    {
        return $results;
    }
}
