<div style="width: 100%; height: 500px; border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; background: #f9fafb;">
    <iframe 
        src="{{ $url }}" 
        style="width: 100%; height: 100%; border: 0;" 
        title="Vista previa del frontend"
        loading="lazy"
    ></iframe>
</div>
<div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
    <span style="font-size: 12px; color: #9ca3af;">
        💡 Si no se ve actualizado, recarga el iframe
    </span>
    <a href="{{ $url }}" target="_blank" style="font-size: 13px; color: #2563eb; text-decoration: underline; font-weight: 500;">
        🔗 Abrir en nueva pestaña
    </a>
</div>
