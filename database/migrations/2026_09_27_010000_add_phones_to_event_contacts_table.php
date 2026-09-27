<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_contacts', function (Blueprint $table) {
            $table->string('primary_phone', 40)->nullable()->after('emails')->index();
            $table->json('phones')->nullable()->after('primary_phone');
        });
    }

    public function down(): void
    {
        Schema::table('event_contacts', function (Blueprint $table) {
            $table->dropColumn(['primary_phone', 'phones']);
        });
    }
};
