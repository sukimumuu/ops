<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    public function myProperties()
    {
        $properties = Property::where('seller_id', Auth::user()->id)->paginate(10);
        return view('dashboard.seller.my-properties', compact('properties'));
    }

    public function requestSurvey()
    {
        return view('dashboard.seller.request-survey');
    }

    public function sellingTransaction()
    {
        return view('dashboard.seller.selling-transaction');
    }

    public function disbursementAccount()
    {
        return view('dashboard.seller.disbursement-account');
    }
}
