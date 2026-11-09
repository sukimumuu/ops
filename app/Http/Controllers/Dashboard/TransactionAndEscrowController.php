<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DpPaymentAttempt;
use App\Models\EscrowDisbursement;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use App\Models\User;
use App\Services\Payments\EscrowFundReleaseException;
use App\Services\Payments\EscrowFundReleaseService;
use Brick\Math\BigDecimal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TransactionAndEscrowController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'payment_type' => ['nullable', 'in:dp,final'],
            'payment_status' => ['nullable', 'in:creating,pending,paid,expired,failed,canceled'],
            'release_status' => ['nullable', 'in:pending,released'],
        ]);
        $search = trim($filters['search'] ?? '');

        $payments = DpPaymentAttempt::query()
            ->with([
                'transaction.property:id,title,city,province',
                'transaction.buyer:id,name',
                'transaction.seller:id,name',
            ])
            ->when($search !== '', fn (Builder $query): Builder => $this->searchPayments($query, $search))
            ->when($filters['payment_type'] ?? null, function (Builder $query, string $type): void {
                $query->where(function (Builder $query) use ($type): void {
                    $query->where('payment_type', $type);

                    if ($type === DpPaymentAttempt::TYPE_DP) {
                        $query->orWhereNull('payment_type');
                    }
                });
            })
            ->when(
                $filters['payment_status'] ?? null,
                fn (Builder $query, string $status): Builder => $query->where('status', $status),
            )
            ->latest('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'payments_page')
            ->withQueryString();

        $disbursements = EscrowDisbursement::query()
            ->with([
                'transaction.property:id,title,city,province',
                'recipient:id,name',
                'initiatedBy:id,name',
                'confirmedBy:id,name',
            ])
            ->when($search !== '', fn (Builder $query): Builder => $this->searchDisbursements($query, $search))
            ->when(
                $filters['release_status'] ?? null,
                fn (Builder $query, string $status): Builder => $query->where('status', $status),
            )
            ->latest('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'disbursements_page')
            ->withQueryString();

        $eligibleTransactions = $this->eligibleTransactions($search);
        $availableToRelease = (clone $eligibleTransactions)->sum('total_property_amount');
        $eligibleTransactions = $eligibleTransactions
            ->with([
                'property:id,title,city,province',
                'buyer:id,name',
                'seller:id,name',
            ])
            ->orderByDesc('ajb_signed_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'release_page')
            ->withQueryString();

        $reservedFunds = BigDecimal::of((string) TransactionLedgerEntry::query()
            ->where('status', TransactionLedgerEntry::STATUS_RESERVED)
            ->sum('amount'));
        $pendingDisbursements = BigDecimal::of((string) EscrowDisbursement::query()
            ->where('status', EscrowDisbursement::STATUS_PENDING)
            ->sum('amount'));
        $heldFunds = $reservedFunds->minus($pendingDisbursements);

        $summary = [
            'held' => (string) ($heldFunds->compareTo('0') < 0 ? BigDecimal::of('0') : $heldFunds),
            'pending_release' => (string) $pendingDisbursements,
            'released' => (string) TransactionLedgerEntry::query()
                ->where('status', TransactionLedgerEntry::STATUS_RELEASED)
                ->sum('amount'),
            'pending_payments' => (string) DpPaymentAttempt::query()
                ->whereIn('status', [DpPaymentAttempt::STATUS_CREATING, DpPaymentAttempt::STATUS_PENDING])
                ->sum('amount'),
            'available_to_release' => (string) $availableToRelease,
        ];

        $view = $request->user()?->hasRole('Superadmin')
            ? 'dashboard.superadmin.transaction-and-escrow'
            : 'dashboard.admin.transaction-and-escrow';

        return view($view, compact('payments', 'disbursements', 'eligibleTransactions', 'summary', 'filters'));
    }

    public function store(
        Request $request,
        Transaction $transaction,
        EscrowFundReleaseService $releases,
    ): RedirectResponse {
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        try {
            $releases->initiate($transaction, $actor);
        } catch (EscrowFundReleaseException $exception) {
            return back()->withErrors(['escrow_release' => $exception->getMessage()]);
        }

        return back()->with('status', 'Pencairan escrow dicatat sebagai menunggu konfirmasi transfer.');
    }

    public function confirm(
        Request $request,
        EscrowDisbursement $escrowDisbursement,
        EscrowFundReleaseService $releases,
    ): RedirectResponse {
        $validated = $request->validate([
            'transfer_reference' => ['required', 'string', 'max:255'],
        ]);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        try {
            $releases->confirm(
                $escrowDisbursement,
                $actor,
                trim($validated['transfer_reference']),
            );
        } catch (EscrowFundReleaseException $exception) {
            return back()->withErrors(['escrow_release' => $exception->getMessage()]);
        }

        return back()->with('status', 'Pencairan escrow berhasil dikonfirmasi dan transaksi telah diperbarui.');
    }

    private function eligibleTransactions(string $search): Builder
    {
        return Transaction::query()
            ->where('state', 'ajb_scheduled')
            ->whereNotNull('ajb_document_path')
            ->whereNotNull('ajb_signed_at')
            ->whereHas('property', fn (Builder $property): Builder => $property->where('category', 'sale'))
            ->whereHas('property.appointments', function (Builder $query): void {
                $query->whereColumn('appointments.buyer_id', 'transactions.buyer_id')
                    ->where('appointments.status', Appointment::STATUS_COMPLETED)
                    ->where('appointments.buyer_intent', Appointment::BUYER_INTENT_DEAL)
                    ->where('appointments.ajb_meeting_status', Appointment::AJB_MEETING_STATUS_CONFIRMED)
                    ->whereColumn('appointments.ajb_document_path', 'transactions.ajb_document_path')
                    ->whereNotNull('appointments.ajb_signed_at');
            })
            ->whereHas('ledgerEntries', function (Builder $query): void {
                $query->where('status', TransactionLedgerEntry::STATUS_RESERVED)
                    ->where('type', TransactionLedgerEntry::TYPE_DP)
                    ->where('currency', 'IDR')
                    ->whereHas('dpPaymentAttempt', function (Builder $attempts): void {
                        $attempts->where('status', DpPaymentAttempt::STATUS_PAID)
                            ->where('currency', 'IDR')
                            ->whereColumn('dp_payment_attempts.amount', 'transaction_ledger_entries.amount')
                            ->where(function (Builder $attempts): void {
                                $attempts->where('payment_type', DpPaymentAttempt::TYPE_DP)
                                    ->orWhereNull('payment_type');
                            });
                    });
            })
            ->whereHas('ledgerEntries', function (Builder $query): void {
                $query->where('status', TransactionLedgerEntry::STATUS_RESERVED)
                    ->where('type', TransactionLedgerEntry::TYPE_FINAL)
                    ->where('currency', 'IDR')
                    ->whereHas('dpPaymentAttempt', fn (Builder $attempts): Builder => $attempts
                        ->where('status', DpPaymentAttempt::STATUS_PAID)
                        ->where('currency', 'IDR')
                        ->whereColumn('dp_payment_attempts.amount', 'transaction_ledger_entries.amount')
                        ->where('payment_type', DpPaymentAttempt::TYPE_FINAL));
            })
            ->whereDoesntHave('escrowDisbursements', fn (Builder $query): Builder => $query
                ->whereIn('status', [
                    EscrowDisbursement::STATUS_PENDING,
                    EscrowDisbursement::STATUS_RELEASED,
                ]))
            ->whereRaw(
                '(SELECT COALESCE(SUM(amount), 0) FROM transaction_ledger_entries WHERE transaction_id = transactions.id AND status = ?) = transactions.total_property_amount',
                [TransactionLedgerEntry::STATUS_RESERVED],
            )
            ->when($search !== '', fn (Builder $query): Builder => $this->searchTransactions($query, $search));
    }

    private function searchPayments(Builder $query, string $search): Builder
    {
        $term = '%'.$search.'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('reference_id', 'like', $term)
                ->orWhere('xendit_payment_request_id', 'like', $term)
                ->orWhereHas('transaction', function (Builder $transaction) use ($term): void {
                    $transaction->where('uuid', 'like', $term)
                        ->orWhereHas('property', fn (Builder $property): Builder => $property->where('title', 'like', $term))
                        ->orWhereHas('buyer', fn (Builder $buyer): Builder => $buyer->where('name', 'like', $term))
                        ->orWhereHas('seller', fn (Builder $seller): Builder => $seller->where('name', 'like', $term));
                });
        });
    }

    private function searchDisbursements(Builder $query, string $search): Builder
    {
        $term = '%'.$search.'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('uuid', 'like', $term)
                ->orWhere('recipient_name', 'like', $term)
                ->orWhere('transfer_reference', 'like', $term)
                ->orWhereHas('transaction', function (Builder $transaction) use ($term): void {
                    $transaction->where('uuid', 'like', $term)
                        ->orWhereHas('property', fn (Builder $property): Builder => $property->where('title', 'like', $term))
                        ->orWhereHas('buyer', fn (Builder $buyer): Builder => $buyer->where('name', 'like', $term))
                        ->orWhereHas('seller', fn (Builder $seller): Builder => $seller->where('name', 'like', $term));
                });
        });
    }

    private function searchTransactions(Builder $query, string $search): Builder
    {
        $term = '%'.$search.'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('uuid', 'like', $term)
                ->orWhereHas('property', fn (Builder $property): Builder => $property->where('title', 'like', $term))
                ->orWhereHas('buyer', fn (Builder $buyer): Builder => $buyer->where('name', 'like', $term))
                ->orWhereHas('seller', fn (Builder $seller): Builder => $seller->where('name', 'like', $term));
        });
    }
}
