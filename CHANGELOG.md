# Changelog

All notable changes to `laravel-view-analyzer` will be documented in this file.

## [Unreleased]

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
