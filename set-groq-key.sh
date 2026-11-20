#!/bin/bash
# ============================================================
# 🔑 Configurar Groq API Key - Costa Resin & Construction
# ============================================================

echo ""
echo "╔══════════════════════════════════════════════════════╗"
echo "║   🔑 Configurar Groq API Key                       ║"
echo "╚══════════════════════════════════════════════════════╝"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ No se encontró el archivo .env"
    echo "   Ejecuta: cp .env.example .env"
    exit 1
fi

# Check if key is already configured
CURRENT_KEY=$(grep "^GROQ_API_KEY=" .env | cut -d'=' -f2)
if [ -n "$CURRENT_KEY" ] && [ "$CURRENT_KEY" != "" ]; then
    echo "✅ Ya tienes una API key configurada"
    echo "   Key: ${CURRENT_KEY:0:10}..."
    echo ""
    read -p "¿Quieres reemplazarla? (s/n): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        echo "Cancelado."
        exit 0
    fi
fi

echo "📝 Obtén tu API key en: https://console.groq.com/keys"
echo ""
read -p "Pega tu API key aquí: " GROQ_KEY

if [ -z "$GROQ_KEY" ]; then
    echo "❌ No se introdujo ninguna key"
    exit 1
fi

# Update .env
if grep -q "^GROQ_API_KEY=" .env; then
    sed -i "s/^GROQ_API_KEY=.*/GROQ_API_KEY=$GROQ_KEY/" .env
else
    echo "GROQ_API_KEY=$GROQ_KEY" >> .env
fi

echo ""
echo "✅ API key configurada correctamente"
echo ""
echo "🧪 Para probar la conexión:"
echo "   php artisan ai:test"
echo ""
echo "🌐 Para usar en el admin:"
echo "   1. php artisan serve"
echo "   2. http://localhost:8000/admin"
echo "   3. Menú → Asistente IA"
echo ""
