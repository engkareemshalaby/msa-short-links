<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibition_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 30)->unique();
            $table->string('registrant_role', 20);
            $table->string('student_email');
            $table->string('parent_email')->nullable();
            $table->string('student_name');
            $table->string('student_mobile', 50);
            $table->string('parent_mobile', 50)->nullable();
            $table->string('certificate_type', 120);
            $table->string('certificate_type_other')->nullable();
            $table->string('current_result');
            $table->json('interested_faculties');
            $table->string('preferred_contact_method', 20);
            $table->string('exhibition_location')->default('Jordan');
            $table->string('status', 30)->default('new')->index();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['student_email', 'created_at']);
            $table->index('student_mobile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibition_registrations');
    }
};
