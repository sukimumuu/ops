<?php

namespace App\Services\Payments;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class XenditPaymentProvider
{
    public function assertConfigured(): void
    {
        $this->secretKey();
    }

    public function virtualAccountChannelCode(): string
    {
        $channelCode = config('services.xendit.virtual_account_channel_code');

        if (! is_string($channelCode) || $channelCode === '') {
            throw new XenditGatewayException('Metode Virtual Account belum dikonfigurasi.', false);
        }

        return $channelCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildVirtualAccountRequest(
        string $referenceId,
        int $amount,
        string $channelCode,
        string $customerName,
        CarbonInterface $expiresAt,
    ): array {
        return [
            'reference_id' => $referenceId,
            'currency' => 'IDR',
            'amount' => $amount,
            'country' => 'ID',
            'payment_method' => [
                'type' => 'VIRTUAL_ACCOUNT',
                'reusability' => 'ONE_TIME_USE',
                'reference_id' => $referenceId,
                'virtual_account' => [
                    'channel_code' => $channelCode,
                    'channel_properties' => [
                        'customer_name' => mb_substr($customerName, 0, 255),
                        'expires_at' => $expiresAt->toIso8601String(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createVirtualAccount(array $payload, string $idempotencyKey): array
    {
        $response = $this->send(
            fn (): Response => $this->client()
                ->withHeaders(['idempotency-key' => $idempotencyKey])
                ->post('/payment_requests', $payload),
        );

        return $this->decodeResponse($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaymentRequest(string $paymentRequestId): array
    {
        $response = $this->send(
            fn (): Response => $this->client()
                ->get('/payment_requests/'.rawurlencode($paymentRequestId)),
        );

        return $this->decodeResponse($response);
    }

    public function hasValidWebhookToken(?string $providedToken): bool
    {
        $expectedToken = $this->webhookToken();

        return $expectedToken !== null
            && is_string($providedToken)
            && hash_equals($expectedToken, $providedToken);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl('https://api.xendit.co')
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($this->secretKey(), '')
            ->connectTimeout(3)
            ->timeout(10);
    }

    private function secretKey(): string
    {
        $environment = config('services.xendit.environment');
        $secretKey = match ($environment) {
            'sandbox' => config('services.xendit.sandbox_secret_key'),
            'production' => config('services.xendit.production_secret_key'),
            default => null,
        };

        if (! is_string($secretKey) || $secretKey === '') {
            throw new XenditGatewayException('Kredensial Xendit belum dikonfigurasi.', false);
        }

        return $secretKey;
    }

    private function webhookToken(): ?string
    {
        $environment = config('services.xendit.environment');
        $token = match ($environment) {
            'sandbox' => config('services.xendit.sandbox_webhook_token'),
            'production' => config('services.xendit.production_webhook_token'),
            default => null,
        };

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * @param  callable(): Response  $request
     */
    private function send(callable $request): Response
    {
        try {
            $response = $request();
        } catch (ConnectionException $exception) {
            throw new XenditGatewayException(
                'Xendit tidak dapat dijangkau. Coba lagi menggunakan permintaan yang sama.',
                true,
                previous: $exception,
            );
        }

        if (! $response->successful()) {
            throw new XenditGatewayException(
                $response->serverError() || $response->status() === 429
                    ? 'Xendit sedang tidak tersedia. Coba lagi.'
                    : 'Xendit menolak permintaan pembayaran.',
                $response->serverError() || $response->status() === 429,
                $response->status(),
            );
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(Response $response): array
    {
        $payload = $response->json();

        if (! is_array($payload)) {
            throw new XenditGatewayException('Respons Xendit tidak dapat diproses. Coba lagi.', true);
        }

        return $payload;
    }
}
