# 🧪 GUIDE DE TEST COMPLET - MODULE POS

## ✅ PRÉ-REQUIS DE TEST

- [ ] Database créée et migrations exécutées
- [ ] Laravel 12 installé et fonctionnel
- [ ] Laragon avec Apache/PHP running
- [ ] Navigateur moderne (Chrome, Firefox, Edge)
- [ ] Accès aux logs Laravel: `tail -f storage/logs/laravel.log`

---

## 🎯 TEST 1: INITIALISATION DU SYSTÈME

### Commande à exécuter:
```bash
cd c:\laragon\www\complex_royal
php artisan db:seed --class=POSSeeder
```

### Résultat attendu:
```
Seeding: Database\Seeders\POSSeeder ... DONE.

✅ Données du POS créées avec succès!
📊 Résumé:
  • 4 Catégories créées
  • 5 Plats créés
  • 6 Produits créés
  • 4 Recettes créées avec items
  • 1 Stock avec quantités initiales
```

### Vérification en Base de Données:

**Terminal MySQL:**
```sql
-- Connexion
mysql -u root -p complex_royal

-- Vérifier les catégories (4 attendues)
SELECT COUNT(*) AS total FROM categories;
-- Résultat: 4

-- Vérifier les plats (5 attendus)
SELECT COUNT(*) FROM meals;
-- Résultat: 5

-- Vérifier les recettes (4 attendues)
SELECT COUNT(*) FROM recipes;
-- Résultat: 4

-- Vérifier les items recette (13 attendus)
SELECT COUNT(*) FROM recipe_items;
-- Résultat: 13

-- Vérifier le stock initial
SELECT si.id, p.name, si.quantity
FROM stock_items si
JOIN products p ON si.product_id = p.id
ORDER BY si.id;

-- Résultat attendu:
-- id | name           | quantity
-- 1  | Pain Burger    | 100
-- 2  | Fromage        | 50
-- 3  | Tomate         | 80
-- 4  | Salade         | 60
-- 5  | Beurre         | 30
-- 6  | Oignon         | 40
```

✅ **Test 1 Status: PASS/FAIL**

---

## 🎨 TEST 2: INTERFACE POS CHARGE

### Étapes:
1. Ouvrez: **http://localhost/complex_royal/modules/pos**
2. Attendez 2-3 secondes le chargement
3. Vérifiez chaque élément

### Écran attendu:
```
╔════════════════════════════════════════════════════════════════════════════╗
║                                                                            ║
║  COMPLEX ROYAL - POINT DE VENTE                                          ║
│                                                                            │
│ ┌─────────────────┬───────────────────────────┬──────────────────────┐  │
│ │ CATÉGORIES      │ PLATS                     │  PANIER (DROITE)     │  │
│ │                 │                           │                      │  │
│ │ [Burgers]       │ • Burger Classique        │ Numéro Client:       │  │
│ │ [Pizzas]        │   8.50€ [Ajouter]        │ [_____________]      │  │
│ │ [Sandwiches]    │                           │                      │  │
│ │ [Desserts]      │ • Burger Deluxe           │ Articles:            │  │
│ │ [Tous les Plats]│   11.50€ [Ajouter]       │ ┌──────────────────┐ │  │
│ │                 │                           │ │                  │ │  │
│ │ (Grille 3 cols) │ • Pizza Margherita        │ │ (Vide pour début)│ │  │
│ │                 │   10.00€ [Ajouter]       │ │                  │ │  │
│ │                 │                           │ └──────────────────┘ │  │
│ │                 │ • Sandwich Omelette       │                      │  │
│ │                 │   6.50€ [Ajouter]        │ Sous-Total: -€       │  │
│ │                 │                           │ Total: 0€            │  │
│ │                 │ • Tiramisu                │                      │  │
│ │                 │   5.50€ [Ajouter]        │ [Valider] (grisé)    │  │
│ │                 │                           │ [Vider]              │  │
│ │                 │                           │ [Historique]         │  │
│ └─────────────────┴───────────────────────────┴──────────────────────┘  │
│                                                                            │
╚════════════════════════════════════════════════════════════════════════════╝
```

### Points à Vérifier:
- [ ] Titre "COMPLEX ROYAL - POINT DE VENTE" visible
- [ ] 4 boutons de catégories distinctifs (couleurs différentes)
- [ ] Bouton "Tous les Plats"
- [ ] Grille de 5 plats affichée (3 colonnes)
- [ ] Chaque plat a: image/placeholder, nom, prix, bouton "Ajouter"
- [ ] Panier vide à droite
- [ ] Champ "Numéro Client" présent
- [ ] Bouton "Valider" en gris (désactivé)
- [ ] Total = 0€

### Vérification Console JavaScript (F12):
```javascript
// Aucune erreur dans la console
// Vous devriez voir:
// - Les plats chargés par fetch('/pos/meals')
// - Array de 5 plats
```

✅ **Test 2 Status: PASS/FAIL**

---

## 🔄 TEST 3: FILTRAGE PAR CATÉGORIE

### Étapes:
```
1. Cliquez sur [Burgers]
2. Vérifiez que seuls les Burgers s'affichent
3. Cliquez sur [Pizzas]
4. Vérifiez que seule la Pizza s'affiche
5. Cliquez sur [Tous les Plats]
6. Vérifiez que tous les 5 s'affichent
```

### Résultats attendus:

**Après clic [Burgers]:**
- Affichage: Burger Classique, Burger Deluxe (2 plats)
- Autres plats: cachés (opacity 0.3)

**Après clic [Pizzas]:**
- Affichage: Pizza Margherita (1 plat)
- Autres plats: cachés

**Après clic [Tous les Plats]:**
- Affichage: Tous les 5 plats visibles
- Aucun caché

### Vérification Styles:
```css
/* Éléments actifs: opacity 1, zIndex normal */
/* Éléments cachés: opacity 0.3, pointer-events none */
```

✅ **Test 3 Status: PASS/FAIL**

---

## 🛒 TEST 4: AJOUTER AU PANIER

### Étapes:
```
1. Cliquez [Ajouter] sur "Burger Classique"
2. Vérifiez le panier
3. Cliquez [Ajouter] de nouveau
4. Vérifiez que quantité augmente
```

### Panier attendu après 1er clic:

```
┌─────────────────────────────┐
│ Panier (1 article)          │
├─────────────────────────────┤
│ Burger Classique            │
│   Qty: [1] - Qty: [+]       │
│   8.50€ × 1 = 8.50€ X       │
│                             │
│ Sous-Total: 8.50€           │
│ Total: 8.50€                │
│                             │
│ [Valider] (VERT - ACTIF)    │
│ [Vider] [Historique]        │
└─────────────────────────────┘
```

### Panier attendu après 2e clic (même article):

```
┌─────────────────────────────┐
│ Panier (1 article)          │
├─────────────────────────────┤
│ Burger Classique            │
│   Qty: [2] - Qty: [+]       │
│   8.50€ × 2 = 17.00€ X      │
│                             │
│ Sous-Total: 17.00€          │
│ Total: 17.00€               │
│                             │
│ [Valider] (VERT - ACTIF)    │
│ [Vider] [Historique]        │
└─────────────────────────────┘
```

### Vérifications:
- [ ] Article apparaît dans le panier
- [ ] Quantité correcte (1, puis 2)
- [ ] Prix total recalculé (8.50€, puis 17.00€)
- [ ] Bouton "Valider" devient VERT et cliquable
- [ ] Bouton "Vider" disponible

✅ **Test 4 Status: PASS/FAIL**

---

## 🧮 TEST 5: AUGMENTER/DIMINUER QUANTITÉ

### Étapes (continuer de Test 4):
```
1. Panier contient: Burger Classique (Qty: 2)
2. Cliquez [+] pour augmenter
3. Vérifiez que Qty = 3
4. Cliquez [-] deux fois
5. Vérifiez que Qty = 1
```

### État du panier attendu:

**Après 1er clic [+] (Qty: 2 → 3):**
```
Burger Classique
  Qty: [3] - Qty: [+]
  8.50€ × 3 = 25.50€
Total: 25.50€
```

**Après 1er clic [-] (Qty: 3 → 2):**
```
Burger Classique
  Qty: [2] - Qty: [+]
  8.50€ × 2 = 17.00€
Total: 17.00€
```

**Après 2e clic [-] (Qty: 2 → 1):**
```
Burger Classique
  Qty: [1] - Qty: [+]
  8.50€ × 1 = 8.50€
Total: 8.50€
```

### Vérifications:
- [ ] Boutons +/- fonctionnent
- [ ] Quantités augmentent/diminuent de 1
- [ ] Totaux recalculés instantanément
- [ ] Article ne disparaît pas à Qty: 1
- [ ] Pas d'erreurs en console

✅ **Test 5 Status: PASS/FAIL**

---

## 🗑️ TEST 6: SUPPRIMER UN ARTICLE

### Étapes (continuer):
```
1. Cliquez sur le [X] de l'article
2. L'article doit disparaître du panier
3. Total doit revenir à 0€
4. Bouton "Valider" doit redevenir gris
5. Message "Le panier est vide" doit réapparaître
```

### Avant suppression:
```
Panier (1 article)
Burger Classique (Qty: 1)
Total: 8.50€
[Valider] (VERT)
```

### Après suppression:
```
Panier
┌───────────────────┐
│ Le panier est     │
│ vide             │
└───────────────────┘
Sous-Total: 0€
Total: 0€
[Valider] (GRIS - DÉSACTIVÉ)
```

### Vérifications:
- [ ] Article disparaît quand on clique [X]
- [ ] Total revient à 0€
- [ ] Bouton "Valider" redevient gris
- [ ] Message "vide" réapparaît

✅ **Test 6 Status: PASS/FAIL**

---

## 🛍️ TEST 7: CRÉER UNE COMMANDE

### Préparation:
```
1. Ajoutez au panier:
   - 2x Burger Classique (17.00€)
   - 1x Pizza Margherita (10.00€)
   Total: 27.00€

2. Entrez "CLIENT-001" dans le champ Numéro Client

3. Cliquez [Valider la Commande]
```

### Résultat attendu:

**Modal de succès:**
```
╔════════════════════════════════════════╗
║ ✅ COMMANDE CRÉÉE AVEC SUCCÈS          ║
├────────────────────────────────────────┤
│                                        │
│  Numéro de commande: #2                │
│  Total: 27.00€                         │
│  Nombre d'articles: 2                  │
│                                        │
│  Stock déduit automatiquement!         │
│                                        │
│          [CONTINUER]                   │
╚════════════════════════════════════════╝
```

### Vérifications:
- [ ] Modal apparaît
- [ ] Numéro de commande est affiché (ex: #2)
- [ ] Total correct (27.00€)
- [ ] Nombre d'articles correct (2)
- [ ] Panier se vide après fermeture de la modal

### En base de données:

```sql
-- Vérifier la commande créée
SELECT id, customer_number, total_amount, status 
FROM orders 
WHERE customer_number = 'CLIENT-001';

-- Résultat attendu:
-- id | customer_number | total_amount | status
-- 2  | CLIENT-001      | 27.00        | pending

-- Vérifier les articles de la commande
SELECT oi.id, m.name, oi.quantity, oi.price
FROM order_items oi
JOIN meals m ON oi.meal_id = m.id
WHERE oi.order_id = 2;

-- Résultat attendu:
-- id | name                | quantity | price
-- 1  | Burger Classique    | 2        | 8.50
-- 2  | Pizza Margherita    | 1        | 10.00
```

✅ **Test 7 Status: PASS/FAIL**

---

## 📦 TEST 8: VÉRIFIER LA DÉDUCTION DE STOCK

### Avant la commande (du Test 7):
```sql
SELECT p.name, si.quantity FROM stock_items si
JOIN products p ON si.product_id = p.id
ORDER BY p.name;

-- Résultat AVANT:
-- Pain Burger:  100
-- Fromage:      50
-- Tomate:       80
-- Salade:       60
-- Beurre:       30
-- Oignon:       40
```

### Commande effectuée:
```
Burger Classique (×2) + Pizza Margherita (×1)

Déductions attendues:
Burger Classique (×2):
  - 2 pain × 2 = 4 pain
  - 1 fromage × 2 = 2 fromage
  - 0.5 tomate × 2 = 1 tomate
  - 0.3 salade × 2 = 0.6 salade

Pizza Margherita (×1):
  - 1 pain × 1 = 1 pain
  - 1.5 fromage × 1 = 1.5 fromage
  - 1 tomate × 1 = 1 tomate

TOTAL DÉDUIT:
  - Pain: 4 + 1 = 5 (100 - 5 = 95) ✅
  - Fromage: 2 + 1.5 = 3.5 (50 - 3.5 = 46.5) ✅
  - Tomate: 1 + 1 = 2 (80 - 2 = 78) ✅
  - Salade: 0.6 (60 - 0.6 = 59.4) ✅
  - Beurre: 0 (30 - 0 = 30) ✅
  - Oignon: 0 (40 - 0 = 40) ✅
```

### Après la commande (vérifier en BD):
```sql
SELECT p.name, si.quantity FROM stock_items si
JOIN products p ON si.product_id = p.id
ORDER BY p.name;

-- Résultat APRÈS (ATTENDU):
-- Pain Burger:  95       (100 - 5)
-- Fromage:      46.5     (50 - 3.5)
-- Tomate:       78       (80 - 2)
-- Salade:       59.4     (60 - 0.6)
-- Beurre:       30       (30 - 0)
-- Oignon:       40       (40 - 0)
```

### Vérifications:
- [ ] Pain Burger: 95
- [ ] Fromage: 46.5
- [ ] Tomate: 78
- [ ] Salade: 59.4
- [ ] Beurre: 30 (inchangé)
- [ ] Oignon: 40 (inchangé)

✅ **Test 8 Status: PASS/FAIL**

---

## 📋 TEST 9: AFFICHER L'HISTORIQUE

### Étapes (depuis le POS):
```
1. Cliquez [Historique] dans le panier
2. Vous êtes redirigé à /pos/orders
3. Vérifiez la liste des commandes
```

### Écran attendu:

```
╔════════════════════════════════════════════════════════════════╗
║ HISTORIQUE DES COMMANDES                                       ║
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  Filtre: [Tous les Statuts ▼] [Rechercher...]                │
│                                                                │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ ID │ Client     │ Articles │ Total  │ Statut    │ Date  │ │
│ ├──────────────────────────────────────────────────────────┤ │
│ │ 2  │ CLIENT-001 │ 2        │ 27.00€ │ En attente│ Auj.  │ │
│ │ 1  │ -          │ 0        │ 0€     │ Payée    │ Auj.  │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                                │
│ Affichage: Page 1 de 1 (2 commandes / 20 par page)           │
│                                                                │
╚════════════════════════════════════════════════════════════════╝
```

### Vérifications:
- [ ] Page charge sans erreur
- [ ] Commande #2 affichée
- [ ] Numéro client correct (CLIENT-001)
- [ ] Total correct (27.00€)
- [ ] Statut affiché (En attente, Payée, etc.)
- [ ] Date affichée
- [ ] Pagination visible

✅ **Test 9 Status: PASS/FAIL**

---

## 👁️ TEST 10: DÉTAILS D'UNE COMMANDE

### Étapes (depuis l'historique):
```
1. Cliquez sur la commande #2
2. Vous êtes redirigé à /pos/orders/2
3. Vérifiez tous les détails
```

### Écran attendu:

```
╔════════════════════════════════════════════════════════════════╗
║ DÉTAILS DE LA COMMANDE #2                                      ║
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ Statut: [En attente] | Date: Aujourd'hui                      │
│                                                                │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ Serveur: - | Caissier: - | Client: CLIENT-001           │ │
│ │ Total: 27.00€                                            │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                                │
│ ARTICLES:                                                     │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ Article            │ Qty │ Prix  │ Sous-total           │ │
│ ├──────────────────────────────────────────────────────────┤ │
│ │ Burger Classique   │ 2   │ 8.50€ │ 17.00€               │ │
│ │ Pizza Margherita   │ 1   │10.00€ │ 10.00€               │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                                │
│ DÉDUCTION DE STOCK EFFECTUÉE:                                 │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ Produit       │ Quantité déduite                         │ │
│ ├──────────────────────────────────────────────────────────┤ │
│ │ Pain Burger   │ -5 (pour les 2 burgers + 1 pizza)       │ │
│ │ Fromage       │ -3.5 (pour les 2 burgers + 1 pizza)     │ │
│ │ Tomate        │ -2 (pour les 2 burgers + 1 pizza)       │ │
│ │ Salade        │ -0.6 (seulement pour les 2 burgers)     │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                                │
│ ACTIONS:                                                      │
│ [ Marquer Payée ]  [ Annuler ]  [ Imprimer ]                │
│                                                                │
╚════════════════════════════════════════════════════════════════╝
```

### Vérifications:
- [ ] Numéro de commande correct (#2)
- [ ] Statut correct (En attente)
- [ ] Client correct (CLIENT-001)
- [ ] Articles corrects (2x Burger, 1x Pizza)
- [ ] Total correct (27.00€)
- [ ] Déductions visibles et correctes
- [ ] Boutons d'actions présents

✅ **Test 10 Status: PASS/FAIL**

---

## ✅ TEST 11: MARQUER COMME PAYÉE

### Étapes (depuis détails commande #2):
```
1. Cliquez [Marquer Payée]
2. Confirmez l'action (si modal)
3. Vérifiez que le statut change
```

### Avant:
```
Statut: [En attente - Jaune]
Action: [Marquer Payée] [Annuler]
```

### Après:
```
Statut: [Payée - Vert]
Action: [Annuler] (Marquer Payée disparu)
```

### En base de données:

```sql
SELECT id, status FROM orders WHERE id = 2;

-- Avant:  status = 'pending'
-- Après:  status = 'paid'
```

### Vérifications:
- [ ] Statut change à "Payée" (vert)
- [ ] Bouton "Marquer Payée" disparaît
- [ ] Redirection vers la commande OK
- [ ] Base de données mise à jour

✅ **Test 11 Status: PASS/FAIL**

---

## ❌ TEST 12: ANNULER UNE COMMANDE

### Préparation:
```
1. Créez une nouvelle commande (pour avoir 2 commandes)
   - 1x Burger Deluxe (11.50€)
   Total: 11.50€

2. Allez à l'historique
3. Cliquez sur cette nouvelle commande
```

### Avant annulation:
```sql
-- Stock avant
Pain Burger: 95 (avait augmenté après déduction précédente)
Fromage: 46.5

-- Commande
Status: pending
```

### Étapes d'annulation:
```
1. Cliquez [Annuler] sur la commande
2. Confirmez l'action
3. Statut doit passer à "Annulée"
4. Stock doit être RESTAURÉ
```

### Après annulation:
```
Statut: [Annulée - Rouge]
```

### En base de données:

```sql
-- Stock après annulation (RESTAURÉ)
SELECT p.name, si.quantity FROM stock_items si
JOIN products p ON si.product_id = p.id
WHERE p.name IN ('Pain Burger', 'Fromage');

-- Pain Burger: 97 (95 + 2)
-- Fromage: 48.5 (46.5 + 2)

-- Commande
SELECT id, status FROM orders WHERE id = 3;
-- status = 'cancelled'
```

### Vérifications:
- [ ] Statut change à "Annulée"
- [ ] Pain Burger augmente de 2 (restauration)
- [ ] Fromage augmente de 2 (restauration)
- [ ] Tomate restaurée si utilisée
- [ ] Base de données cohérente
- [ ] Pas d'erreur 500

✅ **Test 12 Status: PASS/FAIL**

---

## 🔍 TEST 13: VIDER LE PANIER

### Étapes (depuis le POS principal):
```
1. Ajoutez 3 articles différents au panier
2. Vérifiez le total
3. Cliquez [Vider]
4. Confirmez
```

### Avant:
```
Panier (3 articles)
Total: XX€
```

### Après:
```
Panier
Le panier est vide

Total: 0€
Sous-Total: 0€
[Valider] (GRIS)
```

### Vérifications:
- [ ] Tous les articles disparaissent
- [ ] Total revient à 0€
- [ ] Message "Le panier est vide"
- [ ] Bouton "Valider" désactivé

✅ **Test 13 Status: PASS/FAIL**

---

## 🚨 TEST 14: GESTION DES ERREURS

### Test 14a: Plat sans recette
```
1. Ajoutez "Tiramisu" au panier (pas de recette)
2. Créez une commande
3. Vérifiez que stock ne se déduit PAS
```

**Attendu:**
```
✅ Commande créée (Tiramisu déduit du total)
❌ Stock ne bouge pas (pas de recette)
```

### Test 14b: Stock insuffisant
```
1. Videz tout le stock de Pain Burger en BD
2. Essayez de créer une commande avec burger
3. Vérifiez comportement
```

**Position actuelle:** Déduction même si stock insuffisant
**Comportement attendu:** Article devenait négatif
(Vous pouvez implémenter une validation)

### Test 14c: Erreur CSRF
```
1. Videz les cookies du navigateur
2. Rechargez le POS
3. Essayez de créer une commande
```

**Attendu:** Aucune erreur 419 (CSRF token présent dans meta)

✅ **Test 14 Status: PASS/FAIL**

---

## 📊 RÉCAPITULATIF DES TESTS

| Test | Nom | Statut |
|------|-----|--------|
| 1 | Initialisation système | ⏳ |
| 2 | Interface charge | ⏳ |
| 3 | Filtrage catégories | ⏳ |
| 4 | Ajouter panier | ⏳ |
| 5 | Gérer quantités | ⏳ |
| 6 | Supprimer article | ⏳ |
| 7 | Créer commande | ⏳ |
| 8 | Vérifier stock | ⏳ |
| 9 | Afficher historique | ⏳ |
| 10 | Détails commande | ⏳ |
| 11 | Marquer payée | ⏳ |
| 12 | Annuler commande | ⏳ |
| 13 | Vider panier | ⏳ |
| 14 | Gestion erreurs | ⏳ |

**Score: 0/14 tests passés**

---

## 🎯 RÉSULTAT FINAL

**Tous les tests passent? ✅**
→ Module POS PRÊT POUR PRODUCTION

**Certains tests échouent?**
→ Consultez les logs: `storage/logs/laravel.log`
→ Vérifiez en console JS: F12 → Console
→ Exécutez les tests de troubleshooting

---

**Ce guide aide à valider chaque aspect du système POS.**
**Suivez chaque test dans l'ordre pour une couverture complète.**

