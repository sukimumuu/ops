<?php

namespace App\Services\Payments;

use App\Models\Appointment;
use App\Models\DpPaymentAttempt;
use App\Models\EscrowDisbursement;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use App\Models\TransactionLogs;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EscrowFundReleaseService
{
    public function initiate(Transaction $transaction, User $actor): EscrowDisbursement
    {
        return DB::transaction(function () use ($transaction, $actor): EscrowDisbursement {
            $lockedTransaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($transaction->getKey());

            $amount = $this->eligibleAmount($lockedTransaction);
            $hasActiveDisbursement = $lockedTransaction->escrowDisbursements()
                ->whereIn('status', [
                    EscrowDisbursement::STATUS_PENDING,
                    EscrowDisbursement::STATUS_RELEASED,
                ])
                ->exists();

            if ($hasActiveDisbursement) {
                throw new EscrowFundReleaseException('Pencairan untuk transaksi ini sudah dimulai atau telah selesai.');
            }

            $recipient = User::query()->findOrFail($lockedTransaction->seller_id);

            $disbursement = $lockedTransaction->escrowDisbursements()->create([
                'uuid' => (string) Str::uuid(),
                'recipient_user_id' => $recipient->getKey(),
                'recipient_name' => $recipient->name,
                'amount' => (string) $amount,
                'currency' => 'IDR',
                'status' => EscrowDisbursement::STATUS_PENDING,
                'initiated_by' => $actor->getKey(),
            ]);

            TransactionLogs::query()->create([
                'transaction_id' => $lockedTransaction->getKey(),
                'actor_id' => $actor->getKey(),
                'from_state' => (string) $lockedTransaction->state,
                'to_state' => (string) $lockedTransaction->state,
                'notes' => 'Pencairan escrow '.$disbursement->uuid.' sebesar Rp '
                    .number_format((float) $disbursement->amount, 0, ',', '.')
                    .' kepada '.$disbursement->recipient_name.' menunggu konfirmasi transfer bank.',
            ]);

            return $disbursement;
        }, 3);
    }

    public function confirm(
        EscrowDisbursement $disbursement,
        User $actor,
        string $transferReference,
    ): EscrowDisbursement {
        return DB::transaction(function () use ($disbursement, $actor, $transferReference): EscrowDisbursement {
            $lockedDisbursement = EscrowDisbursement::query()
                ->lockForUpdate()
                ->findOrFail($disbursement->getKey());
            $lockedTransaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($lockedDisbursement->transaction_id);

            if ($lockedDisbursement->status !== EscrowDisbursement::STATUS_PENDING) {
                throw new EscrowFundReleaseException('Hanya pencairan yang masih menunggu konfirmasi yang dapat diselesaikan.');
            }

            $eligibleAmount = $this->eligibleAmount($lockedTransaction);

            if ($eligibleAmount->compareTo((string) $lockedDisbursement->amount) !== 0) {
                throw new EscrowFundReleaseException('Saldo escrow berubah dan tidak cocok dengan nilai pencairan. Periksa transaksi sebelum melanjutkan.');
            }

            $releasedEntries = $lockedTransaction->ledgerEntries()
                ->where('status', TransactionLedgerEntry::STATUS_RESERVED)
                ->update([
                    'status' => TransactionLedgerEntry::STATUS_RELEASED,
                    'updated_at' => now(),
                ]);

            if ($releasedEntries === 0) {
                throw new EscrowFundReleaseException('Tidak ada dana escrow tertahan yang dapat dilepas.');
            }

            $lockedDisbursement->update([
                'status' => EscrowDisbursement::STATUS_RELEASED,
                'confirmed_by' => $actor->getKey(),
                'transfer_reference' => $transferReference,
                'confirmed_at' => now(),
            ]);

            $fromState = (string) $lockedTransaction->state;
            $lockedTransaction->update(['state' => Transaction::STATE_CERTIFICATE_TRANSFER_PENDING]);

            TransactionLogs::query()->create([
                'transaction_id' => $lockedTransaction->getKey(),
                'actor_id' => $actor->getKey(),
                'from_state' => $fromState,
                'to_state' => Transaction::STATE_CERTIFICATE_TRANSFER_PENDING,
                'notes' => 'Pencairan escrow sebesar Rp '.number_format((float) $lockedDisbursement->amount, 0, ',', '.')
                    .' kepada '.$lockedDisbursement->recipient_name.' dikonfirmasi. Referensi transfer: '.$transferReference
                    .'. Tahap 7 balik nama sertifikat telah dibuka.',
            ]);

            return $lockedDisbursement->refresh();
        }, 3);
    }

    private function eligibleAmount(Transaction $transaction): BigDecimal
    {
        if ($transaction->state !== 'ajb_scheduled'
            || blank($transaction->ajb_document_path)
            || $transaction->ajb_signed_at === null) {
            throw new EscrowFundReleaseException('Pencairan hanya dapat dilakukan setelah bukti AJB ditandatangani dan diunggah PPAT.');
        }

        $transaction->loadMissing('property');

        if ($transaction->property->category !== 'sale') {
            throw new EscrowFundReleaseException('Pencairan escrow tahap ini hanya tersedia untuk transaksi jual beli.');
        }

        $signedAppointment = Appointment::query()
            ->where('property_id', $transaction->property_id)
            ->where('buyer_id', $transaction->buyer_id)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->where('buyer_intent', Appointment::BUYER_INTENT_DEAL)
            ->where('ajb_meeting_status', Appointment::AJB_MEETING_STATUS_CONFIRMED)
            ->where('ajb_document_path', $transaction->ajb_document_path)
            ->whereNotNull('ajb_signed_at')
            ->lockForUpdate()
            ->first();

        if ($signedAppointment === null) {
            throw new EscrowFundReleaseException('Bukti AJB dan jadwal penandatanganan yang disetujui PPAT harus terverifikasi.');
        }

        $ledgerEntries = $transaction->ledgerEntries()
            ->where('status', TransactionLedgerEntry::STATUS_RESERVED)
            ->with('dpPaymentAttempt')
            ->lockForUpdate()
            ->get();
        $amount = BigDecimal::of('0');
        $hasConfirmedDp = false;
        $hasConfirmedFinalPayment = false;

        foreach ($ledgerEntries as $entry) {
            $attempt = $entry->dpPaymentAttempt;

            if ($attempt === null || $attempt->status !== DpPaymentAttempt::STATUS_PAID) {
                throw new EscrowFundReleaseException('Terdapat catatan escrow yang tidak terkait dengan pembayaran terverifikasi.');
            }

            $entryAmount = BigDecimal::of((string) $entry->amount);

            if ($entry->currency !== 'IDR'
                || $attempt->currency !== 'IDR'
                || $entryAmount->compareTo((string) $attempt->amount) !== 0
                || $entryAmount->compareTo('0') <= 0) {
                throw new EscrowFundReleaseException('Jumlah atau mata uang pada catatan escrow tidak cocok dengan pembayaran terverifikasi.');
            }

            if ($entry->type === TransactionLedgerEntry::TYPE_DP && $attempt->isDpPayment()) {
                $hasConfirmedDp = true;
            } elseif ($entry->type === TransactionLedgerEntry::TYPE_FINAL && $attempt->isFinalPayment()) {
                $hasConfirmedFinalPayment = true;
            } else {
                throw new EscrowFundReleaseException('Jenis pembayaran pada catatan escrow tidak cocok dengan pembayaran terverifikasi.');
            }

            $amount = $amount->plus($entryAmount);
        }

        if (! $hasConfirmedDp || ! $hasConfirmedFinalPayment) {
            throw new EscrowFundReleaseException('Pembayaran DP dan pelunasan escrow harus terverifikasi sebelum pencairan.');
        }

        if ($amount->compareTo((string) $transaction->total_property_amount) !== 0) {
            throw new EscrowFundReleaseException('Jumlah dana escrow terverifikasi tidak sama dengan nilai transaksi properti.');
        }

        return $amount;
    }
}
