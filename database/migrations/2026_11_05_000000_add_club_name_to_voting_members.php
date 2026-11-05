<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('voting_members', function (Blueprint $table) {
            $table->string('club_name')->nullable()->after('staff_id');
        });
    }

    public function down(): void
    {
        Schema::table('voting_members', function (Blueprint $table) {
            $table->dropColumn('club_name');
        });
    }
};
