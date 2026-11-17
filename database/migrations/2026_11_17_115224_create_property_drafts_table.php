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
        Schema::create('property_drafts', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->json('payload');
            $table->json('uploads')->nullable();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_drafts');
    }
};
