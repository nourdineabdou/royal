# Module de Gestion des Utilisateurs, Rôles et Permissions

Ce module fournit une gestion complète des **utilisateurs**, **rôles** et **permissions** pour votre application Laravel.

## 🎯 Fonctionnalités

### 👥 Gestion des Utilisateurs
- ✅ Créer, lire, éditer et supprimer des utilisateurs
- ✅ Assigner plusieurs rôles à un utilisateur
- ✅ Gérer les permissions directes d'un utilisateur
- ✅ Pagination pour les listes

### 🔐 Gestion des Rôles
- ✅ Créer, lire, éditer et supprimer des rôles
- ✅ Assigner des permissions à des rôles
- ✅ Modifier les permissions d'un rôle existant
- ✅ Voir tous les utilisateurs ayant un rôle

### 🔑 Gestion des Permissions
- ✅ Créer, lire, éditer et supprimer des permissions
- ✅ Voir quels rôles utilisent une permission
- ✅ Description détaillée pour chaque permission

## 📁 Structure du Module

```
app/
├── Http/Controllers/
│   ├── UserController.php        # Gestion des utilisateurs
│   ├── RoleController.php        # Gestion des rôles
│   ├── PermissionController.php  # Gestion des permissions
│   └── DashboardController.php   # Tableau de bord
│
└── Models/
    └── User.php                  # Modèle User avec traits

resources/views/
├── users/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
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

database/seeders/
└── PermissionSeeder.php          # Données initiales

routes/
└── web.php                       # Routes du module
```

## 🚀 Installation et Configuration

### 1. **Package Spatie/Laravel-Permission**
Le package est déjà installé dans votre `composer.json`.

### 2. **Migrations de la base de données**
Exécutez les migrations Spatie:
```bash
php artisan migrate
```

### 3. **Charger le Seeder**
Pour initialiser les permissions et rôles par défaut:
```bash
php artisan db:seed --class=PermissionSeeder
```

### 4. **Accéder au module**
- **Dashboard**: `http://yourapp.local/dashboard`
- **Utilisateurs**: `http://yourapp.local/users`
- **Rôles**: `http://yourapp.local/roles`
- **Permissions**: `http://yourapp.local/permissions`

## 🔧 Utilisation dans le Code

### Vérifier des Permissions dans les Vues
```blade
@if(auth()->user()->can('edit user'))
    <a href="{{ route('users.edit', $user) }}">Éditer</a>
@endif
```

### Vérifier des Permissions dans les Contrôleurs
```php
if (auth()->user()->can('delete user')) {
    // Permettre la suppression
}
```

### Assigner un Rôle
```php
$user->assignRole('admin');
// ou
$user->syncRoles(['admin', 'moderator']);
```

### Assigner une Permission Directe
```php
$user->givePermissionTo('edit user');
```

### Vérifier si un Utilisateur a un Rôle
```php
if ($user->hasRole('admin')) {
    // L'utilisateur est admin
}
```

### Rôles et Permissions Prédéfinis

#### Rôles:
1. **Super Admin** - Accès à toutes les fonctionnalités
2. **Admin** - Accès à la plupart des fonctionnalités sauf suppression
3. **Moderator** - Accès à la lecture et à l'édition
4. **User** - Accès à la lecture uniquement

#### Permissions:
- `view users` - Voir la liste des utilisateurs
- `create user` - Créer un nouvel utilisateur
- `edit user` - Éditer un utilisateur
- `delete user` - Supprimer un utilisateur
- `view roles` - Voir la liste des rôles
- `create role` - Créer un nouveau rôle
- `edit role` - Éditer un rôle
- `delete role` - Supprimer un rôle
- `view permissions` - Voir la liste des permissions
- `create permission` - Créer une nouvelle permission
- `edit permission` - Éditer une permission
- `delete permission` - Supprimer une permission

## 🛡️ Middleware et Protection

Toutes les routes du module sont protégées par le middleware `auth`:
```php
Route::middleware(['auth'])->group(function () {
    // Routes du module
});
```

Pour ajouter une protection par permission, utilisez:
```php
Route::middleware(['auth', 'permission:view users'])->get('/users', ...)
```

## 📝 Bonnes Pratiques

### 1. **Utiliser les Rôles plutôt que les Permissions Directes**
```php
// ✅ Bonne pratique
$user->assignRole('admin');

// ❌ À éviter
$user->givePermissionTo('edit user')
$user->givePermissionTo('delete user');
```

### 2. **Nommer les Permissions de manière Cohérente**
Utilisez le format: `[VERBE] [OBJET]`
- `view users`
- `create user`
- `edit user`
- `delete user`

### 3. **Ajouter des Descriptions**
Décrivez toujours le rôle ou la permission pour faciliter la maintenance.

### 4. **Sécuriser l'Authentification**
Assurez-vous que l'authentification est bien configurée:
```php
if (!auth()->check()) {
    redirect('/login');
}
```

## 🔄 Cas d'Usage Courants

### Créer un Admin avec Toutes les Permissions
```php
$user = User::find(1);
$user->assignRole('super-admin');
```

### Créer un Utilisateur Standard avec Rôle
```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
]);
$user->assignRole('user');
```

### Modifier les Permissions d'un Rôle
```php
$role = Role::find(1);
$role->syncPermissions(['view users', 'edit user']);
```

## 📊 Tableau de Bord

Le dashboard affiche:
- **Nombre total d'utilisateurs**
- **Nombre de rôles disponibles**
- **Nombre de permissions définies**
- **Liens rapides** vers les sections principales

## 🐛 Dépannage

### Les permissions ne se mettent pas à jour
```php
// Vider le cache des permissions
Artisan::call('permission:cache-reset');
// ou
php artisan permission:cache-reset
```

### L'utilisateur n'a pas les permissions attendues
1. Vérifiez que le rôle a bien la permission
2. Vérifiez que l'utilisateur a bien le rôle
3. Videz le cache des permissions

## 📚 Ressources Supplémentaires

- [Documentation Spatie/Laravel-Permission](https://spatie.be/docs/laravel-permission/v6/introduction)
- [Documentation Laravel Authentication](https://laravel.com/docs/authentication)
- [Documentation Laravel Authorization](https://laravel.com/docs/authorization)

---

**Module créé le:** 4 avril 2026
**Version Laravel:** 12.0
**Package Spatie/Laravel-Permission:** 6.25
