<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            $table->string('patient_name');
            $table->integer('age');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('contact_number', 15);
            $table->string('diagnosising_image')->nullable(); // path to uploaded image
            $table->string('result')->nullable(); // diagnosis result (e.g., "Malignant")
            $table->float('confidence', 5, 2)->nullable(); // confidence score (e.g., 95.23)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
