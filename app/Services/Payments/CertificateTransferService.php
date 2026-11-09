<?php

namespace App\Services\Payments;

use App\Models\EscrowDisbursement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CertificateTransferService
{
    public function submit(
        Transaction $transaction,
        User $ppat,
        UploadedFile $receipt,
        string $reference,
        ?string $notes,
    ): Transaction {
        $storedPath = $receipt->store('certificate-transfer-receipts', 'local');

        if (! is_string($storedPath)) {
            throw new CertificateTransferException('Tanda Terima BPN gagal disimpan. Silakan coba kembali.');
        }

        $previousPath = null;

        try {
            $savedTransaction = DB::transaction(function () use (
                $transaction,
                $ppat,
                $storedPath,
                $reference,
                $notes,
                &$previousPath,
            ): Transaction {
                $lockedTransaction = Transaction::query()
                    ->lockForUpdate()
                    ->findOrFail($transaction->getKey());
                $lockedTransaction->loadMissing('property');

                if (! $ppat->hasRole('PPAT')
                    || $lockedTransaction->property->assigned_ppat_id !== $ppat->getKey()) {
                    throw new CertificateTransferException('Hanya PPAT yang ditugaskan untuk properti ini yang dapat mengunggah tanda terima.');
                }

                $hasReleasedEscrow = $lockedTransaction->escrowDisbursements()
                    ->where('status', EscrowDisbursement::STATUS_RELEASED)
                    ->exists();
                $isStageSevenOpen = in_array($lockedTransaction->state, [
                    Transaction::STATE_CERTIFICATE_TRANSFER_PENDING,
                    Transaction::STATE_COMPLETED,
                ], true);

                if ($lockedTransaction->property->category !== 'sale'
                    || ! $hasReleasedEscrow
                    || ! $isStageSevenOpen) {
                    throw new CertificateTransferException('Tahap 7 hanya dapat diproses setelah Admin mengonfirmasi pencairan dana escrow pada Tahap 6.');
                }

                $previousPath = $lockedTransaction->certificate_transfer_receipt_path;
                $isReplacement = filled($previousPath);
                $fromState = (string) $lockedTransaction->state;
                $submittedAt = now();

                $lockedTransaction->forceFill([
                    'certificate_transfer_receipt_path' => $storedPath,
                    'certificate_transfer_reference' => $reference,
                    'certificate_transfer_notes' => $notes,
                    'certificate_transfer_submitted_at' => $submittedAt,
                    'certificate_transfer_submitted_by' => $ppat->getKey(),
                    'state' => Transaction::STATE_COMPLETED,
                ])->save();
                $lockedTransaction->property->forceFill(['status' => 'sold'])->save();

                $lockedTransaction->logs()->create([
                    'actor_id' => $ppat->getKey(),
                    'from_state' => $fromState,
                    'to_state' => Transaction::STATE_COMPLETED,
                    'notes' => ($isReplacement ? 'PPAT memperbarui' : 'PPAT mengunggah')
                        .' Tanda Terima BPN. Referensi: '.$reference.'.'
                        .(filled($notes) ? ' Catatan: '.$notes : ''),
                ]);

                return $lockedTransaction->refresh();
            }, 3);
        } catch (Throwable $exception) {
            $localDisk = Storage::disk('local');

            if ($localDisk->exists($storedPath) && ! $localDisk->delete($storedPath)) {
                Log::warning('Failed to remove an uncommitted BPN receipt upload.', [
                    'transaction_id' => $transaction->getKey(),
                ]);
            }

            throw $exception;
        }

        if (filled($previousPath) && $previousPath !== $storedPath) {
            $localDisk = Storage::disk('local');

            if ($localDisk->exists($previousPath) && ! $localDisk->delete($previousPath)) {
                Log::warning('Failed to remove a replaced BPN receipt upload.', [
                    'transaction_id' => $transaction->getKey(),
                ]);
            }
        }

        return $savedTransaction;
    }
}
