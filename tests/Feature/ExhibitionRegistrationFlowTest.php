<?php

namespace Tests\Feature;

use App\Mail\IraqExhibitionParentWelcome;
use App\Mail\IraqExhibitionWelcome;
use App\Models\ExhibitionRegistration;
use App\Models\User;
use App\Support\GeneralRegistrationCertificates;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        $iraq['certificate_type'] = 'Iraqi Secondary School Certificate (Sixth Preparatory)';
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
        $rows = $this->csvRows($export->streamedContent());
        $this->assertCount(2, $rows);
        $this->assertSame('Iraq Student', $rows[1][4]);
    }

    public function test_authorized_user_can_export_filtered_registrations_as_excel(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post('/crm/jordan-exhibition', $this->payload());
        $admin = User::firstOrFail();

        $response = $this->actingAs($admin)->get(route('crm.exhibition.export', ['status' => 'new']));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('.csv', (string) $response->headers->get('content-disposition'));
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $rows = $this->csvRows($content);
        $this->assertCount(2, $rows);
        $this->assertCount(18, $rows[1]);
        $this->assertSame('Jordan Student', $rows[1][4]);
    }

    public function test_general_form_requires_country_and_saves_source_and_country_separately(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->get(route('crm.general.create'))->assertOk()
            ->assertSee('General registration')
            ->assertSee('name="registration_country"', false)
            ->assertSee('role="combobox"', false)
            ->assertSee('id="country-options"', false)
            ->assertSee('id="country-search"', false)
            ->assertSee('Arab High School Certificates')
            ->assertSee('Asian National Certificates')
            ->assertSee('International Systems')
            ->assertDontSee('Additional information')
            ->assertDontSee('name="preferred_contact_method" value=', false)
            ->assertDontSee('name="has_relatives_or_acquaintances_in_egypt" value=', false)
            ->assertSee('name="registration_country_other"', false)
            ->assertSee('name="certificate_category"', false)
            ->assertDontSee('MSA University · Jordan');

        $this->post(route('crm.general.store'), $this->generalPayload())
            ->assertSessionHasErrors('registration_country');
        $this->assertDatabaseEmpty('exhibition_registrations');

        $payload = $this->generalPayload();
        $payload['registration_country'] = 'Saudi Arabia';
        $this->post(route('crm.general.store'), $payload)
            ->assertRedirect(route('crm.general.thank-you'));

        $registration = ExhibitionRegistration::firstOrFail();
        $this->assertSame('General', $registration->exhibition_location);
        $this->assertSame('Saudi Arabia', $registration->registration_country);
        $this->assertSame('email', $registration->preferred_contact_method);
        $this->assertNull($registration->has_relatives_or_acquaintances_in_egypt);
        $this->assertNull($registration->has_accommodation_in_egypt);
        $this->assertMatchesRegularExpression('/^GEN-\d{6}$/', $registration->reference_code);
        $this->get(route('crm.general.thank-you'))->assertOk()
            ->assertSee(route('crm.general.create'), false);

        $this->actingAs(User::firstOrFail())->get(route('crm.exhibition.index', [
            'exhibition_location' => 'General', 'registration_country' => 'Saudi Arabia',
        ]))->assertOk()->assertSee('Jordan Student')->assertSee('Saudi Arabia');

        $rows = $this->csvRows($this->get(route('crm.exhibition.export', [
            'exhibition_location' => 'General', 'registration_country' => 'Saudi Arabia',
        ]))->assertOk()->streamedContent());
        $this->assertCount(2, $rows);
        $this->assertSame('General', $rows[1][15]);
        $this->assertSame('Saudi Arabia', $rows[1][16]);
    }

    public function test_general_form_uses_certificate_categories_independently_of_country(): void
    {
        $countries = GeneralRegistrationCertificates::countries();
        $this->assertSame('Other', end($countries));
        $this->assertContains('Egypt', $countries);
        $this->assertContains('Somalia', $countries);
        $this->assertContains('Nigeria', $countries);
        $this->assertContains('Iran', $countries);

        $payload = $this->generalPayload();
        unset($payload['certificate_category']);
        $payload['registration_country'] = 'Egypt';
        $this->post(route('crm.general.store'), $payload)
            ->assertSessionHasErrors('certificate_category');

        $payload['certificate_category'] = 'International Systems';
        $payload['registration_country'] = 'Other';
        $this->post(route('crm.general.store'), $payload)
            ->assertSessionHasErrors('registration_country_other');
        $this->assertDatabaseEmpty('exhibition_registrations');

        $payload['registration_country'] = 'Saudi Arabia';
        $payload['certificate_type'] = 'Iraq - Sixth Preparatory Certificate';
        $this->post(route('crm.general.store'), $payload)
            ->assertSessionHasErrors('certificate_type');
        $this->assertDatabaseEmpty('exhibition_registrations');

        $payload['certificate_category'] = 'Arab High School Certificates';
        $this->post(route('crm.general.store'), $payload)
            ->assertRedirect(route('crm.general.thank-you'));
        $this->assertDatabaseHas('exhibition_registrations', [
            'exhibition_location' => 'General', 'registration_country' => 'Saudi Arabia',
            'certificate_type' => 'Iraq - Sixth Preparatory Certificate',
        ]);

        $payload['registration_country'] = 'Other';
        $payload['registration_country_other'] = 'Kenya';
        $payload['certificate_category'] = 'Asian National Certificates';
        $payload['certificate_type'] = 'India - CBSE / ICSE / State Board 12th Standard';
        $this->post(route('crm.general.store'), $payload)
            ->assertRedirect(route('crm.general.thank-you'));
        $this->assertDatabaseHas('exhibition_registrations', [
            'registration_country' => 'Kenya',
            'certificate_type' => 'India - CBSE / ICSE / State Board 12th Standard',
        ]);
    }

    public function test_general_form_accepts_international_certificates_for_listed_countries(): void
    {
        $payload = $this->generalPayload();
        $payload['registration_country'] = 'India';
        $this->post(route('crm.general.store'), $payload)
            ->assertRedirect(route('crm.general.thank-you'));
        $this->assertDatabaseHas('exhibition_registrations', [
            'registration_country' => 'India',
            'certificate_type' => 'British System (IGCSE / GCSE / GCE A-Levels)',
        ]);
    }

    public function test_existing_country_specific_forms_do_not_accept_a_spoofed_country(): void
    {
        $payload = $this->payload();
        $payload['registration_country'] = 'Iraq';
        $this->post(route('crm.jordan.store'), $payload)
            ->assertSessionHasErrors('registration_country');
        $this->assertDatabaseEmpty('exhibition_registrations');
    }

    public function test_general_registration_does_not_change_an_existing_registration(): void
    {
        $this->post(route('crm.jordan.store'), $this->payload())
            ->assertRedirect(route('crm.jordan.thank-you'));
        $existing = ExhibitionRegistration::firstOrFail();
        $before = $existing->getAttributes();
        $this->assertNull($existing->registration_country);

        $new = $this->generalPayload();
        $new['student_name'] = 'New general student';
        $new['student_email'] = 'general@example.com';
        $new['registration_country'] = 'Iraq';
        $this->post(route('crm.general.store'), $new)
            ->assertRedirect(route('crm.general.thank-you'));

        $this->assertSame($before, $existing->fresh()->getAttributes());
        $this->assertDatabaseCount('exhibition_registrations', 2);
        $this->assertDatabaseHas('exhibition_registrations', [
            'student_email' => 'general@example.com',
            'exhibition_location' => 'General',
            'registration_country' => 'Iraq',
        ]);
    }

    public function test_export_handles_multiple_batches_and_keeps_arabic_and_formulas_safe(): void
    {
        $this->seed(DatabaseSeeder::class);
        $now = now();
        foreach (range(0, 2) as $batch) {
            $registrations = [];
            for ($number = $batch * 500 + 1; $number <= min(($batch + 1) * 500, 1001); $number++) {
                $registrations[] = [
                    'reference_code' => 'EX-'.$number,
                    'registrant_role' => 'student',
                    'student_email' => "student{$number}@example.com",
                    'student_name' => $number === 1 ? '=HYPERLINK("https://example.com")' : 'طالب '.$number,
                    'student_mobile' => '+201234567890',
                    'certificate_type' => 'Cambridge IGCSE',
                    'interested_faculties' => '[]',
                    'preferred_contact_method' => 'whatsapp',
                    'exhibition_location' => 'Iraq (Baghdad)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('exhibition_registrations')->insert($registrations);
        }

        $rows = $this->csvRows($this->actingAs(User::firstOrFail())
            ->get(route('crm.exhibition.export', ['exhibition_location' => 'Iraq (Baghdad)']))
            ->assertOk()->streamedContent());

        $this->assertCount(1002, $rows);
        $this->assertSame("'=HYPERLINK(\"https://example.com\")", $rows[1][4]);
        $this->assertSame("'+201234567890", $rows[1][6]);
        $this->assertSame('طالب 1001', $rows[1001][4]);
        $this->assertDatabaseCount('exhibition_registrations', 1001);
    }

    public function test_iraq_form_defaults_to_english_and_saves_without_scholarship_answer(): void
    {
        Mail::fake();

        $this->get('/crm/iraq-exhibition')->assertOk()
            ->assertSee('lang="en"', false)
            ->assertDontSee('Additional information')
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:description"', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('images/iraq-exhibition-en.jpeg', false)
            ->assertSee('images/iraq-exhibition-en.jpeg', false)
            ->assertDontSee('هل لديك أقارب أو معارف في مصر؟');

        $payload = $this->payload();
        unset($payload['current_result'], $payload['has_relatives_or_acquaintances_in_egypt'], $payload['has_accommodation_in_egypt'], $payload['preferred_contact_method']);
        $payload['student_mobile'] = '+964 770 123 4567';
        $payload['certificate_type'] = 'Iraqi Secondary School Certificate (Sixth Preparatory)';
        $this->post('/crm/iraq-exhibition', $payload)->assertRedirect('/crm/iraq-exhibition/thank-you');

        $registration = ExhibitionRegistration::firstOrFail();
        $this->assertSame('Iraq (Baghdad)', $registration->exhibition_location);
        $this->assertSame('email', $registration->preferred_contact_method);
        $this->assertNull($registration->full_scholarship_eligibility);
        $this->assertNull($registration->current_result);
        $this->assertMatchesRegularExpression('/^IRQ-\d{6}$/', $registration->reference_code);
        Mail::assertSent(IraqExhibitionWelcome::class, fn ($mail) => $mail->hasTo('student@example.com')
            && $mail->referenceCode === $registration->reference_code
            && $mail->messageLocale === 'en'
            && str_contains($mail->render(), 'https://go.msa.edu.eg/iraq-study-in-egypt')
            && str_contains($mail->render(), 'https://go.msa.edu.eg/iraq-email')
        );
        $this->get('/crm/iraq-exhibition/thank-you')->assertOk()->assertSee('Registration Successfully Received');
    }

    public function test_iraq_certificate_options_are_specific_to_iraq_and_other_requires_a_name(): void
    {
        Mail::fake();
        $page = $this->get('/crm/iraq-exhibition')->assertOk();
        foreach (ExhibitionRegistration::IRAQ_CERTIFICATES as $certificate) {
            $page->assertSee('value="'.$certificate.'"', false);
        }
        $page->assertDontSee('value="WAEC / WASSCE"', false);

        $payload = $this->payload();
        unset($payload['current_result'], $payload['has_relatives_or_acquaintances_in_egypt'], $payload['has_accommodation_in_egypt'], $payload['preferred_contact_method']);
        $this->post('/crm/iraq-exhibition', $payload)->assertSessionHasErrors('certificate_type');

        $payload['certificate_type'] = 'Other';
        $this->post('/crm/iraq-exhibition', $payload)->assertSessionHasErrors('certificate_type_other');
        $payload['certificate_type_other'] = 'Another secondary certificate';
        $this->post('/crm/iraq-exhibition', $payload)->assertRedirect('/crm/iraq-exhibition/thank-you');
        $this->assertDatabaseHas('exhibition_registrations', [
            'certificate_type' => 'Other',
            'certificate_type_other' => 'Another secondary certificate',
            'exhibition_location' => 'Iraq (Baghdad)',
        ]);
    }

    public function test_iraq_banner_uses_arabic_image_for_arabic_locale(): void
    {
        $this->withSession(['iraq_locale_initialized' => true, 'locale' => 'ar'])
            ->get('/crm/iraq-exhibition')
            ->assertOk()
            ->assertSee('images/iraq-exhibition-ar.jpeg', false);
    }

    public function test_iraq_sends_both_emails_in_the_form_language(): void
    {
        foreach (['en', 'ar'] as $locale) {
            Mail::fake();
            $payload = $this->payload();
            unset($payload['current_result'], $payload['has_relatives_or_acquaintances_in_egypt'], $payload['has_accommodation_in_egypt'], $payload['preferred_contact_method']);
            $payload['certificate_type'] = 'Iraqi Secondary School Certificate (Sixth Preparatory)';
            $payload['parent_email'] = 'parent@example.com';
            $this->withSession(['iraq_locale_initialized' => true, 'locale' => $locale])
                ->post('/crm/iraq-exhibition', $payload)
                ->assertRedirect('/crm/iraq-exhibition/thank-you');
            Mail::assertSent(IraqExhibitionWelcome::class, fn ($mail) => $mail->hasTo('student@example.com') && $mail->messageLocale === $locale
            );
            Mail::assertSent(IraqExhibitionParentWelcome::class, function ($mail) use ($locale) {
                $mail->assertSeeInHtml('dir="'.($locale === 'ar' ? 'rtl' : 'ltr').'"', false);
                $mail->assertSeeInHtml('https://go.msa.edu.eg/iraq-study-in-egypt');
                $mail->assertSeeInText('https://go.msa.edu.eg/iraq-email');
                if ($locale === 'en') {
                    $mail->assertSeeInHtml('Dear Parent / Guardian,');
                    $mail->assertSeeInText('Dear Parent / Guardian,');
                }

                return $mail->hasTo('parent@example.com') && $mail->messageLocale === $locale;
            });
            Mail::assertSentCount(2);
        }
    }

    public function test_only_crm_managers_can_delete_a_single_exhibition_registration(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post('/crm/jordan-exhibition', $this->payload());
        $first = ExhibitionRegistration::firstOrFail();
        $secondPayload = $this->payload();
        $secondPayload['student_email'] = 'second@example.com';
        $this->post('/crm/jordan-exhibition', $secondPayload);
        $second = ExhibitionRegistration::where('student_email', 'second@example.com')->firstOrFail();
        $deleteUrl = route('crm.exhibition.destroy', $first);

        $staff = User::factory()->create();
        $staff->assignRole('CRM Staff');
        $this->actingAs($staff)->get(route('crm.exhibition.index'))
            ->assertOk()->assertDontSee('Delete form');
        $this->actingAs($staff)->delete($deleteUrl)->assertForbidden();
        $this->assertDatabaseHas('exhibition_registrations', ['id' => $first->id]);

        $admin = User::factory()->create();
        $admin->assignRole('CRM Admin');
        $this->actingAs($admin)->get(route('crm.exhibition.index'))
            ->assertOk()->assertSee('Delete form');
        $this->from(route('crm.exhibition.index'))->delete($deleteUrl)
            ->assertRedirect(route('crm.exhibition.index'));
        $this->assertDatabaseMissing('exhibition_registrations', ['id' => $first->id]);
        $this->assertDatabaseHas('exhibition_registrations', ['id' => $second->id]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => ExhibitionRegistration::class,
            'subject_id' => $first->id,
            'action' => 'deleted',
        ]);
    }

    private function csvRows(string $content): array
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, substr($content, 3));
        rewind($stream);
        $rows = [];
        while (($row = fgetcsv($stream, escape: '')) !== false) {
            $rows[] = $row;
        }
        fclose($stream);

        return $rows;
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

    private function generalPayload(): array
    {
        $payload = $this->payload();
        unset($payload['preferred_contact_method'], $payload['has_relatives_or_acquaintances_in_egypt'], $payload['has_accommodation_in_egypt']);
        $payload['certificate_type'] = 'British System (IGCSE / GCSE / GCE A-Levels)';
        $payload['certificate_category'] = 'International Systems';

        return $payload;
    }
}
