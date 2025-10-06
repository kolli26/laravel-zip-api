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
        Schema::create('counties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('place_names', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('zip_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->foreignId('place_name_id')->constrained('place_names')->onDelete('cascade');
            $table->foreignId('county_id')->nullable()->constrained('counties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zip_codes');
        Schema::dropIfExists('counties');
        Schema::dropIfExists('place_names');
    }
};
