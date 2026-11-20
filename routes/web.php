<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\TrapController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// ====================
// WordPress 301 Redirects
// ====================

// Productos (WooCommerce)
Route::redirect('/producto/fusion-promoter/', '/tienda/fusion-promoter', 301);
Route::redirect('/producto/diamond-coat/', '/tienda/diamond-coat', 301);
Route::redirect('/producto/fresch-coat/', '/tienda/fresch-coat', 301);
Route::redirect('/producto/rox-binder/', '/tienda/rox-binder', 301);
Route::redirect('/producto/spray-granite-1kg/', '/tienda/spray-granite-1kg', 301);
Route::redirect('/producto/spray-granite-10kg/', '/tienda/spray-granite-10kg', 301);
Route::redirect('/producto/spray-granite-10-1/', '/tienda/spray-granite-10-1', 301);
Route::redirect('/producto/armourguard/', '/tienda/armourguard', 301);
Route::redirect('/producto/terrace-coat/', '/tienda/terrace-coat', 301);
Route::redirect('/producto/uv-protect/', '/tienda/uv-protect', 301);
Route::redirect('/producto/epoxy-primer/', '/tienda/epoxy-primer', 301);
Route::redirect('/producto/polyurethane-primer/', '/tienda/polyurethane-primer', 301);
Route::redirect('/producto/top-coat/', '/tienda/top-coat', 301);
Route::redirect('/producto/granite-chips/', '/tienda/granite-chips', 301);
Route::redirect('/producto/pressure-pot-kit/', '/tienda/pressure-pot-kit', 301);
Route::redirect('/producto/spray-gun/', '/tienda/spray-gun', 301);
Route::redirect('/producto/mixing-paddle/', '/tienda/mixing-paddle', 301);
Route::redirect('/producto/notched-trowel/', '/tienda/notched-trowel', 301);
Route::redirect('/producto/squeegee/', '/tienda/squeegee', 301);
Route::redirect('/producto/paint-tray/', '/tienda/paint-tray', 301);
Route::redirect('/producto/telescopic-pole/', '/tienda/telescopic-pole', 301);
Route::redirect('/producto/resin-mixer/', '/tienda/resin-mixer', 301);
Route::redirect('/producto/magic-trowel/', '/tienda/magic-trowel', 301);
Route::redirect('/producto/rubber-grout-float/', '/tienda/rubber-grout-float', 301);
Route::redirect('/producto/cubo-pintura/', '/tienda/cubo-pintura', 301);

// WooCommerce product category pages
Route::redirect('/producto-categoria/resinas/', '/tienda', 301);
Route::redirect('/producto-categoria/aglutinantes/', '/tienda', 301);
Route::redirect('/producto-categoria/kits/', '/tienda', 301);
Route::redirect('/producto-categoria/herramientas/', '/tienda', 301);
Route::redirect('/producto-categoria/spray-granite/', '/tienda', 301);
Route::redirect('/producto-categoria/imprimaciones/', '/tienda', 301);
Route::redirect('/producto-categoria/proteccion/', '/tienda', 301);
Route::redirect('/producto-categoria/limpieza/', '/tienda', 301);
Route::redirect('/product-category/', '/tienda', 301);
Route::redirect('/productos/', '/tienda', 301);

// Colores de áridos
Route::redirect('/color/blanco-clasico/', '/tienda', 301);
Route::redirect('/color/blanco-especial/', '/tienda', 301);
Route::redirect('/color/crema-marfil/', '/tienda', 301);
Route::redirect('/color/cuarzo-espanol/', '/tienda', 301);
Route::redirect('/color/cuarzo-roble/', '/tienda', 301);
Route::redirect('/color/gris-azulado/', '/tienda', 301);
Route::redirect('/color/gris-claro/', '/tienda', 301);
Route::redirect('/color/gris-oscuro/', '/tienda', 301);
Route::redirect('/color/negro/', '/tienda', 301);
Route::redirect('/color/rojo-alicante/', '/tienda', 301);
Route::redirect('/color/rosa-valencia/', '/tienda', 301);

// Páginas principales
Route::redirect('/instalacion-de-pisos/', '/servicios', 301);
Route::redirect('/instalacion-resina-epoxica/', '/servicios', 301);
Route::redirect('/galeria/', '/proyectos', 301);
Route::redirect('/noticias/', '/blog', 301);
Route::redirect('/contactar/', '/contacto', 301);
Route::redirect('/solicitud-presupuesto/', '/cotizacion', 301);
Route::redirect('/sobre-nosotros/', '/', 301);
Route::redirect('/empresa/', '/', 301);

// Textos legales
Route::redirect('/politica-de-privacidad/', '/legal/privacy-policy', 301);
Route::redirect('/aviso-legal/', '/legal/legal-notice', 301);
Route::redirect('/politica-de-cookies/', '/legal/cookie-policy', 301);
Route::redirect('/condiciones-de-venta/', '/legal/terms-of-sale', 301);
Route::redirect('/terminos-y-condiciones/', '/legal/terms-of-sale', 301);
Route::redirect('/privacy-policy/', '/legal/privacy-policy', 301);
Route::redirect('/cookie-policy/', '/legal/cookie-policy', 301);
Route::redirect('/legal/cookies', '/legal/cookie-policy', 301);
Route::redirect('/terms-of-sale/', '/legal/terms-of-sale', 301);
Route::redirect('/legal-notice/', '/legal/legal-notice', 301);

// Carrito / Checkout
Route::redirect('/cart/', '/carrito', 301);
Route::redirect('/finalizar-compra/', '/checkout', 301);
Route::redirect('/mi-cuenta/', '/', 301);
Route::redirect('/pedido-realizado/', '/checkout/exito', 301);
Route::redirect('/order-received/', '/checkout/exito', 301);

// Search
Route::redirect('/search/', '/tienda', 301);

// WordPress uploads
Route::redirect('/wp-content/uploads/', '/storage/', 301);

// ====================
// Visual Installer (web-based, no SSH required)
// ====================

Route::prefix('install')->name('install.')->middleware(\App\Http\Middleware\BlockInstallerWhenInstalled::class)->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'postDatabase'])->name('database.post');
    Route::get('/site', [InstallerController::class, 'site'])->name('site');
    Route::post('/site', [InstallerController::class, 'postSite'])->name('site.post');
    Route::get('/confirm', [InstallerController::class, 'confirm'])->name('confirm');
    Route::post('/run', [InstallerController::class, 'run'])->name('run');
    Route::get('/complete', [InstallerController::class, 'complete'])->name('complete');
});

// ====================
// Main Routes
// ====================

// Locale Switch (no middleware needed - just saves to session)
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// SEO: XML Sitemap (with hreflang alternates)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/admin/locale/{locale}', function (string $locale) {
    // Admin uses ONLY EN/ES, stored in 'admin_locale' (separate from frontend)
    $availableLocales = ['en', 'es'];
    if (in_array($locale, $availableLocales)) {
        session()->put('admin_locale', $locale);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('admin.locale.switch');



// ====================
// Anti-downloader trap (never linked in the UI; fed by honeypot + .htaccess)
// ====================
Route::get('/site-archive/{any?}', TrapController::class)
    ->where('any', '.*')
    ->name('trap');

// Home
Route::get('/', HomeController::class)->name('home');
Route::view('/plataforma-empresarial', 'pages.home-corporate')->name('platform.home');

// About Us
Route::get('/about-us', function () {
    return view('pages.about.index');
})->name('about');

// Search
Route::middleware('module.enabled:products')->group(function () {
    Route::get('/buscar', [ShopController::class, 'search'])->name('shop.search');

    // Shop
    Route::get('/tienda', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/tienda/categoria/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
    Route::get('/tienda/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

    Route::view('/carrito', 'pages.shop.cart')->name('cart.index');
    Route::view('/checkout', 'pages.shop.checkout')->name('checkout.index');
});

Route::get('/checkout/exito', [\App\Http\Controllers\ProductCheckoutController::class, 'success'])
    ->name('checkout.success');

// Stripe Webhook
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Projects / Gallery
Route::get('/proyectos', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/proyectos/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Services
Route::get('/servicios', [PageController::class, 'services'])->name('services');

// Quote
Route::get('/cotizacion', function () {
    return view('pages.quote.create');
})->name('quote.create');

// Admin PDF routes (protected by auth middleware in Filament)
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('quotes/{quote}/pdf', function (\App\Models\Quote $quote) {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.quotes.pdf', ['quote' => $quote->load('items.product', 'items.variant')]);
        return $pdf->stream($quote->formatted_number . '.pdf');
    })->name('quotes.pdf');
});

// Contact
Route::get('/contacto', [PageController::class, 'contact'])->name('contact');

// Health / deployment checks
Route::get('/health', function () {
    $report = app(\App\Services\ProductionReadiness::class)->check();

    return response()->json([
        'status' => $report['ready'] ? 'ok' : 'warning',
        'ready' => $report['ready'],
        'environment' => app()->environment(),
        'app_url' => config('app.url'),
        'checks' => $report['checks'],
        'warnings' => $report['warnings'],
        'errors' => $report['errors'],
    ], $report['ready'] ? 200 : 503);
})->name('health');

// Legal pages
Route::get('/legal/{slug}', [PageController::class, 'show'])->name('legal.page');

// Login routes (for mapscalculator auth middleware)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

use Illuminate\Support\Facades\Auth;

Route::post('/login', function () {
    $attrs = request()->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($attrs)) {
        request()->session()->regenerate();
        return redirect()->intended('/mapscalculator');
    }

    return back()->withErrors(['email' => 'Credenciales incorrectas'])->onlyInput('email');
})->name('login.post')->middleware('throttle:6,1');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware('module.enabled:customer_portal')->prefix('cliente')->group(function () {
    Route::get('/acceso', [\App\Http\Controllers\CustomerPortalController::class, 'showLogin'])->name('customer.login');
    Route::post('/acceso', [\App\Http\Controllers\CustomerPortalController::class, 'login'])->middleware('throttle:6,1')->name('customer.login.post');
    Route::get('/registro', [\App\Http\Controllers\CustomerPortalController::class, 'showRegistration'])->name('customer.register');
    Route::post('/registro', [\App\Http\Controllers\CustomerPortalController::class, 'register'])->middleware('throttle:6,1')->name('customer.register.post');
    Route::get('/verificar-email', [\App\Http\Controllers\CustomerPortalController::class, 'verificationNotice'])->middleware('customer.auth')->name('verification.notice');
    Route::post('/verificar-email', [\App\Http\Controllers\CustomerPortalController::class, 'sendVerification'])->middleware(['customer.auth', 'throttle:6,1'])->name('verification.send');
    Route::get('/verificar-email/{id}/{hash}', [\App\Http\Controllers\CustomerPortalController::class, 'verify'])
        ->middleware(['customer.auth', 'signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::get('/portal', [\App\Http\Controllers\CustomerPortalController::class, 'portal'])->middleware(['customer.auth', 'customer.verified'])->name('customer.portal');
    Route::post('/cotizaciones/{quote}/respuesta', [\App\Http\Controllers\CustomerPortalController::class, 'respondToQuote'])
        ->middleware(['customer.auth', 'customer.verified', 'module.enabled:quotes'])
        ->name('customer.quotes.respond');
    Route::get('/cotizaciones/{quote}/pdf', [\App\Http\Controllers\CustomerPortalController::class, 'downloadQuotePdf'])
        ->middleware(['customer.auth', 'customer.verified', 'module.enabled:quotes'])
        ->name('customer.quotes.pdf');
    Route::post('/cotizaciones/{quote}/pago/{paymentType}', [\App\Http\Controllers\QuotePaymentController::class, 'checkout'])
        ->whereIn('paymentType', ['deposit', 'balance'])
        ->middleware(['customer.auth', 'customer.verified', 'module.enabled:quotes', 'module.enabled:payments'])
        ->name('customer.quotes.payment');
    Route::post('/salir', [\App\Http\Controllers\CustomerPortalController::class, 'logout'])->middleware('customer.auth')->name('customer.logout');
});

require __DIR__ . '/mapscalculator.php';
