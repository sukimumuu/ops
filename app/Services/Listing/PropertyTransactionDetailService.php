<?php

namespace App\Services\Listing;

use App\Models\Appointment;
use App\Models\DpPaymentAttempt;
use App\Models\EscrowDisbursement;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use App\Models\TransactionLogs;
use App\Models\User;
use App\Services\Payments\DpAmountCalculator;
use Illuminate\Support\Collection;

class PropertyTransactionDetailService
{
    public function __construct(private DpAmountCalculator $amountCalculator) {}

    /**
     * Assemble the data required by the shared property detail partial.
     *
     * @return array{
     *     property: Property,
     *     appointments: Collection<int, Appointment>,
     *     latestAppointment: ?Appointment,
     *     selectedAppointment: ?Appointment,
     *     transaction: ?Transaction,
     *     transactionLogs: Collection<int, TransactionLogs>,
     *     escrowDisbursement: ?EscrowDisbursement,
     *     isStageSevenUnlocked: bool,
     *     escrowReleaseAmount: int|float,
     *     dpPaymentAttempt: ?DpPaymentAttempt,
     *     escrowPaymentAttempt: ?DpPaymentAttempt,
     *     requiredDpAmount: int|float|null,
     *     remainingEscrowAmount: int|null,
     *     progress: array{label: string, description: string, percentage: int, tone: string, completed_steps: int, stopped: bool},
     *     mapUrl: string,
     *     clientBuyer: ?User,
     *     bpnProgress: array<string, mixed>,
     *     isStageThreeUnlocked: bool,
     *     isStageFourUnlocked: bool,
     *     isEscrowPaid: bool
     * }
     */
    public function build(Property $property, ?int $selectedAppointmentId = null): array
    {
        $property->load([
            'photos',
            'documents.validatedBy:id,name',
            'assignedPpat:id,name,email,phone,avatar_path',
            'seller:id,name,email,phone,avatar_path',
        ]);

        $appointments = $property->appointments()
            ->with([
                'buyer:id,name,email,phone,avatar_path',
                'seller:id,name,email,phone,avatar_path',
            ])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->get();

        $transaction = $property->transactions()
            ->with(['buyer:id,name,email,phone,avatar_path'])
            ->with('certificateTransferSubmittedBy:id,name')
            ->latest('id')
            ->first();

        $transactionLogs = $transaction?->logs()
            ->with('actor:id,name')
            ->orderByDesc('id')
            ->get() ?? collect();

        $escrowDisbursement = $transaction?->latestEscrowDisbursement();
        $isStageSevenUnlocked = $escrowDisbursement?->status === EscrowDisbursement::STATUS_RELEASED;
        $escrowReleaseAmount = $transaction?->ledgerEntries()
            ->where('status', TransactionLedgerEntry::STATUS_RESERVED)
            ->sum('amount') ?? 0;

        $dpPaymentAttempt = $transaction?->latestDpPaymentAttempt();
        $escrowPaymentAttempt = $transaction?->latestEscrowPaymentAttempt();

        $latestAppointment = $appointments->first();
        $selectedAppointment = $appointments->firstWhere('id', $selectedAppointmentId)
            ?? $latestAppointment;

        $progress = $latestAppointment?->progressState() ?? [
            'label' => 'Belum ada pengajuan survei',
            'description' => 'Progres survei akan muncul setelah calon pembeli mengajukan jadwal kunjungan.',
            'percentage' => 0,
            'tone' => 'slate',
            'completed_steps' => 0,
            'stopped' => true,
        ];

        if ($latestAppointment) {
            if ($latestAppointment->hasPendingReschedule()) {
                $progress['description'] = 'Penjual menyarankan jadwal pengganti. Menunggu tanggapan calon pembeli.';
            } elseif ($latestAppointment->status === Appointment::STATUS_REQUESTED) {
                $progress['description'] = 'Permintaan jadwal survei menunggu persetujuan penjual.';
            } elseif ($latestAppointment->status === Appointment::STATUS_CONFIRMED) {
                $progress['description'] = 'Jadwal survei telah disetujui penjual.';
            } elseif ($latestAppointment->status === Appointment::STATUS_COMPLETED) {
                $progress['description'] = 'Survei telah selesai dilaksanakan dan hasil telah dicatat.';
            }
        }

        $property->setAttribute('image_url', $property->primaryPhotoUrl());

        $mapLocation = filled($property->latitude) && filled($property->longitude)
            ? "{$property->latitude},{$property->longitude}"
            : trim("{$property->address}, {$property->district}, {$property->city}, {$property->province}");
        $mapUrl = filled($property->url_maps)
            ? $property->url_maps
            : 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapLocation);

        $clientBuyer = $transaction?->buyer ?? $latestAppointment?->buyer;
        $bpnProgress = $property->bpnVerificationProgress();
        $isStageThreeUnlocked = $transaction?->hasCompletedDownPayment() ?? false;
        $isStageFourUnlocked = $transaction?->isStageFourUnlocked() ?? false;
        $isEscrowPaid = $transaction?->hasCompletedEscrowPayment() ?? false;
        $remainingEscrowAmount = $transaction?->remainingEscrowAmount();
        $requiredDpAmount = $transaction?->state === 'draft'
            ? $this->amountCalculator->calculate($transaction->total_property_amount)
            : null;

        return compact(
            'property',
            'appointments',
            'latestAppointment',
            'selectedAppointment',
            'transaction',
            'transactionLogs',
            'escrowDisbursement',
            'isStageSevenUnlocked',
            'escrowReleaseAmount',
            'dpPaymentAttempt',
            'escrowPaymentAttempt',
            'requiredDpAmount',
            'remainingEscrowAmount',
            'progress',
            'mapUrl',
            'clientBuyer',
            'bpnProgress',
            'isStageThreeUnlocked',
            'isStageFourUnlocked',
            'isEscrowPaid',
        );
    }
}
