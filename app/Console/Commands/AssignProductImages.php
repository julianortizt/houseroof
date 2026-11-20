<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignProductImages extends Command
{
    protected $signature = 'products:assign-images';
    protected $description = 'Assign existing storage images to products by filename matching';

    public function handle(): int
    {
        $products = DB::table('products')->select('id', 'slug', 'image_path')->get();
        $diskPath = storage_path('app/public');

        // Scan all images in storage
        $images = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($diskPath)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['webp', 'png', 'jpg', 'jpeg'])) {
                $relativePath = str_replace($diskPath . '/', '', $file->getPathname());
                $images[] = $relativePath;
            }
        }

        $this->info("Found " . count($images) . " images in storage");
        $this->info("Found " . count($products) . " products in database");
        $this->newLine();

        $assigned = 0;
        $alreadyAssigned = 0;
        $skipped = 0;

        foreach ($products as $product) {
            // Skip products that already have an image
            if ($product->image_path) {
                $alreadyAssigned++;
                continue;
            }

            $slug = strtolower($product->slug);
            $bestMatch = null;
            $bestScore = 0;

            foreach ($images as $img) {
                $filename = strtolower(pathinfo($img, PATHINFO_FILENAME));

                // Exact match
                if ($filename === $slug) {
                    $bestMatch = $img;
                    $bestScore = 100;
                    break;
                }

                // Filename starts with slug
                if (str_starts_with($filename, $slug)) {
                    $score = strlen($slug) / strlen($filename) * 90;
                    if ($score > $bestScore) {
                        $bestMatch = $img;
                        $bestScore = $score;
                    }
                }

                // Slug starts with filename
                if (str_starts_with($slug, $filename) && strlen($filename) > 5) {
                    $score = strlen($filename) / strlen($slug) * 80;
                    if ($score > $bestScore) {
                        $bestMatch = $img;
                        $bestScore = $score;
                    }
                }

                // Partial match (slug contains key words from filename)
                if (strlen($filename) > 5 && str_contains($slug, $filename)) {
                    $score = strlen($filename) / strlen($slug) * 70;
                    if ($score > $bestScore) {
                        $bestMatch = $img;
                        $bestScore = $score;
                    }
                }
            }

            if ($bestMatch && $bestScore >= 50) {
                // Prefer webp > png > jpg
                $dir = dirname($bestMatch);
                $base = pathinfo($bestMatch, PATHINFO_FILENAME);
                $webpPath = $dir . '/' . $base . '.webp';
                $pngPath = $dir . '/' . $base . '.png';

                if (file_exists(storage_path('app/public/' . $webpPath))) {
                    $bestMatch = $webpPath;
                } elseif (file_exists(storage_path('app/public/' . $pngPath))) {
                    $bestMatch = $pngPath;
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['image_path' => $bestMatch]);

                $this->info("  ✅ {$product->slug} ← {$bestMatch} (score: {$bestScore})");
                $assigned++;
            } else {
                $this->line("  ⚠️  {$product->slug} — no match found");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Results:");
        $this->info("  Assigned: {$assigned}");
        $this->info("  Already had image: {$alreadyAssigned}");
        $this->info("  Skipped (no match): {$skipped}");

        return self::SUCCESS;
    }
}
