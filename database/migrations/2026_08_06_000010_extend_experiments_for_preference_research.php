<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->string('protocol_version')->nullable()->after('status');
            $table->string('current_task')->nullable()->after('protocol_version');
            $table->json('task_order')->nullable()->after('current_task');
            $table->timestamp('last_activity_at')->nullable()->after('completed_at');
            $table->unsignedBigInteger('total_active_duration_ms')->default(0)->after('last_activity_at');
            $table->string('consent_version')->nullable()->after('total_active_duration_ms');
            $table->timestamp('consented_at')->nullable()->after('consent_version');

            $table->unique(['user_id', 'protocol_version'], 'experiments_user_protocol_unique');
            $table->index(['protocol_version', 'status'], 'experiments_protocol_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->dropIndex('experiments_protocol_status_index');
            $table->dropUnique('experiments_user_protocol_unique');
            $table->dropColumn([
                'protocol_version',
                'current_task',
                'task_order',
                'last_activity_at',
                'total_active_duration_ms',
                'consent_version',
                'consented_at',
            ]);
        });
    }
};
