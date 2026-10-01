<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function myProperties() 
    {
        return view('dashboard.pages.properti-saya');
    }

    public function detailProperty()
    {
        return view('detail-properti');
    }

}
