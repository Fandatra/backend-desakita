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
        Schema::create('residents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('head_of_family_id')->constrained('head_of_families')->onDelete('cascade');
    $table->string('name');
    $table->string('nik')->unique();
    $table->enum('gender', ['male', 'female']);
    $table->date('date_of_birth');
    $table->string('phone_number')->nullable();
    $table->string('occupation')->nullable();
    $table->enum('marital_status', ['single', 'married'])->nullable();
    $table->string('relation'); // hubungan keluarga
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
