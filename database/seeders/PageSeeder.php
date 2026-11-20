<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title_es' => 'Política de Privacidad',
                'title_en' => 'Privacy Policy',
                'content_es' => '<h2>1. Responsable del tratamiento</h2>
<p><strong>House Roof</strong><br>
Calle Principal 123 — 28001 Madrid, España<br>
Email: hello@houseroof.com<br>
Teléfono: +34 900 000 000</p>

<h2>2. Datos que recogemos</h2>
<p>Recogemos los datos que usted nos facilita al solicitar un presupuesto, contratar un servicio de limpieza, mantenimiento o reparación de techos, o contactar con nosotros:</p>
<ul>
<li>Nombre y apellidos</li>
<li>Dirección de email</li>
<li>Número de teléfono</li>
<li>Dirección de la propiedad o cubierta a revisar</li>
<li>Detalles de la solicitud y documentación adjunta</li>
</ul>

<h2>3. Finalidad del tratamiento</h2>
<p>Utilizamos sus datos para:</p>
<ul>
<li>Atender solicitudes de presupuesto y visitas técnicas</li>
<li>Planificar y ejecutar los trabajos de limpieza, mantenimiento y reparación contratados</li>
<li>Enviar comunicaciones relacionadas con el servicio</li>
<li>Cumplir obligaciones legales, fiscales y de seguridad</li>
<li>Mejorar la calidad de nuestros servicios</li>
</ul>

<h2>4. Base legal</h2>
<p>El tratamiento se basa en la ejecución del contrato de servicios (art. 6.1.b RGPD), el consentimiento (art. 6.1.a), el cumplimiento de obligaciones legales (art. 6.1.c) y nuestro interés legítimo en gestionar la relación comercial (art. 6.1.f).</p>

<h2>5. Sus derechos</h2>
<p>Usted puede acceder, rectificar, suprimir, limitar u oponerse al tratamiento de sus datos, solicitar su portabilidad y retirar el consentimiento en cualquier momento, escribiendo a hello@houseroof.com.</p>

<h2>6. Conservación de datos</h2>
<p>Conservamos sus datos durante el tiempo necesario para prestar el servicio y cumplir las obligaciones legales aplicables, incluidos los plazos fiscales y de responsabilidad en obras.</p>

<h2>7. Seguridad</h2>
<p>Aplicamos medidas técnicas y organizativas adecuadas, incluyendo cifrado SSL, control de accesos y copias de seguridad, para proteger su información.</p>

<h2>8. Contacto</h2>
<p>Para cualquier consulta sobre privacidad, écritanos a hello@houseroof.com.</p>',
                'content_en' => '<h2>1. Data Controller</h2>
<p><strong>House Roof</strong><br>
Calle Principal 123 — 28001 Madrid, Spain<br>
Email: hello@houseroof.com<br>
Phone: +34 900 000 000</p>

<h2>2. Data We Collect</h2>
<p>We collect the details you provide when requesting a quote, booking a roof cleaning, maintenance or repair service, or contacting us: name, email, phone number, property address and the details of your enquiry.</p>

<h2>3. Purpose</h2>
<p>We use your data to answer quote requests, arrange site visits, deliver the contracted roofing services, send service communications and comply with legal obligations.</p>

<h2>4. Legal Basis</h2>
<p>Processing is based on performance of a service contract, consent, compliance with legal obligations and our legitimate interest in managing the customer relationship.</p>

<h2>5. Your Rights</h2>
<p>You may access, rectify, erase, restrict or object to the processing of your data, request portability and withdraw consent at any time by writing to hello@houseroof.com.</p>

<h2>6. Contact</h2>
<p>For any privacy enquiry, contact hello@houseroof.com.</p>',
            ],
            [
                'slug' => 'cookie-policy',
                'title_es' => 'Aviso de Cookies',
                'title_en' => 'Cookie Policy',
                'content_es' => '<h2>¿Qué son las cookies?</h2>
<p>Las cookies son pequeños archivos de texto que se almacenan en su dispositivo cuando visita nuestro sitio web y nos ayudan a mejorar su experiencia.</p>

<h2>Tipos de cookies que utilizamos</h2>
<ul>
<li><strong>Cookies necesarias:</strong> imprescindibles para el funcionamiento del sitio y la seguridad de la sesión.</li>
<li><strong>Cookies de análisis:</strong> nos ayudan a entender cómo se utiliza el sitio.</li>
<li><strong>Cookies de preferencias:</strong> recuerdan el idioma y sus ajustes de visualización.</li>
</ul>

<h2>Gestión de cookies</h2>
<p>Puede aceptar o rechazar las cookies no esenciales a través de nuestro banner de consentimiento y modificar su decisión en cualquier momento desde la configuración de su navegador.</p>

<h2>Contacto</h2>
<p>Para más información, contáctenos en hello@houseroof.com.</p>',
                'content_en' => '<h2>What are cookies?</h2>
<p>Cookies are small text files stored on your device when you visit our website to improve your browsing experience.</p>

<h2>Types of cookies we use</h2>
<ul><li><strong>Necessary cookies:</strong> required for site functionality and session security.</li><li><strong>Analytics cookies:</strong> help us understand how the site is used.</li><li><strong>Preference cookies:</strong> remember your language and display settings.</li></ul>

<h2>Managing cookies</h2>
<p>You can accept or reject non-essential cookies through our consent banner and change your decision at any time in your browser settings.</p>

<h2>Contact</h2>
<p>For more information, contact hello@houseroof.com.</p>',
            ],
            [
                'slug' => 'terms-of-sale',
                'title_es' => 'Condiciones de Servicio',
                'title_en' => 'Terms of Service',
                'content_es' => '<h2>1. Identificación del prestador</h2>
<p><strong>House Roof</strong><br>
Calle Principal 123 — 28001 Madrid, España<br>
Email: hello@houseroof.com<br>
Teléfono: +34 900 000 000</p>

<h2>2. Servicios</h2>
<p>House Roof ofrece servicios de limpieza de techos, mantenimiento preventivo, reparación de cubiertas y goteras, inspección técnica, impermeabilización y atención de urgencias para clientes residenciales, comerciales e industriales en Europa.</p>

<h2>3. Presupuestos</h2>
<p>Los presupuestos son personalizados, se basan en la información facilitada y, cuando es necesario, en una visita técnica. Indican el alcance, los materiales, el plazo estimado y el precio. Son válidos durante 30 días desde su emisión.</p>

<h2>4. Ejecución de los trabajos</h2>
<p>Los trabajos se ejecutan con personal cualificado y conforme a la normativa aplicable de seguridad y construcción. Cualquier modificación del alcance se acordará por escrito antes de su realización.</p>

<h2>5. Precios y pago</h2>
<p>Los precios se expresan en euros e incluyen los impuestos aplicables. El pago se realiza según las condiciones indicadas en el presupuesto o factura.</p>

<h2>6. Garantía y incidencias</h2>
<p>Los trabajos realizados cuentan con la garantía legal aplicable. Para comunicar una incidencia, contacte con hello@houseroof.com indicando la referencia del servicio.</p>

<h2>7. Cancelación</h2>
<p>Las visitas y trabajos programados pueden cancelarse con al menos 48 horas de antelación. La cancelación posterior podrá generar cargos por los gastos ya incurridos.</p>

<h2>8. Legislación aplicable</h2>
<p>Estas condiciones se rigen por la legislación española y, en su caso, por la normativa europea aplicable a los servicios contratados.</p>',
                'content_en' => '<h2>1. Service Provider</h2>
<p><strong>House Roof</strong><br>
Calle Principal 123 — 28001 Madrid, Spain<br>
Email: hello@houseroof.com<br>
Phone: +34 900 000 000</p>

<h2>2. Services</h2>
<p>House Roof provides roof cleaning, preventive maintenance, roof and leak repair, technical inspection, waterproofing and emergency call-outs for residential, commercial and industrial clients across Europe.</p>

<h2>3. Quotes</h2>
<p>Quotes are tailored to the information provided and, where necessary, to a site visit. They state the scope, materials, estimated timeframe and price, and remain valid for 30 days.</p>

<h2>4. Performance</h2>
<p>Work is carried out by qualified staff in line with applicable health, safety and building regulations. Any change of scope is agreed in writing before it is carried out.</p>

<h2>5. Prices and Payment</h2>
<p>Prices are stated in euros and include applicable taxes. Payment is made according to the terms in the quote or invoice.</p>

<h2>6. Warranty and Issues</h2>
<p>Work carried out benefits from the applicable legal warranty. Report any issue to hello@houseroof.com quoting the service reference.</p>

<h2>7. Cancellation</h2>
<p>Scheduled visits and work may be cancelled with at least 48 hours notice. Later cancellations may incur charges for costs already incurred.</p>',
            ],
            [
                'slug' => 'legal-notice',
                'title_es' => 'Aviso Legal',
                'title_en' => 'Legal Notice',
                'content_es' => '<h2>Información del titular del sitio web</h2>
<p><strong>House Roof</strong><br>
Calle Principal 123 — 28001 Madrid, España<br>
Email: hello@houseroof.com<br>
Teléfono: +34 900 000 000<br>
Web: houseroof.com</p>

<h2>Condiciones de uso</h2>
<p>El acceso y uso de este sitio atribuye la condición de usuario e implica la aceptación de las condiciones publicadas en esta página. Los contenidos tienen fines informativos sobre nuestros servicios de limpieza, mantenimiento y reparación de techos.</p>

<h2>Propiedad intelectual</h2>
<p>Todos los contenidos de este sitio (textos, imágenes, logotipos y gráficos) están protegidos por las leyes de propiedad intelectual. Queda prohibida su reproducción total o parcial sin autorización expresa.</p>

<h2>Exclusión de responsabilidad</h2>
<p>House Roof no garantiza la disponibilidad continua del sitio ni se hace responsable de daños derivados de errores en los contenidos informativos. La información técnica facilitada no sustituye una inspección profesional de la cubierta.</p>

<h2>Legislación aplicable</h2>
<p>Las presentes condiciones se rigen por la legislación española y la normativa europea aplicable.</p>',
                'content_en' => '<h2>Website Owner Information</h2>
<p><strong>House Roof</strong><br>
Calle Principal 123 — 28001 Madrid, Spain<br>
Email: hello@houseroof.com<br>
Phone: +34 900 000 000<br>
Web: houseroof.com</p>

<h2>Terms of Use</h2>
<p>Accessing and using this website means you accept the conditions published on this page. The content provides information about our roof cleaning, maintenance and repair services.</p>

<h2>Intellectual Property</h2>
<p>All content on this site (text, images, logos and graphics) is protected by intellectual property law. Reproduction in whole or in part without express authorisation is prohibited.</p>

<h2>Disclaimer</h2>
<p>House Roof does not guarantee uninterrupted availability of the website and is not liable for damage arising from errors in informational content. Technical information does not replace a professional roof inspection.</p>',
            ],
        ];

        foreach ($pages as $data) {
            $page = Page::updateOrCreate(
                ['slug' => $data['slug']],
                ['is_active' => true]
            );

            $page->setTranslation('title', 'es', $data['title_es']);
            $page->setTranslation('title', 'en', $data['title_en']);
            $page->setTranslation('content', 'es', $data['content_es']);
            $page->setTranslation('content', 'en', $data['content_en'] ?? '');
            $page->save();
        }
    }
}
