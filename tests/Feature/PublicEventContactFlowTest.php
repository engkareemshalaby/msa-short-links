<?php

namespace Tests\Feature;

use App\Mail\IraqEventContactConfirmation;
use App\Models\EventContact;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicEventContactFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_without_controlling_crm_fields(): void
    {
        Mail::fake();
        $this->seed(DatabaseSeeder::class);
        $this->get('/crm/event-contacts/register')->assertRedirect('/crm/iraq-exhibition-agency/register');
        $this->get('/crm/iraq-event-contacts/register')->assertRedirect('/crm/iraq-exhibition-agency/register');
        $this->get(route('crm.event-contacts.register'))
            ->assertOk()->assertSee('name="email"', false)
            ->assertDontSee('name="event_contact_stage_id"', false)
            ->assertDontSee('name="tag_ids[]"', false)
            ->assertSee('name="notes"', false)
            ->assertSee('Iraq Exhibition Contact Registration');

        $payload = [
            'name' => '  Guest Contact  ', 'email' => 'GUEST@EXAMPLE.COM',
            'phone' => '+20 100 123 4567', 'organization_name' => 'Example Org',
            'job_title' => 'Director', 'consent' => '1',
            'event_name' => 'Forged Event', 'source' => 'business_card',
            'status' => 'closed', 'event_contact_stage_id' => 999,
            'notes' => 'Please call after 4 pm.', 'tag_ids' => [1],
        ];
        $this->post(route('crm.event-contacts.register.store'), $payload)
            ->assertRedirect(route('crm.event-contacts.register.thank-you'));
        $contact = EventContact::where('primary_email', 'guest@example.com')->firstOrFail();
        $this->assertSame('Guest Contact', $contact->name);
        $this->assertSame('Iraq Exhibition', $contact->event_name);
        $this->assertSame('self_registration', $contact->source);
        $this->assertSame('new', $contact->status);
        $this->assertSame('Awareness', $contact->stage?->name);
        $this->assertSame('Please call after 4 pm.', $contact->notes);
        $this->assertCount(0, $contact->tags);
        $this->assertSame(['guest@example.com'], $contact->emails);
        $this->assertSame(['+20 100 123 4567'], $contact->phones);
        Mail::assertSent(IraqEventContactConfirmation::class, fn ($mail) =>
            $mail->hasTo('guest@example.com')
            && $mail->contactName === 'Guest Contact'
            && $mail->messageLocale === 'en'
            && str_contains($mail->render(), 'We received your contact details')
        );
        $this->get(route('crm.event-contacts.register.thank-you'))
            ->assertOk()->assertSee('Iraq Exhibition Registration Received');

        $this->post(route('crm.event-contacts.register.store'), $payload)
            ->assertRedirect(route('crm.event-contacts.register.thank-you'));
        $this->assertSame(1, EventContact::where('primary_email', 'guest@example.com')->count());
        Mail::assertSentCount(1);
    }

    public function test_arabic_registration_sends_arabic_confirmation(): void
    {
        Mail::fake();
        $this->withSession(['locale' => 'ar'])->post(route('crm.event-contacts.register.store'), [
            'name' => 'Ahmed', 'email' => 'ahmed@example.com', 'consent' => '1',
        ])->assertRedirect(route('crm.event-contacts.register.thank-you'));

        Mail::assertSent(IraqEventContactConfirmation::class, function ($mail) {
            $this->assertSame('تم استلام بيانات التواصل | معرض العراق 2026', $mail->envelope()->subject);
            $mail->assertSeeInHtml('تم استلام بيانات التواصل الخاصة بك');
            $mail->assertSeeInText('تم استلام بيانات التواصل الخاصة بك');

            return $mail->hasTo('ahmed@example.com') && $mail->messageLocale === 'ar';
        });
    }

    public function test_public_form_requires_contact_details_and_consent(): void
    {
        $this->post(route('crm.event-contacts.register.store'), [
            'name' => 'Guest', 'email' => 'invalid', 'consent' => '0',
        ])->assertSessionHasErrors(['email', 'consent']);
        $this->assertSame(7, EventContact::count());
    }
}
