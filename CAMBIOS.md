# Registro de cambios

## 2026-08-23

- Corregida la integración del slider: la home consume la configuración del panel y resuelve imágenes del disco público.
- Corregido el guardado de imágenes de Site Settings y la caché de Content Blocks.
- Añadidas traducciones inglesas para hero, badges y CTA del home.
- Corregido el consentimiento de cookies para Livewire 3.
- Corregido el almacenamiento y edición de imágenes de proyectos.
- Eliminadas llamadas a conversiones Media Library inexistentes (`large`) que provocaban HTTP 500.
- Corregidos los enlaces de previsualización de páginas legales a `/legal/...`.
- Añadido selector visible de tema claro/oscuro en Filament.
- Aumentados los logos del frontend y del panel.
- Configurado el filesystem público y cache basado en archivos para Hostinger.
- Aislados los tests en SQLite en memoria para evitar modificar MySQL local.
- Añadido `install-hostinger.sh` para despliegue guiado en hosting compartido.
- Añadido este manual y la guía de administración.

## Validación

- Tests: 2 pasados, 5 aserciones.
- Home: HTTP 200.
- About Us: HTTP 200.
- Ficha de proyecto: HTTP 200.
- Build Vite: correcto.
- Cachés de Laravel: correctas.
