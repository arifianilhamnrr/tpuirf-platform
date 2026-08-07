<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('daily_device_usage')->nullable();
            $table->string('device_usage_time')->nullable();
            $table->string('primary_device_purpose')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['daily_device_usage', 'device_usage_time', 'primary_device_purpose']);
        });
    }
};
