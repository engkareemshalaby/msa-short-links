<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortLinkFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_create_a_random_six_digit_link(): void
    {
        $admin = User::first();
        $this->actingAs($admin)->post('/links', [
            'title' => 'Admissions',
            'destination_url' => 'https://www.msa.edu.eg/admissions',
            'code_type' => 'random',
            'is_active' => '1',
        ])->assertRedirect();

        $link = ShortLink::firstOrFail();
        $this->assertMatchesRegularExpression('/^\d{6}$/', $link->code);
        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'subject_id' => $link->id]);
    }

    public function test_admin_can_create_a_custom_slug(): void
    {
        $this->actingAs(User::first())->post('/links', [
            'title' => 'Open Day',
            'destination_url' => 'https://www.msa.edu.eg/open-day',
            'code_type' => 'custom',
            'custom_code' => 'Open-Day',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('short_links', ['code' => 'open-day', 'code_type' => 'custom']);
    }

    public function test_admin_can_create_campaign_and_tags_inside_link_form(): void
    {
        $this->actingAs(User::first())->post('/links', [
            'title' => 'September intake',
            'destination_url' => 'https://www.msa.edu.eg/admissions',
            'code_type' => 'random',
            'is_active' => '1',
            'new_campaign_name' => 'Admissions 2026',
            'new_campaign_utm_source' => 'facebook',
            'new_campaign_utm_medium' => 'social',
            'new_campaign_utm_campaign' => 'september-intake',
            'new_tags' => 'Admissions, Facebook',
            'new_tag_color' => '#538F3F',
        ])->assertRedirect();

        $link = ShortLink::with(['campaign', 'tags'])->firstOrFail();
        $this->assertSame('Admissions 2026', $link->campaign->name);
        $this->assertStringContainsString('utm_source=facebook', $link->destination_url);
        $this->assertEqualsCanonicalizing(['Admissions', 'Facebook'], $link->tags->pluck('name')->all());
    }

    public function test_public_redirect_records_visit_analytics(): void
    {
        $link = ShortLink::create([
            'title' => 'Test', 'code' => '123456', 'destination_url' => 'https://example.com/page',
            'code_type' => 'random', 'is_active' => true, 'created_by' => User::first()->id,
        ]);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone) AppleWebKit/605.1.15 Safari/604.1',
            'Referer' => 'https://facebook.com/post/1',
        ])->get('/123456')->assertRedirect('https://example.com/page');

        $this->assertDatabaseHas('visits', [
            'short_link_id' => $link->id, 'device_type' => 'Mobile',
            'browser' => 'Safari', 'referer_host' => 'facebook.com',
        ]);
    }

    public function test_inactive_link_returns_not_found(): void
    {
        ShortLink::create([
            'title' => 'Inactive', 'code' => '654321', 'destination_url' => 'https://example.com',
            'code_type' => 'random', 'is_active' => false, 'created_by' => User::first()->id,
        ]);
        $this->get('/654321')->assertNotFound();
    }

    public function test_analyst_cannot_create_links(): void
    {
        $analyst = User::factory()->create();
        $analyst->assignRole('Analyst');
        $this->actingAs($analyst)->get('/links/create')->assertForbidden();
    }
}
