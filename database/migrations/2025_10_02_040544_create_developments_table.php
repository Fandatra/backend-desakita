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
        Schema::create('developments', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('pic')->nullable();
        // person in charge
        $table->text('description')->nullable();
        $table->string('location');
        $table->enum('status', ['planning', 'ongoing', 'completed'])->default('planning');
        $table->decimal('budget', 15, 2)->default(0);
        $table->date('start_date');
        $table->date('end_date')->nullable();
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developments');
    }
};
