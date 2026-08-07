<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interaction_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_uuid')->unique();
            $table->foreignUuid('experiment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('experiment_task_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('sequence_number');
            $table->string('protocol_version');
            $table->string('task_key')->nullable();
            $table->string('option_id')->nullable();
            $table->string('component_id')->nullable();
            $table->string('event_type');
            $table->timestamp('client_timestamp')->nullable();
            $table->timestamp('server_received_at');
            $table->unsignedBigInteger('elapsed_task_ms')->nullable();
            $table->unsignedBigInteger('elapsed_experiment_ms')->nullable();
            $table->unsignedBigInteger('delta_previous_event_ms')->nullable();
            $table->unsignedBigInteger('delta_previous_click_ms')->nullable();
            $table->string('previous_component_id')->nullable();
            $table->unsignedInteger('viewport_width')->nullable();
            $table->unsignedInteger('viewport_height')->nullable();
            $table->string('input_method')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['experiment_id', 'sequence_number']);
            $table->index(['experiment_id', 'task_key']);
            $table->index(['component_id', 'event_type']);
            $table->index(['task_key', 'option_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interaction_events');
    }
};
