# 🚀 Guide d'Installation - Module POS

## Étape 1: Vérifier les Routes

Vérifiez que les routes POS sont bien enregistrées dans `routes/web.php`:

```php
use App\Http\Controllers\POSController;

// POS Routes
Route::prefix('pos')->group(function () {
    Route::get('/meals', [POSController::class, 'getMeals']);
    Route::post('/create-order', [POSController::class, 'createOrder']);
    Route::get('/orders', [POSController::class, 'orders'])->name('pos.orders');
    Route::get('/orders/{id}', [POSController::class, 'orderDetail'])->name('pos.order-detail');
    Route::post('/orders/{id}/paid', [POSController::class, 'markAsPaid'])->name('pos.mark-paid');
    Route::post('/orders/{id}/cancel', [POSController::class, 'cancelOrder'])->name('pos.cancel');
});
```

## Étape 2: Charger les Données de Test

Exécutez le seeder pour créer les données de test:

```bash
# Run specific seeder
php artisan db:seed --class=POSSeeder

# Or run from DatabaseSeeder.php (add this line):
# $this->call(POSSeeder::class);
```

Cela créera:
- ✅ 4 catégories (Burgers, Pizzas, Sandwiches, Desserts)
- ✅ 5 plats avec prix
- ✅ 6 ingrédients/produits
- ✅ 4 recettes avec détails
- ✅ 1 stock principal avec quantités

## Étape 3: Vérifier les Modèles

Assurez-vous que tous les modèles ont les bonnes relations:

**Fichiers à vérifier:**
- `/app/Models/Meal.php` ✓
- `/app/Models/Category.php` ✓
- `/app/Models/Recipe.php` ✓
- `/app/Models/RecipeItem.php` ✓
- `/app/Models/Order.php` ✓
- `/app/Models/OrderItem.php` ✓
- `/app/Models/Stock.php` ✓
- `/app/Models/StockItem.php` ✓
- `/app/Models/Product.php` ✓
- `/app/Models/Unit.php` ✓
- `/app/Models/Accompaniment.php` ✓
- `/app/Models/StockMovement.php` ✓

## Étape 4: Tester le POS

1. **Accédez au POS:**
   ```
   http://localhost/complex_royal/modules/pos
   ```

2. **Testez les fonctionnalités:**
   - ✅ Sélectionner une catégorie
   - ✅ Ajouter des plats au panier
   - ✅ Ajuster les quantités
   - ✅ Créer une commande
   - ✅ Vérifier l'historique
   - ✅ Consulter les détails

3. **Vérifiez le stock:**
   - Consultez la table `stock_items`
   - Vérifiez que les quantités ont diminué après une vente

## Étape 5: Tests Manuels via SQL

### Après d'une vente (ex: 1 Burger Classique)
```sql
SELECT * FROM stock_items WHERE stock_id = 1;
-- Pain Burger: 100 - 2 = 98
-- Fromage: 50 - 1 = 49
-- Tomate: 80 - 0.5 = 79.5
-- Salade: 60 - 0.3 = 59.7
```

### Vérifier les commandes
```sql
SELECT * FROM orders ORDER BY created_at DESC LIMIT 5;
SELECT * FROM order_items WHERE order_id = 1;
```

## Dépannage Rapide

### ❌ Le POS ne charge pas
Vérifiez:
- Les routes sont bien enregistrées
- Le contrôleur POSController existe
- La vue `pos/index.blade.php` existe

### ❌ Les plats n'apparaissent pas
Vérifiez:
- Les plats sont créés dans la base de données
- Les catégories existent et sont liées aux plats
- Exécutez: `php artisan db:seed --class=POSSeeder`

### ❌ Le stock ne se déduit pas
Vérifiez:
- Les recettes existent: `SELECT * FROM recipes;`
- Les recipe_items existent: `SELECT * FROM recipe_items;`
- Les stock_items existent: `SELECT * FROM stock_items;`
- Les transactions ne sont pas rollback (vérifiez les logs)

### ❌ Erreur CSRF
Assurez-vous que:
- La métabalise CSRF exist dans le layout production:
  ```html
  <meta name="csrf-token" content="{{ csrf_token() }}">
  ```

## Commandes Utiles

```bash
# Rafraîchir la base de données et charger les données
php artisan migrate:fresh --seed

# Voir les routes enregistrées
php artisan route:list | grep pos

# Vérifier les logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
```

## Structure des Fichiers Créés

```
app/Http/Controllers/
  └── POSController.php         ✓ Contrôleur principal

app/Models/
  ├── Meal.php                  ✓ Mis à jour
  ├── Category.php              ✓ Mis à jour
  ├── Recipe.php                ✓ Mis à jour
  ├── RecipeItem.php            ✓ Mis à jour
  ├── Order.php                 ✓ Mis à jour
  ├── OrderItem.php             ✓ Mis à jour
  ├── Stock.php                 ✓ Mis à jour
  ├── StockItem.php             ✓ Mis à jour
  ├── Product.php               ✓ Mis à jour
  ├── Unit.php                  ✓ Mis à jour
  ├── Accompaniment.php         ✓ Mis à jour
  └── StockMovement.php         ✓ Mis à jour

resources/views/pos/
  ├── index.blade.php           ✓ Interface POS
  ├── orders.blade.php          ✓ Historique
  └── order-detail.blade.php    ✓ Détails

database/seeders/
  └── POSSeeder.php             ✓ Données de test

routes/
  └── web.php                   ✓ Routes POS ajoutées

Documentation/
  ├── POS_GUIDE.md              ✓ Guide complet
  └── INSTALLATION.md           ✓ Ce fichier
```

## Prochaines Étapes

1. **Tester complètement** le POS avec les données de test
2. **Adapter** les prix et catégories à votre restaurant
3. **Ajouter** des plats et des recettes réiaux
4. **Imprimer** les tickets (optionnel)
5. **Intégrer** avec le système de paiement

## Support

Si vous rencontrez des problèmes:
1. Consultez `POS_GUIDE.md`
2. Vérifiez les logs: `storage/logs/laravel.log`
3. Testez les API manuellement avec Postman
4. Vérifiez les données en base de données

Bon courage! 🚀
