<?php

namespace Tests\Feature;

use App\Models\ExhibitionRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolsOlympicsRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_schools_olympics_registration(): void
    {
        $this->get(route('crm.schools-olympics.create'))->assertOk()
            ->assertSee('MSA Schools Olympics Registration')
            ->assertSee('Parent/Guardian’s Mobile Number')
            ->assertSee('Faculty of Languages');

        $this->post(route('crm.schools-olympics.store'), $this->payload())
            ->assertRedirect(route('crm.schools-olympics.thank-you'));

        $registration = ExhibitionRegistration::firstOrFail();
        $this->assertSame('MSA Schools Olympics', $registration->exhibition_location);
        $this->assertSame('01012345678', $registration->whatsapp_number);
        $this->assertSame('12:00 PM to 02:00 PM', $registration->preferred_contact_time);
        $this->assertSame(['Faculty of Dentistry', 'Faculty of Languages'], $registration->interested_faculties);
        $this->assertMatchesRegularExpression('/^MSO-\d{6}$/', $registration->reference_code);
        $this->get(route('crm.schools-olympics.thank-you'))->assertOk()->assertSee('Registration received');
    }

    public function test_required_fields_and_faculty_limit_are_enforced(): void
    {
        $this->post(route('crm.schools-olympics.store'), [])->assertSessionHasErrors([
            'student_email', 'registrant_role', 'student_name', 'student_mobile',
            'parent_mobile', 'certificate_type', 'interested_faculties', 'preferred_contact_time',
        ]);

        $payload = $this->payload();
        $payload['interested_faculties'] = array_slice(ExhibitionRegistration::FACULTIES, 0, 5);
        $this->post(route('crm.schools-olympics.store'), $payload)
            ->assertSessionHasErrors('interested_faculties');
        $this->assertDatabaseEmpty('exhibition_registrations');
    }

    private function payload(): array
    {
        return [
            'student_email' => 'student@example.com',
            'registrant_role' => 'student',
            'student_name' => 'Test Student',
            'student_mobile' => '01012345678',
            'whatsapp_number' => '01012345678',
            'parent_mobile' => '01098765432',
            'certificate_type' => 'Egyptian Thanaweya Amma',
            'interested_faculties' => ['Faculty of Dentistry', 'Faculty of Languages'],
            'preferred_contact_time' => '12:00 PM to 02:00 PM',
        ];
    }
}
