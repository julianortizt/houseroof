<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The ProductResource form saves `image_path` and `video_url` for products,
     * and `image_path` for product variants. These columns were missing from the
     * schema, which caused SQL errors ("Column not found 1054") when trying to
     * save a product from `/admin/products/create` or `/edit`.
     */
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'image_path')) {
                    $table->string('image_path')->nullable();
                }

                if (!Schema::hasColumn('products', 'video_url')) {
                    $table->string('video_url')->nullable();
                }
            });
        }

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'image_path')) {
                    $table->string('image_path')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'image_path')) {
                    $table->dropColumn('image_path');
                }

                if (Schema::hasColumn('products', 'video_url')) {
                    $table->dropColumn('video_url');
                }
            });
        }

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (Schema::hasColumn('product_variants', 'image_path')) {
                    $table->dropColumn('image_path');
                }
            });
        }
    }
};