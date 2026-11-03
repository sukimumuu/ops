<?php

namespace App\Services\Payments;

use App\Models\Appointment;
use App\Models\DpPaymentAttempt;
use App\Models\PaymentWebhookEvent;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use App\Models\TransactionLogs;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DpPaymentService
{
    public function __construct(
        private readonly DpAmountCalculator $amountCalculator,
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

            if ($lockedTransaction->state !== 'draft') {
                throw new DpPaymentException('Transaksi ini belum atau tidak dapat menerima pembayaran DP.');
            }

            $lockedTransaction->loadMissing(['property', 'buyer']);

            if ($lockedTransaction->property->category !== 'sale') {
                throw new DpPaymentException('Pembayaran DP hanya tersedia untuk transaksi jual beli.');
            }

            $hasDealSurvey = Appointment::query()
                ->where('property_id', $lockedTransaction->property_id)
                ->where('buyer_id', $buyer->getKey())
                ->where('status', Appointment::STATUS_COMPLETED)
                ->where('buyer_intent', Appointment::BUYER_INTENT_DEAL)
                ->whereNotNull('ppat_meeting_date')
                ->whereIn('ppat_meeting_status', [
                    Appointment::PPAT_MEETING_STATUS_CONFIRMED,
                    Appointment::PPAT_MEETING_STATUS_RESCHEDULED,
                ])
                ->exists();

            if (! $hasDealSurvey) {
                throw new DpPaymentException('Pembayaran DP hanya dapat dilakukan setelah jadwal temu PPAT disetujui.');
            }

            $activeAttempt = $lockedTransaction->dpPaymentAttempts()
                ->whereIn('status', [
                    DpPaymentAttempt::STATUS_CREATING,
                    DpPaymentAttempt::STATUS_PENDING,
                ])
                ->latest('id')
                ->first();

            if ($activeAttempt !== null) {
                return $activeAttempt;
            }

            $uuid = (string) Str::uuid();
            $referenceId = 'mihom-dp-'.$uuid;
            $amount = $this->amountCalculator->calculate($lockedTransaction->total_property_amount);
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
        $virtualAccountNumber = data_get(
            $providerResponse,
            'payment_method.virtual_account.channel_properties.account_number',
        ) ?? data_get(
            $providerResponse,
            'payment_method.virtual_account.channel_properties.virtual_account_number',
        ) ?? data_get(
            $providerResponse,
            'payment_method.virtual_account.account_number',
        ) ?? ($providerResponse['virtual_account_number'] ?? null);

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

    /**
     * @param  array<string, mixed>  $payload
     */
    public function processWebhook(string $rawPayload, array $payload, ?string $webhookId): void
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $paymentRequestId = $data['payment_request_id']
            ?? data_get($data, 'payment_request.id')
            ?? $data['id']
            ?? $payload['payment_request_id']
            ?? null;
        $paymentRequestId = is_string($paymentRequestId) && $paymentRequestId !== ''
            ? $paymentRequestId
            : null;
        $eventType = $payload['event'] ?? $payload['type'] ?? null;
        $eventKey = hash('sha256', is_string($webhookId) && $webhookId !== '' ? $webhookId : $rawPayload);

        DB::table('payment_webhook_events')->insertOrIgnore([
            'event_key' => $eventKey,
            'event_type' => is_string($eventType) ? $eventType : null,
            'payment_request_id' => $paymentRequestId,
            'raw_payload' => $rawPayload,
            'processing_status' => 'received',
            'received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $event = PaymentWebhookEvent::query()->where('event_key', $eventKey)->firstOrFail();

        if (in_array($event->processing_status, ['processed', 'ignored'], true)) {
            return;
        }

        if ($paymentRequestId === null) {
            $event->update([
                'processing_status' => 'ignored',
                'processing_error' => 'Webhook tidak berisi ID Payment Request.',
                'processed_at' => now(),
            ]);

            return;
        }

        $attempt = DpPaymentAttempt::query()
            ->where('xendit_payment_request_id', $paymentRequestId)
            ->first();

        if ($attempt === null) {
            $event->update([
                'processing_status' => 'ignored',
                'processing_error' => 'Payment Request tidak dikenal.',
                'processed_at' => now(),
            ]);

            return;
        }

        $event->update(['dp_payment_attempt_id' => $attempt->getKey()]);

        try {
            $providerResponse = $this->provider->getPaymentRequest($paymentRequestId);
            $this->assertPaymentRequestMatches($providerResponse, $attempt);
            $status = $this->mapProviderStatus($providerResponse['status'] ?? null);
        } catch (DpPaymentException|XenditGatewayException $exception) {
            $event->update([
                'processing_status' => 'failed',
                'processing_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        DB::transaction(function () use ($event, $attempt, $status): void {
            $lockedEvent = PaymentWebhookEvent::query()->lockForUpdate()->findOrFail($event->getKey());

            if (in_array($lockedEvent->processing_status, ['processed', 'ignored'], true)) {
                return;
            }

            $lockedAttempt = DpPaymentAttempt::query()->lockForUpdate()->findOrFail($attempt->getKey());
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($lockedAttempt->transaction_id);

            if ($status === DpPaymentAttempt::STATUS_PAID) {
                $lockedAttempt->update([
                    'status' => DpPaymentAttempt::STATUS_PAID,
                    'paid_at' => $lockedAttempt->paid_at ?? now(),
                ]);

                $transaction->ledgerEntries()->firstOrCreate(
                    ['dp_payment_attempt_id' => $lockedAttempt->getKey()],
                    [
                        'amount' => $lockedAttempt->amount,
                        'currency' => $lockedAttempt->currency,
                        'status' => TransactionLedgerEntry::STATUS_RESERVED,
                        'reserved_at' => now(),
                    ],
                );

                if ($transaction->state === 'draft') {
                    $transaction->update([
                        'state' => 'dp_paid',
                        'payment_gateway_ref' => $lockedAttempt->xendit_payment_request_id,
                        'payment_method' => 'xendit_virtual_account',
                        'paid_at' => $lockedAttempt->paid_at ?? now(),
                    ]);

                    TransactionLogs::query()->create([
                        'transaction_id' => $transaction->getKey(),
                        'actor_id' => null,
                        'from_state' => 'draft',
                        'to_state' => 'dp_paid',
                        'notes' => 'DP dikonfirmasi oleh Xendit dan dicatat sebagai dana cadangan. Ref: '.$lockedAttempt->reference_id,
                    ]);
                }
            } elseif ($lockedAttempt->status !== DpPaymentAttempt::STATUS_PAID) {
                $lockedAttempt->update(['status' => $status]);
            }

            $lockedEvent->update([
                'processing_status' => 'processed',
                'processing_error' => null,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $providerResponse
     */
    private function assertPaymentRequestMatches(array $providerResponse, DpPaymentAttempt $attempt): void
    {
        $paymentRequestId = $providerResponse['id'] ?? $providerResponse['payment_request_id'] ?? null;
        $amount = $providerResponse['amount'] ?? $providerResponse['request_amount'] ?? null;
        $currency = $providerResponse['currency'] ?? null;
        $amountString = is_string($amount) || is_int($amount) || is_float($amount)
            ? (string) $amount
            : null;

        if ($paymentRequestId !== $attempt->xendit_payment_request_id
            || ($providerResponse['reference_id'] ?? null) !== $attempt->reference_id
            || ! is_string($currency)
            || $currency !== 'IDR'
            || $amountString === null
            || ! preg_match('/^\d+(?:\.\d+)?$/D', $amountString)
            || BigDecimal::of($amountString)->compareTo((string) $attempt->amount) !== 0) {
            throw new DpPaymentException('Data pembayaran yang dikonfirmasi Xendit tidak cocok dengan transaksi.');
        }
    }

    private function mapProviderStatus(mixed $providerStatus): string
    {
        if (! is_string($providerStatus)) {
            throw new DpPaymentException('Status pembayaran Xendit tidak valid.');
        }

        return match (strtoupper($providerStatus)) {
            'SUCCEEDED' => DpPaymentAttempt::STATUS_PAID,
            'PENDING', 'REQUIRES_ACTION' => DpPaymentAttempt::STATUS_PENDING,
            'EXPIRED' => DpPaymentAttempt::STATUS_EXPIRED,
            'FAILED' => DpPaymentAttempt::STATUS_FAILED,
            'CANCELED', 'CANCELLED' => DpPaymentAttempt::STATUS_CANCELED,
            default => throw new DpPaymentException('Status pembayaran Xendit tidak didukung.'),
        };
    }
}
