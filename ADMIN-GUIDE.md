# Manual del panel administrativo

## Acceso

URL local: `http://127.0.0.1:8000/admin`

En Hostinger: `https://tu-dominio.com/admin`

El panel usa inglés como idioma principal. El selector de idioma está en la barra superior y permite cambiar a español.

## Dónde editar cada página

| Página pública | Lugar en el panel | Observación |
|---|---|---|
| Home | Content > Site Content y Settings > Site Settings | Slider, CTA y bloques de contenido |
| About Us | Content > Site Content, filtrando `about` | La estructura visual está en la vista About Us; los textos disponibles se editan como bloques |
| Projects | Content > Projects | Crear título, descripción, ubicación, estado, imagen y video externo |
| Blog | Content > Blog Posts | Crear publicaciones, imágenes y estado publicado |
| Services | Content > Pages o Content > Site Content | Depende de si el texto pertenece a una página o a un bloque |
| Privacy Policy | Content > Pages > `privacy-policy` | URL pública: `/legal/privacy-policy` |
| Cookie Policy | Content > Pages > `cookie-policy` | URL pública: `/legal/cookie-policy` |
| Terms of Sale | Content > Pages > `terms-of-sale` | URL pública: `/legal/terms-of-sale` |
| Legal Notice | Content > Pages > `legal-notice` | URL pública: `/legal/legal-notice` |

Si una página legal no aparece en Pages, primero hay que crearla con el slug exacto indicado. No se debe cambiar el slug después de publicar enlaces.

## Qué significa Content Blocks

Un Content Block es una pieza de contenido independiente guardada en la tabla `content_blocks`. Cada registro se identifica por:

- `Page`: página lógica, por ejemplo `home` o `about`.
- `Section`: zona de la página, por ejemplo `hero`, `story`, `values` o `cta`.
- `Key`: campo concreto, por ejemplo `slide_1_title` o `subtitle`.
- `Locale`: idioma del contenido, normalmente `en` o `es`.
- `Type`: texto, rich text, imagen, video o JSON.
- `Value`: el contenido real que verá el visitante.

Ejemplo:

```text
Page: home
Section: hero
Key: slide_1_title
Locale: en
Type: text
Value: Factory in Spain
```

Para evitar mezclas de idioma, crea una versión `en` y otra `es` del mismo bloque. El frontend en inglés prioriza `en`.

## Projects

La primera imagen del proyecto se muestra en la tarjeta. Las imágenes se almacenan en Media Library. Para video se recomienda usar una URL de YouTube o Vimeo, no subir archivos grandes a Hostinger.

La ubicación puede ser una ciudad o zona general, por ejemplo `Benissa, Alicante`; no es necesario publicar una dirección privada.

## Usuarios

Existen tres cuentas administrativas con permisos `super_admin`: Developer, Paul Noble y Cristian Quiñonez. Cambia las contraseñas temporales en el primer acceso.
