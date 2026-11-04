<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->string('whatsapp_number', 50)->nullable();
            $table->string('preferred_contact_time', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'preferred_contact_time']);
        });
    }
};
