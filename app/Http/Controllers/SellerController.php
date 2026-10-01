<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function myProperties()
    {
        return view('dashboard.seller.my-properties');
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
