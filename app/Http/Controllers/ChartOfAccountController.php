<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $this->perm('accounting.view');

        $accounts = ChartOfAccount::forCompany()
            ->orderBy('code')
            ->get();

        return view('settings.chart-of-accounts', compact('accounts'));
    }
}
