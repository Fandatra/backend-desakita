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
        Schema::create('head_of_families', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('profile_picture')->nullable();
    $table->string('nik')->unique();
    $table->enum('gender', ['male', 'female']);
    $table->date('date_of_birth');
    $table->string('phone_number');
    $table->string('address');
    $table->string('occupation');
    $table->enum('marital_status', ['single', 'married', 'divorced']);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('head_of_families');
    }
};
