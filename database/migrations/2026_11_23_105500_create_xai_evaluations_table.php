<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xai_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('experiment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('recommendation_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('perceived_transparency');
            $table->unsignedTinyInteger('system_trust');
            $table->unsignedTinyInteger('decision_confidence');
            $table->unsignedTinyInteger('overall_satisfaction');
            $table->unsignedTinyInteger('explanation_understandability');
            $table->unsignedTinyInteger('causal_attribution_coherence');
            $table->unsignedTinyInteger('perceived_scrutability');
            $table->unsignedTinyInteger('cognitive_load_appropriateness');
            $table->string('instrument_version')->default('XAI-LIKERT-v1');
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->softDeletes();
            $table->unique('recommendation_id');
            $table->unique(['user_id', 'experiment_id']);
            $table->index(['experiment_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xai_evaluations');
    }
};
