<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function savedProperties()
    {
        return view('dashboard.user.saved-properti');
    }

    public function surveySchedule()
    {
        return view('dashboard.user.survey-schedule');
    }

    public function transactionEscrow()
    {
        return view('dashboard.user.transaction-and-escrow');
    }
}
