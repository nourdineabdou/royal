# ✅ VÉRIFICATION RAPIDE - MODULE POS

## 📋 CHECKLIST PRE-DÉMARRAGE

Exécutez ces commandes pour vérifier que tout est en place:

---

## 1️⃣ VÉRIFIER LES FICHIERS

```bash
# Contrôleur
test -f app/Http/Controllers/POSController.php && echo "✅ POSController existe" || echo "❌ POSController manquant"

# Vues
test -f resources/views/pos/index.blade.php && echo "✅ Vue POS existe" || echo "❌ Vue POS manquante"
test -f resources/views/pos/orders.blade.php && echo "✅ Vue Historique existe" || echo "❌ Vue Historique manquante"
test -f resources/views/pos/order-detail.blade.php && echo "✅ Vue Détail existe" || echo "❌ Vue Détail manquante"

# Seeder
test -f database/seeders/POSSeeder.php && echo "✅ POSSeeder existe" || echo "❌ POSSeeder manquant"
```

---

## 2️⃣ VÉRIFIER LES ROUTES

```bash
php artisan route:list | grep "pos" | wc -l
# Doit retourner: 7 (sept routes)
```

**Détail attendu:**
```
GET|HEAD  /modules/pos                          modules.pos        ✓
GET|HEAD  /pos/meals                                                ✓
POST      /pos/create-order                                        ✓
GET|HEAD  /pos/orders                           pos.orders         ✓
GET|HEAD  /pos/orders/{id}                      pos.order-detail   ✓
POST      /pos/orders/{id}/paid                 pos.mark-paid      ✓
POST      /pos/orders/{id}/cancel               pos.cancel         ✓
```

---

## 3️⃣ VÉRIFIER LES MODÈLES

```bash
php artisan tinker

# Vérifier les relations
Meal::first()->category; # ✅ Doit retourner une catégorie
Recipe::first()->items; # ✅ Doit retourner les items
Order::first()->items; # ✅ Doit retourner les articles
Stock::first()->items; # ✅ Doit retourner les items de stock

# Exit
exit
```

---

## 4️⃣ VÉRIFIER LA BASE DE DONNÉES

```bash
mysql -u root -p complex_royal

# Compteurs de tables
SELECT 
    (SELECT COUNT(*) FROM meals) as meals,
    (SELECT COUNT(*) FROM categories) as categories,
    (SELECT COUNT(*) FROM recipes) as recipes,
    (SELECT COUNT(*) FROM products) as products,
    (SELECT COUNT(*) FROM stocks) as stocks;

# Résultat attendu:
# meals: 0 (avant seeder)
# categories: 0 (avant seeder)
# recipes: 0 (avant seeder)
# products: 0 (avant seeder)
# stocks: 0 (avant seeder)

# Après seeder, devrait être:
# meals: 5
# categories: 4
# recipes: 4
# products: 6
# stocks: 1

exit
```

---

## 5️⃣ VÉRIFIER LE CSRF TOKEN

```bash
grep -n "csrf-token" resources/views/layouts/production.blade.php
# Doit trouver une ligne avec: <meta name="csrf-token"...
```

---

## 6️⃣ VÉRIFIER L'ALIMENTATION DANS LE MENU

```bash
grep -n "modules.pos" resources/views/layouts/production.blade.php
# Doit trouver une référence à la route POS
```

---

## ✨ RÉSUMÉ OK SI:

- ✅ 3 vues Blade existent
- ✅ POSController existe
- ✅ POSSeeder existe
- ✅ 7 routes enregistrées
- ✅ Modèles ont les relations
- ✅ CSRF token en place
- ✅ Menu intégré

---

## 🚀 SI TOUT EST ✅, ALORS:

```bash
# 1. Charger les données
php artisan db:seed --class=POSSeeder

# 2. Ouvrir le navigateur
# URL: http://localhost/complex_royal/modules/pos

# 3. Créer une commande test
# Puis vérifier le stock en BD
```

---

## 🎯 PROCHAINES ÉTAPES

1. Ouvrir: [COMMENCEZ_ICI.md](COMMENCEZ_ICI.md)
2. Suivre: Les 3 étapes de démarrage
3. Tester: La création de commandes
4. Vérifier: La déduction du stock

---

**Tous les checks passent? ✅ VOUS ÊTES PRÊT!**

