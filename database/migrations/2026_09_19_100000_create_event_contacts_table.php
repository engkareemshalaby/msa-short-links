<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('import_key')->unique();
            $table->string('name');
            $table->string('primary_email')->index();
            $table->json('emails');
            $table->string('event_name')->index();
            $table->string('source')->default('business_card');
            $table->string('status', 30)->default('new')->index();
            $table->text('notes')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });

        $now = now();
        $contacts = [
            ['ahmad-rufai-ahmad', 'Ahmad Rufai Ahmad', ['rufai@najmaglobaltours.com']],
            ['comr-ajir-victor', 'Comr. Ajir Victor', ['ajirvictor@gmail.com', 'rjaempirenigerialimited@gmail.com']],
            ['yusuf-wada-yunusa', 'Yusuf Wada Yunusa', ['iakengineeringserv@gmail.com', 'yuseh@hotmail.com']],
            ['richard-morgan', 'Richard Morgan', ['richard@morganoxfordeducation.co.uk']],
            ['bolanle-jegede', 'Bolanle Jegede', ['lifezonespecialschool007@gmail.com']],
            ['abubakar-salisu-s', 'Abubakar Salisu S.', ['abusals@yahoo.com']],
            ['olalekan-abdulganiyu-abolarin', 'Olalekan Abdulganiyu Abolarin', ['olalekan.abolarin@acpdyss.org']],
        ];

        DB::table('event_contacts')->insert(array_map(fn (array $contact): array => [
            'import_key' => 'nigeria-business-cards-2026:'.$contact[0],
            'name' => $contact[1],
            'primary_email' => $contact[2][0],
            'emails' => json_encode($contact[2], JSON_THROW_ON_ERROR),
            'event_name' => 'Nigeria Exhibition',
            'source' => 'business_card',
            'status' => 'new',
            'raw_data' => json_encode(['name' => $contact[1], 'emails' => $contact[2]], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ], $contacts));
    }

    public function down(): void
    {
        Schema::dropIfExists('event_contacts');
    }
};
