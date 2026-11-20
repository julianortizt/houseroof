<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products') || Schema::hasColumn('products', 'video_path')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'video_url')) {
                $table->string('video_path')->nullable()->after('video_url');
                return;
            }

            $table->string('video_path')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'video_path')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('video_path');
            });
        }
    }
};
