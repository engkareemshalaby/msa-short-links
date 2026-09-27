<?php

namespace Tests\Feature;

use App\Models\EventContact;
use App\Models\EventContactTag;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventContactFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_nigeria_business_cards_are_imported_without_duplicate_people(): void
    {
        $this->assertDatabaseCount('event_contacts', 7);
        $this->assertSame(9, EventContact::all()->sum(fn (EventContact $contact) => count($contact->emails)));

        $ajir = EventContact::where('name', 'Comr. Ajir Victor')->firstOrFail();
        $this->assertSame(['ajirvictor@gmail.com', 'rjaempirenigerialimited@gmail.com'], $ajir->emails);

        $yusuf = EventContact::where('name', 'Yusuf Wada Yunusa')->firstOrFail();
        $this->assertSame(['iakengineeringserv@gmail.com', 'yuseh@hotmail.com'], $yusuf->emails);
    }

    public function test_authorized_staff_can_view_search_and_update_event_contacts(): void
    {
        $admin = User::firstOrFail();
        $contact = EventContact::where('name', 'Richard Morgan')->firstOrFail();

        $this->actingAs($admin)->get(route('crm.event-contacts.index', ['search' => 'morganoxford']))
            ->assertOk()->assertSee('Richard Morgan')->assertDontSee('Bolanle Jegede');
        $this->actingAs($admin)->get(route('crm.event-contacts.show', $contact))
            ->assertOk()->assertSee('richard@morganoxfordeducation.co.uk');
        $this->actingAs($admin)->patch(route('crm.event-contacts.follow-up', $contact), [
            'status' => 'contacted', 'notes' => 'Follow-up email sent.',
        ])->assertRedirect();

        $this->assertDatabaseHas('event_contacts', [
            'id' => $contact->id, 'status' => 'contacted', 'notes' => 'Follow-up email sent.',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => EventContact::class, 'subject_id' => $contact->id, 'action' => 'updated',
        ]);
    }

    public function test_authorized_staff_can_create_edit_and_delete_event_contacts(): void
    {
        $admin = User::firstOrFail();
        $tag = EventContactTag::create(['name' => 'New lead', 'color' => '#538F3F']);

        $response = $this->actingAs($admin)->post(route('crm.event-contacts.store'), [
            'name' => 'New Contact',
            'emails_text' => "PRIMARY@EXAMPLE.COM\nsecondary@example.com",
            'phones_text' => "+20 100 123 4567\n+20 111 765 4321",
            'event_name' => 'Lagos Education Fair',
            'source' => 'Business Card',
            'status' => 'new',
            'notes' => 'Met at the admissions stand.',
            'tag_ids' => [$tag->id],
        ]);
        $contact = EventContact::where('primary_email', 'primary@example.com')->firstOrFail();
        $response->assertRedirect(route('crm.event-contacts.show', $contact));
        $this->assertSame(['primary@example.com', 'secondary@example.com'], $contact->emails);
        $this->assertSame(['+20 100 123 4567', '+20 111 765 4321'], $contact->phones);
        $this->assertSame('+20 100 123 4567', $contact->primary_phone);
        $this->assertTrue($contact->tags()->whereKey($tag->id)->exists());
        $this->assertSame('business_card', $contact->source);

        $this->actingAs($admin)->put(route('crm.event-contacts.update', $contact), [
            'name' => 'Updated Contact',
            'emails_text' => 'updated@example.com',
            'phones_text' => '+962 (7) 9000-0000',
            'event_name' => 'Lagos Education Fair 2026',
            'source' => 'Manual Entry',
            'status' => 'qualified',
            'notes' => 'Ready for follow-up.',
        ])->assertRedirect(route('crm.event-contacts.show', $contact));
        $this->assertDatabaseHas('event_contacts', [
            'id' => $contact->id, 'name' => 'Updated Contact', 'primary_email' => 'updated@example.com',
            'primary_phone' => '+962 (7) 9000-0000',
            'event_name' => 'Lagos Education Fair 2026', 'source' => 'manual_entry', 'status' => 'qualified',
        ]);

        $this->actingAs($admin)->delete(route('crm.event-contacts.destroy', $contact))
            ->assertRedirect(route('crm.event-contacts.index'));
        $this->assertDatabaseMissing('event_contacts', ['id' => $contact->id]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => EventContact::class, 'subject_id' => $contact->id, 'action' => 'deleted',
        ]);
    }

    public function test_contacts_can_be_found_by_phone_number(): void
    {
        $admin = User::firstOrFail();
        $contact = EventContact::firstOrFail();
        $contact->update(['primary_phone' => '+20 100 555 1234', 'phones' => ['+20 100 555 1234', '+20 111 999 8888']]);

        $this->actingAs($admin)->get(route('crm.event-contacts.index', ['search' => '111 999']))
            ->assertOk()
            ->assertSee($contact->name);
    }

    public function test_contact_tags_are_independent_many_to_many_and_support_multiple_filter_values(): void
    {
        $admin = User::firstOrFail();
        $vip = EventContactTag::create(['name' => 'VIP', 'color' => '#C0392B']);
        $followUp = EventContactTag::create(['name' => 'Follow up', 'color' => '#2980B9']);
        $first = EventContact::firstOrFail();
        $second = EventContact::query()->whereKeyNot($first->id)->firstOrFail();

        $first->tags()->attach([$vip->id, $followUp->id]);
        $second->tags()->attach($followUp);

        $this->assertCount(2, $first->fresh()->tags);
        $this->assertCount(2, $followUp->fresh()->contacts);

        $this->actingAs($admin)->get(route('crm.event-contacts.index', ['tag_ids' => [$vip->id]]))
            ->assertOk()
            ->assertSee($first->name)
            ->assertDontSee($second->name);

        $this->actingAs($admin)->get(route('crm.event-contacts.index', ['tag_ids' => [$vip->id, $followUp->id]]))
            ->assertOk()
            ->assertSee($first->name)
            ->assertSee($second->name);
    }

    public function test_admin_can_manage_contact_tags(): void
    {
        $admin = User::firstOrFail();

        $this->actingAs($admin)->post(route('crm.event-contact-tags.store'), [
            'name' => 'Priority', 'color' => '#123ABC',
        ])->assertRedirect();

        $tag = EventContactTag::firstWhere('name', 'Priority');
        $this->actingAs($admin)->put(route('crm.event-contact-tags.update', $tag), [
            'name' => 'High priority', 'color' => '#ABC123',
        ])->assertRedirect();
        $this->assertDatabaseHas('event_contact_tags', ['id' => $tag->id, 'name' => 'High priority', 'color' => '#ABC123']);

        $this->actingAs($admin)->delete(route('crm.event-contact-tags.destroy', $tag))->assertRedirect();
        $this->assertDatabaseMissing('event_contact_tags', ['id' => $tag->id]);
    }

    public function test_unauthorized_users_cannot_access_event_contacts(): void
    {
        $user = User::factory()->create();
        $contact = EventContact::firstOrFail();

        $this->actingAs($user)->get(route('crm.event-contacts.index'))->assertForbidden();
        $this->actingAs($user)->patch(route('crm.event-contacts.follow-up', $contact), [
            'status' => 'contacted',
        ])->assertForbidden();
    }
}
