<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\ShortLink;
use App\Models\Tag;
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

    public function test_missing_link_uses_the_branded_not_found_page(): void
    {
        $this->get('/missing-short-link')
            ->assertNotFound()
            ->assertSee('MSA Go')
            ->assertSee(__('Link not found'))
            ->assertSee('404');
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

    public function test_links_can_be_filtered_by_tag_campaign_creator_status_and_creation_date(): void
    {
        $admin = User::first();
        $otherUser = User::factory()->create();
        $campaign = Campaign::create(['name' => 'Autumn campaign', 'created_by' => $admin->id]);
        $tag = Tag::create(['name' => 'Admissions', 'color' => '#538F3F']);

        $matching = ShortLink::create([
            'title' => 'Matching link', 'code' => 'match1', 'destination_url' => 'https://example.com/match',
            'code_type' => 'custom', 'is_active' => true, 'created_by' => $admin->id, 'campaign_id' => $campaign->id,
        ]);
        $matching->tags()->attach($tag);
        $matching->forceFill(['created_at' => '2026-08-15 10:00:00'])->saveQuietly();

        ShortLink::create([
            'title' => 'Different link', 'code' => 'other1', 'destination_url' => 'https://example.com/other',
            'code_type' => 'custom', 'is_active' => false, 'created_by' => $otherUser->id,
        ]);

        $this->actingAs($admin)->get(route('links.index', [
            'tag_id' => $tag->id,
            'campaign_id' => $campaign->id,
            'created_by' => $admin->id,
            'status' => 'active',
            'created_from' => '2026-08-01',
            'created_to' => '2026-08-31',
        ]))->assertOk()->assertSee('Matching link')->assertDontSee('Different link');
    }

    public function test_admin_can_view_and_restore_an_archived_link(): void
    {
        $admin = User::first();
        $link = ShortLink::create([
            'title' => 'Archived link', 'code' => 'archive1', 'destination_url' => 'https://example.com/archive',
            'code_type' => 'custom', 'is_active' => true, 'created_by' => $admin->id,
        ]);
        $link->delete();

        $this->actingAs($admin)->get(route('links.index', ['archive' => 'archived']))
            ->assertOk()->assertSee('Archived link')->assertSee(__('Restore'));

        $this->actingAs($admin)->patch(route('links.restore', $link->id))
            ->assertRedirect(route('links.index', ['archive' => 'archived']));

        $this->assertNotSoftDeleted($link);
        $this->assertDatabaseHas('audit_logs', ['action' => 'restored', 'subject_id' => $link->id]);
    }
}
