<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dp_payment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('transaction_id')->constrained()->restrictOnDelete();
            $table->string('reference_id')->unique();
            $table->string('xendit_payment_request_id')->nullable()->unique();
            $table->string('channel_code');
            $table->string('virtual_account_number')->nullable();
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3)->default('IDR');
            $table->string('status')->default('creating');
            $table->json('request_payload');
            $table->json('provider_response')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'status']);
        });

        Schema::create('payment_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->char('event_key', 64)->unique();
            $table->string('event_type')->nullable();
            $table->string('payment_request_id')->nullable();
            $table->foreignId('dp_payment_attempt_id')->nullable()
                ->constrained('dp_payment_attempts')->restrictOnDelete();
            $table->longText('raw_payload');
            $table->string('processing_status')->default('received');
            $table->text('processing_error')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['payment_request_id', 'processing_status']);
        });

        Schema::create('transaction_ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->restrictOnDelete();
            $table->foreignId('dp_payment_attempt_id')->unique()
                ->constrained('dp_payment_attempts')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3);
            $table->string('status')->default('reserved');
            $table->timestamp('reserved_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_ledger_entries');
        Schema::dropIfExists('payment_webhook_events');
        Schema::dropIfExists('dp_payment_attempts');
    }
};
