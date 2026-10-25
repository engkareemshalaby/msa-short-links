<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\ShortLink;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QuickLinkCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::first());
    }

    private function link(array $attributes = []): ShortLink
    {
        return ShortLink::create(array_merge([
            'code' => 'source-link', 'destination_url' => 'https://example.com/admissions',
            'code_type' => 'custom', 'is_active' => true, 'created_by' => User::first()->id,
        ], $attributes));
    }

    public function test_blank_names_follow_the_current_code_and_named_links_keep_their_title(): void
    {
        $link = $this->link();
        $this->assertSame('source-link', $link->display_title);
        $this->get(route('links.show', $link))->assertOk()->assertSee('source-link')->assertDontSee('Untitled link');
        $link->update(['code' => 'renamed-link']);
        $this->assertSame('renamed-link', $link->display_title);
        $link->update(['title' => 'Admissions']);
        $this->assertSame('Admissions', $link->display_title);
        $this->assertSame('123456', (new ShortLink(['code' => '123456', 'title' => '  ']))->display_title);
    }

    public function test_duplicate_prefills_only_reusable_fields_without_creating_a_link(): void
    {
        $campaign = Campaign::create(['name' => 'Iraq', 'created_by' => User::first()->id, 'is_active' => false]);
        $tag = Tag::create(['name' => 'Expo', 'color' => '#538f3f']);
        $link = $this->link(['title' => 'Original', 'campaign_id' => $campaign->id, 'max_visits' => 25, 'access_password' => 'secret-hash']);
        $link->tags()->attach($tag);
        $this->get(route('links.create', ['duplicate' => $link->id]))->assertOk()
            ->assertViewHas('campaigns', fn ($campaigns) => $campaigns->contains($campaign))
            ->assertViewHas('defaults', fn ($defaults) => $defaults['destination_url'] === $link->destination_url
                && $defaults['campaign_id'] === $campaign->id && $defaults['tag_ids'] === [$tag->id]
                && ! isset($defaults['custom_code'], $defaults['access_password'], $defaults['max_visits']))
            ->assertSee('name="custom_code" value=""', false);
        $this->assertDatabaseCount('short_links', 1);
        $this->post(route('links.store'), ['destination_url' => $link->destination_url, 'code_type' => 'custom', 'custom_code' => $link->code])->assertSessionHasErrors('custom_code');
        $this->assertDatabaseCount('short_links', 1);
    }

    public function test_save_and_add_another_preserves_new_campaign_and_tags_but_clears_link_fields(): void
    {
        $response = $this->post(route('links.store'), [
            'title' => 'First link', 'destination_url' => 'https://example.com', 'code_type' => 'custom', 'custom_code' => 'first-link',
            'new_campaign_name' => 'New campaign', 'new_tags' => 'Expo, Iraq', 'after_save' => 'another', 'is_active' => '0',
        ]);
        $link = ShortLink::with('tags')->firstOrFail();
        $response->assertRedirect(route('links.create'))->assertSessionHas('link_defaults', [
            'campaign_id' => $link->campaign_id, 'tag_ids' => $link->tags->modelKeys(), 'code_type' => 'custom',
        ]);
        $this->assertFalse($link->is_active);
        $this->get(route('links.create'))->assertOk()->assertSee('name="title" value=""', false)->assertSee('name="custom_code" value=""', false);
    }

    public function test_bulk_page_and_preview_do_not_create_records(): void
    {
        $this->get(route('links.bulk.create'))->assertOk()->assertSee('bulk-row-template');
        $this->postJson(route('links.bulk.store'), ['intent' => 'validate', 'rows' => [
            ['destination_url' => 'https://example.com', 'code' => 'first'],
            ['destination_url' => 'https://example.com/en', 'code' => ''],
            ['destination_url' => 'https://example.com/ar', 'code' => ''],
        ]])->assertOk();
        $this->assertDatabaseCount('short_links', 0);
    }

    public function test_bulk_page_supports_arabic_and_preserves_rows_after_validation_errors(): void
    {
        $this->withSession(['locale' => 'ar'])->get(route('links.bulk.create'))->assertOk()
            ->assertSee('إضافة روابط جماعية')->assertSee('dir="rtl"', false);
        $this->from(route('links.bulk.create'))->post(route('links.bulk.store'), ['rows' => [
            ['destination_url' => 'https://example.com', 'code' => 'preserved-slug'],
            ['destination_url' => 'invalid', 'code' => 'other-slug'],
        ]])->assertRedirect(route('links.bulk.create'))->assertSessionHasErrors('rows.1.destination_url')
            ->assertSessionHas('_old_input.rows.0.code', 'preserved-slug');
        $this->get(route('links.bulk.create'))->assertOk()->assertSee('preserved-slug');
        $this->assertDatabaseCount('short_links', 0);
    }

    public function test_bulk_creation_adds_campaign_tracking_tags_audit_and_copy_results(): void
    {
        $campaign = Campaign::create(['name' => 'Iraq', 'utm_source' => 'facebook', 'created_by' => User::first()->id]);
        $tag = Tag::create(['name' => 'Expo', 'color' => '#538f3f']);
        $this->post(route('links.bulk.store'), [
            'campaign_id' => $campaign->id, 'tag_ids' => [$tag->id], 'rows' => [
                ['destination_url' => 'https://example.com/ar?ref=expo#apply', 'code' => ' Iraq-AR ', 'title' => ''],
                ['destination_url' => 'https://example.com/en', 'code' => '', 'title' => 'English'],
            ],
        ])->assertRedirect(route('links.bulk.create'))->assertSessionHas('created_links', fn ($links) => count($links) === 2 && $links[0] === url('iraq-ar'));
        $this->assertDatabaseCount('short_links', 2);
        foreach (ShortLink::all() as $link) {
            $this->assertSame($campaign->id, $link->campaign_id);
            $this->assertTrue($link->tags->contains($tag));
            $this->assertStringContainsString('utm_source=facebook', $link->destination_url);
            $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'subject_id' => $link->id]);
        }
        $this->assertSame('https://example.com/ar?ref=expo&utm_source=facebook#apply', ShortLink::where('code', 'iraq-ar')->first()->destination_url);
        $this->get(route('links.bulk.create'))->assertOk()->assertSee(url('iraq-ar'));
    }

    public function test_invalid_batch_is_rejected_without_partial_writes(): void
    {
        $this->link(['code' => 'archived'])->delete();
        $this->postJson(route('links.bulk.store'), ['rows' => [
            ['destination_url' => 'https://example.com', 'code' => 'valid-new'],
            ['destination_url' => 'https://example.com', 'code' => 'DUPLICATE'],
            ['destination_url' => 'https://example.com', 'code' => 'duplicate'],
            ['destination_url' => 'ftp://example.com', 'code' => 'other'],
            ['destination_url' => 'https://example.com', 'code' => 'archived'],
            ['destination_url' => 'https://example.com', 'code' => 'login'],
        ]])->assertUnprocessable()->assertJsonValidationErrors(['rows.1.code', 'rows.2.code', 'rows.3.destination_url', 'rows.4.code', 'rows.5.code']);
        $this->assertDatabaseCount('short_links', 1);
    }

    public function test_csv_is_loaded_for_review_and_malformed_rows_are_reported(): void
    {
        $csv = UploadedFile::fake()->createWithContent('links.csv', "\xEF\xBB\xBFdestination_url,title,code\nhttps://example.com,Example,example\n");
        $this->post(route('links.bulk.store'), ['csv' => $csv])->assertRedirect(route('links.bulk.create'))
            ->assertSessionHas('_old_input.rows.0.code', 'example');
        $this->get(route('links.bulk.create'))->assertOk()->assertSee('example');
        $this->assertDatabaseCount('short_links', 0);
        $bad = UploadedFile::fake()->createWithContent('bad.csv', "destination_url,code\nhttps://example.com,slug,extra\n");
        $this->post(route('links.bulk.store'), ['csv' => $bad])->assertSessionHasErrors('csv');
        $this->assertDatabaseCount('short_links', 0);
    }

    public function test_bulk_limit_and_permissions_are_enforced(): void
    {
        $this->postJson(route('links.bulk.store'), ['rows' => array_fill(0, 201, ['destination_url' => 'https://example.com'])])
            ->assertUnprocessable()->assertJsonValidationErrors('rows');
        $analyst = User::factory()->create();
        $analyst->assignRole('Analyst');
        $this->actingAs($analyst);
        $this->get(route('links.bulk.create'))->assertForbidden();
        $this->postJson(route('links.bulk.store'), ['rows' => [['destination_url' => 'https://example.com']]])->assertForbidden();
        $this->get(route('links.create', ['duplicate' => $this->link()->id]))->assertForbidden();
    }
}
