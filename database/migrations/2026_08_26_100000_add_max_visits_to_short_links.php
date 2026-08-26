<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->unsignedInteger('max_visits')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->dropColumn('max_visits');
        });
    }
};
