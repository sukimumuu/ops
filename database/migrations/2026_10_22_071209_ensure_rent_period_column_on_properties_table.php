<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair databases created before `rent_period` was added to the base
     * properties migration.
     */
    public function up(): void
    {
        if (Schema::hasColumn('properties', 'rent_period')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->string('rent_period')->nullable();
        });
    }

    /**
     * The column is owned by the base properties migration, so it is not
     * removed when this corrective migration is rolled back.
     */
    public function down(): void
    {
        //
    }
};
