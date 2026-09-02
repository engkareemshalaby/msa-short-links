<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('campaigns', function (Blueprint $table) { $table->string('utm_content',100)->nullable(); $table->string('ref',150)->nullable(); $table->string('bref',150)->nullable(); $table->string('sem',100)->nullable(); }); } public function down(): void { Schema::table('campaigns', function (Blueprint $table) { $table->dropColumn(['utm_content','ref','bref','sem']); }); } };
