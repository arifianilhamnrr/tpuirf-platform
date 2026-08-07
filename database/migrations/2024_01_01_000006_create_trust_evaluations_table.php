<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trust_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('experiment_id')->constrained()->cascadeOnDelete();
            $table->float('trust_score')->nullable();
            $table->float('transparency_score')->nullable();
            $table->float('ui_satisfaction')->nullable();
            $table->float('recommendation_quality')->nullable();
            $table->text('feedback')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('experiment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trust_evaluations');
    }
};
