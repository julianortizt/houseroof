<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Project;

class SitemapController extends Controller
{
    protected $locales = ['es', 'en', 'de', 'it', 'fr', 'pt', 'nl'];

    protected $priorities = [
        'home' => 1.0, 'shop' => 0.9, 'services' => 0.9, 'projects' => 0.8,
        'about' => 0.7, 'blog' => 0.8, 'contact' => 0.6, 'quote' => 0.6,
        'product' => 0.8, 'category' => 0.7, 'project' => 0.7,
        'post' => 0.6, 'page' => 0.5,
    ];

    public function index()
    {
        $urls = [];

        // Static pages
        $static = [
            '/' => 'home',
            '/tienda' => 'shop',
            '/servicios' => 'services',
            '/proyectos' => 'projects',
            '/about-us' => 'about',
            '/blog' => 'blog',
            '/contacto' => 'contact',
            '/cotizacion' => 'quote',
        ];
        foreach ($static as $path => $type) {
            $this->addUrl($urls, config('app.url') . $path, $type, 'daily');
        }

        // Products
        foreach (Product::query()->active()->get() as $product) {
            $this->addUrl($urls, route('shop.show', $product->slug), 'product', 'weekly');
        }

        // Categories
        foreach (Category::query()->active()->get() as $category) {
            $this->addUrl($urls, route('shop.category', $category->slug), 'category', 'weekly');
        }

        // Projects
        foreach (Project::query()->active()->get() as $project) {
            $this->addUrl($urls, route('projects.show', $project->slug), 'project', 'monthly');
        }

        // Blog posts
        foreach (BlogPost::query()->published()->get() as $post) {
            $this->addUrl($urls, route('blog.show', $post->slug), 'post', 'monthly');
        }

        // Legal pages
        foreach (Page::all() as $page) {
            $this->addUrl($urls, route('legal.page', $page->slug), 'page', 'monthly');
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        foreach ($urls as $u) {
            $xml .= '<url>';
            $xml .= '<loc>' . e($u['loc']) . '</loc>';
            $xml .= '<changefreq>' . $u['freq'] . '</changefreq>';
            $xml .= '<priority>' . $u['priority'] . '</priority>';
            foreach ($u['alts'] as $alt) {
                $xml .= '<xhtml:link rel="alternate" hreflang="' . $alt['lang'] . '" href="' . e($alt['href']) . '"/>';
            }
            $xml .= '</url>';
        }
        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml');
    }

    protected function addUrl(array &$urls, string $loc, string $type, string $freq): void
    {
        $alts = [];
        foreach ($this->locales as $l) {
            $alts[] = ['lang' => $l, 'href' => $loc . '?lang=' . $l];
        }
        $alts[] = ['lang' => 'x-default', 'href' => $loc];

        $urls[] = [
            'loc' => $loc,
            'freq' => $freq,
            'priority' => $this->priorities[$type] ?? 0.5,
            'alts' => $alts,
        ];
    }
}
