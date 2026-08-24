<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->text('description')->nullable();
            $table->string('utm_source')->nullable(); $table->string('utm_medium')->nullable(); $table->string('utm_campaign')->nullable();
            $table->boolean('is_active')->default(true); $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); $table->timestamps();
        });
        Schema::create('tags', function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->string('color', 7)->default('#538F3F'); $table->timestamps(); });
        Schema::create('short_link_tag', function (Blueprint $table) { $table->foreignId('short_link_id')->constrained()->cascadeOnDelete(); $table->foreignId('tag_id')->constrained()->cascadeOnDelete(); $table->primary(['short_link_id', 'tag_id']); });
        Schema::table('short_links', function (Blueprint $table) { $table->foreignId('campaign_id')->nullable()->after('id')->constrained()->nullOnDelete(); $table->string('health_status', 20)->default('unknown')->index(); $table->unsignedSmallInteger('last_status_code')->nullable(); $table->timestamp('last_checked_at')->nullable(); $table->boolean('retargeting_enabled')->default(false); });
        Schema::table('visits', function (Blueprint $table) { $table->string('country', 2)->nullable()->index(); $table->string('city')->nullable(); });
        Schema::create('smart_targets', function (Blueprint $table) { $table->id(); $table->foreignId('short_link_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->text('destination_url'); $table->string('condition_type', 20); $table->string('condition_value', 100); $table->unsignedSmallInteger('priority')->default(100); $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['short_link_id', 'condition_type', 'condition_value']); });
        Schema::create('retargeting_pixels', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('provider')->nullable(); $table->text('snippet'); $table->boolean('is_active')->default(true); $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); $table->timestamps(); });
        Schema::create('retargeting_pixel_short_link', function (Blueprint $table) { $table->foreignId('retargeting_pixel_id')->constrained()->cascadeOnDelete(); $table->foreignId('short_link_id')->constrained()->cascadeOnDelete(); $table->primary(['retargeting_pixel_id', 'short_link_id']); });
        Schema::create('api_keys', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('key_hash', 64)->unique(); $table->string('prefix', 12); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->timestamp('last_used_at')->nullable(); $table->timestamp('expires_at')->nullable(); $table->timestamps(); });
    }
    public function down(): void
    {
        Schema::dropIfExists('api_keys'); Schema::dropIfExists('retargeting_pixel_short_link'); Schema::dropIfExists('retargeting_pixels'); Schema::dropIfExists('smart_targets'); Schema::table('visits', fn (Blueprint $table) => $table->dropColumn(['country','city'])); Schema::table('short_links', fn (Blueprint $table) => $table->dropConstrainedForeignId('campaign_id')->dropColumn(['health_status','last_status_code','last_checked_at','retargeting_enabled'])); Schema::dropIfExists('short_link_tag'); Schema::dropIfExists('tags'); Schema::dropIfExists('campaigns');
    }
};
