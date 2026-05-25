#!/bin/bash

# 🚀 INSTALLATION RAPIDE - Module Gestion Utilisateurs
# =======================================================

echo "📦 Installation du Module de Gestion Utilisateurs..."
echo ""

# Étape 1: Migrations
echo "1️⃣  Exécution des migrations..."
php artisan migrate

echo "✅ Migrations terminées"
echo ""

# Étape 2: Seeders
echo "2️⃣  Chargement des permissions et rôles..."
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UserSeeder

echo "✅ Données initiales créées"
echo ""

# Étape 3: Cache
echo "3️⃣  Réinitialisation du cache..."
php artisan permission:cache-reset
php artisan cache:clear

echo "✅ Cache réinitialisé"
echo ""

# Résultat final
echo "======================================================="
echo "🎉 INSTALLATION TERMINÉE AVEC SUCCÈS!"
echo "======================================================="
echo ""
echo "📍 Accédez au module:"
echo ""
echo "   Dashboard:   http://localhost:8000/dashboard"
echo "   Utilisateurs: http://localhost:8000/users"
echo "   Rôles:       http://localhost:8000/roles"
echo "   Permissions: http://localhost:8000/permissions"
echo ""
echo "👤 Utilisateurs de test:"
echo ""
echo "   Super Admin: admin@example.com / password123"
echo "   Admin:       manager@example.com / password123"
echo "   Modérateur:  moderator@example.com / password123"
echo "   Utilisateur: user@example.com / password123"
echo ""
echo "📚 Documentation:"
echo ""
echo "   - USERS_ROLES_PERMISSIONS_MODULE.md (Complet)"
echo "   - INSTALLATION_GUIDE.md (Détails)"
echo "   - EXAMPLES_USAGE.php (Exemples)"
echo "   - MODULE_SUMMARY.md (Résumé)"
echo ""
echo "======================================================="

# Démarrer le serveur (optionnel)
echo ""
read -p "Démarrer le serveur Laravel? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan serve
fi
