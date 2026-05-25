# 🚀 COMMANDES ESSENTIELLES - Module Utilisateurs/Rôles/Permissions

## ⚡ Installation Rapide

```bash
# Windows (CMD ou PowerShell)
install.bat

# Linux/Mac
bash install.sh

# Ou manuellement:
php artisan migrate
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UserSeeder
php artisan permission:cache-reset
php artisan cache:clear
php artisan serve
```

---

## 🔧 Commandes Utiles

### Migration
```bash
# Exécuter les migrations
php artisan migrate

# Réinitialiser les migrations (⚠️ supprime les données)
php artisan migrate:reset
php artisan migrate

# Migrations + Seeders
php artisan migrate --seed
php artisan migrate:fresh --seed
```

### Seeders
```bash
# Charger un seeder spécifique
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UserSeeder

# Charger tous les seeders
php artisan db:seed

# Charger tout nouvellement (⚠️ supprime les données)
php artisan migrate:fresh --seed
```

### Cache & Permissions
```bash
# Réinitialiser le cache des permissions
php artisan permission:cache-reset

# Vider le cache Laravel
php artisan cache:clear

# Vider tous les caches
php artisan cache:clear && php artisan config:clear && php artisan view:clear
```

---

## 📊 Commandes de Base de Données (Tinker)

```bash
php artisan tinker

# Dans Tinker:

# Vérifier les utilisateurs
User::count()
User::all()

# Vérifier les rôles
\Spatie\Permission\Models\Role::all()

# Vérifier les permissions
\Spatie\Permission\Models\Permission::all()

# Assigner un rôle
$user = User::find(1)
$user->assignRole('admin')

# Vérifier les rôles d'un utilisateur
$user->roles

# Vérifier les permissions d'un utilisateur
$user->getAllPermissions()

# Ajouter une permission
$user->givePermissionTo('edit user')

# Retirer une permission
$user->revokePermissionTo('edit user')
```

---

## 🌐 Routes d'Accès

```
Dashboard:    http://localhost:8000/dashboard (Auth requis)
Utilisateurs: http://localhost:8000/users (Auth requis)
Rôles:        http://localhost:8000/roles (Auth requis)
Permissions:  http://localhost:8000/permissions (Auth requis)
```

---

## 👤 Utilisateurs de Test

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@example.com | password123 | super-admin |
| manager@example.com | password123 | admin |
| moderator@example.com | password123 | moderator |
| user@example.com | password123 | user |

---

## 🔑 Permissions Disponibles

**Utilisateurs:**
- `view users` - Voir la liste
- `create user` - Créer
- `edit user` - Éditer
- `delete user` - Supprimer

**Rôles:**
- `view roles` - Voir la liste
- `create role` - Créer
- `edit role` - Éditer
- `delete role` - Supprimer

**Permissions:**
- `view permissions` - Voir la liste
- `create permission` - Créer
- `edit permission` - Éditer
- `delete permission` - Supprimer

---

## 🎯 Rôles Disponibles

| Rôle | Permissions |
|------|------------|
| **super-admin** | Toutes |
| **admin** | Tout sauf suppression |
| **moderator** | Voir + Éditer |
| **user** | Voir seulement |

---

## 📁 Fichiers Clés

```
app/Http/Controllers/
├── UserController.php
├── RoleController.php
├── PermissionController.php
└── DashboardController.php

app/Models/
└── User.php (avec trait HasRoles)

routes/
└── web.php (routes du module)

database/seeders/
├── PermissionSeeder.php
└── UserSeeder.php

resources/views/
├── layouts/app.blade.php
├── users/
├── roles/
├── permissions/
└── dashboard.blade.php

Documentation/
├── USERS_ROLES_PERMISSIONS_MODULE.md
├── INSTALLATION_GUIDE.md
├── MODULE_SUMMARY.md
├── EXAMPLES_USAGE.php
└── QUICK_COMMANDS.md (ce fichier)
```

---

## 🆘 Dépannage Rapide

```bash
# Les permissions ne se mettent pas à jour?
php artisan permission:cache-reset
php artisan cache:clear

# Erreur "User model not implementing HasRoles"
# → Vérifiez que User.php a le trait HasRoles

# Route Not Found?
# → Vérifiez que vous êtes authentifié
# → Vérifiez que le serveur est démarré

# Réinitialiser tout
php artisan migrate:fresh --seed
php artisan permission:cache-reset
```

---

## 💻 Exemples d'Utilisation

### Vérifier une Permission (Contrôleur)
```php
if (auth()->user()->can('edit user')) {
    // Permettre l'édition
}
```

### Vérifier un Rôle (Contrôleur)
```php
if (auth()->user()->hasRole('admin')) {
    // Code admin
}
```

### Dans une Vue Blade
```blade
@can('edit user')
    <a href="{{ route('users.edit', $user) }}">Éditer</a>
@endcan
```

### Assigner un Rôle
```php
$user->assignRole('moderator');
```

### Assigner une Permission
```php
$user->givePermissionTo('edit user');
```

---

## 📞 Support Rapide

- **Documentation complète**: `USERS_ROLES_PERMISSIONS_MODULE.md`
- **Guide d'installation**: `INSTALLATION_GUIDE.md`
- **Exemples de code**: `EXAMPLES_USAGE.php`

---

## ✅ Checklist Post-Installation

- [ ] Migrations exécutées (`php artisan migrate`)
- [ ] Seeders chargés (`php artisan db:seed`)
- [ ] Cache réinitialisé (`php artisan permission:cache-reset`)
- [ ] Utilisateurs de test vérifiés
- [ ] Rôles créés avec les bonnes permissions
- [ ] Dashboard accessible
- [ ] Formulaires de création/édition fonctionnels
- [ ] Dépôt git initialisé (optionnel)
- [ ] Documentation lue

---

✨ **Tout est prêt! Commencez à utiliser le module!** ✨

