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
        Schema::dropIfExists('aid_applications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('aid_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_aid_id')->constrained()->onDelete('cascade');
            $table->foreignId('head_of_family_id')->constrained()->onDelete('cascade');
            $table->string('bank_account')->nullable();
            $table->decimal('requested_nominal', 15, 2)->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('proof_photo')->nullable();
            $table->timestamps();
        });
    }
};
