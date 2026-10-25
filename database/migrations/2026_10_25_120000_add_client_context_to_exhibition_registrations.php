<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable()->index();
            $table->string('browser', 50)->nullable()->index();
            $table->string('platform', 50)->nullable()->index();
            $table->string('browser_language', 20)->nullable();
            $table->string('connection_country', 2)->nullable()->index();
            $table->string('connection_region', 100)->nullable();
            $table->string('connection_city', 100)->nullable();
            $table->string('client_timezone', 100)->nullable()->index();
            $table->unsignedSmallInteger('screen_width')->nullable();
            $table->unsignedSmallInteger('screen_height')->nullable();
            $table->unsignedSmallInteger('viewport_width')->nullable();
            $table->unsignedSmallInteger('viewport_height')->nullable();
            $table->decimal('pixel_ratio', 5, 2)->nullable();
            $table->unsignedSmallInteger('hardware_concurrency')->nullable();
            $table->decimal('device_memory', 5, 2)->nullable();
            $table->unsignedSmallInteger('touch_points')->nullable();
            $table->json('client_hints')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->dropIndex(['device_type']);
            $table->dropIndex(['browser']);
            $table->dropIndex(['platform']);
            $table->dropIndex(['connection_country']);
            $table->dropIndex(['client_timezone']);
            $table->dropColumn([
                'ip_address', 'user_agent', 'device_type', 'browser', 'platform', 'browser_language',
                'connection_country', 'connection_region', 'connection_city', 'client_timezone',
                'screen_width', 'screen_height', 'viewport_width', 'viewport_height', 'pixel_ratio',
                'hardware_concurrency', 'device_memory', 'touch_points', 'client_hints',
            ]);
        });
    }
};
