# Auditoría de contenido legal — 2026-11-15

> Resultado de auditoría de SOLO LECTURA. Ningún archivo fue modificado; la SQLite local se consultó en modo solo lectura. Las páginas legales **no están sembradas** en la BD local (`pages` = 0 filas), por lo que el sitio oculta los enlaces hasta sembrarlas.

## Contenido actual (database/seeders/PageSeeder.php)

Cuatro páginas bilingües ES/EN: `privacy-policy`, `cookie-policy`, `terms-of-sale`, `legal-notice`. Mezclan tres identidades:

1. **"ResinFlow"** (ficticia): Calle Principal 123, 28001 Madrid · `hello@resinflow.app` · `+34 900 000 000`.
2. **"Fortis Resin Systems"**: `fortisresin.es` · C/ industrial s/n, 03720 Benissa (Alicante) · `info@fortisresin.es` · `+34 654 195 493` · fuero Alicante (terms-of-sale). PENDIENTE CONFIRMAR: los propios documentos internos indican que estos datos **no están verificados**.
3. **"Infinity Effects"** (marca anterior, solo en documentación/legacy, no en el texto legal).

### Detalles por página

| Página | Idioma | Entidad indicada | Fuero | Contacto | Problemas |
|---|---|---|---|---|---|
| privacy-policy | ES | ResinFlow | no indica (base RGPD sí) | hello@resinflow.app, +34 900 000 000, Madrid | Placeholder; versión EN incompleta (no equivale) |
| privacy-policy | EN | ResinFlow | no indica | hello@resinflow.app | Contenido EN muy reducido |
| cookie-policy | ES | no indica | no indica | hello@resinflow.app | Google Analytics/Stripe sin base jurídica detallada; EN sin contacto |
| cookie-policy | EN | no indica | no indica | ninguno | Incompleto |
| terms-of-sale | ES | Fortis Resin Systems (cuerpo dice "ResinFlow", l. 131/157; desistimiento l. 146 apunta a hello@resinflow.app) | Alicante, legislación española | Benissa; info@fortisresin.es; +34 654 195 493; **CIF: [pendiente]** (l. 128) | Datos no verificados; garantía cita Directiva 1999/44/CE **derogada** (hoy: Directiva UE 2019/771, RDL 7/2021); envío gratis >500 € y desistimiento 14 días sin confirmar B2B/B2C |
| terms-of-sale | EN | ResinFlow | no indica | hello@resinflow.app | Esqueleto: faltan la mitad de las cláusulas |
| legal-notice | ES | ResinFlow (l. 178) pero "Fortis Resin Systems no garantiza…" (l. 191) | legislación española sin fuero | Madrid; hello@resinflow.app; +34 900 000 000; resinflow.app | Entidad LSSI incompleta (falta NIF, datos registrales) |
| legal-notice | EN | Fortis Resin Systems | no indica | Benissa; info@fortisresin.es | Distinta del ES; sin teléfono/web/NIF |

## Otras fuentes con datos de empresa

- `config/platform.php` (name/public_name → defaults ResinFlow; developer_email), `config/app.php` (APP_NAME), `config/services.php` (bank holder `ResinFlow`, IBAN `ES00…` placeholder, Bizum `654195493` hardcodeado).
- `database/seeders/SettingsSeeder.php` (site_* ficticios), `PopulateContentBlocksSeeder.php` (mezcla Benissa + hello@resinflow.app).
- `app/Services/JsonLdService.php`, `app/Services/QuotePdfService.php`, `app/Filament/Pages/Settings.php` (defaults ResinFlow), `resources/views/emails/*` (fallbacks), `lang/*.json` ("Fortis Resin Systems" → traducción "ResinFlow…").

## Preguntas pendientes para el titular (bloquean publicar)

1. Entidad jurídica y forma (autónomo/SL…), denominación exacta, NIF/CIF, datos registrales.
2. Domicilio social/fiscal para LSSI y RGPD: ¿Benissa o Madrid? ¿se publica completo?
3. Email/telefóno de contacto legal: ¿`info@fortisresin.es` / `+34 654 195 493`? ¿email específico de privacidad?
4. Fuero: ¿Alicante confirmado? ¿B2B (sumisión) o B2C (fuero consumidor)?
5. Marca definitiva: ¿«Fortis Resin Systems» o genérica? ¿Qué hacer con "ResinFlow", "Infinity Effects", infinityeffects.es, @infinityeffectsspain, «Spray Stone»?
6. Confirmar dirección/teléfono/email de Fortis (internamente no verificados) antes de publicarlos.
7. Datos de cobro reales (banco, IBAN, BANK_HOLDER, Bizum — ¿ese mismo teléfono?).
8. Cookies reales en uso (GA propiedad, Stripe, CMP/banner) y base jurídica.
9. Plazos: desistimiento 14 días, garantía RDL 7/2021, envío gratis 500 €, excepciones — y normativa española aplicable.
10. Idioma prevaleciente ES/EN (el EN está muy por detrás).
11. Plazos de conservación RGPD (¿6 años mercantiles / 4 fiscales?) y persona responsable/DPO.

## Conclusión

Las páginas legales son **plantilla con datos ficticios**; requieren reescritura por el titular (o su asesoría jurídica) y posterior re-seed. No basta con "rellenar huecos". Hasta entonces: **no publicar**.
