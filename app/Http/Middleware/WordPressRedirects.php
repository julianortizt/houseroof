<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WordPressRedirects
{
    /**
     * WordPress URL slugs mapped to Laravel routes.
     * Based on the previous WordPress site content structure
     */
    protected array $redirects = [
        // === PRODUCTOS (WooCommerce) ===
        '/producto/fusion-promoter/' => '/tienda/fusion-promoter',
        '/producto/diamond-coat/' => '/tienda/diamond-coat',
        '/producto/fresch-coat/' => '/tienda/fresch-coat',
        '/producto/rox-binder/' => '/tienda/rox-binder',
        '/producto/spray-granite-1kg/' => '/tienda/spray-granite-1kg',
        '/producto/spray-granite-10kg/' => '/tienda/spray-granite-10kg',
        '/producto/spray-granite-10-1/' => '/tienda/spray-granite-10-1',
        '/producto/armourguard/' => '/tienda/armourguard',
        '/producto/terrace-coat/' => '/tienda/terrace-coat',
        '/producto/uv-protect/' => '/tienda/uv-protect',
        '/producto/epoxy-primer/' => '/tienda/epoxy-primer',
        '/producto/polyurethane-primer/' => '/tienda/polyurethane-primer',
        '/producto/top-coat/' => '/tienda/top-coat',
        '/producto/granite-chips/' => '/tienda/granite-chips',
        '/producto/pressure-pot-kit/' => '/tienda/pressure-pot-kit',
        '/producto/spray-gun/' => '/tienda/spray-gun',
        '/producto/mixing-paddle/' => '/tienda/mixing-paddle',
        '/producto/notched-trowel/' => '/tienda/notched-trowel',
        '/producto/squeegee/' => '/tienda/squeegee',
        '/producto/paint-tray/' => '/tienda/paint-tray',
        '/producto/telescopic-pole/' => '/tienda/telescopic-pole',
        '/producto/resin-mixer/' => '/tienda/resin-mixer',
        '/producto/magic-trowel/' => '/tienda/magic-trowel',
        '/producto/rubber-grout-float/' => '/tienda/rubber-grout-float',
        '/producto/cubo-pintura/' => '/tienda/cubo-pintura',

        // WooCommerce product category pages
        '/producto-categoria/resinas/' => '/tienda',
        '/producto-categoria/aglutinantes/' => '/tienda',
        '/producto-categoria/kits/' => '/tienda',
        '/producto-categoria/herramientas/' => '/tienda',
        '/producto-categoria/spray-granite/' => '/tienda',
        '/producto-categoria/imprimaciones/' => '/tienda',
        '/producto-categoria/proteccion/' => '/tienda',
        '/producto-categoria/limpieza/' => '/tienda',
        '/product-category/' => '/tienda',
        '/productos/' => '/tienda',

        // === COLORES DE ÁRIDOS ===
        '/color/blanco-clasico/' => '/tienda',
        '/color/blanco-especial/' => '/tienda',
        '/color/crema-marfil/' => '/tienda',
        '/color/cuarzo-espanol/' => '/tienda',
        '/color/cuarzo-roble/' => '/tienda',
        '/color/gris-azulado/' => '/tienda',
        '/color/gris-claro/' => '/tienda',
        '/color/gris-oscuro/' => '/tienda',
        '/color/negro/' => '/tienda',
        '/color/rojo-alicante/' => '/tienda',
        '/color/rosa-valencia/' => '/tienda',
        '/color/verde/' => '/tienda',

        // === PÁGINAS PRINCIPALES ===
        '/servicios/' => '/servicios',
        '/instalacion-de-pisos/' => '/servicios',
        '/instalacion-resina-epoxica/' => '/servicios',
        '/galeria/' => '/proyectos',
        '/proyectos/' => '/proyectos',
        '/blog/' => '/blog',
        '/noticias/' => '/blog',
        '/contacto/' => '/contacto',
        '/contactar/' => '/contacto',
        '/cotizacion/' => '/cotizacion',
        '/solicitud-presupuesto/' => '/cotizacion',
        '/sobre-nosotros/' => '/',
        '/empresa/' => '/',

        // === TEXTOS LEGALES ===
        '/politica-de-privacidad/' => '/legal/privacy-policy',
        '/aviso-legal/' => '/legal/legal-notice',
        '/politica-de-cookies/' => '/legal/cookie-policy',
        '/condiciones-de-venta/' => '/legal/terms-of-sale',
        '/terminos-y-condiciones/' => '/legal/terms-of-sale',
        '/privacy-policy/' => '/legal/privacy-policy',
        '/cookie-policy/' => '/legal/cookie-policy',
        '/legal/cookies/' => '/legal/cookie-policy',
        '/terms-of-sale/' => '/legal/terms-of-sale',
        '/legal-notice/' => '/legal/legal-notice',

        // === PÁGINAS DE CARRITO/CHECKOUT ===
        '/carrito/' => '/carrito',
        '/cart/' => '/carrito',
        '/checkout/' => '/checkout',
        '/finalizar-compra/' => '/checkout',
        '/mi-cuenta/' => '/',
        '/pedido-realizado/' => '/checkout/exito',
        '/order-received/' => '/checkout/exito',

        // === BÚSQUEDA ===
        '/?s=' => '/tienda',
        '/search/' => '/tienda',

        // === IMÁGENES DE PRODUCTOS (WooCommerce uploads) ===
        '/wp-content/uploads/' => '/storage/',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $fullPath = '/' . $path;

        // Check for exact match
        if (isset($this->redirects[$fullPath])) {
            return redirect($this->redirects[$fullPath], 301);
        }

        // Check for prefix match (for dynamic URLs)
        foreach ($this->redirects as $oldPath => $newPath) {
            $oldPrefix = rtrim($oldPath, '/');
            $fullPrefix = rtrim($fullPath, '/');

            if (str_starts_with($fullPrefix, $oldPrefix)) {
                // Extract the remaining part
                $remaining = substr($fullPath, strlen($oldPrefix));
                $newUrl = $newPath . $remaining;

                // Clean up double slashes
                $newUrl = str_replace('//', '/', $newUrl);

                return redirect($newUrl, 301);
            }
        }

        return $next($request);
    }
}
