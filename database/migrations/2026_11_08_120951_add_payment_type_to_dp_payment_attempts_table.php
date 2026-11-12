<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dp_payment_attempts', function (Blueprint $table): void {
            $table->string('payment_type', 32)->default('dp');
            $table->index(['transaction_id', 'payment_type', 'status']);
        });

        Schema::table('transaction_ledger_entries', function (Blueprint $table): void {
            $table->string('type', 32)->default('dp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_ledger_entries', function (Blueprint $table): void {
            $table->dropColumn('type');
        });

        Schema::table('dp_payment_attempts', function (Blueprint $table): void {
            $table->dropIndex(['transaction_id', 'payment_type', 'status']);
            $table->dropColumn('payment_type');
        });
    }
};
