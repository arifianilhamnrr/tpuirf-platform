<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('education')->nullable();
            $table->string('occupation')->nullable();
            $table->integer('digital_literacy')->nullable();
            $table->string('device_type')->nullable();
            $table->string('visual_preference')->nullable();
            $table->string('context_environment')->nullable();
            $table->integer('ai_familiarity')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
