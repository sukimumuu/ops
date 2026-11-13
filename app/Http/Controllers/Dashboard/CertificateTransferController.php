<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Payments\CertificateTransferException;
use App\Services\Payments\CertificateTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateTransferController extends Controller
{
    public function store(
        Request $request,
        Property $property,
        CertificateTransferService $certificateTransfer,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $ppat = $request->user();
        abort_unless(
            $ppat instanceof User
                && $ppat->hasRole('PPAT')
                && $property->assigned_ppat_id === $ppat->id,
            403,
        );

        $validated = $request->validate([
            'bpn_receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'bpn_reference' => ['required', 'string', 'max:255'],
            'bpn_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'bpn_receipt.required' => 'Pilih tanda terima BPN untuk diunggah.',
            'bpn_receipt.file' => 'Berkas tanda terima BPN tidak valid.',
            'bpn_receipt.mimes' => 'Tanda terima harus berupa PDF, JPG, JPEG, atau PNG.',
            'bpn_receipt.max' => 'Ukuran tanda terima BPN maksimal 10MB.',
            'bpn_reference.required' => 'Nomor atau referensi tanda terima BPN wajib diisi.',
        ]);
        $receipt = $validated['bpn_receipt'];
        abort_unless($receipt instanceof UploadedFile, 422);

        $transaction = $property->transactions()->latest('id')->firstOrFail();
        $isReplacement = filled($transaction->certificate_transfer_receipt_path);

        try {
            $certificateTransfer->submit(
                $transaction,
                $ppat,
                $receipt,
                trim($validated['bpn_reference']),
                isset($validated['bpn_notes']) ? trim($validated['bpn_notes']) : null,
            );
        } catch (CertificateTransferException $exception) {
            return back()->withErrors(['certificate_transfer' => $exception->getMessage()]);
        }

        $auditLogger->record(
            AuditLog::EVENT_TRANSACTION_CERTIFICATE_TRANSFERRED,
            'Tanda terima BPN untuk properti '.$property->title.' diunggah dengan referensi '
                .trim($validated['bpn_reference']).'.',
            $transaction,
            [
                'bpn_reference' => trim($validated['bpn_reference']),
                'is_replacement' => $isReplacement,
                'state' => $transaction->state,
            ],
            $ppat,
        );

        return back()->with(
            'status',
            $isReplacement
                ? 'Tanda Terima BPN berhasil diperbarui.'
                : 'Tanda Terima BPN berhasil disimpan. Transaksi properti telah selesai.',
        );
    }

    public function viewReceipt(Request $request, Property $property): BinaryFileResponse
    {
        $transaction = $this->authorizedReceiptTransaction($request, $property);
        $path = $transaction->certificate_transfer_receipt_path;
        $filename = $this->receiptFilename($property, $path);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function downloadReceipt(Request $request, Property $property): StreamedResponse
    {
        $transaction = $this->authorizedReceiptTransaction($request, $property);
        $path = $transaction->certificate_transfer_receipt_path;
        $filename = $this->receiptFilename($property, $path);

        return Storage::disk('local')->download($path, $filename);
    }

    private function authorizedReceiptTransaction(Request $request, Property $property): Transaction
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $transaction = $property->transactions()->latest('id')->firstOrFail();
        $isBuyerOrSeller = in_array($user->getKey(), [
            $transaction->buyer_id,
            $transaction->seller_id,
        ], true);
        $isAssignedPpat = $user->hasRole('PPAT')
            && $property->assigned_ppat_id === $user->getKey();

        abort_unless($isBuyerOrSeller || $isAssignedPpat, 403);
        abort_if(
            blank($transaction->certificate_transfer_receipt_path)
                || ! Storage::disk('local')->exists($transaction->certificate_transfer_receipt_path),
            404,
        );

        return $transaction;
    }

    private function receiptFilename(Property $property, string $path): string
    {
        return Str::slug($property->title).'-tanda-terima-bpn.'.pathinfo($path, PATHINFO_EXTENSION);
    }
}
