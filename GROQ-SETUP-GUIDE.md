# 🤖 Configurar Groq API para el Asistente IA

## 📋 Índice

1. [Crear cuenta en Groq](#1-crear-cuenta)
2. [Obtener API Key](#2-obtener-api-key)
3. [Configurar en el proyecto](#3-configurar-en-el-proyecto)
4. [Probar la conexión](#4-probar-la-conexion)
5. [Usar el asistente en el admin](#5-usar-el-asistente)
6. [Configurar otros proveedores (opcional)](#6-otros-proveedores)

---

## 1. Crear cuenta en Groq

1. Ve a **https://console.groq.com**
2. Click **"Sign Up"**
3. Regístrate con Google, GitHub o email
4. Verifica tu email

> ✅ **Groq es GRATIS** para uso moderado (hasta 30 requests/minuto)

---

## 2. Obtener API Key

1. Una vez logueado, ve a **https://console.groq.com/keys**
2. Click **"Create API Key"**
3. Ponle un nombre: `costa-resin-admin`
4. Copia la key (empieza con `gsk_`)

---

## 3. Configurar en el proyecto

Edita el archivo `.env` en la raíz del proyecto:

```env
# AI Providers
GROQ_API_KEY=gsk_tu_api_key_aqui
```

> ⚠️ **NUNCA** subas la API key a Git. El archivo `.env` ya está en `.gitignore`.

---

## 4. Probar la conexión

### Opción A: Script de prueba (recomendado)

```bash
cd costa-resin-website
php artisan ai:test
```

### Opción B: Tinker

```bash
php artisan tinker
```

```php
$result = app(App\Services\AiAssistantService::class)->generate(
    prompt: "Eres un copywriter de Costa Resin & Construction. Escribe una descripción de 2 líneas para el producto 'Diamond Coat': resina epoxica de dos componentes para suelos industriales.",
    provider: 'groq',
    model: 'llama-3.3-70b-versatile'
);
dd($result);
```

### Opción cURL directa

```bash
curl -X POST https://api.groq.com/openai/v1/chat/completions \
  -H "Authorization: Bearer gsk_tu_key" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "llama-3.3-70b-versatile",
    "messages": [{"role": "user", "content": "Hola, escribe una descripción de producto para Diamond Coat"}],
    "temperature": 0.7,
    "max_tokens": 256
  }'
```

---

## 5. Usar el asistente en el admin

1. Inicia el servidor: `php artisan serve`
2. Ve a **http://localhost:8000/admin**
3. Login: `admin@TU_DOMINIO.com` / `password`
4. Menú lateral → **Asistente IA** ✨

### Plantillas disponibles

| Plantilla | Uso |
|-----------|-----|
| 📝 Descripción de producto (ES) | Genera descripción profesional en español |
| 🇬🇧 Product Description (EN) | Genera descripción en inglés británico |
| 📰 Artículo de blog (ES) | Escribe artículos completos |
| 📧 Plantilla de email (ES) | Emails comerciales |
| 🔍 Meta tags SEO (ES) | Titles y descriptions para Google |
| 💬 Mensaje WhatsApp (ES) | Mensajes comerciales |
| 📋 Ficha técnica (ES) | Fichas técnicas de producto |
| 🔄 Traducir ES→EN | Traducción automática |
| 🔄 Traducir EN→ES | Traducción automática |

### Ejemplo de uso

1. Selecciona **Groq** como proveedor
2. Selecciona **Llama 3.3 70B** como modelo
3. Selecciona la plantilla **"Descripción de producto (ES)"**
4. Escribe: `Diamond Coat - Resina epoxica bicomponente para suelos industriales`
5. Click **"Generar"**
6. Copia el resultado al portapapeles

---

## 6. Otros proveedores (opcional)

### OpenAI (GPT-4)

1. Crea cuenta: https://platform.openai.com
2. Ve a API Keys: https://platform.openai.com/api-keys
3. Añade al `.env`:
   ```env
   OPENAI_API_KEY=sk-tu_key
   ```

### Anthropic (Claude)

1. Crea cuenta: https://console.anthropic.com
2. Ve a API Keys
3. Añade al `.env`:
   ```env
   ANTHROPIC_API_KEY=sk-ant-tu_key
   ```

### DeepSeek

1. Crea cuenta: https://platform.deepseek.com
2. Ve a API Keys
3. Añade al `.env`:
   ```env
   DEEPSEEK_API_KEY=sk-tu_key
   ```

---

## ⚠️ Notas importantes

- **Groq** es el más rápido (~200ms de respuesta)
- **OpenAI GPT-4o** es el más potente para textos largos
- **Claude** es mejor para análisis técnico
- **DeepSeek** es el más barato
- Puedes cambiar de proveedor en tiempo real desde el admin

---

## 🔗 Recursos

- [Groq Console](https://console.groq.com)
- [Groq API Docs](https://console.groq.com/docs/api-reference)
- [Modelos disponibles](https://console.groq.com/docs/models)
- [Límites gratuitos](https://console.groq.com/docs/rate-limits)
