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
        Schema::table('property_photos', function (Blueprint $table): void {
            $table->string('validation_status')->default('pending');
            $table->text('validation_notes')->nullable();
            $table->foreignId('validated_by_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_photos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('validated_by_id');
            $table->dropColumn(['validation_status', 'validation_notes', 'validated_at']);
        });
    }
};
