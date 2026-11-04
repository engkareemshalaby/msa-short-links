<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voting_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_open')->default(false);
            $table->boolean('show_results_to_members')->default(false);
            $table->timestamps();
        });
        Schema::create('voting_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->timestamps();
        });
        Schema::create('voting_ballots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voter_id')->unique()->constrained('voting_members')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('voting_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ballot_id')->constrained('voting_ballots')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('voting_members')->cascadeOnDelete();
            $table->unique(['ballot_id', 'candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_choices');
        Schema::dropIfExists('voting_ballots');
        Schema::dropIfExists('voting_members');
        Schema::dropIfExists('voting_settings');
    }
};
