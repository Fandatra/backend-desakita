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
        Schema::create('social_aid_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_aid_id')->constrained('social_aids')->onDelete('cascade');
            $table->foreignId('head_of_family_id')->constrained('head_of_families')->onDelete('cascade');
            $table->enum('status', ['approved', 'distributed'])->default('approved');
            $table->decimal('received_nominal', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_aid_recipients');
    }
};
