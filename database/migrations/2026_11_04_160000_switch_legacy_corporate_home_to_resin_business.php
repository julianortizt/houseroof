<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'homepage_template')
            ->where('value', 'corporate')
            ->update([
                'value' => 'resin_business',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'homepage_template')
            ->where('value', 'resin_business')
            ->update([
                'value' => 'corporate',
                'updated_at' => now(),
            ]);
    }
};
