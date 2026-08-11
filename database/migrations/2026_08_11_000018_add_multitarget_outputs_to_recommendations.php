<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->json('recommended_config')->nullable()->after('recommended_ui');
            $table->json('confidence_by_target')->nullable()->after('confidence_score');
        });
    }

    public function down(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropColumn(['recommended_config', 'confidence_by_target']);
        });
    }
};
