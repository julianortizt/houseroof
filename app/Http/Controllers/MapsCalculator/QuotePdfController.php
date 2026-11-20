<?php

namespace App\Http\Controllers\MapsCalculator;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\QuotePdfService;

class QuotePdfController extends Controller
{
    public function download(Quote $quote)
    {
        $this->authorize('view', $quote);

        return app(QuotePdfService::class)->make($quote)->download('presupuesto-' . $quote->formatted_number . '.pdf');
    }

    public function view(Quote $quote)
    {
        $this->authorize('view', $quote);

        return app(QuotePdfService::class)->make($quote)->stream('presupuesto-' . $quote->formatted_number . '.pdf');
    }

    /**
     * El prompt pide el presupuesto con el membrete (logo de la empresa) y con
     * el detalle de la superficie medida incluyendo una imagen del polígono
     * dibujado; antes el PDF era sólo texto y tablas.
     */
}
