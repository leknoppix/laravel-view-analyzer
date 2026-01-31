# Laravel View Analyzer

[![Dernière Version sur Packagist](https://img.shields.io/packagist/v/leknoppix/laravel-view-analyzer.svg?style=flat-square)](https://packagist.org/packages/leknoppix/laravel-view-analyzer)
[![Téléchargements Totaux](https://img.shields.io/packagist/dt/leknoppix/laravel-view-analyzer.svg?style=flat-square)](https://packagist.org/packages/leknoppix/laravel-view-analyzer)
[![Licence](https://img.shields.io/packagist/l/leknoppix/laravel-view-analyzer.svg?style=flat-square)](https://packagist.org/packages/leknoppix/laravel-view-analyzer)

Un package Laravel puissant pour analyser l'utilisation des vues dans votre application. Identifiez les templates Blade utilisés et inutilisés, détectez les vues orphelines et obtenez des informations sur l'architecture de vos vues.

## 🚀 Fonctionnalités

- ✅ **Analyse Complète** - Détecte les vues dans les contrôleurs, fichiers Blade, mailables, commandes, composants, routes et middlewares
- ✅ **Formats de Sortie Multiples** - Tableaux console, JSON, HTML et CSV
- ✅ **Détection Intelligente** - Gère `view()`, `View::make()`, `@extends`, `@include`, et les patterns Mailable Laravel 11+
- ✅ **Détection de Vues Inutilisées** - Trouvez les templates orphelins qui peuvent être supprimés en toute sécurité
- ✅ **Performance Optimisée** - Mise en cache intégrée pour les grands projets
- ✅ **Extensible** - Ajoutez des analyseurs personnalisés pour vos besoins spécifiques

## 📦 Installation

Installez via Composer :

```bash
composer require leknoppix/laravel-view-analyzer --dev
```

Publiez le fichier de configuration :

```bash
php artisan vendor:publish --tag=view-analyzer-config
```

## 🛠 Utilisation

### Analyser toutes les vues

Lancez une analyse complète des vues de votre application :

```bash
php artisan views:analyze
```

**Exemple de sortie :**
```
===========================================
Laravel View Analyzer - Rapport d'Analyse
===========================================

Résumé des Vues :
  Total Vues Trouvées : 382
  Vues Utilisées : 341
  Vues Inutilisées : 41
  Dynamique/Incertain : 12
```

### Lister les vues utilisées

Affichez toutes les vues référencées dans votre base de code :

```bash
php artisan views:used --show-locations
```

**Options :**
- `--type=controller|blade|mailable|all` - Filtrer par type de référence
- `--sort=name|count` - Ordre de tri
- `--min-references=N` - Nombre minimum de références
- `--show-locations` - Afficher les chemins de fichiers et numéros de ligne

### Trouver les vues inutilisées

Identifiez les templates orphelins :

```bash
php artisan views:unused --size --suggest-delete
```

**Options :**
- `--path=resources/views/admin` - Filtrer par chemin spécifique
- `--size` - Afficher la taille des fichiers
- `--suggest-delete` - Générer les commandes de suppression

### Exporter les rapports

Générez des rapports dans différents formats :

```bash
# Export JSON
php artisan views:analyze --format=json --output=report.json

# Rapport HTML
php artisan views:analyze --format=html --output=public/view-report.html

# Export CSV
php artisan views:analyze --format=csv --output=report.csv
```

## 🔍 Ce qui est analysé

Le package détecte les vues dans :

1. **Contrôleurs** - `view()`, `View::make()`, `response()->view()`
2. **Templates Blade** - `@extends`, `@include`, `@includeIf`, `@component`, `@each`
3. **Mailables** - Classe `Content` de Laravel 11+ et méthode legacy `view()`
4. **Notifications** - Méthodes `toMail` et `toMarkdown`
5. **Commandes** - Appels de vue dans les commandes Artisan
6. **Composants** - Méthode `render()` des composants basés sur des classes
7. **Providers** - `Paginator::defaultView()` et `defaultSimpleView()`
8. **Routes** - Définitions `Route::view()`
9. **Middleware** - Appels de vue et `view()->share()`

## ⚙️ Configuration

Editez `config/view-analyzer.php` pour personnaliser :

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

## 🚀 Utilisation Avancée

### Utilisation Programmatique

```php
use LaravelViewAnalyzer\ViewAnalyzer;

$analyzer = new ViewAnalyzer(config('view-analyzer'));
$result = $analyzer->analyze();

// Obtenir les vues utilisées
$usedViews = $result->usedViews;

// Obtenir les vues inutilisées
$unusedViews = $result->unusedViews;

// Obtenir les statistiques
$stats = $result->statistics;
```

### Analyseurs Personnalisés

Étendez le package avec vos propres analyseurs :

```php
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use Illuminate\Support\Collection;

class InertiaAnalyzer implements AnalyzerInterface
{
    public function analyze(): Collection
    {
        // Votre logique de détection personnalisée
    }

    public function getName(): string
    {
        return 'Analyseur Inertia';
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

// Enregistrer dans un service provider
$analyzer = app(ViewAnalyzer::class);
$analyzer->addAnalyzer(new InertiaAnalyzer);
```

## 📋 Prérequis

- PHP 8.3 ou supérieur
- Laravel 11.0 ou 12.0

## 📄 Licence

Licence MIT

## 👤 Auteur

**Pascal Canadas**

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à soumettre une Pull Request.

## support

Pour les problèmes et questions, veuillez utiliser le [suivi des problèmes GitHub](https://github.com/leknoppix/laravel-view-analyzer/issues).
