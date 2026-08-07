<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interaction_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('experiment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('ui_version', 10);
            $table->string('event_type');
            $table->json('event_data')->nullable();
            $table->string('target_element')->nullable();
            $table->string('page_url')->nullable();
            $table->integer('viewport_width')->nullable();
            $table->integer('viewport_height')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->float('task_time')->nullable();
            $table->float('session_time')->nullable();
            $table->float('idle_time')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();

            $table->index(['experiment_id', 'event_type']);
            $table->index(['user_id', 'ui_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interaction_logs');
    }
};
