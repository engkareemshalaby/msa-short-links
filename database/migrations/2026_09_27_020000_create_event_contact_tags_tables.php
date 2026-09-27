<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_contact_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('color', 7)->default('#538F3F');
            $table->timestamps();
        });

        Schema::create('event_contact_tag_assignments', function (Blueprint $table) {
            $table->foreignId('event_contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_contact_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['event_contact_id', 'event_contact_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_contact_tag_assignments');
        Schema::dropIfExists('event_contact_tags');
    }
};
