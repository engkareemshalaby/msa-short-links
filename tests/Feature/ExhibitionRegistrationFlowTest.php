<?php

namespace Tests\Feature;

use App\Mail\IraqExhibitionWelcome;
use App\Models\ExhibitionRegistration;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        $registration = ExhibitionRegistration::firstOrFail();
        $this->assertSame(['Faculty of Dentistry', 'Faculty of Engineering'], $registration->interested_faculties);
        $this->assertMatchesRegularExpression('/^JOR-\d{6}$/', $registration->reference_code);
    }

    public function test_jordan_form_uses_the_crm_meta_pixel_and_thank_you_shows_the_next_step(): void
    {
        config()->set('services.meta.crm_pixel_id', '123456789012345');

        $this->get('/crm/jordan-exhibition')
            ->assertOk()
            ->assertSee("fbq('init', \"123456789012345\")", false)
            ->assertSee("fbq('track', 'PageView')", false);

        $this->followingRedirects()->post('/crm/jordan-exhibition', $this->payload())
            ->assertOk()
            ->assertSee('Complete your application by registering on the Study in Egypt platform.')
            ->assertSee('https://admission.study-in-egypt.gov.eg/signup', false)
            ->assertDontSee('Your reference number')
            ->assertSee("fbq('track', 'CompleteRegistration'", false);
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

    public function test_exhibition_location_filter_separates_iraq_and_jordan_and_applies_to_export(): void
    {
        Mail::fake();
        $this->seed(DatabaseSeeder::class);
        $this->post('/crm/jordan-exhibition', $this->payload());
        $this->get('/crm/iraq-exhibition');
        $iraq = $this->payload();
        $iraq['student_name'] = 'Iraq Student';
        $iraq['student_email'] = 'iraq@example.com';
        $iraq['student_mobile'] = '+964 770 123 4567';
        unset($iraq['current_result'], $iraq['preferred_contact_method'], $iraq['has_relatives_or_acquaintances_in_egypt'], $iraq['has_accommodation_in_egypt']);
        $this->post('/crm/iraq-exhibition', $iraq)->assertRedirect('/crm/iraq-exhibition/thank-you');

        $admin = User::firstOrFail();
        $this->actingAs($admin)->get(route('crm.exhibition.index'))
            ->assertOk()->assertSee('Jordan Student')->assertSee('Iraq Student');
        $this->get(route('crm.exhibition.index', ['exhibition_location' => 'Iraq (Baghdad)']))
            ->assertOk()->assertSee('Iraq Student')->assertDontSee('Jordan Student');
        $this->get(route('crm.exhibition.index', ['exhibition_location' => 'Jordan']))
            ->assertOk()->assertSee('Jordan Student')->assertDontSee('Iraq Student');

        $export = $this->get(route('crm.exhibition.export', ['exhibition_location' => 'Iraq (Baghdad)']));
        $export->assertOk();
        $file = tempnam(sys_get_temp_dir(), 'exhibition-export-');
        file_put_contents($file, $export->streamedContent());
        try {
            $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        } finally {
            unlink($file);
        }
        $rows = $sheet->getActiveSheet()->toArray();
        $this->assertCount(2, $rows);
        $this->assertSame('Iraq Student', $rows[1][4]);
    }
    public function test_authorized_user_can_export_filtered_registrations_as_excel(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post('/crm/jordan-exhibition', $this->payload());
        $admin = User::firstOrFail();

        $response = $this->actingAs($admin)->get(route('crm.exhibition.export', ['status' => 'new']));

        $response->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
        $content = $response->streamedContent();
        $this->assertStringStartsWith('PK', $content);
        $this->assertGreaterThan(2000, strlen($content));
    }

    public function test_iraq_form_defaults_to_arabic_and_saves_without_scholarship_answer(): void
    {
        Mail::fake();

        $this->get('/crm/iraq-exhibition')->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('العراق (بغداد)')
            ->assertDontSee('هل لديك أقارب أو معارف في مصر؟');

        $payload = $this->payload();
        unset($payload['current_result'], $payload['has_relatives_or_acquaintances_in_egypt'], $payload['has_accommodation_in_egypt'], $payload['preferred_contact_method']);
        $payload['student_mobile'] = '+964 770 123 4567';
        $this->post('/crm/iraq-exhibition', $payload)->assertRedirect('/crm/iraq-exhibition/thank-you');

        $registration = ExhibitionRegistration::firstOrFail();
        $this->assertSame('Iraq (Baghdad)', $registration->exhibition_location);
        $this->assertSame('email', $registration->preferred_contact_method);
        $this->assertNull($registration->full_scholarship_eligibility);
        $this->assertNull($registration->current_result);
        $this->assertMatchesRegularExpression('/^IRQ-\d{6}$/', $registration->reference_code);
        Mail::assertSent(IraqExhibitionWelcome::class, fn ($mail) =>
            $mail->hasTo('student@example.com')
            && $mail->referenceCode === $registration->reference_code
            && $mail->messageLocale === 'ar'
            && str_contains($mail->render(), 'https://go.msa.edu.eg/iraq-study-in-egypt')
            && str_contains($mail->render(), 'https://go.msa.edu.eg/iraq-email')
        );
        $this->get('/crm/iraq-exhibition/thank-you')->assertOk()->assertSee('تم التسجيل بنجاح');
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




