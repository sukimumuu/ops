<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    public function savedProperties()
    {
        return view('dashboard.buyer.saved-properti');
    }

    public function surveySchedule()
    {
        return view('dashboard.buyer.survey-schedule');
    }

    public function transactionEscrow()
    {
        return view('dashboard.buyer.transaction-and-escrow');
    }
}
