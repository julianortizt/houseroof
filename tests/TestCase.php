<?php

namespace Tests;

use App\Models\ContentBlock;
use App\Models\Setting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // These models keep a per-request in-memory snapshot of their tables.
        ContentBlock::flushCache();
        Setting::flushCache();
    }
}
