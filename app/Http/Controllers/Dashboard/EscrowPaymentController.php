<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payments\EscrowPaymentException;
use App\Services\Payments\EscrowPaymentService;
use App\Services\Payments\XenditGatewayException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EscrowPaymentController extends Controller
{
    public function store(Request $request, Transaction $transaction, EscrowPaymentService $payments): RedirectResponse
    {
        $buyer = $request->user();
        abort_unless($buyer instanceof User, 404);

        try {
            $payments->createOrResume($transaction, $buyer);
        } catch (EscrowPaymentException $exception) {
            return back()->withErrors(['escrow_payment' => $exception->getMessage()]);
        } catch (XenditGatewayException $exception) {
            Log::warning('Xendit escrow settlement payment request could not be completed.', [
                'transaction_id' => $transaction->getKey(),
                'retryable' => $exception->retryable,
                'http_status' => $exception->statusCode,
            ]);

            return back()->withErrors([
                'escrow_payment' => 'Pembayaran pelunasan belum dapat diproses. Silakan coba kembali.',
            ]);
        }

        $propertyUuid = $transaction->property()->value('uuid');

        return redirect()
            ->route('buyer.bought-properties.show', ['property' => $propertyUuid])
            ->with('status', 'Virtual Account pelunasan berhasil disiapkan. Transfer hanya ke nomor VA yang ditampilkan.');
    }
}
