<p align="center">
    <a href="https://github.com/leknoppix/laravel-view-analyzer" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel View Analyzer">
    </a>
</p>

<p align="center">
    <a href="https://github.com/leknoppix/laravel-view-analyzer/actions"><img src="https://img.shields.io/badge/build-passing-brightgreen.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/leknoppix/laravel-view-analyzer"><img src="https://img.shields.io/packagist/dt/leknoppix/laravel-view-analyzer" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/leknoppix/laravel-view-analyzer"><img src="https://img.shields.io/packagist/v/leknoppix/laravel-view-analyzer" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/leknoppix/laravel-view-analyzer"><img src="https://img.shields.io/packagist/l/leknoppix/laravel-view-analyzer" alt="License"></a>
    <a href="https://github.com/leknoppix/laravel-view-analyzer"><img src="https://img.shields.io/badge/coverage-97%25-brightgreen" alt="Test Coverage"></a>
</p>

## About Laravel View Analyzer

Laravel View Analyzer est un package puissant pour analyser l'utilisation des vues dans vos applications Laravel. Il permet d'identifier les templates Blade utilisés et inutilisés, de détecter les vues orphelines et d'obtenir des informations précises sur l'architecture de vos vues (y compris les chemins physiques complets).

Le package tente de simplifier la maintenance de vos vues en facilitant des tâches comme :

- [Détection automatique des vues inutilisées](#trouver-les-vues-inutilisées).
- [Analyse des références dans les contrôleurs, fichiers Blade, mailables, etc.](#-ce-qui-est-analysé)
- [Résolution des chemins absolus sur le disque](#documentation-technique).
- [Exports aux formats JSON, HTML et CSV](#exporter-les-rapports).

## Learning Laravel View Analyzer

Le plugin est conçu pour être prêt à l'emploi dès l'installation. Vous pouvez commencer par lancer une analyse complète avec `php artisan views:analyze` pour voir l'état actuel de votre projet. Pour des cas plus complexes, vous pouvez consulter la section [Configuration](#%EF%B8%8F-configuration).

## Contributing

Merci de considérer votre contribution au Laravel View Analyzer ! Le guide de contribution se trouve dans le fichier [CONTRIBUTING.md](CONTRIBUTING.md) (à venir).

## Code of Conduct

Afin de garantir que la communauté est accueillante pour tous, merci de consulter et de respecter le [Code de conduite](CODE_OF_CONDUCT.md).

## Security Vulnerabilities

Si vous découvrez une vulnérabilité de sécurité, merci d'envoyer un e-mail à contact@lejournaldugers.fr. Toutes les vulnérabilités de sécurité seront traitées rapidement.

## License

Le package Laravel View Analyzer est un logiciel libre sous licence [MIT](LICENSE).

---

# Documentation Technique

## 🚀 Fonctionnalités

- ✅ **Analyse Complète** - Détecte les vues dans les contrôleurs, fichiers Blade, mailables, commandes, composants, routes et middlewares.
- ✅ **Formats de Sortie Multiples** - Tableaux console, JSON, HTML et CSV.
- ✅ **Détection Intelligente** - Gère `view()`, `View::make()`, `@extends`, `@include`, et les patterns Mailable Laravel 11+.
- ✅ **Chemins Complets** - Affiche désormais le chemin physique absolu des fichiers pour toutes les vues détectées.
- ✅ **Performance Optimisée** - Mise en cache intégrée pour les grands projets.

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

### Lister les vues utilisées

Affichez toutes les vues référencées dans votre base de code :

```bash
php artisan views:used --show-locations
```

### Trouver les vues inutilisées

Identifiez les templates orphelins :

```bash
php artisan views:unused --size --suggest-delete
```

## ⚙️ Configuration

Le fichier `config/view-analyzer.php` permet de personnaliser les chemins de scan, les dossiers exclus et les analyseurs activés.

## 📋 Prérequis

- PHP 8.3 ou supérieur
- Laravel 11.0 ou 12.0
