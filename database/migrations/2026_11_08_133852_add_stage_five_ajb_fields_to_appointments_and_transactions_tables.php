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
        Schema::table('appointments', function (Blueprint $table): void {
            $table->date('ajb_meeting_date')->nullable();
            $table->string('ajb_meeting_time', 10)->nullable();
            $table->string('ajb_meeting_status', 32)->nullable();
            $table->date('ajb_suggested_date')->nullable();
            $table->string('ajb_suggested_time', 10)->nullable();
            $table->text('ajb_notes')->nullable();
            $table->string('ajb_document_path')->nullable();
            $table->timestamp('ajb_signed_at')->nullable();
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->string('ajb_document_path')->nullable();
            $table->timestamp('ajb_signed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropColumn(['ajb_document_path', 'ajb_signed_at']);
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropColumn([
                'ajb_meeting_date',
                'ajb_meeting_time',
                'ajb_meeting_status',
                'ajb_suggested_date',
                'ajb_suggested_time',
                'ajb_notes',
                'ajb_document_path',
                'ajb_signed_at',
            ]);
        });
    }
};
