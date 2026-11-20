<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContentBlock;
use App\Models\Product;
use App\Models\Project;
use App\Models\Setting;
use App\Services\PlatformModules;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(PlatformModules $modules)
    {
        $defaultTemplate = config('platform.default_home_template', 'roof');
        $homeTemplate = Setting::get('homepage_template', $defaultTemplate);

        // Old installations may still store the previous template key. Serve
        // the new roof homepage instead of the retired resin landing page.
        if (! array_key_exists($homeTemplate, config('platform.home_templates', []))) {
            $homeTemplate = $defaultTemplate;
        }

        // Every standard layout now renders the House Roof homepage. The
        // template choice remains available for styling/ordered sections, but
        // the retired resin landing page is never served again.
        if ($homeTemplate !== 'corporate') {
            return view('pages.home-roof');
        }

        return view('pages.home-corporate');

        // Cache homepage data for 5 minutes — reduces DB load significantly
        // Cache is automatically flushed when products/projects/posts are updated
        $featuredProducts = $modules->enabled('products')
            ? Cache::remember('home:featured_products', 300, fn () =>
                Product::active()
                    ->featured()
                    ->with(['category', 'media'])
                    ->limit(8)
                    ->get()
            )
            : collect();

        $featuredProjects = Cache::remember('home:featured_projects', 300, fn () =>
            Project::active()
                ->featured()
                ->with('media')
                ->limit(6)
                ->get()
        );

        $latestPosts = Cache::remember('home:latest_posts', 300, fn () =>
            BlogPost::published()
                ->with(['category', 'media'])
                ->latest('published_at')
                ->limit(3)
                ->get()
        );

        $categories = $modules->enabled('products')
            ? Cache::remember('home:categories', 300, fn () =>
                Category::active()
                    ->roots()
                    ->withCount('products')
                    ->with(['media'])
                    ->get()
            )
            : collect();

        // Generate JSON-LD for homepage
        $jsonLdService = app(\App\Services\JsonLdService::class);
        $jsonLd = $jsonLdService->localBusiness();

        return view('pages.home', compact(
            'featuredProducts', 'featuredProjects', 'latestPosts', 'categories', 'jsonLd'
        ));
    }
}
