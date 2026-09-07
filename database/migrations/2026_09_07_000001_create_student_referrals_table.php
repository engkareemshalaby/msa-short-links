<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_partner_id')->constrained()->cascadeOnDelete();
            $table->string('reference_code', 30)->unique();
            $table->string('student_name');
            $table->string('mobile', 50);
            $table->string('nationality', 120);
            $table->string('desired_program', 120);
            $table->string('passport_path')->nullable();
            $table->boolean('consent')->default(false);
            $table->string('status', 30)->default('new')->index();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['recruitment_partner_id', 'created_at']);
            $table->index('mobile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_referrals');
    }
};
