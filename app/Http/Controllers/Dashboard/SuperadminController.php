<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuperadminController extends Controller
{
    public function index()
    {
        return view('dashboard.superadmin.index');
    }

    public function userManagement()
    {
        return view('dashboard.superadmin.user-management');
    }

    public function transactionAndEscrow()
    {
        return view('dashboard.superadmin.transaction-and-escrow');
    }

    public function masterDataProperty()
    {
        return view('dashboard.superadmin.master-data-property');
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
