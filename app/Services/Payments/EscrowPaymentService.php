<?php

namespace App\Services\Payments;

use App\Models\DpPaymentAttempt;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EscrowPaymentService
{
    public function __construct(
        private readonly XenditPaymentProvider $provider,
    ) {}

    public function createOrResume(Transaction $transaction, User $buyer): DpPaymentAttempt
    {
        $this->provider->assertConfigured();

        $attempt = DB::transaction(function () use ($transaction, $buyer): DpPaymentAttempt {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->getKey())
                ->where('buyer_id', $buyer->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedTransaction->loadMissing(['property', 'buyer']);

            if ($lockedTransaction->property->category !== 'sale') {
                throw new EscrowPaymentException('Pelunasan escrow hanya tersedia untuk transaksi jual beli.');
            }

            if ($lockedTransaction->hasCompletedEscrowPayment()) {
                throw new EscrowPaymentException('Pelunasan untuk transaksi ini telah selesai dikonfirmasi.');
            }

            if (! $lockedTransaction->hasCompletedDownPayment()) {
                throw new EscrowPaymentException('Pembayaran DP harus diselesaikan terlebih dahulu.');
            }

            if ($lockedTransaction->state !== 'bpn_cleared') {
                throw new EscrowPaymentException('Pelunasan hanya dapat dilakukan setelah pengecekan legalitas BPN dan validasi pajak selesai disetujui.');
            }

            $activeAttempt = $lockedTransaction->dpPaymentAttempts()
                ->where('payment_type', DpPaymentAttempt::TYPE_FINAL)
                ->whereIn('status', [
                    DpPaymentAttempt::STATUS_CREATING,
                    DpPaymentAttempt::STATUS_PENDING,
                ])
                ->latest('id')
                ->first();

            if ($activeAttempt !== null) {
                return $activeAttempt;
            }

            $amount = $lockedTransaction->remainingEscrowAmount();

            if ($amount <= 0) {
                throw new EscrowPaymentException('Tidak ada sisa pelunasan yang perlu dibayarkan.');
            }

            $uuid = (string) Str::uuid();
            $referenceId = 'mihom-escrow-'.$uuid;
            $expiresAt = now()->addHours((int) config('services.xendit.virtual_account_expiry_hours'));
            $channelCode = $this->provider->virtualAccountChannelCode();
            $requestPayload = $this->provider->buildVirtualAccountRequest(
                $referenceId,
                $amount,
                $channelCode,
                $lockedTransaction->buyer->name,
                $expiresAt,
            );

            return $lockedTransaction->dpPaymentAttempts()->create([
                'uuid' => $uuid,
                'payment_type' => DpPaymentAttempt::TYPE_FINAL,
                'reference_id' => $referenceId,
                'channel_code' => $channelCode,
                'amount' => $amount,
                'currency' => 'IDR',
                'status' => DpPaymentAttempt::STATUS_CREATING,
                'request_payload' => $requestPayload,
                'expires_at' => $expiresAt,
            ]);
        });

        if ($attempt->status === DpPaymentAttempt::STATUS_PENDING) {
            return $attempt;
        }

        try {
            $providerResponse = $this->provider->createVirtualAccount(
                $attempt->request_payload,
                $attempt->uuid,
            );
        } catch (XenditGatewayException $exception) {
            if (! $exception->retryable) {
                $attempt->update(['status' => DpPaymentAttempt::STATUS_FAILED]);
            }

            throw $exception;
        }

        $paymentRequestId = $providerResponse['id'] ?? $providerResponse['payment_request_id'] ?? null;
        $referenceId = $providerResponse['reference_id'] ?? null;
        $virtualAccountNumber = data_get($providerResponse, 'actions.0.value')
            ?? data_get($providerResponse, 'payment_method.virtual_account.channel_properties.virtual_account_number')
            ?? data_get($providerResponse, 'payment_method.virtual_account.account_number');

        if (! is_string($paymentRequestId)
            || $paymentRequestId === ''
            || $referenceId !== $attempt->reference_id
            || ! is_string($virtualAccountNumber)
            || $virtualAccountNumber === '') {
            throw new XenditGatewayException('Respons pembuatan Virtual Account tidak lengkap. Coba lagi.', true);
        }

        return DB::transaction(function () use ($attempt, $providerResponse, $paymentRequestId, $virtualAccountNumber): DpPaymentAttempt {
            $lockedAttempt = DpPaymentAttempt::query()->lockForUpdate()->findOrFail($attempt->getKey());
            $lockedAttempt->update([
                'xendit_payment_request_id' => $paymentRequestId,
                'virtual_account_number' => $virtualAccountNumber,
                'provider_response' => $providerResponse,
                'status' => DpPaymentAttempt::STATUS_PENDING,
            ]);

            return $lockedAttempt->refresh();
        });
    }
}
