<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->string('recommended_ui', 100)->change();
            $table->json('explanations')->nullable()->after('features');
            $table->json('model_outputs')->nullable()->after('explanations');
            $table->json('input_features')->nullable()->after('model_outputs');
        });
    }

    public function down(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->string('recommended_ui', 10)->change();
            $table->dropColumn(['explanations', 'model_outputs', 'input_features']);
        });
    }
};
