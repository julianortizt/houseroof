<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--disk=public}';
    protected $description = 'Generate XML sitemap for SEO';

    public function handle(): int
    {
        $this->info('🗺️  Generando sitemap XML...');
        $this->info('');

        $baseUrl = config('app.url', 'https://TU_DOMINIO.com');
        $sitemap = $this->buildSitemap($baseUrl);

        // Save to storage
        $disk = $this->option('disk');
        Storage::disk($disk)->put('sitemap.xml', $sitemap);

        // Also copy to public directory for direct access
        $publicPath = public_path('sitemap.xml');
        file_put_contents($publicPath, $sitemap);

        $this->info("✅ Sitemap generado exitosamente");
        $this->info("   📁 Storage: storage/{$disk}/sitemap.xml");
        $this->info("   📁 Public: public/sitemap.xml");
        $this->info("   🌐 URL: {$baseUrl}/sitemap.xml");
        $this->info('');

        // Count URLs
        $urlCount = substr_count($sitemap, '<url>');
        $this->info("📊 Total de URLs: {$urlCount}");

        return Command::SUCCESS;
    }

    protected function buildSitemap(string $baseUrl): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n\n";

        // === STATIC PAGES ===
        $xml .= $this->addUrl($baseUrl, '1.0', 'daily', 'homepage');
        $xml .= $this->addUrl($baseUrl . '/tienda', '0.9', 'daily', 'shop');
        $xml .= $this->addUrl($baseUrl . '/servicios', '0.8', 'weekly', 'services');
        $xml .= $this->addUrl($baseUrl . '/proyectos', '0.8', 'weekly', 'projects');
        $xml .= $this->addUrl($baseUrl . '/blog', '0.9', 'daily', 'blog');
        $xml .= $this->addUrl($baseUrl . '/contacto', '0.7', 'monthly', 'contact');
        $xml .= $this->addUrl($baseUrl . '/cotizacion', '0.7', 'monthly', 'quote');

        // Legal pages
        $xml .= $this->addUrl($baseUrl . '/legal/privacy-policy', '0.3', 'yearly', 'privacy');
        $xml .= $this->addUrl($baseUrl . '/legal/cookie-policy', '0.3', 'yearly', 'cookies');
        $xml .= $this->addUrl($baseUrl . '/legal/terms-of-sale', '0.3', 'yearly', 'terms');
        $xml .= $this->addUrl($baseUrl . '/legal/legal-notice', '0.3', 'yearly', 'legal');

        // === PRODUCTS ===
        $this->info('📦 Generando URLs de productos...');
        $products = Product::with('category')->where('is_active', true)->get();
        foreach ($products as $product) {
            $xml .= $this->addUrl(
                $baseUrl . '/tienda/' . $product->slug,
                '0.8',
                'weekly',
                'product',
                $product->updated_at
            );

            // Add alternate language versions
            $xml .= $this->addAlternate($baseUrl . '/tienda/' . $product->slug, 'es');
            $xml .= $this->addAlternate($baseUrl . '/en/shop/' . $product->slug, 'en');
        }
        $this->info("   ✅ " . $products->count() . " productos");

        // === BLOG POSTS ===
        $this->info('📰 Generando URLs de blog...');
        $posts = BlogPost::where('status', 'published')->whereNotNull('published_at')->get();
        foreach ($posts as $post) {
            $xml .= $this->addUrl(
                $baseUrl . '/blog/' . $post->slug,
                '0.7',
                'weekly',
                'blog_post',
                $post->published_at
            );
        }
        $this->info("   ✅ " . $posts->count() . " artículos de blog");

        // === PROJECTS ===
        $this->info('🖼️  Generando URLs de proyectos...');
        $projects = Project::where('is_active', true)->get();
        foreach ($projects as $project) {
            $xml .= $this->addUrl(
                $baseUrl . '/proyectos/' . $project->slug,
                '0.6',
                'monthly',
                'project',
                $project->updated_at
            );
        }
        $this->info("   ✅ " . $projects->count() . " proyectos");

        // === SHOP CATEGORIES ===
        $this->info('📁 Generando URLs de categorías...');
        $categories = Category::all();
        foreach ($categories as $category) {
            $xml .= $this->addUrl(
                $baseUrl . '/tienda/categoria/' . $category->slug,
                '0.7',
                'weekly',
                'category'
            );
        }
        $this->info("   ✅ " . $categories->count() . " categorías");

        $xml .= '</urlset>';

        return $xml;
    }

    protected function addUrl(
        string $url,
        string $priority = '0.5',
        string $changeFreq = 'monthly',
        string $pageType = 'page',
        ?\Illuminate\Support\Carbon $lastMod = null
    ): string {
        $lastMod = $lastMod ?? now();

        $xml = "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "        <lastmod>" . $lastMod->format('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>{$changeFreq}</changefreq>\n";
        $xml .= "        <priority>{$priority}</priority>\n";
        $xml .= "    </url>\n\n";

        return $xml;
    }

    protected function addAlternate(string $url, string $lang): string
    {
        // This is a simplified version; in production you'd add xhtml:link
        return '';
    }
}
