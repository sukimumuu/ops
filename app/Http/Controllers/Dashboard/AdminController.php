<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function userManagement()
    {
        $users = User::withoutRole('Superadmin')->select('id', 'name', 'email', 'phone')->paginate(10);

        return view('dashboard.admin.user-management', compact('users'));
    }

    public function transactionAndEscrow()
    {
        return view('dashboard.admin.transaction-and-escrow');
    }

    public function masterDataProperty()
    {
        return view('dashboard.admin.master-data-property');
    }

    public function auditLog()
    {
        return view('dashboard.admin.audit-log');
    }
}
