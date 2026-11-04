<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('voting_members', fn ($table) => $table->renameColumn('code', 'staff_id'));
    }

    public function down(): void
    {
        Schema::table('voting_members', fn ($table) => $table->renameColumn('staff_id', 'code'));
    }
};
