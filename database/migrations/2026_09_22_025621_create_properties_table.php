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
            $table->unsignedBigInteger('assigned_ppat_id')->nullable();
            $table->foreign('assigned_ppat_id')->nullable()->references('id')->on('users')->nullOnDelete();

            // Core Info
            $table->string('property_type'); // 'bangunan' or 'tanah'
            $table->string('type'); // 'rumah', 'apartemen', 'kavling', etc.
            $table->enum('category', ['sale', 'rent']);
            $table->enum('status', ['draft', 'pending_verification', 'published', 'rejected', 'reserved', 'sold', 'archived'])->default('draft');
            $table->string('title');
            $table->text('description');

            // Price
            $table->decimal('price', 15, 2);
            $table->string('rent_period')->nullable();
            $table->boolean('negotiable')->default(false);

            // Location
            $table->string('province');
            $table->string('province_code', 16)->nullable();
            $table->string('city');
            $table->string('city_code', 16)->nullable();
            $table->string('district');
            $table->string('district_code', 16)->nullable();
            $table->string('postal_code')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('url_maps')->nullable();

            // Specifics
            $table->decimal('land_area_sqm', 10, 2);
            $table->decimal('building_area_sqm', 10, 2)->nullable();

            // Building specifics
            $table->string('rooms')->nullable();
            $table->string('bathrooms')->nullable();
            $table->string('floors')->nullable();
            $table->string('condition')->nullable(); // 'baru', 'siap_huni', etc.

            // Land specifics
            $table->decimal('front_width', 10, 2)->nullable();
            $table->string('land_contour')->nullable(); // 'datar', 'miring', etc.
            $table->string('road_access')->nullable();
            $table->string('zone_type')->nullable();

            // Legal
            $table->string('certificate_type'); // 'SHM', 'SHGB', 'GIRIK', 'AJB', 'OTHER'

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
