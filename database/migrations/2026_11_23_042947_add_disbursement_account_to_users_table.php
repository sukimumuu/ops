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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('bank_name')->nullable()->after('address');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_holder_name')->nullable()->after('bank_account_number');
            $table->timestamp('bank_account_updated_at')->nullable()->after('bank_account_holder_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'bank_name',
                'bank_account_number',
                'bank_account_holder_name',
                'bank_account_updated_at',
            ]);
        });
    }
};
