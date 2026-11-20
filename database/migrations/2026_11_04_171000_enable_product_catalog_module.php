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

        $setting = DB::table('settings')->where('key', 'module_products_enabled');

        if ($setting->exists()) {
            $setting->update([
                'value' => '1',
                'type' => 'boolean',
                'group' => 'modules',
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('settings')->insert([
            'key' => 'module_products_enabled',
            'value' => '1',
            'type' => 'boolean',
            'group' => 'modules',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where('key', 'module_products_enabled')
                ->update([
                    'value' => '0',
                    'type' => 'boolean',
                    'group' => 'modules',
                    'updated_at' => now(),
                ]);
        }
    }
};
