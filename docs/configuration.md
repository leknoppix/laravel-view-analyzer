# Guide de Configuration

Laravel View Analyzer est conçu pour fonctionner immédiatement, mais il offre des options de configuration flexibles pour s'adapter aux besoins de votre projet.

## Publication de la Configuration

Pour personnaliser le package, publiez d'abord le fichier de configuration :

```bash
php artisan vendor:publish --tag=view-analyzer-config
```

Cela créera un fichier `config/view-analyzer.php` dans votre application.

## Options Principales

### Chemins des Vues (View Paths)
Définissez où se trouvent vos templates Blade.

```php
'view_paths' => [
    resource_path('views'),
],
```

### Chemins d'Analyse (Scan Paths)
Définissez les répertoires qui doivent être scannés pour trouver des références aux vues.

```php
'scan_paths' => [
    app_path('Http/Controllers'),
    app_path('Mail'),
    app_path('Console'),
    app_path('View/Components'),
    app_path('Http/Middleware'),
    app_path('Providers'),
    app_path('Notifications'),
    base_path('routes'),
    resource_path('views'),
],
```

### Vues Ignorées (Ignored Views)
Empêchez certaines vues d'être signalées comme inutilisées. C'est crucial pour :
- Les vues des packages Vendor
- Les layouts utilisés dynamiquement
- Les pages d'erreur standard

```php
'ignored_views' => [
    'auth.*',
    'layouts.*',
    'errors.*',
    'vendor.*',
    'pagination.*',
],
```

## Interface Web

Le tableau de bord web vous permet de visualiser l'utilisation de vos vues.

```php
'web' => [
    'enabled' => true,
    'path' => 'admin/viewpackage', // Changez l'URL
    'middleware' => ['web', 'auth'], // Sécurisez la route
],
```

### Note de Sécurité
Il est fortement recommandé de garder le middleware `auth` activé ou de restreindre l'accès aux environnements locaux pour éviter d'exposer la structure interne de votre application.

## Analyseurs

Vous pouvez activer ou désactiver des analyseurs spécifiques pour améliorer les performances ou cibler des zones spécifiques.

```php
'analyzers' => [
    'controller' => ['enabled' => true, 'priority' => 10],
    'blade' => ['enabled' => true, 'priority' => 20],
    'mailable' => ['enabled' => true, 'priority' => 30],
    'notification' => ['enabled' => true, 'priority' => 32],
    'command' => ['enabled' => true, 'priority' => 35],
    'component' => ['enabled' => true, 'priority' => 40],
    'route' => ['enabled' => true, 'priority' => 50],
    'middleware' => ['enabled' => true, 'priority' => 60],
    'provider' => ['enabled' => true, 'priority' => 70],
],
```
