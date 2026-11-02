<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exhibition_mail_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->longText('body_html');
            $table->json('filters');
            $table->boolean('include_parents');
            $table->unsignedInteger('recipient_count');
            $table->timestamps();
        });
        Schema::create('exhibition_mail_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('exhibition_mail_campaigns')->cascadeOnDelete();
            $table->string('email');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibition_mail_recipients');
        Schema::dropIfExists('exhibition_mail_campaigns');
    }
};
