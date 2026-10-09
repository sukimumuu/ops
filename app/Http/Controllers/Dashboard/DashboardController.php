<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $viewData = [];
        $seller = $request->user();

        if ($seller !== null && $seller->hasRole('Seller')) {
            $viewData = [
                'sellerName' => $seller->name,
                'sellerStats' => [
                    'totalProperties' => Property::query()
                        ->where('seller_id', $seller->id)
                        ->count(),
                    'publishedProperties' => Property::query()
                        ->where('seller_id', $seller->id)
                        ->where('status', 'published')
                        ->count(),
                    'pendingSurveys' => Appointment::query()
                        ->where('seller_id', $seller->id)
                        ->where('status', 'requested')
                        ->count(),
                    'activeTransactions' => Transaction::query()
                        ->where('seller_id', $seller->id)
                        ->whereIn('state', ['held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled'])
                        ->count(),
                ],
                'recentProperties' => Property::query()
                    ->where('seller_id', $seller->id)
                    ->select(['id', 'title', 'city', 'province', 'price', 'status', 'created_at'])
                    ->latest()
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(),
                'recentSurveys' => Appointment::query()
                    ->where('seller_id', $seller->id)
                    ->where('status', 'requested')
                    ->with(['property:id,title', 'buyer:id,name'])
                    ->latest()
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(['id', 'property_id', 'buyer_id', 'scheduled_at', 'created_at']),
                'recentTransactions' => Transaction::query()
                    ->where('seller_id', $seller->id)
                    ->with(['property:id,title', 'buyer:id,name'])
                    ->latest()
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(['id', 'property_id', 'buyer_id', 'total_property_amount', 'state', 'created_at']),
            ];
        } elseif ($seller !== null && $seller->hasRole('Buyer')) {
            $buyer = $seller;

            $viewData = [
                'buyerName' => $buyer->name,
                'buyerStats' => [
                    'totalSurveys' => Appointment::query()
                        ->where('buyer_id', $buyer->id)
                        ->count(),
                    'upcomingSurveys' => Appointment::query()
                        ->where('buyer_id', $buyer->id)
                        ->whereIn('status', ['requested', 'confirmed'])
                        ->where('scheduled_at', '>=', now())
                        ->count(),
                    'activeTransactions' => Transaction::query()
                        ->where('buyer_id', $buyer->id)
                        ->whereIn('state', ['draft', 'held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled'])
                        ->count(),
                    'completedTransactions' => Transaction::query()
                        ->where('buyer_id', $buyer->id)
                        ->where('state', 'completed')
                        ->count(),
                ],
                'upcomingSurveys' => Appointment::query()
                    ->where('buyer_id', $buyer->id)
                    ->whereIn('status', ['requested', 'confirmed'])
                    ->where('scheduled_at', '>=', now())
                    ->with(['property:id,title,city,province', 'seller:id,name'])
                    ->orderBy('scheduled_at')
                    ->orderBy('id')
                    ->limit(5)
                    ->get(['id', 'property_id', 'seller_id', 'scheduled_at', 'status']),
                'recentTransactions' => Transaction::query()
                    ->where('buyer_id', $buyer->id)
                    ->with(['property:id,title,city,province', 'seller:id,name'])
                    ->latest()
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(['id', 'property_id', 'seller_id', 'total_property_amount', 'booking_fee_amount', 'state', 'created_at']),
            ];
        }

        return view('dashboard.index', $viewData);
    }

    public function profile()
    {
        return view('dashboard.profile');
    }

    public function detailNotif()
    {
        return view('dashboard.detail-notif');
    }
}
