# Guide de Développement - Laravel View Analyzer

## 🔧 Configuration de l'Environnement de Développement

Ce package peut être développé selon deux modes:

---

## Mode 1: Package Intégré (Actuel)

### Configuration

Le package est directement dans le projet Laravel:
```
/home/public_html/lejournaldugersv3/packages/laravel-view-analyzer/
```

**Avantages:**
- ✅ Accessible depuis Docker sans configuration
- ✅ Modifications immédiates
- ✅ Pas de duplication

**Composer configuration:**
```json
"repositories": {
    "view-analyzer": {
        "type": "path",
        "url": "./packages/laravel-view-analyzer"
    }
}
```

### Commandes

```bash
# Tester le package
docker exec JDG32_php php artisan views:analyze

# Modifier le code
vim packages/laravel-view-analyzer/src/...

# Tests immédiats (pas de rebuild nécessaire)
docker exec JDG32_php php artisan views:analyze
```

---

## Mode 2: Package Externe (Avec Volume Docker)

### Configuration

Le package est à l'extérieur du projet:
```
/home/public_html/laravel/ViewPackage/
```

**Avantages:**
- ✅ Package réutilisable pour d'autres projets
- ✅ Séparation claire
- ✅ Potentiellement publiable sur Packagist

### Étapes de Migration vers Mode Externe

#### 1. Ajouter le volume Docker

Dans `compose.yml`, ajouter à la section `php.volumes`:
```yaml
php:
  volumes:
    - ./:/var/www
    - /home/uploads:/home/uploads
    - /run/media/leknoppix/ZIPPER:/run/media/leknoppix/ZIPPER
    - /home/public_html/laravel:/home/public_html/laravel  # Package ViewAnalyzer
```

#### 2. Redémarrer les conteneurs

```bash
docker-compose down
docker-compose up -d
```

#### 3. Déplacer le package

```bash
# Créer le répertoire externe
mkdir -p /home/public_html/laravel

# Déplacer le package
mv /home/public_html/lejournaldugersv3/packages/laravel-view-analyzer \
   /home/public_html/laravel/ViewPackage
```

#### 4. Créer un lien symbolique

```bash
cd /home/public_html/lejournaldugersv3/packages
ln -s /home/public_html/laravel/ViewPackage laravel-view-analyzer
```

#### 5. Mettre à jour Composer

Modifier `composer.json`:
```json
"repositories": {
    "view-analyzer": {
        "type": "path",
        "url": "/home/public_html/laravel/ViewPackage"
    }
}
```

Ou utiliser le lien symbolique (préféré):
```json
"repositories": {
    "view-analyzer": {
        "type": "path",
        "url": "./packages/laravel-view-analyzer"
    }
}
```

#### 6. Réinstaller

```bash
docker exec JDG32_php composer update leknoppix/laravel-view-analyzer
```

#### 7. Vérifier

```bash
docker exec JDG32_php ls -la /home/public_html/laravel/ViewPackage
docker exec JDG32_php php artisan views:analyze
```

---

## Développement et Tests

### Structure de Tests

```bash
# Lancer tous les tests
docker exec JDG32_php ./vendor/bin/phpunit packages/laravel-view-analyzer

# Lancer un test spécifique
docker exec JDG32_php ./vendor/bin/phpunit \
    packages/laravel-view-analyzer/tests/Unit/Analyzers/ControllerAnalyzerTest.php

# Tests avec couverture
docker exec JDG32_php ./vendor/bin/phpunit \
    --coverage-html packages/laravel-view-analyzer/coverage \
    packages/laravel-view-analyzer
```

### Formatage du Code

```bash
# Lancer Laravel Pint
docker exec JDG32_php ./vendor/bin/pint \
    packages/laravel-view-analyzer/src

# Vérifier sans modifier
docker exec JDG32_php ./vendor/bin/pint \
    --test packages/laravel-view-analyzer/src
```

### Analyse Statique

```bash
# PHPStan (si configuré)
docker exec JDG32_php ./vendor/bin/phpstan analyse \
    packages/laravel-view-analyzer/src
```

---

## Workflow de Développement Recommandé

### 1. Créer une Branche

```bash
cd /home/public_html/lejournaldugersv3
git checkout -b feature/view-analyzer-cache
```

### 2. Développer la Fonctionnalité

```bash
# Éditer les fichiers
vim packages/laravel-view-analyzer/src/Cache/AnalysisCache.php

# Tester immédiatement
docker exec JDG32_php php artisan views:analyze --cache
```

### 3. Ajouter des Tests

```bash
# Créer le test
vim packages/laravel-view-analyzer/tests/Unit/Cache/AnalysisCacheTest.php

# Lancer le test
docker exec JDG32_php ./vendor/bin/phpunit \
    packages/laravel-view-analyzer/tests/Unit/Cache
```

### 4. Formater et Valider

```bash
# Formatter le code
docker exec JDG32_php ./vendor/bin/pint \
    packages/laravel-view-analyzer/src/Cache

# Lancer tous les tests
docker exec JDG32_php ./vendor/bin/phpunit \
    packages/laravel-view-analyzer
```

### 5. Commit

```bash
git add packages/laravel-view-analyzer/
git commit -m "feat: implement analysis cache system"
```

---

## Débogage

### Activer le Mode Verbose

```bash
# Utiliser -vvv pour voir tous les détails
docker exec JDG32_php php artisan views:analyze -vvv
```

### Logs Laravel

```bash
# Voir les logs en temps réel
docker exec JDG32_php tail -f storage/logs/laravel.log

# Avec Laravel Pail (si installé)
docker exec JDG32_php php artisan pail
```

### Tinker pour Tests Rapides

```bash
docker exec JDG32_php php artisan tinker

# Dans Tinker:
$analyzer = new \LaravelViewAnalyzer\ViewAnalyzer(config('view-analyzer'));
$result = $analyzer->analyze();
dd($result->statistics);
```

### Ray pour Débogage Visuel

Si Ray est configuré:
```php
// Dans le code
ray($result)->label('Analysis Result');
ray()->measure();
```

---

## Publication du Package

### Préparation pour Packagist

#### 1. Créer un Repository Git Séparé

```bash
cd /home/public_html/laravel/ViewPackage
git init
git add .
git commit -m "Initial commit: Laravel View Analyzer v1.0.0"
```

#### 2. Créer un Repo GitHub

```bash
# Créer le repo sur GitHub: leknoppix/laravel-view-analyzer

# Ajouter le remote
git remote add origin git@github.com:leknoppix/laravel-view-analyzer.git
git branch -M main
git push -u origin main
```

#### 3. Créer un Tag de Version

```bash
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

#### 4. Publier sur Packagist

1. Aller sur https://packagist.org
2. Se connecter avec GitHub
3. Submit Package avec l'URL: `https://github.com/leknoppix/laravel-view-analyzer`
4. Configurer le hook GitHub pour auto-update

#### 5. Utilisation Publique

Après publication, installation classique:
```bash
composer require leknoppix/laravel-view-analyzer --dev
```

---

## Migration: Passage de Mode Intégré → Mode Externe

**Si vous décidez plus tard de passer en mode externe:**

```bash
# 1. Ajouter volume Docker (voir Mode 2 ci-dessus)
# 2. Redémarrer conteneurs
docker-compose down && docker-compose up -d

# 3. Déplacer le package
mkdir -p /home/public_html/laravel
cp -r /home/public_html/lejournaldugersv3/packages/laravel-view-analyzer \
     /home/public_html/laravel/ViewPackage

# 4. Supprimer l'ancien et créer lien symbolique
rm -rf /home/public_html/lejournaldugersv3/packages/laravel-view-analyzer
ln -s /home/public_html/laravel/ViewPackage \
      /home/public_html/lejournaldugersv3/packages/laravel-view-analyzer

# 5. Vérifier
docker exec JDG32_php php artisan views:analyze
```

---

## Dépannage

### Erreur: "Package not found"

```bash
# Vérifier la configuration Composer
docker exec JDG32_php composer config repositories

# Forcer la régénération de l'autoloader
docker exec JDG32_php composer dump-autoload
```

### Erreur: "Class not found"

```bash
# Vérifier le namespace dans composer.json du package
cat packages/laravel-view-analyzer/composer.json | grep psr-4

# Régénérer l'autoloader
docker exec JDG32_php composer dump-autoload
```

### Les modifications ne sont pas prises en compte

```bash
# Vérifier que le lien symbolique pointe bien
docker exec JDG32_php ls -la /var/www/vendor/leknoppix/laravel-view-analyzer

# Forcer la mise à jour
docker exec JDG32_php composer update leknoppix/laravel-view-analyzer

# Vider le cache Laravel
docker exec JDG32_php php artisan cache:clear
docker exec JDG32_php php artisan config:clear
```

### Volume Docker non accessible

```bash
# Vérifier les volumes montés
docker inspect JDG32_php | grep -A 20 Mounts

# Redémarrer le conteneur après modification compose.yml
docker-compose restart php
```

---

## Configuration Actuelle

**Mode:** Intégré
**Emplacement:** `/home/public_html/lejournaldugersv3/packages/laravel-view-analyzer/`
**Volume Docker additionnel:** ✅ Ajouté (`/home/public_html/laravel:/home/public_html/laravel`)
**Prêt pour migration:** ✅ Oui

**Date:** 19 janvier 2026
