<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfileImagesTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_profile_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); // One profile image per user
            $table->string('profile_image')->nullable(); // Store image path or URL
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // Delete image record when user deleted
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profile_images');
    }
}
