<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trust_evaluations', function (Blueprint $table) {
            $table->string('ai_usage_frequency')->nullable();
            $table->float('ai_answer_trust')->nullable();
            $table->float('ai_explanation_preference')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trust_evaluations', function (Blueprint $table) {
            $table->dropColumn(['ai_usage_frequency', 'ai_answer_trust', 'ai_explanation_preference']);
        });
    }
};
