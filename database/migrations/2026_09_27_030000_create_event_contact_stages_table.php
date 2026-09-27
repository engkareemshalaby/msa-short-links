<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_contact_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('color', 7)->default('#538F3F');
            $table->unsignedInteger('position')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        DB::table('event_contact_stages')->insert([
            ['name' => 'Awareness', 'color' => '#6C8EBF', 'position' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Consideration', 'color' => '#8E7CC3', 'position' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Decision', 'color' => '#E69138', 'position' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Action Stage', 'color' => '#CC0000', 'position' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Partner', 'color' => '#538F3F', 'position' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('event_contacts', function (Blueprint $table) {
            $table->foreignId('event_contact_stage_id')->nullable()->after('status')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_contact_stage_id');
        });
        Schema::dropIfExists('event_contact_stages');
    }
};
