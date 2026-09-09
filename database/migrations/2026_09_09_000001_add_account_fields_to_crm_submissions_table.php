<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_submissions', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
        });

        Schema::table('recruitment_partners', function (Blueprint $table) {
            $table->foreignId('crm_submission_id')->nullable()->after('user_id')->unique()->constrained('crm_submissions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_partners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crm_submission_id');
        });

        Schema::table('crm_submissions', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
