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
<p><strong>Costa Resin & Construction</strong><br>
Calle Principal 123 — 28001 Madrid, España<br>
Email: hello@TU_DOMINIO.com<br>
Teléfono: +34 900 000 000</p>

<h2>2. Datos que recogemos</h2>
<p>Recogemos los siguientes datos personales cuando usted utiliza nuestro sitio web o realiza una compra:</p>
<ul>
<li>Nombre y apellidos</li>
<li>Dirección de email</li>
<li>Número de teléfono</li>
<li>Dirección postal (envío y facturación)</li>
<li>NIF/CIF (para facturación B2B)</li>
<li>Datos de pago (procesados por Stripe, no almacenados en nuestros servidores)</li>
</ul>

<h2>3. Finalidad del tratamiento</h2>
<p>Utilizamos sus datos para:</p>
<ul>
<li>Procesar y gestionar sus pedidos</li>
<li>Enviar comunicaciones sobre su pedido</li>
<li>Enviar nuestro boletín informativo (solo si usted se suscribe)</li>
<li>Cumplir obligaciones legales y fiscales</li>
<li>Mejorar nuestros servicios</li>
</ul>

<h2>4. Base legal</h2>
<p>El tratamiento se basa en:</p>
<ul>
<li>Ejecución de un contrato (art. 6.1.b RGPD)</li>
<li>Consentimiento del interesado (art. 6.1.a RGPD)</li>
<li>Cumplimiento de obligaciones legales (art. 6.1.c RGPD)</li>
<li>Interés legítimo del responsable (art. 6.1.f RGPD)</li>
</ul>

<h2>5. Sus derechos</h2>
<p>Usted tiene derecho a:</p>
<ul>
<li>Acceder a sus datos personales</li>
<li>Rectificar datos inexactos</li>
<li>Solicitar la eliminación de sus datos</li>
<li>Oponerse al tratamiento</li>
<li>Solicitar la portabilidad de datos</li>
<li>Retirar el consentimiento en cualquier momento</li>
</ul>

<h2>6. Conservación de datos</h2>
<p>Los datos se conservarán durante el tiempo necesario para cumplir la finalidad para la que fueron recogidos y, en todo caso, durante los plazos legalmente establecidos (6 años para documentos mercantiles, 4 años para obligaciones fiscales).</p>

<h2>7. Seguridad</h2>
<p>Implementamos medidas técnicas y organizativas adecuadas para proteger sus datos personales, incluyendo cifrado SSL y acceso restringido.</p>

<h2>8. Contacto</h2>
<p>Para ejercer sus derechos o para cualquier consulta sobre esta política, contáctenos en hello@TU_DOMINIO.com</p>',
                'content_en' => '<h2>1. Data Controller</h2>
<p><strong>Costa Resin & Construction</strong><br>
Calle Principal 123 — 28001 Madrid, Spain<br>
Email: hello@TU_DOMINIO.com</p>

<h2>2. Data We Collect</h2>
<p>We collect personal data when you use our website or make a purchase:</p>
<ul><li>Name and surname</li><li>Email address</li><li>Phone number</li><li>Postal address</li><li>Tax ID (for B2B invoicing)</li><li>Payment data (processed by Stripe)</li></ul>

<h2>3. Purpose</h2>
<p>We use your data to process orders, send communications, and comply with legal obligations.</p>

<h2>4. Your Rights</h2>
<p>You have the right to access, rectify, delete, and port your personal data, and to object to its processing.</p>',
            ],
            [
                'slug' => 'cookie-policy',
                'title_es' => 'Aviso de Cookies',
                'title_en' => 'Cookie Policy',
                'content_es' => '<h2>¿Qué son las cookies?</h2>
<p>Las cookies son pequeños archivos de texto que se almacenan en su dispositivo cuando visita nuestro sitio web. Nos ayudan a mejorar su experiencia de navegación.</p>

<h2>Tipos de cookies que utilizamos</h2>
<ul>
<li><strong>Cookies necesarias:</strong> Imprescindibles para el funcionamiento del sitio (carrito de compras, sesión).</li>
<li><strong>Cookies de análisis:</strong> Nos ayudan a entender cómo utiliza el sitio (Google Analytics).</li>
<li><strong>Cookies de marketing:</strong> Utilizadas para mostrar publicidad relevante.</li>
</ul>

<h2>Cookies de terceros</h2>
<p>Nuestro sitio utiliza cookies de los siguientes proveedores:</p>
<ul>
<li>Google Analytics (análisis)</li>
<li>Stripe (procesamiento de pagos)</li>
</ul>

<h2>Gestión de cookies</h2>
<p>Puede aceptar o rechazar las cookies no esenciales a través de nuestro banner de consentimiento. También puede configurar su navegador para bloquear cookies.</p>

<h2>Contacto</h2>
<p>Para más información, contáctenos en hello@TU_DOMINIO.com</p>',
                'content_en' => '<h2>What are cookies?</h2>
<p>Cookies are small text files stored on your device when you visit our website.</p>

<h2>Types of cookies we use</h2>
<ul><li><strong>Necessary cookies:</strong> Essential for site functionality.</li><li><strong>Analytics cookies:</strong> Help us understand site usage.</li><li><strong>Marketing cookies:</strong> Used for relevant advertising.</li></ul>',
            ],
            [
                'slug' => 'terms-of-sale',
                'title_es' => 'Condiciones de Venta',
                'title_en' => 'Terms of Sale',
                'content_es' => '<h2>1. Identificación del vendedor</h2>
<p><strong>Costa Resin & Construction</strong><br>
C/ industrial, s/n — 03720 Benissa (Alicante), España<br>
Email: info@TU_DOMINIO.com<br>
Teléfono: +34 654 195 493<br>
CIF: [pendiente de asignación]</p>

<h2>2. Productos</h2>
<p>Todos los productos mostrados en este sitio web son fabricados y distribuidos por Costa Resin & Construction. Las imágenes son orientativas y pueden variar ligeramente del producto real.</p>

<h2>3. Precios</h2>
<p>Los precios incluyen IVA (21%) para envíos dentro de España peninsular. Los precios para envíos a otros países de la UE incluyen IVA español. El cliente es responsable de posibles aranceles o impuestos de importación en su país.</p>

<h2>4. Formas de pago</h2>
<p>Aceptamos pago con tarjeta de crédito/débito a través de Stripe (compatible con PSD2/SCA). Todos los pagos son seguros y están encriptados.</p>

<h2>5. Envíos</h2>
<p>Realizamos envíos a toda la Unión Europea. El plazo de entrega estimado es de 3-5 días laborables para España peninsular y de 5-10 días laborables para el resto de la UE. El envío es gratuito para pedidos superiores a 500€ en España peninsular.</p>

<h2>6. Derecho de desistimiento</h2>
<p>Conforme a la legislación europea (Directiva 2011/83/UE), el consumidor dispone de un plazo de <strong>14 días naturales</strong> para devolver un producto sin necesidad de justificación. El plazo comienza desde la recepción del producto.</p>
<p>Para ejercer el derecho de desistimiento:</p>
<ul>
<li>Notifíquelo por email a hello@TU_DOMINIO.com indicando el número de pedido</li>
<li>El producto debe estar sin usar y en su embalaje original</li>
<li>Los gastos de devolución corren por cuenta del cliente</li>
<li>El reembolso se realizará en un plazo máximo de 14 días desde la recepción de la devolución</li>
</ul>
<p><strong>Excepciones:</strong> No se admiten devoluciones de productos personalizados, mezclados o cortados a medida.</p>

<h2>7. Garantía</h2>
<p>Todos nuestros productos tienen una garantía legal de conformidad de 2 años (Directiva 1999/44/CE). En caso de defecto de fabricación, procederemos a la reparación, sustitución o reembolso del producto.</p>

<h2>8. Responsabilidad</h2>
<p>Costa Resin & Construction no se responsabiliza de daños derivados de una mala aplicación de los productos. Recomendamos seguir siempre las instrucciones de uso y aplicación.</p>

<h2>9. Protección de datos</h2>
<p>Consulte nuestra <a href="/legal/privacy-policy">Política de Privacidad</a> para información sobre el tratamiento de sus datos personales.</p>

<h2>10. Legislación aplicable</h2>
<p>Estas condiciones se rigen por la legislación española. Para cualquier controversia, las partes se someten a los juzgados y tribunales de Alicante.</p>',
                'content_en' => '<h2>1. Seller Identification</h2>
<p><strong>Costa Resin & Construction</strong><br>Calle Principal 123 — 28001 Madrid, Spain<br>Email: hello@TU_DOMINIO.com</p>

<h2>6. Right of Withdrawal</h2>
<p>Under EU law, consumers have <strong>14 calendar days</strong> to return a product without justification.</p>

<h2>7. Warranty</h2>
<p>All products have a legal conformity warranty of 2 years.</p>',
            ],
            [
                'slug' => 'legal-notice',
                'title_es' => 'Aviso Legal',
                'title_en' => 'Legal Notice',
                'content_es' => '<h2>Información del titular del sitio web</h2>
<p><strong>Costa Resin & Construction</strong><br>
Calle Principal 123 — 28001 Madrid, España<br>
Email: hello@TU_DOMINIO.com<br>
Teléfono: +34 900 000 000<br>
Web: TU_DOMINIO.com</p>

<h2>Condiciones de uso</h2>
<p>El acceso y uso de este sitio web atribuye la condición de Usuario e implica la aceptación plena y sin reservas de todas las condiciones de uso publicadas en esta página.</p>

<h2>Propiedad intelectual e industrial</h2>
<p>Todos los contenidos de este sitio web (textos, imágenes, gráficos, logotipos, software, etc.) están protegidos por las leyes de propiedad intelectual e industrial. Queda prohibida su reproducción total o parcial sin autorización expresa.</p>

<h2>Exclusión de responsabilidad</h2>
<p>Costa Resin & Construction no garantiza la disponibilidad continua del sitio web ni se hace responsable de daños que puedan derivarse de errores en los contenidos.</p>

<h2>Legislación aplicable</h2>
<p>Las presentes condiciones se rigen por la legislación española.</p>',
                'content_en' => '<h2>Website Owner Information</h2>
<p><strong>Costa Resin & Construction</strong><br>C/ industrial, s/n — 03720 Benissa (Alicante), Spain<br>Email: info@TU_DOMINIO.com</p>',
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
