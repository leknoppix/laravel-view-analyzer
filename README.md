# Laravel View Analyzer

A powerful Laravel package to analyze view usage in your application. Identify used and unused Blade templates, detect orphaned views, and gain insights into your view architecture.

## Features

- ✅ **Comprehensive Analysis** - Detects views in controllers, Blade files, mailables, components, routes, and middleware
- ✅ **Multiple Output Formats** - Console tables, JSON, HTML, and CSV reports
- ✅ **Smart Detection** - Handles `view()`, `View::make()`, `@extends`, `@include`, and Laravel 11+ Mailable patterns
- ✅ **Unused View Detection** - Find orphaned templates that can be safely removed
- ✅ **Performance Optimized** - Built-in caching for large projects
- ✅ **Extensible** - Add custom analyzers for your specific needs

## Installation

Install via Composer:

```bash
composer require leknoppix/laravel-view-analyzer --dev
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=view-analyzer-config
```

## Usage

### Analyze All Views

Run a complete analysis of your application's views:

```bash
php artisan views:analyze
```

**Example Output:**
```
===========================================
Laravel View Analyzer - Analysis Report
===========================================

Views Summary:
  Total Views Found: 382
  Used Views: 341
  Unused Views: 41
  Dynamic/Uncertain: 12
```

### List Used Views

Show all views that are referenced in your codebase:

```bash
php artisan views:used --show-locations
```

**Options:**
- `--type=controller|blade|mailable|all` - Filter by reference type
- `--sort=name|count` - Sort order
- `--min-references=N` - Minimum reference count
- `--show-locations` - Display file paths and line numbers

### Find Unused Views

Identify orphaned templates:

```bash
php artisan views:unused --size --suggest-delete
```

**Options:**
- `--path=resources/views/admin` - Filter by specific path
- `--size` - Show file sizes
- `--suggest-delete` - Generate delete commands

### Export Reports

Generate reports in different formats:

```bash
# JSON export
php artisan views:analyze --format=json --output=report.json

# HTML report
php artisan views:analyze --format=html --output=public/view-report.html

# CSV export
php artisan views:analyze --format=csv --output=report.csv
```

## What Gets Analyzed

The package detects views in:

1. **Controllers** - `view()`, `View::make()`, `response()->view()`
2. **Blade Templates** - `@extends`, `@include`, `@includeIf`, `@component`, `@each`
3. **Mailables** - Laravel 11+ `Content` class and legacy `view()` method
4. **Components** - Class-based components' `render()` method
5. **Routes** - `Route::view()` definitions
6. **Middleware** - View calls and `view()->share()`

## Configuration

Edit `config/view-analyzer.php` to customize:

```php
return [
    'view_paths' => [
        resource_path('views'),
    ],

    'scan_paths' => [
        app_path('Http/Controllers'),
        app_path('Mail'),
        app_path('View/Components'),
        app_path('Http/Middleware'),
        base_path('routes'),
        resource_path('views'),
    ],

    'exclude_paths' => [
        'vendor',
        'node_modules',
        'storage',
    ],

    'analyzers' => [
        'controller' => ['enabled' => true, 'priority' => 10],
        'blade' => ['enabled' => true, 'priority' => 20],
        'mailable' => ['enabled' => true, 'priority' => 30],
        'component' => ['enabled' => true, 'priority' => 40],
        'route' => ['enabled' => true, 'priority' => 50],
        'middleware' => ['enabled' => true, 'priority' => 60],
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
```

## Advanced Usage

### Programmatic Usage

```php
use LaravelViewAnalyzer\ViewAnalyzer;

$analyzer = new ViewAnalyzer(config('view-analyzer'));
$result = $analyzer->analyze();

// Get used views
$usedViews = $result->usedViews;

// Get unused views
$unusedViews = $result->unusedViews;

// Get statistics
$stats = $result->statistics;
```

### Custom Analyzers

Extend the package with your own analyzers:

```php
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use Illuminate\Support\Collection;

class InertiaAnalyzer implements AnalyzerInterface
{
    public function analyze(): Collection
    {
        // Your custom detection logic
    }

    public function getName(): string
    {
        return 'Inertia Analyzer';
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 100;
    }
}

// Register in a service provider
$analyzer = app(ViewAnalyzer::class);
$analyzer->addAnalyzer(new InertiaAnalyzer);
```

## Requirements

- PHP 8.3 or higher
- Laravel 11.0 or 12.0

## License

MIT License

## Author

**Leknoppix** - [Le Journal du Gers](https://lejournaldugers.fr)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues and questions, please use the [GitHub issue tracker](https://github.com/leknoppix/laravel-view-analyzer/issues).
