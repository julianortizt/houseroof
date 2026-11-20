<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('content_blocks')) {
            return;
        }

        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('page', 50);        // 'home', 'about', 'services', 'contact', etc.
            $table->string('section', 100);    // 'hero', 'trust_bar', 'value_prop', 'services', etc.
            $table->string('key', 100);        // 'title', 'subtitle', 'image', 'video', 'content', etc.
            $table->longText('value')->nullable();
            $table->string('type', 20)->default('text'); // 'text', 'richtext', 'image', 'video', 'json'
            $table->string('locale', 5)->default('es'); // 'es', 'en'
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['page', 'section', 'key', 'locale']);
            $table->index(['page', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
