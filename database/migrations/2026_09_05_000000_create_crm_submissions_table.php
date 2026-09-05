<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name');
            $table->string('country', 120);
            $table->string('city', 120)->nullable();
            $table->string('website', 500)->nullable();
            $table->string('contact_name');
            $table->string('job_title', 150)->nullable();
            $table->string('mobile', 50);
            $table->string('email');
            $table->json('recruitment_countries');
            $table->string('annual_students_range', 50);
            $table->boolean('works_with_egyptian_universities');
            $table->text('current_universities')->nullable();
            $table->string('expected_msa_students_range', 50);
            $table->json('interested_programs');
            $table->text('notes')->nullable();
            $table->string('commission_type', 20);
            $table->decimal('commission_value', 10, 2);
            $table->string('commission_basis', 30)->nullable();
            $table->decimal('exclusive_discount_percent', 5, 2);
            $table->boolean('consent')->default(false);
            $table->string('status', 20)->default('new')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('source', 100)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['email', 'agency_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_submissions');
    }
};
