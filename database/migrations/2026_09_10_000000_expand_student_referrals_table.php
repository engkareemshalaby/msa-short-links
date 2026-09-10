<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_referrals', function (Blueprint $table) {
            $table->string('email')->nullable()->after('mobile')->index();
            $table->boolean('study_in_egypt_applied')->nullable()->after('status');
            $table->foreignId('study_in_egypt_updated_by')->nullable()->after('study_in_egypt_applied')->constrained('users')->nullOnDelete();
            $table->timestamp('study_in_egypt_updated_at')->nullable()->after('study_in_egypt_updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('student_referrals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('study_in_egypt_updated_by');
            $table->dropColumn(['email', 'study_in_egypt_applied', 'study_in_egypt_updated_at']);
        });
    }
};
