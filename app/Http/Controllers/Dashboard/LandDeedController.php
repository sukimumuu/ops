<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LandDeedController extends Controller
{
    public function bpnCheck()
    {
        return view('dashboard.land-deed.bpn-check');
    }

    public function ajbSigning()
    {
        return view('dashboard.land-deed.ajb-signing');
    }

    public function deedManagement()
    {
        return view('dashboard.land-deed.deed-management');
    }

    public function taxValidation()
    {
        return view('dashboard.land-deed.tax-validation');
    }

    public function transferForOwnership()
    {
        return view('dashboard.land-deed.transfer-for-ownership');
    }
}
