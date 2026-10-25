<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->foreignId('source_short_link_id')->nullable()->constrained('short_links')->nullOnDelete();
            $table->foreignId('source_visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->string('source_short_code', 50)->nullable();
            $table->text('source_landing_url')->nullable();
            $table->text('source_referrer')->nullable();
            $table->string('source_form_locale', 2)->nullable();
            $table->string('source_ref', 150)->nullable();
            $table->string('source_bref', 150)->nullable();
            $table->string('source_sem', 100)->nullable();
            $table->string('source_utm_source')->nullable();
            $table->string('source_utm_medium')->nullable();
            $table->string('source_utm_campaign')->nullable();
            $table->string('source_utm_content')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exhibition_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_visit_id');
            $table->dropConstrainedForeignId('source_short_link_id');
            $table->dropColumn([
                'source_short_code', 'source_landing_url', 'source_referrer', 'source_form_locale',
                'source_ref', 'source_bref', 'source_sem', 'source_utm_source',
                'source_utm_medium', 'source_utm_campaign', 'source_utm_content',
            ]);
        });
    }
};
