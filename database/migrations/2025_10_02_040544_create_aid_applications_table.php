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
        Schema::create('aid_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('social_aid_id')->constrained('social_aids')->onDelete('cascade');
    $table->foreignId('head_of_family_id')->constrained('head_of_families')->onDelete('cascade');
    $table->string('bank_account');
    $table->decimal('requested_nominal', 15, 2);
    $table->text('reason');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->string('proof_photo')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aid_applications');
    }
};
