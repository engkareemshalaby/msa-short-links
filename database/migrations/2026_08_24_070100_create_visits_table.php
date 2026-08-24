<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_link_id')->constrained()->cascadeOnDelete();
            $table->timestamp('visited_at')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('visitor_hash', 64)->index();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('referer_host')->nullable()->index();
            $table->string('device_type', 30)->nullable()->index();
            $table->string('browser', 50)->nullable()->index();
            $table->string('platform', 50)->nullable()->index();
            $table->string('language', 10)->nullable();
            $table->text('query_string')->nullable();
            $table->boolean('is_bot')->default(false)->index();
            $table->index(['short_link_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
