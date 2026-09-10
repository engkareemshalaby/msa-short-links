<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->renameColumn('has_connection_or_housing_in_egypt', 'has_relatives_or_acquaintances_in_egypt');
            $table->boolean('has_accommodation_in_egypt')->nullable()->after('has_relatives_or_acquaintances_in_egypt');
        });
    }

    public function down(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->dropColumn('has_accommodation_in_egypt');
            $table->renameColumn('has_relatives_or_acquaintances_in_egypt', 'has_connection_or_housing_in_egypt');
        });
    }
};
