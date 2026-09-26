

<?php

use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\PosTransferController;
use App\Http\Controllers\PosTerminalController;
use App\Http\Controllers\PosTerminalStockController;

// Comptabilité — Transactions globales
use App\Http\Controllers\AccountingController;
Route::get('/accounting/transactions', [AccountingController::class, 'transactions'])->name('accounting.transactions');
Route::get('/accounting/transactions/export/{format}', [AccountingController::class, 'export'])->name('accounting.transactions.export');

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardProductionController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AccompanimentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PackagingController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\CateringController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ResidenceController;

// Home page redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard-modern');
    }
    return redirect()->route('login');
});

// Routes d'authentification (publiques)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');

// Guide de formation RH (public — consultable sans connexion, pour partage avec un client/formateur)
Route::get('/guide-rh', function () {
    return view('hr.guide');
})->name('hr.guide');

// Routes pour la gestion des utilisateurs, rôles et permissions (protégées)
use App\Http\Controllers\ProductionWasteApiController;
Route::middleware(['auth'])->group(function () {
    // API production waste (info produit)
    Route::get('/api/production/product-info', [ProductionWasteApiController::class, 'productInfo']);
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard moderne
    Route::get('/dashboard-modern', function () {
        if (auth()->check() && auth()->user()->employee) {
            $attendance = \App\Models\Attendance::where('employee_id', auth()->user()->employee->id)
                ->where('date', today())
                ->whereNotNull('check_in')
                ->exists();
            if (! $attendance) {
                return redirect()->route('hr.clock');
            }
        }
        return view('dashboard-moderne-simple');
    })->name('dashboard-modern');

    // Génération PDF événement
    Route::get('/event/{event}/pdf', [EventController::class, 'generatePdf'])->name('event.pdf');

    // Module Routes (Modules de gestion)
    Route::prefix('modules')->group(function () {
        Route::get('/pos', [POSController::class, 'index'])->name('modules.pos');
        Route::get('/production', function () { return view('modules.production'); })->name('modules.production');
        Route::get('/residence', function () { return redirect()->route('residence.dashboard'); })->name('modules.residence');
        Route::get('/accounting', [App\Http\Controllers\AccountingController::class, 'index'])->name('modules.accounting');
        Route::get('/accounting/dashboard', [App\Http\Controllers\AccountingController::class, 'dashboard'])->name('accounting.dashboard');
        Route::get('/accounting/sessions', [App\Http\Controllers\AccountingController::class, 'sessions'])->name('accounting.sessions');
        Route::post('/accounting/sessions', [App\Http\Controllers\AccountingController::class, 'store'])->name('accounting.sessions.store');
        Route::get('/accounting/sessions/{id}', [App\Http\Controllers\AccountingController::class, 'detail'])->name('accounting.detail');
        Route::get('/accounting/traces', [App\Http\Controllers\AccountingController::class, 'traces'])->name('accounting.traces');
        Route::get('/accounting/journal', [App\Http\Controllers\AccountingController::class, 'journal'])->name('accounting.journal');
        Route::get('/accounting/journal/export', [App\Http\Controllers\AccountingController::class, 'exportJournal'])->name('accounting.journal.export');
        Route::get('/accounting/grand-livre', [App\Http\Controllers\AccountingController::class, 'ledger'])->name('accounting.ledger');
        Route::get('/accounting/balance', [App\Http\Controllers\AccountingController::class, 'trialBalance'])->name('accounting.balance');
        Route::get('/accounting/bilan', [App\Http\Controllers\AccountingController::class, 'balanceSheet'])->name('accounting.balance-sheet');
        Route::get('/accounting/compte-resultat', [App\Http\Controllers\AccountingController::class, 'incomeStatement'])->name('accounting.income-statement');
        Route::get('/accounting/rapprochement-bancaire', [App\Http\Controllers\AccountingController::class, 'bankReconciliation'])->name('accounting.bank-reconciliation');
        Route::post('/accounting/journal-lines/{line}/toggle-reconciliation', [App\Http\Controllers\AccountingController::class, 'toggleReconciliation'])->name('accounting.toggle-reconciliation');
        Route::get('/accounting/supplier-invoices', [App\Http\Controllers\AccountingController::class, 'supplierInvoices'])->name('accounting.supplier-invoices');
        Route::get('/accounting/supplier-balances', [App\Http\Controllers\AccountingController::class, 'supplierBalances'])->name('accounting.supplier-balances');
        Route::get('/accounting/client-balances', [App\Http\Controllers\AccountingController::class, 'clientBalances'])->name('accounting.client-balances');
        Route::get('/accounting/traces/export-csv', [POSController::class, 'exportAccountingTracesCsv'])->name('accounting.traces-export-csv');
        Route::get('/accounting/expenses', [App\Http\Controllers\ExpenseController::class, 'index'])->name('accounting.expenses.index');
        Route::post('/accounting/expenses', [App\Http\Controllers\ExpenseController::class, 'store'])->name('accounting.expenses.store');
        Route::post('/accounting/expenses/types', [App\Http\Controllers\ExpenseController::class, 'storeType'])->name('accounting.expenses.types.store');
        // Ajout de la route pour fermer la caisse
        Route::post('/accounting/sessions/{id}/close', [App\Http\Controllers\AccountingController::class, 'close'])->name('accounting.close');
        Route::post('/accounting/sessions/{id}/validate', [App\Http\Controllers\AccountingController::class, 'validateRegister'])->name('accounting.validate');
        Route::get('/stock', function () { return redirect()->route('stock.index'); })->name('modules.stock');
        Route::get('/purchases', function () { return redirect()->route('purchases.dashboard'); })->name('modules.purchases');
        Route::get('/hr', function () { return redirect()->route('hr.dashboard'); })->name('modules.hr');
        Route::get('/catering', function () { return redirect()->route('catering.dashboard'); })->name('modules.catering');
        Route::get('/event', function () { return redirect()->route('event.dashboard'); })->name('modules.event');
        Route::get('/settings', function () { return view('modules.settings'); })->name('modules.settings');
    });

    // Purchase Routes
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/dashboard', [PurchaseController::class, 'dashboard'])->name('dashboard');

        // Products & Units & Packaging Management
        Route::resource('products', ProductController::class);
        Route::resource('units', UnitController::class);
        Route::resource('packagings', PackagingController::class);

        // Suppliers
        Route::get('/suppliers',              [PurchaseController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers',             [PurchaseController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}',   [PurchaseController::class, 'updateSupplier'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}',[PurchaseController::class, 'destroySupplier'])->name('suppliers.destroy');

        // Purchase Requests (Demande d'achat) — peut être scindée en plusieurs BC, un par fournisseur
        Route::get('/requests',                       [PurchaseRequestController::class, 'index'])->name('requests');
        Route::get('/requests/create',                [PurchaseRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests',                      [PurchaseRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{purchaseRequest}',      [PurchaseRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{purchaseRequest}/print', [PurchaseRequestController::class, 'print'])->name('requests.print');
        Route::post('/requests/{purchaseRequest}/orders', [PurchaseRequestController::class, 'storeOrder'])->name('requests.orders.store');
        Route::post('/requests/{purchaseRequest}/quotes', [PurchaseRequestController::class, 'storeQuote'])->name('requests.quotes.store');
        Route::delete('/requests/quotes/{quote}', [PurchaseRequestController::class, 'destroyQuote'])->name('requests.quotes.destroy');

        // Historique de prix (produit x fournisseur) — pré-remplissage du prix à la commande
        Route::get('/price-history',          [PurchaseController::class, 'priceHistory'])->name('price-history');

        // Purchase Orders (Bon de Commande)
        Route::get('/orders',                 [PurchaseController::class, 'orders'])->name('orders');
        Route::get('/orders/create',          [PurchaseController::class, 'createOrder'])->name('orders.create');
        Route::post('/orders',                [PurchaseController::class, 'storeOrder'])->name('orders.store');
        Route::get('/orders/{order}',         [PurchaseController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order}/confirm',[PurchaseController::class, 'confirmOrder'])->name('orders.confirm');
        Route::post('/orders/{order}/cancel', [PurchaseController::class, 'cancelOrder'])->name('orders.cancel');

        // Contrôle comptable de la facture (avant paiement)
        Route::post('/orders/{order}/validate-invoice', [PurchaseController::class, 'validateInvoice'])->name('orders.validate-invoice');

        // Retour fournisseur (avoir)
        Route::get('/orders/{order}/return',  [PurchaseController::class, 'createReturn'])->name('orders.return.create');
        Route::post('/orders/{order}/return', [PurchaseController::class, 'storeReturn'])->name('orders.return.store');

        // Delivery Notes (Bon de Livraison) - can be different from order
        Route::post('/orders/{order}/receipt',[PurchaseController::class, 'storeReceipt'])->name('orders.receipt');
        Route::get('/deliveries',             [PurchaseController::class, 'deliveries'])->name('deliveries');
        Route::get('/deliveries/{delivery}',  [PurchaseController::class, 'showDelivery'])->name('deliveries.show');
        Route::put('/deliveries/{delivery}',  [PurchaseController::class, 'updateDelivery'])->name('deliveries.update');

        // Invoices (handled in accounting module)
        Route::post('/orders/{order}/payment',[PurchaseController::class, 'storePayment'])->name('orders.payment');

        // Print / PDF A4
        Route::get('/orders/{order}/print/commande',                             [PurchaseController::class, 'printCommande'])->name('orders.print.commande');
        Route::get('/orders/{order}/print/facture',                              [PurchaseController::class, 'printFacture'])->name('orders.print.facture');
        Route::get('/orders/{order}/receipts/{receipt}/print',                   [PurchaseController::class, 'printLivraison'])->name('orders.print.livraison');
        Route::get('/orders/{order}/payments/{payment}/print',                   [PurchaseController::class, 'printPaiement'])->name('orders.print.paiement');

        // Stock ruptures
        Route::get('/stock-ruptures',         [PurchaseController::class, 'stockRuptures'])->name('stock-ruptures');
    });

    // HR Routes
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/dashboard', [HRController::class, 'dashboard'])->name('dashboard');

        Route::get('/employees', [HRController::class, 'employees'])->name('employees');
        Route::post('/employees', [HRController::class, 'storeEmployee'])->name('employees.store');
        Route::post('/employees/{employee}/documents', [HRController::class, 'storeEmployeeDocument'])->name('employees.documents.store');
        Route::delete('/employees/documents/{document}', [HRController::class, 'destroyEmployeeDocument'])->name('employees.documents.destroy');

        Route::get('/contracts', [HRController::class, 'contracts'])->name('contracts');
        Route::post('/contracts', [HRController::class, 'storeContract'])->name('contracts.store');
        Route::post('/contracts/{contract}/terminate', [HRController::class, 'terminateContract'])->name('contracts.terminate');
        Route::put('/employees/{employee}', [HRController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{employee}', [HRController::class, 'destroyEmployee'])->name('employees.destroy');

        Route::get('/attendance', [HRController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [HRController::class, 'storeAttendance'])->name('attendance.store');
        Route::post('/attendance/clock', [HRController::class, 'storeAttendanceClock'])->name('attendance.clock');
        Route::get('/clock', [HRController::class, 'clock'])->name('clock');

        Route::get('/sites', [HRController::class, 'sites'])->name('sites');
        Route::post('/sites', [HRController::class, 'storeSite'])->name('sites.store');
        Route::put('/sites/{site}', [HRController::class, 'updateSite'])->name('sites.update');
        Route::delete('/sites/{site}', [HRController::class, 'destroySite'])->name('sites.destroy');

        Route::get('/leaves', [HRController::class, 'leaves'])->name('leaves');
        Route::post('/leaves', [HRController::class, 'storeLeave'])->name('leaves.store');
        Route::post('/leaves/{leave}/approve', [HRController::class, 'approveLeave'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [HRController::class, 'rejectLeave'])->name('leaves.reject');

        Route::get('/payroll', [HRController::class, 'payroll'])->name('payroll');
        Route::post('/payroll/generate', [HRController::class, 'generatePayroll'])->name('payroll.generate');
        Route::post('/payroll/{payroll}/paid', [HRController::class, 'markPayrollPaid'])->name('payroll.paid');
        Route::get('/payroll/{payroll}/payslip', [HRController::class, 'payslip'])->name('payroll.payslip');

        Route::get('/advances', [HRController::class, 'advances'])->name('advances');
        Route::post('/advances', [HRController::class, 'storeAdvance'])->name('advances.store');
        Route::post('/advances/{advance}/approve', [HRController::class, 'approveAdvance'])->name('advances.approve');
    });

    // POS Routes
    Route::prefix('pos')->group(function () {
        Route::get('/meals',               [POSController::class, 'getMeals']);
        Route::get('/extras',              [POSController::class, 'getExtras']);
        Route::post('/create-order',       [POSController::class, 'createOrder']);
        Route::get('/orders',              [POSController::class, 'orders'])->name('pos.orders');
        Route::get('/orders/{id}',         [POSController::class, 'orderDetail'])->name('pos.order-detail');
        Route::post('/orders/{id}/paid',   [POSController::class, 'markAsPaid'])->name('pos.mark-paid');
        Route::post('/orders/{id}/cancel', [POSController::class, 'cancelOrder'])->name('pos.cancel');
        // Caissier
        Route::get('/cashier',                    [POSController::class, 'cashier'])->name('pos.cashier');
        Route::get('/pending-orders',             [POSController::class, 'pendingOrders'])->name('pos.pending-orders');
        Route::post('/orders/{id}/payment',       [POSController::class, 'processPayment'])->name('pos.process-payment');
        Route::get('/orders/{id}/receipt',        [POSController::class, 'printReceipt'])->name('pos.order-receipt');
        Route::get('/orders/{id}/kitchen-ticket', [POSController::class, 'printKitchenTicket'])->name('pos.kitchen-ticket');
        // Catering POS — vente libre (boissons, desserts, extras)
        Route::get('/catering',               [POSController::class, 'cateringIndex'])->name('pos.catering');
        Route::post('/catering/create-order', [POSController::class, 'cateringCreateOrder'])->name('pos.catering.create-order');
    });

    // Production Management Routes
    Route::prefix('modules/production')->group(function () {
        Route::get('/dashboard', [DashboardProductionController::class, 'index'])->name('production.dashboard');
        Route::resource('meals', MealController::class);
        Route::get('meals/{meal}/recipe', [\App\Http\Controllers\RecipeController::class, 'edit'])->name('meals.recipe.edit');
        Route::put('meals/{meal}/recipe', [\App\Http\Controllers\RecipeController::class, 'update'])->name('meals.recipe.update');
        Route::resource('categories', CategoryController::class);
        Route::resource('accompaniments', AccompanimentController::class);

        // Plan de production journalier (Catering)
        Route::get('catering-today', [\App\Http\Controllers\ProductionPlanningController::class, 'cateringToday'])->name('production.catering-today');

        // Gestion des produits périmés/gâtés (pertes)
        Route::get('waste', [\App\Http\Controllers\ProductionWasteController::class, 'index'])->name('production.waste.index');
        Route::get('waste/create', [\App\Http\Controllers\ProductionWasteController::class, 'create'])->name('production.waste.create');
        Route::post('waste', [\App\Http\Controllers\ProductionWasteController::class, 'store'])->name('production.waste.store');
    });

    // Parameters Management Routes (Settings - Payment Types & User Management)
    Route::prefix('modules/parameters')->group(function () {
        Route::resource('payment-types', PaymentTypeController::class);
        Route::resource('users', UserController::class);
        Route::post('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.updatePermissions');
    });

    // Gestion des rôles
    // Catering Routes
    Route::prefix('catering')->name('catering.')->group(function () {
        Route::get('/dashboard',                                      [CateringController::class, 'dashboard'])->name('dashboard');

        // Clients (partagés avec Event)
        Route::get('/clients/map',                                    [CateringController::class, 'clientsMap'])->name('clients.map');
        Route::get('/clients',                                        [CateringController::class, 'clients'])->name('clients');
        Route::post('/clients',                                       [CateringController::class, 'storeClient'])->name('clients.store');
        Route::put('/clients/{client}',                               [CateringController::class, 'updateClient'])->name('clients.update');
        Route::delete('/clients/{client}',                            [CateringController::class, 'destroyClient'])->name('clients.destroy');

        // Contracts
        Route::get('/contracts',                                      [CateringController::class, 'contracts'])->name('contracts');
        Route::post('/contracts',                                     [CateringController::class, 'storeContract'])->name('contracts.store');
        Route::get('/contracts/{contract}',                           [CateringController::class, 'showContract'])->name('contracts.show');
        Route::put('/contracts/{contract}',                           [CateringController::class, 'updateContract'])->name('contracts.update');
        Route::delete('/contracts/{contract}',                        [CateringController::class, 'destroyContract'])->name('contracts.destroy');

        // Weekly Menu
        Route::get('/planning',                                       [CateringController::class, 'weeklyPlanningIndex'])->name('planning.index');
        Route::get('/contracts/{contract}/create-menu',               [CateringController::class, 'weeklyMenuCreate'])->name('weekly-menu.create');
        Route::get('/contracts/{contract}/weekly-menu-template',      [CateringController::class, 'weeklyMenuTemplate'])->name('weekly-menu.template');
        Route::get('/contracts/{contract}/weekly-menu-copy-from',     [CateringController::class, 'weeklyMenuCopyFrom'])->name('weekly-menu.copy-from');
        Route::post('/contracts/{contract}/weekly-menu',              [CateringController::class, 'weeklyMenuStore'])->name('weekly-menu.store');
        Route::get('/contracts/{contract}/weekly-menu/print',         [CateringController::class, 'weeklyMenuPrint'])->name('weekly-menu.print');
        Route::get('/weekly-menus/print-all',                         [CateringController::class, 'weeklyMenuPrintAll'])->name('weekly-menu.print-all');
        Route::get('/weekly-menus/{menu}',                            [CateringController::class, 'weeklyMenuShow'])->name('weekly-menu.show');
        Route::delete('/weekly-menus/{menu}',                         [CateringController::class, 'weeklyMenuDestroy'])->name('weekly-menu.destroy');

        // Plats Catering (quels plats sont destinés en priorité au catering)
        Route::get('/meals',                                          [CateringController::class, 'meals'])->name('meals');
        Route::post('/meals/{meal}/toggle-catering',                  [CateringController::class, 'toggleMealCatering'])->name('meals.toggle-catering');

        // Validation
        Route::get('/validate',                                       [CateringController::class, 'validatePage'])->name('validate');

        // Billing (enterprise contracts)
        Route::get('/billing',                                        [CateringController::class, 'billingIndex'])->name('billing.index');
        Route::post('/billing/generate',                              [CateringController::class, 'generateMonthlyInvoice'])->name('billing.generate');
        Route::get('/billing/invoices/{invoice}',                     [CateringController::class, 'showInvoice'])->name('billing.show');
        Route::post('/billing/invoices/{invoice}/payments',           [CateringController::class, 'addInvoicePayment'])->name('billing.payments.store');
        Route::post('/billing/invoices/{invoice}/payments/{payment}/validate', [CateringController::class, 'validateInvoicePayment'])->name('billing.payments.validate');

        // History
        // (consumptions supprimé — remplacé par les transferts POS)
    });

    // Event Routes
    Route::prefix('events')->name('event.')->group(function () {
        Route::get('/dashboard',                          [EventController::class, 'dashboard'])->name('dashboard');

        // Clients
        Route::get('/clients',                            [EventController::class, 'clients'])->name('clients');
        Route::post('/clients',                           [EventController::class, 'storeClient'])->name('clients.store');
        Route::put('/clients/{client}',                   [EventController::class, 'updateClient'])->name('clients.update');
        Route::delete('/clients/{client}',                [EventController::class, 'destroyClient'])->name('clients.destroy');

        // Service catalog
        Route::get('/services',                           [EventController::class, 'services'])->name('services');
        Route::post('/services',                          [EventController::class, 'storeService'])->name('services.store');
        Route::put('/services/{service}',                 [EventController::class, 'updateService'])->name('services.update');
        Route::delete('/services/{service}',              [EventController::class, 'destroyService'])->name('services.destroy');

        // Static + list routes (before /{event} wildcard)
        Route::get('/',                                   [EventController::class, 'index'])->name('index');
        Route::get('/create',                             [EventController::class, 'create'])->name('create');
        Route::post('/',                                  [EventController::class, 'store'])->name('store');

        // Sub-resource delete routes (must be before /{event} wildcard)
        Route::delete('/service-items/{item}',            [EventController::class, 'destroyServiceItem'])->name('service-items.destroy');
        Route::delete('/meals/{meal}',                    [EventController::class, 'destroyMeal'])->name('meals.destroy');
        Route::delete('/options/{option}',                [EventController::class, 'destroyOption'])->name('options.destroy');

        // Event-specific nested routes
        Route::post('/{event}/service-items',             [EventController::class, 'storeServiceItem'])->name('service-items.store');
        Route::post('/{event}/meals',                     [EventController::class, 'storeMeal'])->name('meals.store');
        Route::post('/{event}/options',                   [EventController::class, 'storeOption'])->name('options.store');
        Route::post('/{event}/validate',                  [EventController::class, 'validateEvent'])->name('validate');
        Route::post('/{event}/complete',                  [EventController::class, 'completeEvent'])->name('complete');
        Route::post('/{event}/cancel',                    [EventController::class, 'cancelEvent'])->name('cancel');
        Route::get('/{event}/stock-preview',              [EventController::class, 'stockPreview'])->name('stock-preview');

        // Generic CRUD (must be last — wildcard /{event})
        Route::get('/{event}',                            [EventController::class, 'show'])->name('show');
        Route::put('/{event}',                            [EventController::class, 'update'])->name('update');
        Route::delete('/{event}',                         [EventController::class, 'destroy'])->name('destroy');
    });

    // Stock Routes
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/',                        [StockController::class, 'index'])->name('index');
        Route::get('/all-movements',           [StockController::class, 'allMovements'])->name('all-movements');
        Route::get('/export/inventory',        [StockController::class, 'exportInventoryCsv'])->name('export.inventory');
        Route::get('/export/movements',        [StockController::class, 'exportMovementsCsv'])->name('export.movements');
        Route::get('/transfer',                [StockController::class, 'transfer'])->name('transfer');
        Route::post('/transfer',               [StockController::class, 'storeTransfer'])->name('storeTransfer');
        Route::post('/assign-module',          [StockController::class, 'assignModule'])->name('assign-module');
        Route::get('/{stock}',                 [StockController::class, 'show'])->name('show');
        Route::get('/{stock}/movements',       [StockController::class, 'movements'])->name('movements');
        Route::get('/{stock}/export/inventory',[StockController::class, 'exportStockInventoryCsv'])->name('export.stock-inventory');
        Route::post('/{stock}/products/add',   [StockController::class, 'addProduct'])->name('products.add');
    });

    // Résidence Routes
    Route::prefix('residence')->name('residence.')->group(function () {
        Route::get('/dashboard',                          [ResidenceController::class, 'dashboard'])->name('dashboard');

        // Room types
        Route::get('/room-types',                         [ResidenceController::class, 'roomTypes'])->name('room-types');
        Route::post('/room-types',                        [ResidenceController::class, 'storeRoomType'])->name('room-types.store');
        Route::put('/room-types/{roomType}',              [ResidenceController::class, 'updateRoomType'])->name('room-types.update');
        Route::delete('/room-types/{roomType}',           [ResidenceController::class, 'destroyRoomType'])->name('room-types.destroy');

        // Rooms
        Route::get('/rooms',                              [ResidenceController::class, 'rooms'])->name('rooms');
        Route::post('/rooms',                             [ResidenceController::class, 'storeRoom'])->name('rooms.store');
        Route::put('/rooms/{room}',                       [ResidenceController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/rooms/{room}',                    [ResidenceController::class, 'destroyRoom'])->name('rooms.destroy');

        // Calendar
        Route::get('/calendar',                           [ResidenceController::class, 'calendar'])->name('calendar');

        // Bookings
        Route::get('/bookings',                           [ResidenceController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/create',                    [ResidenceController::class, 'createBooking'])->name('bookings.create');
        Route::post('/bookings',                          [ResidenceController::class, 'storeBooking'])->name('bookings.store');
        Route::get('/bookings/{booking}',                 [ResidenceController::class, 'showBooking'])->name('bookings.show');
        Route::post('/bookings/{booking}/check-in',       [ResidenceController::class, 'checkIn'])->name('bookings.checkin');
        Route::post('/bookings/{booking}/check-out',      [ResidenceController::class, 'checkOut'])->name('bookings.checkout');
        Route::post('/bookings/{booking}/cancel',         [ResidenceController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/extras',         [ResidenceController::class, 'storeExtra'])->name('bookings.extras.store');
        Route::post('/bookings/{booking}/payment',        [ResidenceController::class, 'storePayment'])->name('bookings.payment');

        // Caisse
        Route::get('/caisse',                             [ResidenceController::class, 'caisse'])->name('caisse');
        Route::post('/caisse/open',                       [ResidenceController::class, 'openRegister'])->name('caisse.open');
        Route::post('/caisse/{register}/close',           [ResidenceController::class, 'closeRegister'])->name('caisse.close');
    });

    // Gestion des rôles
    Route::resource('roles', RoleController::class);

    // Gestion des permissions
    Route::resource('permissions', PermissionController::class);

    // ── Caissier : ouverture / gestion de sa propre session ───────────────

    Route::prefix('cashier')->name('cashier.')->group(function () {
        // Caissier
        Route::get('/open',                  [CashRegisterController::class, 'open'])->name('open');
        Route::post('/open',                 [CashRegisterController::class, 'startSession'])->name('start');
        Route::get('/history',               [CashRegisterController::class, 'myHistory'])->name('history');
        Route::get('/session/{id}',          [CashRegisterController::class, 'session'])->name('session');
        Route::post('/session/{id}/close',   [CashRegisterController::class, 'closeSession'])->name('close');
        Route::get('/session/{id}/report',   [CashRegisterController::class, 'report'])->name('report');
        Route::get('/session/{id}/ticket-codes/export', [CashRegisterController::class, 'exportTicketCodesCsv'])->name('ticket-codes.export');

        // Comptable
        Route::get('/all',                   [CashRegisterController::class, 'accountantIndex'])->name('accountant');
        Route::post('/session/{id}/validate',[CashRegisterController::class, 'validateSession'])->name('validate');

        // Paramètres : définition des caisses
        Route::get('/settings',              [CashRegisterController::class, 'settingsIndex'])->name('settings');
        Route::post('/settings',             [CashRegisterController::class, 'settingsStore'])->name('settings.store');
    });
    // ── Plan comptable (lecture seule) ─────────────────────────────────────
    Route::get('/settings/chart-of-accounts', [App\Http\Controllers\ChartOfAccountController::class, 'index'])->name('settings.chart-of-accounts');
    // ── Admin : terminaux POS (points de vente) ───────────────────────────
    Route::prefix('settings/pos-terminals')->name('settings.pos-terminals.')->group(function () {
        Route::get('/',                [PosTerminalController::class, 'index'])->name('index');
        Route::get('/create',          [PosTerminalController::class, 'create'])->name('create');
        Route::post('/',               [PosTerminalController::class, 'store'])->name('store');
        Route::get('/{posTerminal}/edit',    [PosTerminalController::class, 'edit'])->name('edit');
        Route::put('/{posTerminal}',         [PosTerminalController::class, 'update'])->name('update');
        Route::delete('/{posTerminal}',      [PosTerminalController::class, 'destroy'])->name('destroy');
        Route::post('/{posTerminal}/dissociate', [PosTerminalController::class, 'dissociate'])->name('dissociate');
    });
    // ── Transferts production → point de vente catering ───────────────────

    Route::prefix('pos-transfer')->name('pos-transfer.')->group(function () {
        // Production : créer un transfert
        Route::get('/create',              [PosTransferController::class, 'create'])->name('create');
        Route::post('/',                   [PosTransferController::class, 'store'])->name('store');
        Route::get('/{id}/print',          [PosTransferController::class, 'print'])->name('print');

        // API : données client
        Route::get('/api/client-contracts',[PosTransferController::class, 'getClientContracts'])->name('api.contracts');
        Route::get('/api/contract-meals',  [PosTransferController::class, 'getContractMeals'])->name('api.meals');

        // Caissier : transferts en attente + validation
        Route::get('/register/{registerId}/pending', [PosTransferController::class, 'pending'])->name('pending');
        Route::post('/{id}/validate',      [PosTransferController::class, 'validate'])->name('validate');

        // Caissier : retour de marchandises
        Route::get('/register/{registerId}/return',  [PosTransferController::class, 'createReturn'])->name('return.create');
        Route::post('/register/{registerId}/return', [PosTransferController::class, 'storeReturn'])->name('return.store');
        Route::get('/return/{id}/print',             [PosTransferController::class, 'printReturn'])->name('print-return');

        // Service plats (API JSON)
        Route::post('/items/{itemId}/serve',[PosTransferController::class, 'serveItem'])->name('items.serve');
        Route::post('/items/{itemId}/sell', [PosTransferController::class, 'sellItem'])->name('items.sell');
    });

    // Stock terminal POS : distribution et vente via API JSON
    Route::post('/pos-terminal-stock/{id}/distribute', [PosTerminalStockController::class, 'distribute'])->name('pos-terminal-stock.distribute');
    Route::post('/pos-terminal-stock/{id}/sell',        [PosTerminalStockController::class, 'sell'])->name('pos-terminal-stock.sell');
});
