<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiment_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('experiment_id')->constrained()->cascadeOnDelete();
            $table->string('task_key');
            $table->unsignedTinyInteger('sequence');
            $table->string('status')->default('pending');
            $table->json('option_order');
            $table->string('selected_option')->nullable();
            $table->json('selection_payload')->nullable();
            $table->unsignedBigInteger('active_duration_ms')->default(0);
            $table->unsignedBigInteger('idle_duration_ms')->default(0);
            $table->unsignedInteger('change_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['experiment_id', 'task_key']);
            $table->unique(['experiment_id', 'sequence']);
            $table->index(['task_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiment_tasks');
    }
};
