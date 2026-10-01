<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home()
    {
        return view('welcome');
    }

    public function property()
    {
        return view('properties');
    }

    public function propertyDetails()
    {
        return view('property-details');
    }

    public function taxCalculator()
    {
        return view('tax-calculator');
    }
}
