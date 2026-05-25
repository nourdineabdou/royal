# ✅ CHECKLIST MODULE POS - VÉRIFICATION COMPLÈTE

## 📋 PRÉ-REQUIS

- [ ] Laravel 12.0 installé et fonctionnel
- [ ] Base de données créée et migrations exécutées
- [ ] Tables existantes: meals, categories, recipes, recipe_items, products, stocks, stock_items, orders, order_items
- [ ] Authentification fonctionne

## 🔧 INSTALLATION

### Étape 1: Vérifier les Fichiers Créés

#### Contrôleur
- [ ] `app/Http/Controllers/POSController.php` existe
  ```bash
  ls -la app/Http/Controllers/POSController.php
  ```

#### Vues
- [ ] `resources/views/pos/index.blade.php` existe
- [ ] `resources/views/pos/orders.blade.php` existe
- [ ] `resources/views/pos/order-detail.blade.php` existe

#### Modèles (Vérifier les relations)
- [ ] Meal.php a relationships
- [ ] Category.php a relationships
- [ ] Recipe.php a relationships
- [ ] RecipeItem.php a relationships
- [ ] Order.php a relationships
- [ ] OrderItem.php a relationships
- [ ] Stock.php a relationships
- [ ] StockItem.php a relationships
- [ ] Product.php a relationships
- [ ] Unit.php a relationships

#### Routes
- [ ] `routes/web.php` contient les routes POS
  ```bash
  grep -n "POSController" routes/web.php
  ```

#### Seeder
- [ ] `database/seeders/POSSeeder.php` existe

#### Layout
- [ ] `resources/views/layouts/production.blade.php` contient `csrf-token` meta

### Étape 2: Charger les Données

```bash
# En une commande
php artisan migrate:fresh --seed --seeder=POSSeeder

# Ou en deux étapes
php artisan migrate
php artisan db:seed --class=POSSeeder
```

Attendez le message:
```
✅ Données de test du POS créées avec succès!
📊 Résumé:
  • 4 Catégories
  • 5 Plats
  • 6 Ingrédients
  • 4 Recettes
  • 1 Stock avec quantités initiales
```

### Étape 3: Vérifier les Routes

```bash
php artisan route:list | grep -i pos
```

Attendez:
```
GET|HEAD  /modules/pos                          modules.pos              ✓
GET|HEAD  /pos/meals                                                     ✓
POST      /pos/create-order                                             ✓
GET|HEAD  /pos/orders                           pos.orders              ✓
GET|HEAD  /pos/orders/{id}                      pos.order-detail        ✓
POST      /pos/orders/{id}/paid                 pos.mark-paid           ✓
POST      /pos/orders/{id}/cancel               pos.cancel              ✓
```

## 🧪 TESTS MANUELS

### Test 1: Interface POS Charge
```
1. Ouvrez: http://localhost/complex_royal/modules/pos
2. Attendez 2 sec pour que les plats chargent
3. Vérifiez que les catégories apparaissent
4. Vérifiez que les plats s'affichent
```

**✅ Attendu:**
- [ ] Boutons de catégories visibles
- [ ] Grille de plats affichée
- [ ] Panier vide à droite
- [ ] Bouton "Valider" désactivé

### Test 2: Filtrer par Catégorie
```
1. Cliquez sur "Burgers"
2. Vérifiez que seuls les burgers s'affichent
3. Cliquez sur "Tous les Plats"
4. Vérifiez que tous les plats réapparaissent
```

**✅ Attendu:**
- [ ] Filtrage fonctionne
- [ ] Boutons changent de style
- [ ] Plats s'ajustent rapidement

### Test 3: Ajouter au Panier
```
1. Cliquez "Ajouter" sur un plat
2. Vérifiez que le panier se met à jour
3. Cliquez "Ajouter" 2-3 fois
```

**✅ Attendu:**
- [ ] Article apparaît dans le panier
- [ ] Quantité augmente
- [ ] Total se recalcule
- [ ] Message "Le panier est vide" disparaît
- [ ] Bouton "Valider" devient actif (vert)

### Test 4: Gérer le Panier
```
1. Augmentez la quantité d'un article (+)
2. Diminuez la quantité (-)
3. Supprimez un article (icône poubelle)
4. Videz le panier complet
```

**✅ Attendu:**
- [ ] Quantités changent
- [ ] Total se met à jour
- [ ] Articles disparaissent
- [ ] Bouton "Valider" redevient désactivé

### Test 5: Créer une Commande
```
1. Ajoutez 2-3 articles au panier
2. (Optionnel) Entrez un numéro client
3. Cliquez "Valider la Commande"
4. Attendez la confirmation (modal verde)
```

**✅ Attendu:**
- [ ] Modal de succès appear
- [ ] Numéro de commande affiché
- [ ] Panier se vide
- [ ] Page peut créer une nouvelle commande

### Test 6: Vérifier le Stock
```bash
# Dans un terminal (avant la commande)
SELECT stock_items.*, products.name FROM stock_items 
JOIN products ON stock_items.product_id = products.id 
WHERE stock_id = 1;

# Notez les quantités initiales
# Par exemple: Pain Burger = 100

# Après avoir créé une commande (ex: 1 Burger Classic)
# Vérifiez à nouveau
mysql> SELECT stock_items.*, products.name FROM stock_items 
       JOIN products ON stock_items.product_id = products.id 
       WHERE stock_id = 1;

# Pain doit être 100 - 2 = 98
# Fromage doit être 50 - 1 = 49
```

**✅ Attendu:**
- [ ] Stock de pain diminué
- [ ] Stock de fromage diminué
- [ ] Stock de tomate diminué
- [ ] Stock de salade diminué

### Test 7: Historique des Commandes
```
1. Créez 2-3 commandes
2. Cliquez "Historique" dans le POS
3. Vérifiez que toutes les commandes s'affichent
4. Cliquez sur détails d'une commande
```

**✅ Attendu:**
- [ ] Liste des commandes affichée
- [ ] Statuts corrects (en attente, etc.)
- [ ] Pages de détails disponibles
- [ ] Tableau des déductions visible

### Test 8: Marquer comme Payée
```
1. Allez sur les détails d'une commande
2. Cliquez "Marquer Payée"
3. Vérifiez que le statut change à "Payée"
```

**✅ Attendu:**
- [ ] Page redirierce vers détails
- [ ] Statut devient "Payée" (vert)
- [ ] Bouton disparaît

### Test 9: Annuler une Commande
```
1. Créez une nouvelle commande
2. Notez les quantités du stock AVANT
3. Allez à l'historique
4. Cliquez "Annuler" sur la commande
5. Vérifiez le stock APRÈS
```

**✅ Attendu:**
- [ ] Commande marquée "Annulée"
- [ ] Stock restauré à la quantité initiale
- [ ] Aucun message d'erreur

## 🐛 TESTS DE DÉPANNAGE

### T1: Pas de plats affichés
```bash
# Vérifiez les plats en BD
php artisan tinker
>>> Meal::all();
>>> Category::all();
```
**Solution:** Exécutez `php artisan db:seed --class=POSSeeder`

### T2: Erreur CSRF
**Message:** "CSRF token mismatch"
**Solution:** 
- Vérifiez que `csrf-token` meta existe dans le head
- Raz le cache: `php artisan cache:clear`

### T3: Stock ne se déduit pas
```bash
# Vérifiez les recettes
php artisan tinker
>>> Recipe::all();
>>> RecipeItem::all();
```
**Solution:** Les recettes doivent exister pour que le stock se déduit

### T4: Erreur 500
**Console JavaScript:** F12 → Console → Vérifiez les erreurs
**Logs Laravel:** `tail -f storage/logs/laravel.log`

## 📊 VÉRIFICATIONS SQL

```sql
-- Plats créés
SELECT COUNT(*) FROM meals;  -- Doit être ≥ 5

-- Catégories créées
SELECT COUNT(*) FROM categories;  -- Doit être 4

-- Recettes créées
SELECT COUNT(*) FROM recipes;  -- Doit être 4

-- Stock items créés
SELECT COUNT(*) FROM stock_items;  -- Doit être 6

-- Commandes créées
SELECT COUNT(*) FROM orders;  -- Augmente à chaque vente

-- Vérifier le stock d'un ingrédient
SELECT si.*, p.name FROM stock_items si
JOIN products p ON si.product_id = p.id
WHERE si.product_id = 1;
```

## 🎯 SCÉNARIO DE TEST COMPLET

```
1. Lancez le seeder
2. Ouvrez le POS
3. Sélectionnez "Burgers"
4. Ajoutez 1x Burger Classique
5. Modifiez la quantité à 3
6. Entrez "CLIENT123" comme numéro
7. Cliquez "Valider"
8. ✅ Commande créée
9. Vérifiez le stock (Pain -6, Fromage -3, etc.)
10. Allez à "Historique"
11. Consultez les détails
12. Marquez comme payée
13. Vérifiez le statut

Résultat: ✅ SUCCÈS
```

## 📝 NOTES

- Le POS démarre avec un panier vide
- Les couleurs des catégories alternent (4 couleurs)
- Le stock se déduit automatiquement
- Les plats sans recette ne déduisent rien du stock
- Les commandes peuvent être annulées sauf si payées

## 🚨 EN CAS DE PROBLÈME

1. **Nettoyez le cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

2. **Réinitialisez la BD (⚠️ données perdues):**
   ```bash
   php artisan migrate:fresh --seed --seeder=POSSeeder
   ```

3. **Consultez les logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Testez via Postman:**
   - GET `/pos/meals`
   - POST `/pos/create-order` avec JSON

## ✅ CHECKLIST FINALE

- [ ] Seeder exécuté avec succès
- [ ] Routes enregistrées
- [ ] Interface POS charge
- [ ] Plats s'affichent
- [ ] Panier fonctionne
- [ ] Ajouter/retirer articles OK
- [ ] Commande créée avec succès
- [ ] Stock déduit correctement
- [ ] Historique accessible
- [ ] Détails visible
- [ ] Marquer payée fonctionne
- [ ] Annuler restaure le stock

**Tous les tests passent?** ✅ **POS EST PRÊT!**
