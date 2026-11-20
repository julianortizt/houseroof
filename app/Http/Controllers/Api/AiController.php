<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AiController extends Controller
{
    /**
     * Lightweight, self-contained assistant for the mobile app.
     * No external LLM key required: it answers common questions about
     * Costa Resin & Construction products, pricing, installation and quotes using
     * keyword matching, locale-aware.
     */
    public function chat(Request $request)
    {
        $message = strtolower(trim((string) $request->input('message', '')));
        $locale = $request->input('locale', 'es');
        if (!in_array($locale, ['es', 'en', 'de', 'it', 'fr', 'pt', 'nl'], true)) {
            $locale = 'es';
        }

        $reply = $this->answer($message, $locale);

        return Response::json(['content' => $reply]);
    }

    private function answer(string $m, string $locale): string
    {
        $rules = [
            [
                'keys' => ['hola', 'buenas', 'hello', 'hi', 'hey', 'hallo', 'salve'],
                'es' => '¡Hola! Soy el asistente de Costa Resin & Construction. ¿En qué puedo ayudarte? Puedes preguntar por precios, colores, instalación o pedir una cotización.',
                'en' => 'Hello! I am the Costa Resin & Construction assistant. How can I help? Ask about prices, colors, installation or request a quote.',
            ],
            [
                'keys' => ['precio', 'coste', 'cuanto', 'price', 'cost', 'how much', 'preis'],
                'es' => 'El precio depende del producto y los m². Nuestras resinas parten de 39,90 €/m². Para un presupuesto exacto, usa la pantalla "Cotización" y te contactaremos.',
                'en' => 'Price depends on product and square meters. Our resins start at €39.90/m². For an exact quote, use the Quotation screen and we will contact you.',
            ],
            [
                'keys' => ['cotiz', 'presupuesto', 'quote', 'budget', 'offerta'],
                'es' => 'Puedes solicitar una cotización desde la pestaña "Cotización": indica servicio, m² y tus datos, y nuestro equipo te responde.',
                'en' => 'Request a quote from the Quotation tab: enter service, square meters and your details, and our team will reply.',
            ],
            [
                'keys' => ['garant', 'warranty', 'garanzie'],
                'es' => 'Ofrecemos garantía en nuestros sistemas de resina. El plazo exacto depende del producto y la instalación.',
                'en' => 'We offer a warranty on our resin systems. The exact term depends on the product and installation.',
            ],
            [
                'keys' => ['instal', 'montar', 'install', 'apply', 'instalacion', 'instal·lació'],
                'es' => 'La instalación la realizan técnicos certificados: preparación del soporte, imprimación, capa de resina y acabado. ¿Quieres una cotización?',
                'en' => 'Installation is done by certified technicians: surface prep, primer, resin layer and finish. Want a quote?',
            ],
            [
                'keys' => ['color', 'colores', 'tono', 'shade', 'farbe'],
                'es' => 'Tenemos varios acabados: Natural, Gris Pizarra, Blanco, Negro, Terracota y Azul Piedra. Pruébalos en el simulador de suelos.',
                'en' => 'We have several finishes: Natural, Slate Grey, White, Black, Terracotta and Stone Blue. Try them in the floor simulator.',
            ],
            [
                'keys' => ['resina', 'microcemento', 'epoxi', 'resin', 'harz'],
                'es' => 'Trabajamos resinas epoxi, poliuretano y microcemento para suelos de garaje, salón, cocina y baño. ¿Buscas alguno en concreto?',
                'en' => 'We work with epoxy, polyurethane resins and microcement for garage, living room, kitchen and bathroom floors. Looking for a specific one?',
            ],
            [
                'keys' => ['contact', 'teléfono', 'telefono', 'email', 'correo', 'contact', 'phone', 'mail', 'kontakt'],
                'es' => 'Contáctanos desde la pantalla "Cotización" o por email a hello@TU_DOMINIO.com. ¡Te responderemos pronto!',
                'en' => 'Contact us via the Quotation screen or by email at hello@TU_DOMINIO.com. We will reply soon!',
            ],
            [
                'keys' => ['gracias', 'thank', 'danke'],
                'es' => '¡De nada! Si necesitas algo más, aquí estoy.',
                'en' => 'You are welcome! If you need anything else, I am here.',
            ],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['keys'] as $key) {
                if ($key !== '' && str_contains($m, $key)) {
                    return $rule[$locale] ?? $rule['es'];
                }
            }
        }

        return $locale === 'en'
            ? 'I can help you with products, colors, prices, installation and quotes. Could you tell me a bit more about your project?'
            : 'Puedo ayudarte con productos, colores, precios, instalación y cotizaciones. ¿Me cuentas un poco más de tu proyecto?';
    }
}
