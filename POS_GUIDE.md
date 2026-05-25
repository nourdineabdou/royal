# 🍽️ Module Point de Vente (POS) - Complex Royal

Bienvenue dans le module Point de Vente (POS) du système Complex Royal!

## ✨ Fonctionnalités Principales

### 1. **Interface POS Intuitive**
- ✅ Design moderne et responsive
- ✅ Affichage des plats par catégorie
- ✅ Panier en temps réel
- ✅ Calcul automatique du total

### 2. **Gestion des Stocks**
- ✅ Déduction automatique du stock lors de la vente
- ✅ Basée sur les **recettes** des plats
- ✅ Support des plats sans recette
- ✅ Restauration du stock en cas d'annulation

### 3. **Système de Commande**
- ✅ Création rapide de commandes
- ✅ Numéro client optionnel
- ✅ Historique complet des ventes
- ✅ Gestion du statut (en attente, envoyée, payée, annulée)

## 🚀 Accès au Module

```
URL: /modules/pos
Routes:
- GET /modules/pos               → Affiche le POS
- GET /pos/meals                 → API: Liste des plats
- POST /pos/create-order         → API: Créer une commande
- GET /pos/orders                → Historique des commandes
- GET /pos/orders/{id}           → Détails d'une commande
- POST /pos/orders/{id}/paid     → Marquer comme payée
- POST /pos/orders/{id}/cancel   → Annuler la commande
```

## 📦 Structure des Données

### Tables Utilisées
```
meals              → Plats avec prix et catégorie
categories         → Catégories de plats
recipes            → Recettes des plats
recipe_items       → Ingrédients des recettes
products           → Ingrédients/produits pour stocks
stocks             → Emplacements de stockage
stock_items        → Quantités par produit et stock
stock_movements    → Historique des mouvements
orders             → Commandes/ventes
order_items        → Articles dans chaque commande
```

## 🔄 Processus de Vente

### 1. **Client Sélectionne les Plats**
```
Étape 1: Choisir la catégorie
Étape 2: Cliquer sur "Ajouter" pour chaque plat
Étape 3: Ajuster les quantités dans le panier
```

### 2. **Validation de la Commande**
```
Étape 1: Entrer le numéro client (optionnel)
Étape 2: Cliquer sur "Valider la Commande"
Étape 3: Confirmation de la création
```

### 3. **Déduction Automatique du Stock**
```
Pour chaque article commandé:
- Récupérer la recette du plat
- Pour chaque ingrédient (recipe_item):
  - Calculer: quantité_ingrédient × quantité_plat_commandés
  - Déduire cette quantité du stock
```

### 4. **Exemple Pratique**

**Scénario:**
- Vous vendez 3 × "Burger Classique"
- La recette du Burger contient:
  - 2 pains
  - 3 fromages
  - 1 tomate

**Résultat du Stock:**
- Pains: -6 (2 × 3)
- Fromages: -9 (3 × 3)
- Tomates: -3 (1 × 3)

## 🎨 Système de Couleurs

Les catégories sont affichées avec 4 couleurs alternées:
- 🔵 Bleu (#3b82f6)
- 🟢 Vert (#10b981)
- 🟣 Violet (#a855f7)
- 🟠 Orange (#f97316)

## 📊 Historique et Rapports

### Accès à l'Historique
```
1. Cliquer sur "Historique" dans le POS
2. Filtrer par statut
3. Consulter les détails d'une commande
```

### Actions Disponibles
- ✅ Marquer comme payée
- ✅ Annuler la commande (restaure le stock)
- ✅ Consulter les déductions de stock

## ⚙️ Configuration

### Variables d'Environnement (si nécessaire)
```
POS_DEFAULT_STOCK_ID=1  (ID du stock par défaut)
POS_TAX_RATE=0.20       (Taux de TVA, si applicable)
```

### Modèles Utilisés
```php
// Principaux
Meal, Category, Recipe, RecipeItem
Order, OrderItem
Stock, StockItem, StockMovement
Product, Unit
User, Accompaniment
```

## 🐛 Résolution de Problèmes

### Le stock ne se déduit pas
- ✓ Vérifier que la recette existe pour le plat
- ✓ Vérifier que les ingrédients (products) existent
- ✓ Vérifier que le stock_item existe pour le produit

### Les plats n'apparaissent pas
- ✓ Vérifier que les plats sont liés à une catégorie
- ✓ Vérifier que la catégorie existe
- ✓ Vérifier les erreurs dans la console JavaScript

### Erreur lors de la création de commande
- ✓ Vérifier que le panier n'est pas vide
- ✓ Vérifier la connexion à la base de données
- ✓ Vérifier les logs Laravel

## 📝 Notes de Développement

### Contrôleur Principal
**Fichier:** `app/Http/Controllers/POSController.php`

Méthodes principales:
- `index()` → Affiche l'interface POS
- `getMeals()` → API: retourne les plats avec catégories
- `createOrder()` → Crée la commande et déduit le stock
- `deductStockFromRecipe()` → Logique de déduction
- `restoreStockFromRecipe()` → Restaure le stock
- `cancelOrder()` → Annule la commande

### Points d'Extension
```php
// Ajouter TVA ou frais
// Modifier $order->total_amount = $totalAmount * 1.20;

// Ajouter des accompagnements à la commande
// Utiliser order_item_accompaniments

// Ajouter des mouvements de stock
// Créer StockMovement pour traçabilité
```

## 🔐 Sécurité

- ✅ Routes protégées par middleware `auth`
- ✅ Validation CSRF obligatoire
- ✅ Validation des données d'entrée
- ✅ Transactions DB pour éviter les incohérences

## 📈 Amélioration Futures

- [ ] Support des remises/promotions
- [ ] Tickets d'impression
- [ ] Intégration des accompagnements
- [ ] Gestion des tables (restaurant)
- [ ] Rapport de caisse
- [ ] Sync avec imprimante thermique
