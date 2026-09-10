<?php

namespace Tests\Feature;

use App\Models\ExhibitionRegistration;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExhibitionRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_and_submit_jordan_exhibition_form(): void
    {
        $this->get('/crm/jordan-exhibition')->assertOk()->assertSee('Start your journey at MSA University');

        $this->post('/crm/jordan-exhibition', $this->payload())
            ->assertRedirect('/crm/jordan-exhibition/thank-you');

        $this->assertDatabaseHas('exhibition_registrations', [
            'student_email' => 'student@example.com',
            'exhibition_location' => 'Jordan',
            'status' => 'new',
        ]);
        $this->assertSame(['Faculty of Dentistry', 'Faculty of Engineering'], ExhibitionRegistration::firstOrFail()->interested_faculties);
    }

    public function test_a_student_can_select_no_more_than_four_faculties(): void
    {
        $payload = $this->payload();
        $payload['interested_faculties'] = array_slice(ExhibitionRegistration::FACULTIES, 0, 5);

        $this->post('/crm/jordan-exhibition', $payload)->assertSessionHasErrors('interested_faculties');
        $this->assertDatabaseEmpty('exhibition_registrations');
    }

    public function test_authorized_user_can_open_registration_details(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post('/crm/jordan-exhibition', $this->payload());
        $registration = ExhibitionRegistration::firstOrFail();

        $this->actingAs(User::firstOrFail())
            ->get(route('crm.exhibition.show', $registration))
            ->assertOk()
            ->assertSee('Jordan Student')
            ->assertSee('Predicted A grades')
            ->assertSee('Faculty of Dentistry');

        auth()->logout();
        $this->get(route('crm.exhibition.show', $registration))->assertRedirect('/login');
    }

    public function test_listing_filters_and_analytics_are_available_to_authorized_users(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post('/crm/jordan-exhibition', $this->payload());
        $admin = User::firstOrFail();

        $this->actingAs($admin)->get('/crm/exhibition-registrations?registrant_role=student&certificate_type=Cambridge%20IGCSE&faculty=Faculty%20of%20Dentistry&preferred_contact_method=whatsapp')
            ->assertOk()->assertSee('Jordan Student');
        $this->actingAs($admin)->get('/crm/exhibition-registrations?registrant_role=parent')
            ->assertOk()->assertDontSee('Jordan Student');
        $this->actingAs($admin)->get('/crm/exhibition-registrations-analytics?days=30')
            ->assertOk()->assertSee('Registrations over time')->assertSee('Top interested faculties');
    }

    private function payload(): array
    {
        return [
            'registrant_role' => 'student', 'student_email' => 'student@example.com',
            'student_name' => 'Jordan Student', 'student_mobile' => '+962 79 123 4567',
            'certificate_type' => 'Cambridge IGCSE', 'current_result' => 'Predicted A grades',
            'interested_faculties' => ['Faculty of Dentistry', 'Faculty of Engineering'],
            'preferred_contact_method' => 'whatsapp', 'consent' => '1', 'company_fax' => '',
            'has_relatives_or_acquaintances_in_egypt' => '1',
            'has_accommodation_in_egypt' => '1',
        ];
    }
}
