<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function perm(string $permission): void
    {
        abort_if(!auth()->user()?->can($permission), 403, 'Action non autorisée.');
    }
}
