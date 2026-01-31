# Guide de Dépannage

Problèmes courants et solutions lors de l'utilisation de Laravel View Analyzer.

## Erreur "Class not found"

Si vous rencontrez une erreur `Class "LaravelViewAnalyzer\ViewAnalyzerServiceProvider" not found`, cela signifie généralement que l'autoloader de composer n'est pas synchronisé.

**Solution :**
Exécutez la commande suivante pour régénérer l'autoloader :
```bash
composer dump-autoload
```

## Faux Positifs (Vues Inutilisées)

Si une vue est marquée comme inutilisée mais que vous savez qu'elle est utilisée :

1. **Utilisation Dynamique** : L'analyseur peut ne pas détecter les vues construites dynamiquement (ex: `'pages.' . $slug`).
   * *Correction* : Ajoutez ces vues au tableau `ignored_views` dans `config/view-analyzer.php`.

2. **Vues Vendor** : Vues publiées depuis des packages tiers.
   * *Correction* : Ajoutez `vendor.*` à `ignored_views`.

3. **Pagination** : Vues de pagination personnalisées.
   * *Correction* : Assurez-vous d'utiliser la version 1.0.1+ qui inclut le `ProviderAnalyzer`.

## Erreur 404 sur l'Interface Web

Si `/admin/viewpackage` renvoie une erreur 404 :

1. Assurez-vous que le package est installé.
2. Vérifiez si `view-analyzer.web.enabled` est défini sur `true` dans votre config.
3. Exécutez `php artisan optimize:clear` pour vider le cache des routes.

## Problèmes de Performance

Sur les très grands projets, l'analyse peut être lente.

**Astuces :**
- Activez la mise en cache dans `config/view-analyzer.php`.
- Excluez les répertoires inutiles dans `exclude_paths` (ex: `node_modules`, `storage`).
