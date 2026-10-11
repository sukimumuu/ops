<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;

class SuperadminController extends Controller
{
    public function userManagement()
    {
        $users = User::withoutRole('Superadmin')->select('id', 'name', 'email', 'phone')->paginate(10);

        return view('dashboard.superadmin.user-management', compact('users'));
    }

    public function transactionAndEscrow()
    {
        return view('dashboard.superadmin.transaction-and-escrow');
    }

    public function masterDataProperty()
    {
        $pendingProperties = Property::query()
            ->with(['seller:id,name', 'photos', 'certificate'])
            ->where('status', 'pending_verification')
            ->latest()
            ->limit(6)
            ->get();
            
        return view('dashboard.superadmin.master-data-property', compact('pendingProperties'));
    }

    public function configurationSystem()
    {
        return view('dashboard.superadmin.configuration-system');
    }

    public function auditLog()
    {
        return view('dashboard.superadmin.audit-log');
    }
}
