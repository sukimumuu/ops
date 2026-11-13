<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Payments\DpPaymentException;
use App\Services\Payments\DpPaymentService;
use App\Services\Payments\XenditGatewayException;
use App\Services\Payments\XenditPaymentProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JsonException;

class DpPaymentController extends Controller
{
    public function store(Request $request, Transaction $transaction, DpPaymentService $payments, AuditLogger $auditLogger): RedirectResponse
    {
        $buyer = $request->user();
        abort_unless($buyer instanceof User, 404);

        try {
            $attempt = $payments->createOrResume($transaction, $buyer);
        } catch (DpPaymentException $exception) {
            return back()->withErrors(['dp_payment' => $exception->getMessage()]);
        } catch (XenditGatewayException $exception) {
            Log::warning('Xendit DP payment request could not be completed.', [
                'transaction_id' => $transaction->getKey(),
                'retryable' => $exception->retryable,
                'http_status' => $exception->statusCode,
            ]);

            return back()->withErrors([
                'dp_payment' => 'Pembayaran belum dapat diproses. Silakan coba kembali.',
            ]);
        }

        $property = $transaction->property()->first(['uuid', 'title']);

        $auditLogger->record(
            AuditLog::EVENT_TRANSACTION_DP_INITIATED,
            'Virtual Account DP disiapkan untuk properti '.($property?->title ?? $transaction->uuid).'.',
            $transaction,
            [
                'reference_id' => $attempt->reference_id,
                'amount' => (string) $attempt->amount,
                'payment_status' => $attempt->status,
            ],
            $buyer,
        );

        return redirect()
            ->route('buyer.bought-properties.show', ['property' => $property->uuid])
            ->with('status', 'Virtual Account DP berhasil disiapkan. Transfer hanya ke nomor VA yang ditampilkan.');
    }

    public function webhook(
        Request $request,
        XenditPaymentProvider $provider,
        DpPaymentService $payments,
    ): JsonResponse {
        if (! $provider->hasValidWebhookToken($request->header('x-callback-token'))) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $rawPayload = $request->getContent();

        try {
            $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return response()->json(['message' => 'Invalid webhook payload.'], 400);
        }

        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid webhook payload.'], 400);
        }

        try {
            $payments->processWebhook($rawPayload, $payload, $request->header('webhook-id'));
        } catch (XenditGatewayException $exception) {
            Log::warning('Xendit DP webhook could not be verified against the Payment Request API.', [
                'http_status' => $exception->statusCode,
            ]);

            return response()->json(['message' => 'Payment verification is temporarily unavailable.'], 503);
        } catch (DpPaymentException $exception) {
            Log::error('Xendit DP webhook verification failed.', [
                'reason' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        return response()->json([], 200);
    }
}
