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
        // Residen Categories
        Schema::create('residen_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        // Agency Categories
        Schema::create('agency_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        // Parliament Categories
        Schema::create('parliament_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('budget', 15, 2)->default(0);
            $table->string('code')->unique();
            $table->enum('type', ['DUN', 'Parliament'])->default('DUN');
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        // Contractor Categories
        Schema::create('contractor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('code')->unique();
            $table->string('registration_number')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        // Status Master
        Schema::create('status_master', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('color')->default('#007bff');
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_master');
        Schema::dropIfExists('contractor_categories');
        Schema::dropIfExists('parliament_categories');
        Schema::dropIfExists('agency_categories');
        Schema::dropIfExists('residen_categories');
    }
};
