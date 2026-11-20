<?php

namespace App\Services;

use App\Models\Product;
use App\Models\BlogPost;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

class JsonLdService
{
    /**
     * Generate Organization schema for the homepage.
     */
    public function organization(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('platform.public_name'),
            'url' => config('app.url'),
            'description' => 'Instalación de pavimentos de resina, trabajos de construcción y venta de productos para proyectos en Málaga y Costa Blanca.',
            'areaServed' => [
                ['@type' => 'AdministrativeArea', 'name' => 'Málaga'],
                ['@type' => 'Place', 'name' => 'Costa Blanca'],
            ],
            'knowsAbout' => [
                'Instalación de pavimentos de resina',
                'Construcción y reforma',
                'Productos para pavimentos y construcción',
            ],
        ];

        $contactPoint = [
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'availableLanguage' => ['Spanish', 'English'],
        ];
        $phone = \App\Models\Setting::get('site_phone');
        $email = \App\Models\Setting::get('site_email');

        if (filled($phone)) {
            $contactPoint['telephone'] = $phone;
        }

        if (filled($email)) {
            $contactPoint['email'] = $email;
        }

        if (count($contactPoint) > 3) {
            $schema['contactPoint'] = $contactPoint;
        }

        return $schema;
    }

    /**
     * Generate WebSite schema for the homepage.
     */
    public function website(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('platform.public_name'),
            'url' => config('app.url'),
        ];

        if (app(\App\Services\PlatformModules::class)->enabled('products')) {
            $schema['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => config('app.url') . '/tienda?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }

        return $schema;
    }

    /**
     * Generate Product schema for a product page.
     */
    public function product(Product $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->description,
            'sku' => $product->slug,
            'mpn' => $product->sku ?? $product->slug,
            'brand' => [
                '@type' => 'Brand',
                'name' => 'Costa Resin & Construction',
            ],
            'manufacturer' => [
                '@type' => 'Organization',
                'name' => 'Costa Resin & Construction',
            ],
            'category' => $product->category->name ?? 'Pavimentos decorativos',
            'url' => route('shop.show', $product->slug),
            'image' => $product->getFirstMediaUrl('default', 'large') ?: config('app.url') . '/storage/site/placeholder.jpg',
            'offers' => [
                '@type' => 'Offer',
                'url' => route('shop.show', $product->slug),
                'priceCurrency' => 'EUR',
                'price' => number_format($product->price, 2, '.', ''),
                'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => 'Costa Resin & Construction',
                ],
                'shippingDetails' => [
                    '@type' => 'OfferShippingDetails',
                    'shippingDestination' => [
                        '@type' => 'DefinedRegion',
                        'addressCountry' => 'ES',
                    ],
                    'shippingRate' => [
                        '@type' => 'MonetaryAmount',
                        'value' => '15.00',
                        'currency' => 'EUR',
                    ],
                    'deliveryTime' => [
                        '@type' => 'ShippingDeliveryTime',
                        'handlingTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => '1',
                            'maxValue' => '3',
                            'unitCode' => 'DAY',
                        ],
                        'transitTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => '2',
                            'maxValue' => '5',
                            'unitCode' => 'DAY',
                        ],
                    ],
                ],
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => 'ES',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                    'merchantReturnDays' => 14,
                    'returnMethod' => 'https://schema.org/ReturnByMail',
                ],
            ],
        ];

        // Add aggregate rating if reviews exist
        if ($product->reviews_count > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($product->reviews_avg_rating, 1),
                'reviewCount' => $product->reviews_count,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        // Add variants if they exist
        if ($product->variants->count() > 0) {
            $schema['additionalProperty'] = [];
            foreach ($product->variants as $variant) {
                $schema['additionalProperty'][] = [
                    '@type' => 'PropertyValue',
                    'name' => $variant->name,
                    'value' => $variant->value,
                ];
            }
        }

        return $schema;
    }

    /**
     * Generate BreadcrumbList schema.
     */
    public function breadcrumb(array $items): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        foreach ($items as $index => $item) {
            $schema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return $schema;
    }

    /**
     * Generate BlogPosting schema for a blog post.
     */
    public function blogPosting(BlogPost $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->excerpt ?? substr(strip_tags($post->content), 0, 160),
            'image' => $post->getFirstMediaUrl('default', 'large') ?: config('app.url') . '/storage/site/placeholder.jpg',
            'author' => [
                '@type' => 'Organization',
                'name' => config('platform.public_name'),
                'url' => config('app.url'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('platform.public_name'),
            ],
            'datePublished' => $post->published_at?->format('Y-m-d') ?? $post->created_at->format('Y-m-d'),
            'dateModified' => $post->updated_at->format('Y-m-d'),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $post->slug),
            ],
            'keywords' => implode(', ', $post->tags->pluck('name')->toArray()),
            'articleSection' => $post->category->name ?? 'Blog',
            'inLanguage' => app()->getLocale() === 'en' ? 'en-GB' : 'es',
        ];
    }

    /**
     * Generate FAQPage schema.
     */
    public function faq(array $faqs): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [],
        ];

        foreach ($faqs as $faq) {
            $schema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }

        return $schema;
    }

    /**
     * Generate LocalBusiness schema.
     */
    public function localBusiness(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => config('platform.public_name'),
            'url' => config('app.url'),
            'areaServed' => [
                ['@type' => 'AdministrativeArea', 'name' => 'Málaga'],
                ['@type' => 'Place', 'name' => 'Costa Blanca'],
            ],
        ];

        $phone = \App\Models\Setting::get('site_phone');
        $email = \App\Models\Setting::get('site_email');

        if (filled($phone)) {
            $schema['telephone'] = $phone;
        }

        if (filled($email)) {
            $schema['email'] = $email;
        }

        return $schema;
    }

    /**
     * Generate Service schema for the company's resin & granite chip pavement services.
     */
    public function service(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Instalación de pavimentos de resina, construcción y venta de productos',
            'serviceType' => 'Pavimentos de resina y construcción',
            'description' => 'Instalación de pavimentos de resina, trabajos de construcción y venta de productos para proyectos en Málaga y Costa Blanca.',
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => config('platform.public_name'),
                'url' => config('app.url'),
            ],
            'areaServed' => [
                ['@type' => 'AdministrativeArea', 'name' => 'Málaga'],
                ['@type' => 'Place', 'name' => 'Costa Blanca'],
            ],
            'url' => config('app.url') . '/servicios',
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Servicios de pavimentos y construcción',
                'itemListElement' => [
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Instalación de pavimentos de resina']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Trabajos de construcción y reforma']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Venta de productos para pavimentos y construcción']],
                ],
            ],
        ];
    }
}
