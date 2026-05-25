# 🎨 Configuration du Dashboard - Personnalisation

## 📁 Fichiers du Dashboard

```
/resources/views/dashboard-moderne.blade.php    ← Version Laravel (avec authentification)
/public/dashboard-standalone.html               ← Version HTML autonome (sans Laravel)
/DASHBOARD_MODERN_GUIDE.md                      ← Documentation complète
```

---

## 🚀 Accès rapide

### Via Laravel
```bash
# URL
http://localhost:8000/dashboard-modern

# Via blade
<a href="{{ route('dashboard-modern') }}">Accéder au dashboard</a>
```

### Via HTML autonome
```bash
# URL directe
http://localhost:8000/dashboard-standalone.html

# Ou ouvrir directement le fichier HTML
```

---

## 🎨 Personnalisation rapide

### 1️⃣ Changer le nom de l'application
**Fichier:** `dashboard-moderne.blade.php`

```html
<!-- Ligne ~27 -->
<h1 class="hidden sm:block text-2xl font-bold gradient-text">ProManage</h1>

<!-- Remplacer ProManage par votre nom -->
<h1 class="hidden sm:block text-2xl font-bold gradient-text">VotreNom</h1>
```

---

### 2️⃣ Changer la couleur du gradient principal

**Gradient actuel:** Violet → Rose

```html
<!-- Trouvez: -->
class="bg-gradient-to-br from-purple-600 to-blue-600"

<!-- Remplacez par (exemples): -->
from-blue-600 to-cyan-600          <!-- Bleu futuriste -->
from-green-600 to-emerald-600      <!-- Vert écologique -->
from-orange-600 to-red-600         <!-- Orange énergique -->
from-indigo-600 to-purple-600      <!-- Indigo royal -->
```

---

### 3️⃣ Changer les couleurs des cartes de statistiques

**Fichier:** Chercher chaque `.stat-card` ou `.bg-gradient-to-br`

```html
<!-- Ventes du jour (Ligne ~158) -->
<div class="stat-card rounded-lg p-6 text-white relative">
<!-- Actuellement: gradient violet-rose -->
<!-- C'est un style CSS, voir la section CSS ci-dessous -->

<!-- Commandes actives (Ligne ~175) -->
<div class="bg-gradient-to-br from-emerald-400 to-green-600 ..."
<!-- Remplacer les couleurs emerald-400 et green-600 -->

<!-- Articles en stock (Ligne ~192) -->
<div class="bg-gradient-to-br from-blue-400 to-cyan-600 ..."

<!-- Revenus mensuels (Ligne ~209) -->
<div class="bg-gradient-to-br from-orange-400 to-red-600 ..."
```

---

### 4️⃣ Changer les couleurs dans le CSS

**Fichier:** Trouver la section `<style>` (lignes ~13-70)

```css
/* Gradient stat-card (Ligne ~39) */
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    /* 667eea = Violet, 764ba2 = Rose */
    /* Remplacer par vos codes hex */
}

/* Gradient text (Ligne ~67) */
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    /* Même gradient que stat-card pour la cohérence */
}
```

**Codes couleurs hex courants:**
```
Bleu: #0ea5e9, #3b82f6, #2563eb
Violet: #8b5cf6, #7c3aed, #6d28d9
Rose: #ec4899, #f43f5e, #e11d48
Vert: #10b981, #059669, #047857
Orange: #f97316, #fb923c, #fbbf24
Gris: #64748b, #475569, #334155
```

---

### 5️⃣ Ajouter le logo de votre entreprise

**Fichier:** Chercher la section du logo (Ligne ~26)

```html
<!-- Actuel -->
<div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-blue-600 rounded-lg flex items-center justify-center">
    <i class="fas fa-rocket text-white"></i>
</div>

<!-- Remplacer par une image -->
<img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-10 h-10 rounded-lg">

<!-- Ou garder l'icône mais changer -->
<i class="fas fa-crown text-white"></i>    <!-- couronne -->
<i class="fas fa-star text-white"></i>     <!-- étoile -->
<i class="fas fa-shield text-white"></i>   <!-- shield -->
```

---

### 6️⃣ Modifier les statistiques affichées

**Ventes du jour (Ligne ~158):**
```html
<!-- Texte -->
<h3 class="text-gray-100 font-semibold">Ventes du jour</h3>

<!-- Valeur -->
<p class="text-4xl font-bold mb-2">45.2K</p>

<!-- Variation -->
<p class="text-green-100 text-sm">
    <i class="fas fa-arrow-up"></i> 12% par rapport à hier
</p>
```

Remplacer les valeurs statiques par des variables Blade:
```blade
<p class="text-4xl font-bold mb-2">{{ number_format($sales_today, 1) }}K</p>
<p class="text-green-100 text-sm">
    <i class="fas fa-arrow-up"></i> {{ $sales_percentage }}% par rapport à hier
</p>
```

---

### 7️⃣ Ajouter/Supprimer des modules

**Trouver la section "Modules de gestion"** (Ligne ~243)

**Pour SUPPRIMER un module:**
```html
<!-- Supprimer toute la div class="card-hover" ... -->
<!-- Exemple: Supprimer le bloc Point de Vente (lignes ~252-270) -->
```

**Pour AJOUTER un module:**
```html
<!-- Copier-coller une carte existante -->
<div class="card-hover bg-white rounded-xl p-6 border border-gray-200 cursor-pointer group">
    <div class="bg-gradient-to-br from-[COULEUR1]-100 to-[COULEUR2]-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
        <i class="fas fa-[ICON] module-icon text-[COULEUR1]-600"></i>
    </div>
    <h4 class="text-lg font-bold text-gray-900 mb-2">Nom du Module</h4>
    <p class="text-gray-600 text-sm mb-4">Description du module</p>
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-[COULEUR1]-600 bg-[COULEUR1]-50 px-3 py-1 rounded-full">Données pertinentes</span>
        <button class="text-[COULEUR1]-600 hover:text-[COULEUR1]-700">
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>
```

---

### 8️⃣ Modifier les activités récentes

**Fichier:** Chercher "Activité récente" (Ligne ~385)

```html
<!-- Structure d'une activité -->
<div class="flex items-center gap-4 pb-4 border-b border-gray-200">
    <div class="w-10 h-10 rounded-full bg-[COULEUR]-100 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-[ICON] text-[COULEUR]-600"></i>
    </div>
    <div class="flex-1">
        <p class="text-sm font-semibold text-gray-900">Description</p>
        <p class="text-xs text-gray-500">Time</p>
    </div>
    <span class="text-sm font-bold text-gray-900">Valeur</span>
</div>
```

---

### 9️⃣ Changer les icônes Font Awesome

**Icônes disponibles:** https://fontawesome.com/search

```html
<!-- Format -->
<i class="fas fa-[icon-name]"></i>

<!-- Exemples -->
<i class="fas fa-cash-register"></i>     <!-- Caisse -->
<i class="fas fa-shopping-bag"></i>      <!-- Sachet -->
<i class="fas fa-chart-line"></i>        <!-- Graphique -->
<i class="fas fa-users"></i>             <!-- Utilisateurs -->
<i class="fas fa-cog"></i>               <!-- Engrenage -->
<i class="fas fa-bell"></i>              <!-- Cloche -->
<i class="fas fa-search"></i>            <!-- Loupe -->
<i class="fas fa-user"></i>              <!-- Profil -->
```

---

### 🔟 Personnaliser le sidebar

**Fichier:** Chercher `<aside id="sidebar">` (Ligne ~73)

```html
<!-- Récupérer la liste des liens du menu -->
<li>
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition">
        <i class="fas fa-[ICON]"></i>
        <span>Nom du menu</span>
    </a>
</li>

<!-- Remplacer par vos liens -->
<li>
    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition">
        <i class="fas fa-users"></i>
        <span>Utilisateurs</span>
    </a>
</li>
```

---

## 📊 Intégration avec données réelles (Laravel)

### 1️⃣ Passer les données du contrôleur

**Fichier:** `routes/web.php`

```php
Route::get('/dashboard-modern', function () {
    return view('dashboard-moderne', [
        'sales_today' => \App\Models\Order::whereDate('created_at', today())->sum('total'),
        'active_orders' => \App\Models\Order::where('status', 'pending')->count(),
        'stock_items' => \App\Models\Product::sum('quantity'),
        'monthly_revenue' => \App\Models\Order::whereMonthYear(this_month())->sum('total'),
    ]);
})->middleware('auth')->name('dashboard-modern');
```

### 2️⃣ Afficher les données en Blade

**Fichier:** `dashboard-moderne.blade.php`

```blade
<!-- Remplacer -->
<p class="text-4xl font-bold mb-2">45.2K</p>

<!-- Par -->
<p class="text-4xl font-bold mb-2">{{ number_format($sales_today / 1000, 1) }}K</p>
```

---

## 🎯 Checklist de personnalisation

- [ ] Changer le nom de l'application (ProManage → VotreNom)
- [ ] Changer le logo
- [ ] Ajuster les couleurs du gradient principal
- [ ] Modifier les couleurs des cartes de statistiques
- [ ] Ajouter/supprimer des modules selon vos besoins
- [ ] Configurer les vraies routes pour les modules
- [ ] Intégrer les données réelles du contrôleur
- [ ] Adapter le menu sidebar à votre structure
- [ ] Configurer l'authentification Laravel
- [ ] Tester le responsive design sur mobile
- [ ] Ajouter des animations supplémentaires si désiré

---

## 📞 Support & Ressources

### Documentation Tailwind CSS
https://tailwindcss.com/docs

### Font Awesome Icons
https://fontawesome.com/

### Couleurs prédéfinies Tailwind
```
gray, slate, zinc, neutral, stone
red, orange, amber, yellow, lime, green
emerald, teal, cyan, sky, blue, indigo
violet, purple, fuchsia, pink, rose
```

### Gradients Tailwind
```
from-[color]-50 to-[color]-600
from-[color]-100 to-[color]-700
from-[color]-200 to-[color]-800
```

---

## 💡 Conseils de design

1. **Cohérence:** Utilisez max 2-3 couleurs principales
2. **Contraste:** Assurez-vous que le texte est lisible
3. **Espacement:** Utilisez les classes Tailwind pour l'espacement consistent
4. **Ombres:** `shadow-sm` pour subtil, `shadow-lg` pour intense
5. **Animations:** Les transitions doivent être rapides (0.2s-0.3s)
6. **Responsive:** Testez toujours sur mobile
7. **Performance:** Les dégradés CSS sont mieux que les images

---

**Dernière mise à jour:** 6 Avril 2026
**Version de Tailwind utilisée:** v4.0.7
**Version de Font Awesome:** 6.4.0
