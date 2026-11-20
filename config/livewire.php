<?php

return [
    'class_namespace' => 'App\\Livewire',
    'view_path' => resource_path('views/livewire'),
    'layout' => 'layouts.app',

    'temporary_file_upload' => [
        'disk' => null,        // null = default (local)
        'rules' => 'file|max:10240',
        'directory' => null,   // null = default
        'middleware' => null,
        'preview_mimes' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        ],
        'max_upload_time' => 5, // minutes
        'cleanup' => true,
    ],
];
