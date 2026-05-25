<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord
     */
    public function index()
    {
        $this->perm('dashboard.view');
        $usersCount = User::count();
        $rolesCount = Role::count();
        $permissionsCount = Permission::count();

        return view('dashboard', compact('usersCount', 'rolesCount', 'permissionsCount'));
    }
}
