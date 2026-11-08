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
            $table->date('ajb_meeting_date')->nullable()->after('ppat_meeting_status');
            $table->string('ajb_meeting_time', 10)->nullable()->after('ajb_meeting_date');
            $table->string('ajb_meeting_status', 32)->nullable()->after('ajb_meeting_time');
            $table->date('ajb_suggested_date')->nullable()->after('ajb_meeting_status');
            $table->string('ajb_suggested_time', 10)->nullable()->after('ajb_suggested_date');
            $table->text('ajb_notes')->nullable()->after('ajb_suggested_time');
            $table->string('ajb_document_path')->nullable()->after('ajb_notes');
            $table->timestamp('ajb_signed_at')->nullable()->after('ajb_document_path');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->string('ajb_document_path')->nullable()->after('settled_at');
            $table->timestamp('ajb_signed_at')->nullable()->after('ajb_document_path');
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
