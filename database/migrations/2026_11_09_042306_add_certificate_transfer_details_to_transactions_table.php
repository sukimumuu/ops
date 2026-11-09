<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->string('certificate_transfer_receipt_path')->nullable();
            $table->string('certificate_transfer_reference')->nullable();
            $table->text('certificate_transfer_notes')->nullable();
            $table->timestamp('certificate_transfer_submitted_at')->nullable();
            $table->foreignId('certificate_transfer_submitted_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
        });

        $states = [
            'draft',
            'held_in_escrow',
            'dp_paid',
            'bpn_checking',
            'bpn_cleared',
            'ajb_scheduled',
            'certificate_transfer_pending',
            'completed',
            'refunded',
        ];

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_state_check');
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_state_check CHECK (state IN ('draft', 'held_in_escrow', 'dp_paid', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled', 'certificate_transfer_pending', 'completed', 'refunded'))");
        } elseif (DB::getDriverName() === 'mysql') {
            Schema::table('transactions', function (Blueprint $table) use ($states): void {
                $table->enum('state', $states)->default('draft')->change();
            });
        }

        DB::table('transactions')
            ->where('state', 'completed')
            ->whereExists(function (QueryBuilder $query): void {
                $query->selectRaw('1')
                    ->from('escrow_disbursements')
                    ->whereColumn('escrow_disbursements.transaction_id', 'transactions.id')
                    ->where('escrow_disbursements.status', 'released');
            })
            ->update(['state' => 'certificate_transfer_pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('transactions')->whereNotNull('certificate_transfer_receipt_path')->exists()) {
            throw new RuntimeException('Cannot roll back while BPN receipt evidence has been submitted.');
        }

        DB::table('transactions')
            ->where('state', 'certificate_transfer_pending')
            ->update(['state' => 'completed']);

        $states = [
            'draft',
            'held_in_escrow',
            'dp_paid',
            'bpn_checking',
            'bpn_cleared',
            'ajb_scheduled',
            'completed',
            'refunded',
        ];

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_state_check');
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_state_check CHECK (state IN ('draft', 'held_in_escrow', 'dp_paid', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled', 'completed', 'refunded'))");
        } elseif (DB::getDriverName() === 'mysql') {
            Schema::table('transactions', function (Blueprint $table) use ($states): void {
                $table->enum('state', $states)->default('draft')->change();
            });
        }

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('certificate_transfer_submitted_by');
            $table->dropColumn([
                'certificate_transfer_receipt_path',
                'certificate_transfer_reference',
                'certificate_transfer_notes',
                'certificate_transfer_submitted_at',
            ]);
        });
    }
};
