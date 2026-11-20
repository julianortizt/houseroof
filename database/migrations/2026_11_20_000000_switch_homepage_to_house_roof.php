<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')
            ->where('key', 'homepage_template')
            ->update(['value' => 'roof']);

        DB::table('settings')->updateOrInsert(
            ['key' => 'homepage_template'],
            ['value' => 'roof', 'type' => 'text', 'group' => 'platform']
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'site_name'],
            ['value' => 'House Roof', 'type' => 'text', 'group' => 'general']
        );
    }

    public function down(): void
    {
        // Intentionally irreversible: the old resin landing page is retired.
    }
};
