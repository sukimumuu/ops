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
        Schema::table('property_photos', function (Blueprint $table) {
            // Supporting legal documents (IMB/PBG, SPPT/STTS PBB) reuse the
            // existing property_photos table, so the type needs two new values.
            $table->enum('type', ['photo', 'certificate', 'imb', 'pbb'])
                ->default('photo')
                ->change();

            $table->string('certificate_type', 16)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_photos', function (Blueprint $table) {
            $table->enum('type', ['photo', 'certificate'])
                ->default('photo')
                ->change();

            $table->dropColumn('certificate_type');
        });
    }
};
