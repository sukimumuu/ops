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
        Schema::table('escrow_disbursements', function (Blueprint $table): void {
            $table->string('recipient_bank_name')->nullable()->after('recipient_name');
            $table->string('recipient_bank_account_number')->nullable()->after('recipient_bank_name');
            $table->string('recipient_account_holder_name')->nullable()->after('recipient_bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escrow_disbursements', function (Blueprint $table): void {
            $table->dropColumn([
                'recipient_bank_name',
                'recipient_bank_account_number',
                'recipient_account_holder_name',
            ]);
        });
    }
};
