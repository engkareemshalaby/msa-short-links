<?php

namespace Tests\Feature;

use App\Models\ExhibitionRegistration;
use App\Models\User;
use App\Services\ExhibitionMailContent;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExhibitionCampaignMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_uses_all_filtered_rows_deduplicates_addresses_and_requires_confirmation(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('CRM Admin');
        $this->actingAs($admin);

        for ($i = 0; $i < 25; $i++) {
            ExhibitionRegistration::create([
                'reference_code' => 'TEST-'.$i,
                'registrant_role' => 'student',
                'student_email' => "student{$i}@example.com",
                'parent_email' => 'shared-parent@example.com',
                'student_name' => "Student {$i}",
                'student_mobile' => '01000000000',
                'certificate_type' => 'Other',
                'current_result' => 'A',
                'interested_faculties' => [],
                'preferred_contact_method' => 'email',
                'exhibition_location' => 'Jordan',
            ]);
        }
        ExhibitionRegistration::firstOrFail()->update(['exhibition_location' => 'Iraq (Baghdad)']);

        $this->get(route('crm.exhibition.mail.compose', ['exhibition_location' => 'Jordan']))
            ->assertOk()->assertSee('24');
        $this->post(route('crm.exhibition.mail.preview'), [
            'filters' => ['exhibition_location' => 'Jordan'],
            'subject' => 'News',
            'body_html' => '<p>Hello <strong>students</strong></p><script>alert(1)</script>',
            'include_parents' => '1',
        ])->assertOk()->assertSee('25 emails')->assertDontSee('alert(1)');
        $this->assertDatabaseCount('exhibition_mail_campaigns', 0);

        $token = session('exhibition_mail_draft.token');
        $this->post(route('crm.exhibition.mail.send'), ['token' => $token])
            ->assertRedirect(route('crm.exhibition.mail.status', 1));
        $this->assertDatabaseCount('exhibition_mail_recipients', 25);
        $this->assertSame(25, DB::table('jobs')->where('queue', 'exhibition-mail')->count());
        $this->post(route('crm.exhibition.mail.send'), ['token' => $token]);
        $this->assertDatabaseCount('exhibition_mail_campaigns', 1);
    }

    public function test_html_sanitizer_keeps_formatting_and_safe_links(): void
    {
        $html = app(ExhibitionMailContent::class)->clean('<p style="color:red"><u>Hi</u> <a href="javascript:alert(1)" onclick="x()">bad</a> <a href="https://example.com">good</a></p><img src="https://example.com/a.png" onerror="x()">');
        $this->assertStringContainsString('<u>Hi</u>', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('style=', $html);
    }
}
