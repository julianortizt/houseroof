<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class FixProductImagePaths extends Command
{
    protected $signature = 'products:fix-image-paths';
    protected $description = 'Fix absolute image paths to relative paths for products';

    public function handle(): int
    {
        $products = Product::where('image_path', 'LIKE', 'C:%')->get();
        
        $this->info("Found {$products->count()} products with absolute paths");
        
        $fixed = 0;
        foreach ($products as $product) {
            $absPath = str_replace('\\', '/', $product->image_path);
            
            if (preg_match('#storage/app/public/(.+)#', $absPath, $matches)) {
                $newPath = $matches[1];
                $product->image_path = $newPath;
                $product->save();
                $this->line("  <info>FIXED</info> ID={$product->id} | {$product->slug} → {$newPath}");
                $fixed++;
            } else {
                $this->line("  <warning>SKIPPED</warning> ID={$product->id} | {$product->slug} | {$product->image_path}");
            }
        }
        
        $this->newLine();
        $this->info("Fixed {$fixed} product image paths");
        
        return Command::SUCCESS;
    }
}
