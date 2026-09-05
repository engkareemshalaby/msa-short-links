<?php

namespace Tests\Feature;

use App\Models\CrmSubmission;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_can_open_and_submit_the_partner_form(): void
    {
        $this->get('/crm/new')->assertOk()->assertSee('Become an MSA recruitment partner');

        $this->post('/crm/new', $this->validPayload())->assertRedirect('/crm/thank-you');

        $this->assertDatabaseHas('crm_submissions', [
            'agency_name' => 'Global Education',
            'email' => 'partner@example.com',
            'commission_type' => 'percentage',
            'commission_value' => 12.5,
            'commission_basis' => 'academic_year',
            'exclusive_discount_percent' => 5,
            'status' => 'new',
        ]);

        $submission = CrmSubmission::firstOrFail();
        $this->assertSame(['Saudi Arabia', 'Nigeria'], $submission->recruitment_countries);
        $this->assertSame(['Dentistry', 'Engineering'], $submission->interested_programs);
        $this->assertNotNull($submission->ip_hash);
    }

    public function test_commercial_fields_are_validated_conditionally(): void
    {
        $payload = $this->validPayload();
        $payload['commission_value'] = 120;
        $payload['commission_basis'] = null;

        $this->post('/crm/new', $payload)
            ->assertSessionHasErrors(['commission_value', 'commission_basis']);

        $this->assertDatabaseEmpty('crm_submissions');
    }

    public function test_duplicate_agency_and_email_is_not_created_twice(): void
    {
        $this->post('/crm/new', $this->validPayload());
        $this->post('/crm/new', $this->validPayload());

        $this->assertDatabaseCount('crm_submissions', 1);
    }

    public function test_only_a_user_with_the_private_permission_can_view_submissions(): void
    {
        $admin = User::firstOrFail();
        $otherUser = User::factory()->create();

        $this->actingAs($admin)->get('/crm/submissions')->assertOk();
        $this->actingAs($otherUser)->get('/crm/submissions')->assertForbidden();
        auth()->logout();
        $this->get('/crm/submissions')->assertRedirect('/login');
    }

    public function test_opening_an_application_marks_it_as_reviewed(): void
    {
        $this->post('/crm/new', $this->validPayload());
        $submission = CrmSubmission::firstOrFail();

        $this->actingAs(User::firstOrFail())->get("/crm/submissions/{$submission->id}")->assertOk();

        $this->assertDatabaseHas('crm_submissions', ['id' => $submission->id, 'status' => 'reviewed']);
    }

    private function validPayload(): array
    {
        return [
            'agency_name' => 'Global Education',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'website' => 'https://example.com',
            'contact_name' => 'Sara Ahmed',
            'job_title' => 'Managing Director',
            'mobile' => '+971 50 123 4567',
            'email' => 'Partner@Example.com',
            'recruitment_countries' => 'Saudi Arabia, Nigeria',
            'annual_students_range' => '101-250',
            'works_with_egyptian_universities' => '1',
            'current_universities' => 'Example University',
            'expected_msa_students_range' => '26-50',
            'interested_programs' => ['Dentistry', 'Engineering'],
            'notes' => 'Interested in the next intake.',
            'commission_type' => 'percentage',
            'commission_value' => '12.5',
            'commission_basis' => 'academic_year',
            'exclusive_discount_percent' => '5',
            'consent' => '1',
            'source' => 'conference-2026',
            'company_fax' => '',
        ];
    }
}
