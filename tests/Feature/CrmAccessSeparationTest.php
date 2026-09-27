<?php

namespace Tests\Feature;

use App\Models\EventContact;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmAccessSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_crm_only_user_is_sent_to_crm_and_does_not_see_short_link_navigation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CRM Staff');

        $this->actingAs($user)->get('/')
            ->assertRedirect(route('crm.submissions.index'));

        $this->actingAs($user)->get(route('crm.submissions.index'))
            ->assertOk()
            ->assertDontSee('href="'.route('dashboard').'"', false)
            ->assertDontSee('href="'.route('links.index').'"', false)
            ->assertSee('href="'.route('crm.submissions.index').'"', false)
            ->assertSee('href="'.route('crm.event-contacts.index').'"', false);

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('links.index'))->assertForbidden();
    }

    public function test_crm_staff_can_view_but_cannot_modify_crm_records(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CRM Staff');
        $contact = EventContact::firstOrFail();

        $this->actingAs($user)->get(route('crm.event-contacts.index'))->assertOk();
        $this->actingAs($user)->patch(route('crm.event-contacts.follow-up', $contact), [
            'status' => 'contacted',
        ])->assertForbidden();
    }

    public function test_crm_admin_can_modify_crm_records_without_short_link_access(): void
    {
        $user = User::factory()->create();
        $user->assignRole('CRM Admin');
        $contact = EventContact::firstOrFail();

        $this->actingAs($user)->patch(route('crm.event-contacts.follow-up', $contact), [
            'status' => 'contacted',
        ])->assertRedirect();

        $this->actingAs($user)->get(route('links.index'))->assertForbidden();
    }
}
