<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ui_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version', 10)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->string('thumbnail')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_templates');
    }
};
