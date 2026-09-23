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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('seller_id');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('type', ['residential', 'land']);
            $table->enum('status', ['draft', 'pending_verification', 'published', 'reserved', 'sold', 'archived' ])->default('draft');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 15, 2);
            $table->enum('certificate_type', ['SHM', 'SHGB', 'GIRIK', 'OTHER']);
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('land_area_sqm', 10, 2);
            $table->decimal('building_area_sqm', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
