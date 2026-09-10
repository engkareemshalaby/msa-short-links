<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->boolean('has_connection_or_housing_in_egypt')->nullable()->after('preferred_contact_method');
        });
    }

    public function down(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->dropColumn('has_connection_or_housing_in_egypt');
        });
    }
};
