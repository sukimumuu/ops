<?php

use Illuminate\Database\Migrations\Migration;
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
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_state_check');
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_state_check CHECK (state IN ('draft', 'held_in_escrow', 'dp_paid', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled', 'completed', 'refunded'))");
        } elseif (DB::getDriverName() === 'mysql') {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->enum('state', [
                    'draft',
                    'held_in_escrow',
                    'dp_paid',
                    'bpn_checking',
                    'bpn_cleared',
                    'ajb_scheduled',
                    'completed',
                    'refunded',
                ])->default('draft')->change();
            });
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->string('state')->default('draft')->change();
            });
        }

        Schema::table('transaction_logs', function (Blueprint $table): void {
            $table->unsignedBigInteger('actor_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('transactions')->where('state', 'dp_paid')->exists()) {
            throw new RuntimeException('Cannot roll back while transactions are in the dp_paid state.');
        }

        if (DB::table('transaction_logs')->whereNull('actor_id')->exists()) {
            throw new RuntimeException('Cannot roll back while system transaction logs have no actor.');
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_state_check');
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_state_check CHECK (state IN ('draft', 'held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled', 'completed', 'refunded'))");
        } elseif (DB::getDriverName() === 'mysql') {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->enum('state', [
                    'draft',
                    'held_in_escrow',
                    'bpn_checking',
                    'bpn_cleared',
                    'ajb_scheduled',
                    'completed',
                    'refunded',
                ])->default('draft')->change();
            });
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->enum('state', [
                    'draft',
                    'held_in_escrow',
                    'bpn_checking',
                    'bpn_cleared',
                    'ajb_scheduled',
                    'completed',
                    'refunded',
                ])->default('draft')->change();
            });
        }

        Schema::table('transaction_logs', function (Blueprint $table): void {
            $table->unsignedBigInteger('actor_id')->nullable(false)->change();
        });
    }
};
