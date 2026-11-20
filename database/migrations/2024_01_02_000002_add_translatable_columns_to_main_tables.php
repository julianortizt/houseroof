<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories
        Schema::table('categories', function (Blueprint $table) {
            $table->text('name')->nullable()->after('sort_order');
            $table->text('description')->nullable()->after('name');
        });

        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->text('name')->nullable()->after('sort_order');
            $table->text('short_description')->nullable()->after('name');
            $table->longText('description')->nullable()->after('short_description');
        });

        // Product Variants
        Schema::table('product_variants', function (Blueprint $table) {
            $table->text('name')->nullable()->after('sort_order');
            $table->text('description')->nullable()->after('name');
        });

        // Projects
        Schema::table('projects', function (Blueprint $table) {
            $table->text('title')->nullable()->after('sort_order');
            $table->text('description')->nullable()->after('title');
            $table->text('location')->nullable()->after('description');
        });

        // Blog Posts
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->text('title')->nullable()->after('is_featured');
            $table->text('excerpt')->nullable()->after('title');
            $table->longText('content')->nullable()->after('excerpt');
        });

        // Tags
        Schema::table('tags', function (Blueprint $table) {
            $table->text('name')->nullable()->after('slug');
        });

        // Colors
        Schema::table('colors', function (Blueprint $table) {
            $table->text('name')->nullable()->after('sort_order');
            $table->text('description')->nullable()->after('name');
        });

        // Pages
        Schema::table('pages', function (Blueprint $table) {
            $table->text('title')->nullable()->after('is_active');
            $table->longText('content')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['title', 'content']);
        });

        Schema::table('colors', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['title', 'excerpt', 'content']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'location']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name', 'short_description', 'description']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });
    }
};
