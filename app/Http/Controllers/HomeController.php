<?php

namespace App\Http\Controllers;

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

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }
}
