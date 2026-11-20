<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTranslatableData extends Command
{
    protected $signature = 'fix:translatable-data';
    protected $description = 'Convert plain-text translatable fields to JSON format for Spatie HasTranslations';

    public function handle(): int
    {
        $totalUpdated = 0;

        // --- Categories: name, description ---
        $this->info('Fixing categories...');
        $categories = DB::table('categories')->select('id', 'name', 'description')->get();
        foreach ($categories as $cat) {
            $updates = [];
            foreach (['name', 'description'] as $field) {
                $value = $cat->$field;
                if ($value !== null && $value !== '' && ! $this->isJson($value)) {
                    $updates[$field] = json_encode(['es' => $value], JSON_UNESCAPED_UNICODE);
                }
            }
            if (! empty($updates)) {
                DB::table('categories')->where('id', $cat->id)->update($updates);
                $totalUpdated++;
            }
        }
        $this->info("  Updated {$categories->count()} categories.");

        // --- Products: name, description, short_description ---
        $this->info('Fixing products...');
        $products = DB::table('products')->select('id', 'name', 'description', 'short_description')->get();
        $count = 0;
        foreach ($products as $product) {
            $updates = [];
            foreach (['name', 'description', 'short_description'] as $field) {
                $value = $product->$field;
                if ($value !== null && $value !== '' && ! $this->isJson($value)) {
                    $updates[$field] = json_encode(['es' => $value], JSON_UNESCAPED_UNICODE);
                }
            }
            if (! empty($updates)) {
                DB::table('products')->where('id', $product->id)->update($updates);
                $count++;
            }
        }
        $this->info("  Updated {$count} products.");

        // --- BlogPosts: title, content, excerpt (if any exist) ---
        $this->info('Fixing blog posts...');
        $posts = DB::table('blog_posts')->select('id', 'title', 'content', 'excerpt')->get();
        $count = 0;
        foreach ($posts as $post) {
            $updates = [];
            foreach (['title', 'content', 'excerpt'] as $field) {
                $value = $post->$field;
                if ($value !== null && $value !== '' && ! $this->isJson($value)) {
                    $updates[$field] = json_encode(['es' => $value], JSON_UNESCAPED_UNICODE);
                }
            }
            if (! empty($updates)) {
                DB::table('blog_posts')->where('id', $post->id)->update($updates);
                $count++;
            }
        }
        $this->info("  Updated {$count} blog posts.");

        // --- Projects: title, description (if any exist) ---
        $this->info('Fixing projects...');
        $projects = DB::table('projects')->select('id', 'title', 'description')->get();
        $count = 0;
        foreach ($projects as $project) {
            $updates = [];
            foreach (['title', 'description'] as $field) {
                $value = $project->$field;
                if ($value !== null && $value !== '' && ! $this->isJson($value)) {
                    $updates[$field] = json_encode(['es' => $value], JSON_UNESCAPED_UNICODE);
                }
            }
            if (! empty($updates)) {
                DB::table('projects')->where('id', $project->id)->update($updates);
                $count++;
            }
        }
        $this->info("  Updated {$count} projects.");

        $this->newLine();
        $this->info("Done! Total records updated: {$totalUpdated} (categories) + products/blog/projects.");

        return self::SUCCESS;
    }

    private function isJson(string $value): bool
    {
        $decoded = json_decode($value, true);
        return is_array($decoded) && json_last_error() === JSON_ERROR_NONE;
    }
}
