@echo off
REM 🚀 INSTALLATION RAPIDE - Module Gestion Utilisateurs (Windows)
REM ==============================================================

setlocal enabledelayedexpansion

echo.
echo 📦 Installation du Module de Gestion Utilisateurs...
echo.

REM Étape 1: Migrations
echo 1️⃣  Exécution des migrations...
call php artisan migrate
if errorlevel 1 (
    echo ❌ Erreur lors des migrations
    pause
    exit /b 1
)
echo ✅ Migrations terminées
echo.

REM Étape 2: Seeders
echo 2️⃣  Chargement des permissions et rôles...
call php artisan db:seed --class=PermissionSeeder
if errorlevel 1 (
    echo ❌ Erreur lors du seeder PermissionSeeder
    pause
    exit /b 1
)

call php artisan db:seed --class=UserSeeder
if errorlevel 1 (
    echo ❌ Erreur lors du seeder UserSeeder
    pause
    exit /b 1
)

echo ✅ Données initiales créées
echo.

REM Étape 3: Cache
echo 3️⃣  Réinitialisation du cache...
call php artisan permission:cache-reset
call php artisan cache:clear

echo ✅ Cache réinitialisé
echo.

REM Résultat final
echo ==============================================================
echo 🎉 INSTALLATION TERMINÉE AVEC SUCCÈS!
echo ==============================================================
echo.
echo 📍 Accédez au module en démarrant le serveur:
echo.
echo    php artisan serve
echo.
echo    Puis ouvrez dans votre navigateur:
echo    - Dashboard:    http://localhost:8000/dashboard
echo    - Utilisateurs: http://localhost:8000/users
echo    - Rôles:        http://localhost:8000/roles
echo    - Permissions:  http://localhost:8000/permissions
echo.
echo 👤 Utilisateurs de test:
echo.
echo    Super Admin: admin@example.com / password123
echo    Admin:       manager@example.com / password123
echo    Modérateur:  moderator@example.com / password123
echo    Utilisateur: user@example.com / password123
echo.
echo 📚 Documentation:
echo.
echo    - USERS_ROLES_PERMISSIONS_MODULE.md (Complet)
echo    - INSTALLATION_GUIDE.md (Détails complets)
echo    - EXAMPLES_USAGE.php (Exemples de code)
echo    - MODULE_SUMMARY.md (Résumé général)
echo.
echo ==============================================================
echo.

REM Démarrer le serveur (optionnel)
set /p startServer="Démarrer le serveur Laravel maintenant? (o/n): "
if /i "!startServer!"=="o" (
    php artisan serve
) else (
    echo Tapez 'php artisan serve' dans le terminal pour démarrer le serveur.
)

pause
