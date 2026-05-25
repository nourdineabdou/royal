# 📋 Guide d'Installation - Module de Gestion Utilisateur

## 🎯 Objectif
Ce guide vous montre comment configurer et utiliser le **module complet de gestion des utilisateurs, rôles et permissions** que vous venez de créer.

---

## ✅ Étapes d'Installation

### 1️⃣ Vérifier les Dépendances
Le package `spatie/laravel-permission` est déjà installé dans votre `composer.json`.

Confirmez-le:
```bash
composer show | grep spatie
```

Sortie attendue:
```
spatie/laravel-permission                ^6.25
```

### 2️⃣ Exécuter les Migrations
Lancez les migrations pour créer les tables de Spatie:
```bash
php artisan migrate
```

Tables créées:
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

### 3️⃣ Charger les Données Initiales (Seeders)
Exécutez les seeders pour créer les permissions et rôles par défaut:

```bash
# Option 1: Exécuter les seeders individuellement
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UserSeeder

# Option 2: Exécuter tous les seeders
php artisan db:seed
```

**Détail des seeders:**

#### `PermissionSeeder`
✅ Crée **12 permissions**:
- Permissions utilisateurs (view, create, edit, delete)
- Permissions rôles (view, create, edit, delete)
- Permissions permissions (view, create, edit, delete)

✅ Crée **4 rôles**:
- **super-admin**: Accès total
- **admin**: Accès sauf suppression
- **moderator**: Lecture et édition
- **user**: Lecture uniquement

#### `UserSeeder`
✅ Crée **4 utilisateurs de test**:
- Super Admin: `admin@example.com` / `password123`
- Admin: `manager@example.com` / `password123`
- Modérateur: `moderator@example.com` / `password123`
- Utilisateur: `user@example.com` / `password123`

### 4️⃣ Configurer l'Authentification
Assurez-vous que la configuration L'authentification est accessible.

Testez:
```bash
php artisan tinker

# Dans tinker:
> auth()->check()
false // C'est normal si vous n'êtes pas connecté

> User::count()
4 // Les 4 utilisateurs créés par le seeder
```

### 5️⃣ Acceder au Module
Démarrez le serveur Laravel:
```bash
php artisan serve
```

Puis accédez à:
- **Dashboard**: http://localhost:8000/dashboard
- **Utilisateurs**: http://localhost:8000/users
- **Rôles**: http://localhost:8000/roles
- **Permissions**: http://localhost:8000/permissions

**ℹ️ Note**: Vous devez être authentifié. Utilisez les identifiants des seeders.

---

## 📝 Fichiers Créés

### Contrôleurs (app/Http/Controllers/)
- `UserController.php` - CRUD complet des utilisateurs
- `RoleController.php` - CRUD des rôles
- `PermissionController.php` - CRUD des permissions
- `DashboardController.php` - Tableau de bord

### Vues (resources/views/)
```
views/
├── layouts/
│   └── app.blade.php (Layout principal)
├── users/
│   ├── index.blade.php (Liste)
│   ├── create.blade.php (Créer)
│   ├── edit.blade.php (Éditer)
│   └── show.blade.php (Détails)
├── roles/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── permissions/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── dashboard.blade.php
```

### Seeders (database/seeders/)
- `PermissionSeeder.php` - Permissions et rôles
- `UserSeeder.php` - Utilisateurs de test

### Routes (routes/web.php)
Routes pour:
- Dashboard (`/dashboard`)
- Utilisateurs (`/users`, `/users/{id}`, etc.)
- Rôles (`/roles`, `/roles/{id}`, etc.)
- Permissions (`/permissions`, `/permissions/{id}`, etc.)

### Modèles (app/Models/)
- `User.php` - Ajout du trait `HasRoles`

---

## 🧪 Tests Rapides

### Test 1: Accéder au Dashboard
```bash
curl http://localhost:8000/dashboard
# Devrait afficher le tableau de bord (ou rediriger vers login)
```

### Test 2: Vérifier les Utilisateurs
```bash
php artisan tinker

# Vérifier le nombre total d'utilisateurs
> \App\Models\User::count()

# Vérifier les rôles d'un utilisateur
> $user = \App\Models\User::first()
> $user->roles

# Vérifier les permissions d'un utilisateur
> $user->permissions
```

### Test 3: Assigner un Rôle
```bash
php artisan tinker

> $user = \App\Models\User::find(1)
> $user->assignRole('admin')
> $user->getRoleNames()
```

---

## 🔧 Utilisation Pratique

### Créer un Nouvel Utilisateur Avec Rôle
```php
$user = \App\Models\User::create([
    'name' => 'Jean Dupont',
    'email' => 'jean@example.com',
    'password' => Hash::make('password123'),
]);
$user->assignRole('moderator');
```

### Assigner Plusieurs Rôles
```php
$user->syncRoles(['admin', 'moderator']);
```

### Vérifier une Permission
```php
if ($user->can('edit user')) {
    // L'utilisateur peut éditer les utilisateurs
}
```

### Dans une Vue Blade
```blade
@if(auth()->user()->hasRole('admin'))
    <p>Bienvenue Admin!</p>
@endif

@if(auth()->user()->can('delete user'))
    <button>Supprimer</button>
@endif
```

---

## ⚠️ Points Importants

1. **Cache des Permissions**
   Si les permissions ne se mettent pas à jour:
   ```bash
   php artisan permission:cache-reset
   ```

2. **Authentification Requise**
   Toutes les routes du module sont protégées par le middleware `auth`.

3. **Sécurité**
   - Ne partagez jamais les mots de passe
   - Changez les mots de passe des seeders avant la production
   - Utilisez HTTPS en production

4. **Base de Données**
   - Les migrations créent automatiquement les tables
   - Les seeders peuvent être exécutés plusieurs fois (utilise `firstOrCreate`)

---

## 🆘 Dépannage

### Erreur: "Route not found"
- Vérifiez que le serveur est bien en cours d'exécution
- Assurez-vous d'être connecté (vous devez passer l'authentification)

### Erreur: "User model not implementing HasRoles"
- Vérifiez que le trait `HasRoles` a été ajouté à `User.php`

### Permissions non mises à jour
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### Problèmes de migration
```bash
# Réinitialiser les migrations (attention: supprime les données!)
php artisan migrate:reset
php artisan migrate

# Puis recharger les seeders
php artisan db:seed
```

---

## 📚 Ressources

- [Documentation Complète](./USERS_ROLES_PERMISSIONS_MODULE.md)
- [Spatie/Laravel-Permission](https://spatie.be/docs/laravel-permission/v6/introduction)
- [Laravel Authentication](https://laravel.com/docs/authentication)
- [Laravel Authorization](https://laravel.com/docs/authorization)

---

## ✨ Fonctionnalités Disponibles

| Fonctionnalité | Modèle | CRUD | Rôles/Permissions |
|---|---|---|---|
| Gestion Utilisateurs | ✅ | ✅ | ✅ |
| Gestion Rôles | ✅ | ✅ | ✅ |
| Gestion Permissions | ✅ | ✅ | - |
| Dashboard | ✅ | - | - |
| Tableau de bord Statistiques | ✅ | - | - |
| Vérification Permissions | ✅ | - | - |

---

**Date de création**: 4 avril 2026
**Prêt pour la production**: ✅ (Après configuration d'authentification)

