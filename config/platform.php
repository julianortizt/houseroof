<?php

return [
    'name' => env('PLATFORM_NAME', 'House Roof'),
    'public_name' => env('PLATFORM_PUBLIC_NAME', 'House Roof'),
    'developer_email' => env('SUPER_ADMIN_EMAIL') ?: 'julianortizt@gmail.com',
    'styles' => [
        'professional' => [
            'label' => 'Profesional',
            'primary' => '#d97706',
            'primary_hover' => '#b45309',
            'surface' => '#ffffff',
            'canvas' => '#f9fafb',
            'text' => '#111827',
            'muted' => '#4b5563',
        ],
        'modern_dark' => [
            'label' => 'Oscuro moderno',
            'primary' => '#0891b2',
            'primary_hover' => '#0e7490',
            'surface' => '#111827',
            'canvas' => '#0b1120',
            'text' => '#f9fafb',
            'muted' => '#cbd5e1',
        ],
        'portfolio' => [
            'label' => 'Portafolio',
            'primary' => '#7c3aed',
            'primary_hover' => '#6d28d9',
            'surface' => '#ffffff',
            'canvas' => '#f5f3ff',
            'text' => '#1f2937',
            'muted' => '#4b5563',
        ],
        'minimal' => [
            'label' => 'Minimalista',
            'primary' => '#0f766e',
            'primary_hover' => '#115e59',
            'surface' => '#ffffff',
            'canvas' => '#f8fafc',
            'text' => '#0f172a',
            'muted' => '#475569',
        ],
    ],
    'default_home_template' => 'roof',
    'home_templates' => [
        'roof' => 'House Roof · Limpieza, mantenimiento y reparación de techos',
        'standard' => 'Estándar',
        'portfolio' => 'Portafolio primero',
        'services' => 'Servicios primero',
        'corporate' => 'Plataforma corporativa (alternativa)',
    ],
];
