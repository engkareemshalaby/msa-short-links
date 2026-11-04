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
            ->assertSee('Parent / guardian mobile number')
            ->assertSee('Faculty of Languages')
            ->assertSee('images/olympics-header.jpeg', false)
            ->assertSee('>Other</span>', false)
            ->assertDontSee('Other international certificate')
            ->assertDontSee('Please select the most suitable time');

        $this->post(route('crm.schools-olympics.store'), $this->payload())
            ->assertRedirect(route('crm.schools-olympics.thank-you'));

        $registration = ExhibitionRegistration::firstOrFail();
        $this->assertSame('MSA Schools Olympics', $registration->exhibition_location);
        $this->assertSame('01012345678', $registration->whatsapp_number);
        $this->assertNull($registration->preferred_contact_time);
        $this->assertSame(['Faculty of Dentistry', 'Faculty of Languages'], $registration->interested_faculties);
        $this->assertMatchesRegularExpression('/^MSO-\d{6}$/', $registration->reference_code);
        $this->get(route('crm.schools-olympics.thank-you'))->assertOk()->assertSee('Registration received');
    }

    public function test_arabic_form_is_separate_and_keeps_language_through_submission(): void
    {
        $this->get(route('crm.schools-olympics.create.ar', ['utm_source' => 'school']))
            ->assertOk()->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('التسجيل في أولمبياد مدارس MSA')
            ->assertSee('كلية اللغات')
            ->assertSee('images/olympics-header.jpeg', false)
            ->assertDontSee('preferred_contact_time')
            ->assertDontSee('Role of the registrant')
            ->assertSee(route('crm.schools-olympics.create', ['utm_source' => 'school']), false);

        $this->post(route('crm.schools-olympics.store.ar'), $this->payload())
            ->assertRedirect(route('crm.schools-olympics.thank-you.ar'));

        $this->assertSame('ar', ExhibitionRegistration::firstOrFail()->source_form_locale);
        $this->get(route('crm.schools-olympics.thank-you.ar'))->assertOk()
            ->assertSee('تم استلام التسجيل')->assertDontSee('Register another student');
    }

    public function test_required_fields_and_faculty_limit_are_enforced(): void
    {
        $this->post(route('crm.schools-olympics.store'), [])->assertSessionHasErrors([
            'student_email', 'registrant_role', 'student_name', 'student_mobile',
            'parent_mobile', 'certificate_type', 'interested_faculties',
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
        ];
    }
}
