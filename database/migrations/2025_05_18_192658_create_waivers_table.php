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
        Schema::create('waivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('client_email');
            $table->date('date_of_birth');
            $table->text('address');
            $table->string('phone_number');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->boolean('has_allergies')->default(false);
            $table->text('allergies_description')->nullable();
            $table->text('tattoo_description');
            $table->text('tattoo_placement');
            $table->boolean('accepted_terms')->default(false);
            $table->boolean('accepted_aftercare')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waivers');
    }
};
