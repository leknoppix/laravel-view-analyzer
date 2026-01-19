<?php

return [
    /*
    |--------------------------------------------------------------------------
    | View Paths
    |--------------------------------------------------------------------------
    |
    | Directories where Blade views are located. These paths will be scanned
    | to build the registry of available views.
    |
    */
    'view_paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scan Paths
    |--------------------------------------------------------------------------
    |
    | Directories to scan for view usage. These paths will be analyzed to
    | detect references to views in controllers, mailables, components, etc.
    |
    */
    'scan_paths' => [
        app_path('Http/Controllers'),
        app_path('Mail'),
        app_path('View/Components'),
        app_path('Http/Middleware'),
        base_path('routes'),
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude Paths
    |--------------------------------------------------------------------------
    |
    | Directories to exclude from scanning. These typically include vendor
    | packages, build artifacts, and cached files.
    |
    */
    'exclude_paths' => [
        'vendor',
        'node_modules',
        'storage',
        'bootstrap/cache',
        'public/build',
        'public/hot',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Patterns
    |--------------------------------------------------------------------------
    |
    | File patterns to match when scanning directories.
    |
    */
    'file_patterns' => [
        'php' => '*.php',
        'blade' => '*.blade.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Analyzers Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which analyzers to run and their priority order.
    | Lower priority numbers run first.
    |
    */
    'analyzers' => [
        'controller' => [
            'enabled' => true,
            'priority' => 10,
        ],
        'blade' => [
            'enabled' => true,
            'priority' => 20,
        ],
        'mailable' => [
            'enabled' => true,
            'priority' => 30,
        ],
        'component' => [
            'enabled' => true,
            'priority' => 40,
        ],
        'route' => [
            'enabled' => true,
            'priority' => 50,
        ],
        'middleware' => [
            'enabled' => true,
            'priority' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for analysis results. Caching significantly
    | improves performance on large projects.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour in seconds
        'driver' => env('VIEW_ANALYZER_CACHE_DRIVER', 'file'),
        'key_prefix' => 'view_analyzer_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Detection Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns used to detect view references in code.
    |
    */
    'patterns' => [
        'view_functions' => [
            'view(',
            'View::make(',
            'response()->view(',
        ],
        'blade_directives' => [
            '@extends',
            '@include',
            '@includeIf',
            '@includeWhen',
            '@includeUnless',
            '@includeFirst',
            '@component',
            '@each',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic View Handling
    |--------------------------------------------------------------------------
    |
    | Configuration for handling dynamically resolved views.
    |
    */
    'dynamic_views' => [
        'track_variables' => true,
        'confidence_threshold' => 0.7,
        'mark_uncertain' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for generated reports.
    |
    */
    'reports' => [
        'default_format' => 'table',
        'show_references' => false,
        'group_by_directory' => true,
        'max_results' => 1000,
    ],
];
