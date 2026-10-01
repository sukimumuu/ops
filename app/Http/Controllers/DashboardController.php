<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function profile()
    {
        return view('dashboard.profile');
    }

    public function myProperties()
    {
        return view('dashboard.pages.properti-saya');
    }

    public function detailProperty()
    {
        return view('detail-properti');
    }

    public function detailNotif()
    {
        return view('dashboard.detail-notif');
    }
}
