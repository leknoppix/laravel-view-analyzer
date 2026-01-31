# Changelog

All notable changes to `laravel-view-analyzer` will be documented in this file.

## [1.0.1] - 2026-01-31

### Added
- **NotificationAnalyzer**: Support for detecting views used in `app/Notifications` (`toMail`, `toMarkdown`).
- **Tree View Report**: New `--format=tree` option for `views:controllers` command to visualize controller/view hierarchy.
- **Web Dashboard**: New interactive dashboard at `/admin/viewpackage` with charts and tree view.
- **CommandAnalyzer**: Support for detecting views used in Artisan commands.
- **ProviderAnalyzer**: Support for `Paginator::defaultView` in ServiceProviders.

### Improved
- **Pattern Matching**: Enhanced detection for Laravel 11 mailables and dynamic components.
- **Performance**: Optimized file scanning and pattern matching regex.
- **Reporting**: Better handling of dynamic view references in JSON and HTML reports.

## [1.0.0] - 2026-01-19

### Added
- Initial release
- Controller analyzer for `view()` and `View::make()` calls
- Blade analyzer for `@extends`, `@include`, `@component` directives
- Mailable analyzer supporting Laravel 11+ `Content` class
- Component analyzer for class-based and anonymous components
- Route analyzer for `Route::view()` definitions
- Middleware analyzer for view calls
- Console, JSON, HTML, and CSV report formats
- Caching system for improved performance
- Three Artisan commands: `views:analyze`, `views:used`, `views:unused`
- Comprehensive documentation
- MIT License

### Features
- Detects 382+ views in large Laravel projects
- Identifies orphaned/unused views
- Smart dynamic view detection
- Extensible architecture with custom analyzer support
- Performance optimized with built-in caching

[Unreleased]: https://github.com/leknoppix/laravel-view-analyzer/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/leknoppix/laravel-view-analyzer/releases/tag/v1.0.0
