<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $sellerName = $user->name;

        $sellerStats = [
            'totalProperties' => Property::query()
                ->where('seller_id', $user->id)
                ->count(),
            'publishedProperties' => Property::query()
                ->where('seller_id', $user->id)
                ->where('status', 'published')
                ->count(),
            'pendingSurveys' => Appointment::query()
                ->where('seller_id', $user->id)
                ->where('status', 'requested')
                ->count(),
            'activeTransactions' => Transaction::query()
                ->where('seller_id', $user->id)
                ->whereIn('state', ['held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled'])
                ->count(),
        ];

        $recentProperties = Property::query()
            ->where('seller_id', $user->id)
            ->select(['id', 'title', 'city', 'province', 'price', 'status', 'created_at'])
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentSurveys = Appointment::query()
            ->where('seller_id', $user->id)
            ->where('status', 'requested')
            ->with(['property:id,title', 'buyer:id,name'])
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'property_id', 'buyer_id', 'scheduled_at', 'created_at']);

        $recentTransactions = Transaction::query()
            ->where('seller_id', $user->id)
            ->with(['property:id,title,city,province', 'buyer:id,name', 'seller:id,name'])
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'property_id', 'buyer_id', 'seller_id', 'total_property_amount', 'booking_fee_amount', 'state', 'created_at']);

        $properties = Property::query()
            ->where('seller_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('dashboard.listing.index', [
            'sellerName' => $sellerName,
            'sellerStats' => $sellerStats,
            'recentProperties' => $recentProperties,
            'recentSurveys' => $recentSurveys,
            'recentTransactions' => $recentTransactions,
            'properties' => $properties,
        ]);
    }
    public function myProperties()
    {
        $properties = Property::where('seller_id', Auth::user()->id)->paginate(10);
        return view('dashboard.listing.my-properties', compact('properties'));
    }

    public function requestSurvey()
    {
        return view('dashboard.listing.request-survey');
    }

    public function sellingTransaction()
    {
        return view('dashboard.listing.selling-transaction');
    }

    public function disbursementAccount()
    {
        return view('dashboard.listing.disbursement-account');
    }
}
