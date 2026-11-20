<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AiController extends Controller
{
    /**
     * Lightweight, self-contained assistant for the website and mobile app.
     * No external LLM key required: it answers common questions about
     * House Roof cleaning, maintenance and repair services using keyword
     * matching, locale-aware.
     */
    public function chat(Request $request)
    {
        $message = strtolower(trim((string) $request->input('message', '')));
        $locale = $request->input('locale', 'es');
        if (! in_array($locale, ['es', 'en', 'de', 'it', 'fr', 'pt', 'nl'], true)) {
            $locale = 'es';
        }

        return Response::json(['content' => $this->answer($message, $locale)]);
    }

    private function answer(string $m, string $locale): string
    {
        $rules = [
            [
                'keys' => ['hola', 'buenas', 'hello', 'hi', 'hey', 'hallo', 'salve', 'bonjour', 'olá', 'hoi'],
                'es' => '¡Hola! Soy el asistente de House Roof. Puedo ayudarte con la limpieza, el mantenimiento y la reparación de techos en Europa.',
                'en' => 'Hello! I am the House Roof assistant. I can help with roof cleaning, maintenance and repairs across Europe.',
                'de' => 'Hallo! Ich bin der Assistent von House Roof. Ich helfe bei Dachreinigung, Dachwartung und Dachreparaturen in Europa.',
                'it' => 'Ciao! Sono l’assistente di House Roof. Posso aiutarti con pulizia, manutenzione e riparazione dei tetti in Europa.',
                'fr' => 'Bonjour ! Je suis l’assistant House Roof. Je peux vous aider pour le nettoyage, l’entretien et la réparation des toitures en Europe.',
                'pt' => 'Olá! Sou o assistente da House Roof. Posso ajudar com limpeza, manutenção e reparação de telhados na Europa.',
                'nl' => 'Hallo! Ik ben de assistent van House Roof. Ik kan helpen met dakreiniging, dakonderhoud en dakreparatie in Europa.',
            ],
            [
                'keys' => ['precio', 'coste', 'cuanto', 'price', 'cost', 'how much', 'preis', 'prezzo', 'prix', 'preço', 'prijs'],
                'es' => 'El precio depende del tipo de cubierta, su estado y los metros cuadrados. Solicita un presupuesto sin compromiso y lo valoraremos.',
                'en' => 'The price depends on the roof type, its condition and the surface area. Request a free quote and we will assess it.',
                'de' => 'Der Preis hängt von Dachart, Zustand und Fläche ab. Fordern Sie ein unverbindliches Angebot an.',
                'it' => 'Il prezzo dipende dal tipo di tetto, dalle sue condizioni e dalla superficie. Richiedi un preventivo senza impegno.',
                'fr' => 'Le prix dépend du type de toiture, de son état et de la surface. Demandez un devis gratuit.',
                'pt' => 'O preço depende do tipo de telhado, do estado e da área. Peça um orçamento sem compromisso.',
                'nl' => 'De prijs hangt af van het daktype, de staat en het oppervlak. Vraag een vrijblijvende offerte aan.',
            ],
            [
                'keys' => ['cotiz', 'presupuesto', 'quote', 'budget', 'offerta', 'devis', 'offerte', 'angebot'],
                'es' => 'Puedes solicitar una cotización desde la sección "Cotización": indica el tipo de servicio, los metros y tus datos.',
                'en' => 'You can request a quote from the Quote section: tell us the service, the area and your details.',
                'de' => 'Fordern Sie ein Angebot im Bereich „Angebot“ an: nennen Sie Leistung, Fläche und Ihre Kontaktdaten.',
                'it' => 'Puoi richiedere un preventivo dalla sezione "Preventivo": indica il servizio, la superficie e i tuoi dati.',
                'fr' => 'Vous pouvez demander un devis depuis la section « Devis » : indiquez le service, la surface et vos coordonnées.',
                'pt' => 'Pode pedir um orçamento na secção "Orçamento": indique o serviço, a área e os seus dados.',
                'nl' => 'U kunt een offerte aanvragen via de sectie Offerte: vermeld de dienst, het oppervlak en uw gegevens.',
            ],
            [
                'keys' => ['garant', 'warranty', 'garanzie', 'garantie', 'garantia'],
                'es' => 'Los trabajos realizados cuentan con la garantía legal aplicable. El plazo exacto depende del servicio y los materiales.',
                'en' => 'All work carries the applicable legal warranty. The exact term depends on the service and materials.',
                'de' => 'Alle Arbeiten unterliegen der gesetzlichen Gewährleistung. Die Dauer hängt von Leistung und Material ab.',
                'it' => 'I lavori sono coperti dalla garanzia legale. La durata dipende dal servizio e dai materiali.',
                'fr' => 'Les travaux bénéficient de la garantie légale. La durée dépend de la prestation et des matériaux.',
                'pt' => 'Os trabalhos têm a garantia legal aplicável. O prazo depende do serviço e dos materiais.',
                'nl' => 'Alle werkzaamheden vallen onder de wettelijke garantie. De duur hangt af van de dienst en materialen.',
            ],
            [
                'keys' => ['limpieza', 'cleaning', 'reinigung', 'pulizia', 'nettoyage', 'limpeza', 'reiniging'],
                'es' => 'Limpieza profesional de techos: eliminación de musgo, suciedad y hojas, con revisión de canalones y bajantes.',
                'en' => 'Professional roof cleaning: removal of moss, dirt and debris, with gutters and downpipes inspection.',
                'de' => 'Professionelle Dachreinigung: Entfernung von Moos, Schmutz und Laub, inklusive Kontrolle von Rinnen und Fallrohren.',
                'it' => 'Pulizia professionale dei tetti: rimozione di muschio, sporco e foglie, con controllo di gronde e pluviali.',
                'fr' => 'Nettoyage professionnel des toitures : retrait de la mousse, des salissures et des feuilles, avec contrôle des gouttières.',
                'pt' => 'Limpeza profissional de telhados: remoção de musgo, sujidade e folhas, com verificação de calhas e condutas.',
                'nl' => 'Professionele dakreiniging: verwijdering van mos, vuil en bladeren, inclusief controle van goten en regenpijpen.',
            ],
            [
                'keys' => ['gotera', 'fuga', 'leak', 'repar', 'repair', 'reparatur', 'riparaz', 'répar', 'reparaç', 'reparatie'],
                'es' => 'Reparamos goteras y cubiertas dañadas. Cuéntanos el tipo de tejado y la zona afectada para valorar la visita técnica.',
                'en' => 'We repair leaks and damaged roofs. Tell us the roof type and the affected area so we can arrange a site visit.',
                'de' => 'Wir reparieren Undichtigkeiten und beschädigte Dächer. Nennen Sie uns Dachart und betroffene Stelle für einen Vor-Ort-Termin.',
                'it' => 'Ripariamo perdite e tetti danneggiati. Indica il tipo di tetto e la zona interessata per una visita tecnica.',
                'fr' => 'Nous réparons les fuites et les toitures endommagées. Indiquez le type de toit et la zone concernée pour une visite.',
                'pt' => 'Reparamos fugas e telhados danificados. Indique o tipo de telhado e a zona afetada para agendar uma visita.',
                'nl' => 'Wij repareren lekkages en beschadigde daken. Vertel ons het daktype en het getroffen gebied voor een bezichtiging.',
            ],
            [
                'keys' => ['mantenimiento', 'maintenance', 'wartung', 'manutenzione', 'entretien', 'manutenção', 'onderhoud'],
                'es' => 'El mantenimiento preventivo alarga la vida de la cubierta: revisión de juntas, impermeabilización y drenaje.',
                'en' => 'Preventive maintenance extends the life of your roof: joints, waterproofing and drainage checks.',
                'de' => 'Vorbeugende Wartung verlängert die Lebensdauer des Daches: Prüfung von Fugen, Abdichtung und Entwässerung.',
                'it' => 'La manutenzione preventiva allunga la vita del tetto: controllo di giunti, impermeabilizzazione e drenaggio.',
                'fr' => 'L’entretien préventif prolonge la durée de vie de la toiture : contrôle des joints, de l’étanchéité et du drainage.',
                'pt' => 'A manutenção preventiva aumenta a vida do telhado: verificação de juntas, impermeabilização e drenagem.',
                'nl' => 'Preventief onderhoud verlengt de levensduur van uw dak: controle van voegen, waterdichting en afwatering.',
            ],
            [
                'keys' => ['contact', 'teléfono', 'telefono', 'email', 'correo', 'phone', 'mail', 'kontakt'],
                'es' => 'Escríbenos a hello@houseroof.com o usa la sección "Contacto". Te responderemos pronto.',
                'en' => 'Email us at hello@houseroof.com or use the Contact section. We will reply soon.',
                'de' => 'Schreiben Sie an hello@houseroof.com oder nutzen Sie den Bereich „Kontakt“. Wir antworten bald.',
                'it' => 'Scrivici a hello@houseroof.com o usa la sezione "Contatti". Ti risponderemo presto.',
                'fr' => 'Écrivez-nous à hello@houseroof.com ou utilisez la section « Contact ». Nous répondrons rapidement.',
                'pt' => 'Escreva para hello@houseroof.com ou use a secção "Contacto". Responderemos em breve.',
                'nl' => 'Mail ons op hello@houseroof.com of gebruik de sectie Contact. We reageren snel.',
            ],
            [
                'keys' => ['gracias', 'thank', 'danke', 'grazie', 'merci', 'obrigad', 'dank'],
                'es' => '¡De nada! Si necesitas algo más, aquí estoy.',
                'en' => 'You are welcome! If you need anything else, I am here.',
                'de' => 'Gerne! Wenn Sie etwas anderes brauchen, bin ich hier.',
                'it' => 'Prego! Se ti serve altro, sono qui.',
                'fr' => 'De rien ! Si vous avez besoin d’autre chose, je suis là.',
                'pt' => 'De nada! Se precisar de mais alguma coisa, estou aqui.',
                'nl' => 'Graag gedaan! Als u iets anders nodig heeft, ben ik hier.',
            ],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['keys'] as $key) {
                if ($key !== '' && str_contains($m, $key)) {
                    return $rule[$locale] ?? $rule['es'];
                }
            }
        }

        return match ($locale) {
            'en' => 'I can help you with roof cleaning, maintenance, leak repair and quotes. Could you tell me a bit more about your roof?',
            'de' => 'Ich kann bei Dachreinigung, Wartung, Leckreparatur und Angeboten helfen. Erzählen Sie mir mehr über Ihr Dach?',
            'it' => 'Posso aiutarti con pulizia dei tetti, manutenzione, riparazione perdite e preventivi. Raccontami qualcosa sul tuo tetto?',
            'fr' => 'Je peux vous aider pour le nettoyage, l’entretien, la réparation des fuites et les devis. Parlez-moi de votre toiture ?',
            'pt' => 'Posso ajudar com limpeza, manutenção, reparação de fugas e orçamentos. Fale-me mais sobre o seu telhado?',
            'nl' => 'Ik kan helpen met dakreiniging, onderhoud, lekkagereparatie en offertes. Vertelt u iets over uw dak?',
            default => 'Puedo ayudarte con limpieza, mantenimiento, reparación de goteras y presupuestos. ¿Me cuentas un poco más de tu tejado?',
        };
    }
}
