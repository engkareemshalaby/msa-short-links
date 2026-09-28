<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_contacts', function (Blueprint $table) {
            $table->string('organization_name')->nullable()->after('name')->index();
            $table->string('job_title')->nullable()->after('organization_name');
            $table->json('extra_data')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('event_contacts', function (Blueprint $table) {
            $table->dropIndex(['organization_name']);
            $table->dropColumn(['organization_name', 'job_title', 'extra_data']);
        });
    }
};
