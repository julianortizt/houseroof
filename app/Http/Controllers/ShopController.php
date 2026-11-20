<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    /**
     * Get active root categories with product counts (cached 10 min).
     * Used in sidebar on every shop page.
     */
    private function getCachedCategories()
    {
        return Cache::remember('shop:categories', 600, fn () =>
            Category::active()->roots()->withCount('products')->get()
        );
    }
    public function index()
    {
        $query = Product::active()->with(['category', 'media']);

        if ($categoryId = request('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($sort = request('sort')) {
            match ($sort) {
                'name' => $query->orderBy('name'),
                default => $query->latest(),
            };
        } else {
            $query->orderBy('sort_order')->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = $this->getCachedCategories();

        return view('pages.shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'variants.media', 'media']);

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('media')
            ->limit(4)
            ->get();

        // Generate JSON-LD for product
        $jsonLdService = app(\App\Services\JsonLdService::class);
        $jsonLd = [
            $jsonLdService->product($product),
            $jsonLdService->breadcrumb([
                ['name' => 'Inicio', 'url' => config('app.url')],
                ['name' => 'Tienda', 'url' => route('shop.index')],
                ['name' => $product->category->name ?? 'Productos', 'url' => route('shop.category', $product->category->slug ?? '')],
                ['name' => $product->name, 'url' => route('shop.show', $product->slug)],
            ]),
        ];

        return view('pages.shop.show', compact('product', 'relatedProducts', 'jsonLd'));
    }

    public function category(Category $category)
    {
        $category->load('children');

        $query = Product::active()
            ->where('category_id', $category->id)
            ->with(['category', 'media']);

        $products = $query->paginate(12)->withQueryString();
        $categories = $this->getCachedCategories();

        return view('pages.shop.index', compact('products', 'categories', 'category'));
    }

    public function search()
    {
        $search = request('q', '');
        $query = Product::active()->with(['category', 'media']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = $this->getCachedCategories();

        return view('pages.shop.index', compact('products', 'categories'));
    }
}
