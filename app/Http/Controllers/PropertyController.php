<?php

namespace App\Http\Controllers;

class PropertyController extends Controller
{
    public function index()
    {
        return view('katalog');
    }

    public function welcome()
    {
        return view('welcome');
    }

    public function show(string $property)
    {
        return view('detail-properti');
    }
}
