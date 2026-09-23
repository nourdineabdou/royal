<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Vider le cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Définition de toutes les permissions ──────────────────────────────
        $permissions = [

            // ── Authentification / Profil ─────────────────────────────────────
            'auth.login',
            'auth.logout',
            'auth.register',

            // ── Dashboard ─────────────────────────────────────────────────────
            'dashboard.view',

            // ── Utilisateurs ─────────────────────────────────────────────────
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.permissions.edit',

            // ── Rôles ─────────────────────────────────────────────────────────
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // ── Permissions ───────────────────────────────────────────────────
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // ── Production — Tableau de bord ──────────────────────────────────
            'production.dashboard',
            // ── Production — Transferts POS (Production → Caisse Catering) ──────
            'production.transfers.create',
            'production.transfers.view',
            'production.transfers.validate',

            // ── Caisse — Comptabilité & Sessions ────────────────────────────
            'accounting.view',
            'accounting.validate',
            'settings.cashregisters',
            // ── Production — Pertes/Périmés ───────────────────────────────
            'production.waste.view',
            'production.waste.create',
            'production.waste.edit',
            'production.waste.delete',
            'production.waste.validate',

            // ── Production — Plats (Meals) ────────────────────────────────────
            'meals.view',
            'meals.create',
            'meals.edit',
            'meals.delete',

            // ── Production — Catégories ───────────────────────────────────────
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // ── Production — Accompagnements ──────────────────────────────────
            'accompaniments.view',
            'accompaniments.create',
            'accompaniments.edit',
            'accompaniments.delete',

            // ── Production — Produits ─────────────────────────────────────────
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // ── Production — Unités ────────────────────────────────────────────
            'units.view',
            'units.create',
            'units.edit',
            'units.delete',

            // ── Production — Emballages ───────────────────────────────────────
            'packagings.view',
            'packagings.create',
            'packagings.edit',
            'packagings.delete',

            // ── Paramètres — Types de paiement ───────────────────────────────
            'payment-types.view',
            'payment-types.create',
            'payment-types.edit',
            'payment-types.delete',

            // ── POS — Restaurant ─────────────────────────────────────────────
            'pos.view',
            'pos.cashier',
            'pos.orders.view',
            'pos.orders.create',
            'pos.orders.mark-paid',
            'pos.orders.cancel',
            'pos.orders.payment',
            'pos.pending-orders',

            // ── POS — Comptabilité Caisse ─────────────────────────────────────
            'pos.accounting.view',
            'pos.accounting.traces.view',
            'pos.accounting.traces.export',
            'pos.accounting.sessions.export',
            'pos.accounting.register.open',
            'pos.accounting.register.close',
            'pos.accounting.register.validate',
            'pos.accounting.register.view-detail',
            'pos.accounting.profitability',

            // ── Achats (Purchases) ────────────────────────────────────────────
            'purchases.dashboard',
            'purchases.requests.view',
            'purchases.requests.create',
            'purchases.suppliers.view',
            'purchases.suppliers.create',
            'purchases.suppliers.edit',
            'purchases.suppliers.delete',
            'purchases.orders.view',
            'purchases.orders.create',
            'purchases.orders.confirm',
            'purchases.orders.cancel',
            'purchases.orders.receipt',
            'purchases.orders.validate-invoice',
            'purchases.orders.payment',
            'purchases.stock-ruptures.view',

            // ── RH (HR) ───────────────────────────────────────────────────────
            'hr.dashboard',
            'hr.employees.view',
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.delete',
            'hr.attendance.view',
            'hr.attendance.record',
            'hr.leaves.view',
            'hr.leaves.request',
            'hr.leaves.approve',
            'hr.leaves.reject',
            'hr.payroll.view',
            'hr.payroll.generate',
            'hr.payroll.mark-paid',
            'hr.advances.view',
            'hr.advances.request',
            'hr.advances.approve',

            // ── Catering ─────────────────────────────────────────────────────
            'catering.dashboard',
            'catering.clients.view',
            'catering.clients.create',
            'catering.clients.edit',
            'catering.clients.delete',
            'catering.contracts.view',
            'catering.contracts.create',
            'catering.contracts.edit',
            'catering.contracts.delete',
            'catering.weekly-menu.view',
            'catering.weekly-menu.create',
            'catering.weekly-menu.delete',
            'catering.meals.print-codes',
            'catering.validate',
            'catering.consumptions.view',

            // ── Événements ────────────────────────────────────────────────────
            'events.dashboard',
            'events.clients.view',
            'events.clients.create',
            'events.clients.edit',
            'events.clients.delete',
            'events.services.view',
            'events.services.create',
            'events.services.edit',
            'events.services.delete',
            'events.view',
            'events.create',
            'events.edit',
            'events.delete',
            'events.validate',
            'events.complete',
            'events.cancel',
            'events.service-items.manage',
            'events.meals.manage',
            'events.options.manage',
            'events.stock-preview',

            // ── Stock ─────────────────────────────────────────────────────────
            'stock.view',
            'stock.movements.view',
            'stock.movements.all',
            'stock.transfer',
            'stock.assign-module',
            'stock.products.adjust',

            // ── Résidence ─────────────────────────────────────────────────────
            'residence.dashboard',
            'residence.rooms.view',
            'residence.rooms.create',
            'residence.rooms.edit',
            'residence.rooms.delete',
            'residence.calendar',
            'residence.bookings.view',
            'residence.bookings.create',
            'residence.bookings.edit',
            'residence.bookings.cancel',
            'residence.bookings.checkin',
            'residence.bookings.checkout',
            'residence.bookings.payment',
            'residence.caisse.view',
            'residence.caisse.open',
            'residence.caisse.close',
        ];

        // Créer toutes les permissions
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // ── Création des rôles ─────────────────────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        $manager    = Role::firstOrCreate(['name' => 'manager',     'guard_name' => 'web']);
        $caissier   = Role::firstOrCreate(['name' => 'caissier',    'guard_name' => 'web']);
        $serveur    = Role::firstOrCreate(['name' => 'serveur',     'guard_name' => 'web']);
        $rh         = Role::firstOrCreate(['name' => 'rh',          'guard_name' => 'web']);
        $achat      = Role::firstOrCreate(['name' => 'achat',       'guard_name' => 'web']);
        $receptionniste = Role::firstOrCreate(['name' => 'receptionniste', 'guard_name' => 'web']);

        // ── Super-admin : TOUTES les permissions ──────────────────────────────
        $superAdmin->syncPermissions(Permission::all());

        // ── Admin : tout sauf suppression utilisateurs/rôles/permissions ─────
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'users.delete',
                'roles.delete',
                'permissions.delete',
            ])->get()
        );

        // ── Manager : gestion opérationnelle, pas paramétrage système ────────
        $manager->syncPermissions([
            'dashboard.view',
            'production.dashboard',
            'meals.view', 'meals.create', 'meals.edit',
            'categories.view', 'categories.create', 'categories.edit',
            'products.view', 'products.create', 'products.edit',
            'units.view',
            'pos.view', 'pos.orders.view', 'pos.orders.cancel',
            'pos.accounting.view', 'pos.accounting.traces.view',
            'pos.accounting.register.validate', 'pos.accounting.profitability',
            'purchases.dashboard', 'purchases.requests.view', 'purchases.suppliers.view',
            'purchases.orders.view', 'purchases.orders.confirm', 'purchases.orders.cancel',
            'hr.dashboard', 'hr.employees.view',
            'hr.attendance.view', 'hr.leaves.view', 'hr.leaves.approve', 'hr.leaves.reject',
            'hr.payroll.view', 'hr.payroll.generate', 'hr.payroll.mark-paid',
            'hr.advances.view', 'hr.advances.approve',
            'catering.dashboard', 'catering.clients.view', 'catering.contracts.view',
            'catering.weekly-menu.view', 'catering.validate',
            'production.transfers.create', 'production.transfers.view', 'production.transfers.validate',
            'accounting.view', 'accounting.validate', 'settings.cashregisters',
            'events.dashboard', 'events.view', 'events.clients.view',
            'events.validate', 'events.complete',
            'stock.view', 'stock.movements.view', 'stock.movements.all',
            'residence.dashboard', 'residence.calendar',
            'residence.rooms.view',
            'residence.bookings.view', 'residence.bookings.create', 'residence.bookings.edit',
            'residence.bookings.checkin', 'residence.bookings.checkout', 'residence.bookings.cancel',
            'residence.caisse.view',
        ]);

        // ── Caissier : POS + comptabilité + caisse résidence ─────────────────
        $caissier->syncPermissions([
            'dashboard.view',
            'pos.view', 'pos.cashier', 'pos.pending-orders',
            'pos.orders.view', 'pos.orders.create', 'pos.orders.mark-paid',
            'pos.orders.cancel', 'pos.orders.payment',
            'pos.accounting.view', 'pos.accounting.register.open',
            'pos.accounting.register.close', 'pos.accounting.profitability',
            // Catering POS
            'catering.dashboard',
            'production.transfers.view',
            'production.transfers.validate',
            'catering.validate',
            // Résidence
            'residence.dashboard', 'residence.calendar',
            'residence.bookings.view', 'residence.bookings.create', 'residence.bookings.edit',
            'residence.bookings.checkin', 'residence.bookings.checkout', 'residence.bookings.payment',
            'residence.caisse.view', 'residence.caisse.open', 'residence.caisse.close',
        ]);

        // ── Serveur : prise de commandes uniquement ───────────────────────────
        $serveur->syncPermissions([
            'dashboard.view',
            'pos.view', 'pos.orders.view', 'pos.orders.create',
            'pos.pending-orders',
            'meals.view',
        ]);

        // ── RH : module RH uniquement ─────────────────────────────────────────
        $rh->syncPermissions([
            'dashboard.view',
            'hr.dashboard',
            'hr.employees.view', 'hr.employees.create', 'hr.employees.edit',
            'hr.attendance.view', 'hr.attendance.record',
            'hr.leaves.view', 'hr.leaves.approve', 'hr.leaves.reject',
            'hr.payroll.view', 'hr.payroll.generate', 'hr.payroll.mark-paid',
            'hr.advances.view', 'hr.advances.approve',
        ]);

        // ── Achat : module achats + stock ─────────────────────────────────────
        $achat->syncPermissions([
            'dashboard.view',
            'purchases.dashboard',
            'purchases.requests.view', 'purchases.requests.create',
            'purchases.suppliers.view', 'purchases.suppliers.create', 'purchases.suppliers.edit',
            'purchases.orders.view', 'purchases.orders.create', 'purchases.orders.confirm',
            'purchases.orders.cancel', 'purchases.orders.receipt',
            'purchases.orders.validate-invoice', 'purchases.orders.payment',
            'purchases.stock-ruptures.view',
            'stock.view', 'stock.movements.view', 'stock.movements.all',
            'stock.products.adjust', 'stock.transfer',
        ]);
        // ── Réceptionniste : module résidence complet ─────────────────────────
        $receptionniste->syncPermissions([
            'dashboard.view',
            'residence.dashboard',
            'residence.rooms.view',
            'residence.calendar',
            'residence.bookings.view', 'residence.bookings.create', 'residence.bookings.edit',
            'residence.bookings.checkin', 'residence.bookings.checkout', 'residence.bookings.cancel',
            'residence.bookings.payment',
            'residence.caisse.view', 'residence.caisse.open', 'residence.caisse.close',
        ]);
    }
}
