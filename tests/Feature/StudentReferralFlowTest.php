<?php

namespace Tests\Feature;

use App\Models\CrmSubmission;
use App\Models\AuditLog;
use App\Models\RecruitmentPartner;
use App\Models\StudentReferral;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentReferralFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_partner_portal_routes_are_namespaced_under_crm(): void
    {
        $this->assertSame(url('/crm/partner/login'), route('partner.login'));
        $this->assertSame(url('/crm/partner/students'), route('partner.referrals.index'));
        $this->get('/partner/login')->assertNotFound();
        $this->get('/partner/students')->assertNotFound();
        $this->get('/crm/partners/login')->assertRedirect('/crm/partner/login');
    }

    public function test_partner_can_sign_in_and_register_a_student(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['password' => 'Password123!']);
        $user->assignRole('Partner');
        $partner = RecruitmentPartner::create([
            'user_id' => $user->id,
            'name' => 'Global Education',
            'code' => 'global-education',
            'access_token' => str_repeat('a', 48),
            'is_active' => true,
        ]);

        $this->post(route('partner.login.store'), ['email' => $user->email, 'password' => 'Password123!'])
            ->assertRedirect(route('partner.referrals.index'));

        $response = $this->post(route('partner.referrals.store'), [
            'student_name' => 'Ahmed Ali',
            'mobile' => '+20 100 123 4567',
            'nationality' => 'Egyptian',
            'desired_program' => 'Engineering',
            'passport' => UploadedFile::fake()->image('passport.jpg'),
            'consent' => '1',
            'company_fax' => '',
        ]);

        $referral = StudentReferral::firstOrFail();
        $response->assertRedirect(route('partner.referrals.index'));
        $this->assertSame($partner->id, $referral->recruitment_partner_id);
        $this->assertStringStartsWith('MSA-', $referral->reference_code);
        $this->assertSame('new', $referral->status);
        Storage::disk('local')->assertExists($referral->passport_path);
    }

    public function test_partner_cannot_access_another_partners_student_or_edit_reviewed_student(): void
    {
        $firstUser = User::factory()->create();
        $firstUser->assignRole('Partner');
        $partner = RecruitmentPartner::create([
            'user_id' => $firstUser->id,
            'name' => 'First Partner',
            'code' => 'first-partner',
            'access_token' => str_repeat('b', 48),
            'is_active' => true,
        ]);
        $secondUser = User::factory()->create();
        $secondUser->assignRole('Partner');
        $secondPartner = RecruitmentPartner::create([
            'user_id' => $secondUser->id,
            'name' => 'Second Partner',
            'code' => 'second-partner',
            'access_token' => str_repeat('c', 48),
            'is_active' => true,
        ]);
        $referral = StudentReferral::create([
            'recruitment_partner_id' => $partner->id,
            'reference_code' => 'MSA-260909-ABC123',
            'student_name' => 'Ahmed Ali',
            'mobile' => '+20 100 123 4567',
            'nationality' => 'Egyptian',
            'desired_program' => 'Engineering',
            'consent' => true,
            'status' => 'new',
        ]);

        $this->actingAs($secondUser)->get(route('partner.referrals.edit', $referral))->assertNotFound();
        $referral->update(['status' => 'contacted']);
        $this->actingAs($firstUser)->get(route('partner.referrals.edit', $referral))->assertForbidden();
        $this->assertNotNull($secondPartner);
    }

    public function test_partner_can_update_study_in_egypt_status_and_editor_is_recorded(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Partner');
        $partner = RecruitmentPartner::create(['user_id' => $user->id, 'name' => 'Partner', 'code' => 'partner', 'access_token' => str_repeat('d', 48), 'is_active' => true]);
        $referral = StudentReferral::create(['recruitment_partner_id' => $partner->id, 'reference_code' => 'MSA-260910-ABC123', 'student_name' => 'Student', 'mobile' => '+201001234567', 'nationality' => 'Egypt', 'desired_program' => 'Engineering', 'consent' => true]);

        $this->actingAs($user)->patch(route('partner.referrals.study-in-egypt', $referral), ['status' => 'applied_on_study_in_egypt'])->assertRedirect();

        $referral->refresh();
        $this->assertTrue($referral->study_in_egypt_applied);
        $this->assertSame('applied_on_study_in_egypt', $referral->status);
        $this->assertSame($user->id, $referral->study_in_egypt_updated_by);
        $this->assertNotNull($referral->study_in_egypt_updated_at);
        $this->assertTrue(AuditLog::query()->where('subject_id', $referral->id)->where('description', 'Updated student referral status')->exists());
    }

    public function test_staff_can_register_student_for_a_selected_partner(): void
    {
        $admin = User::firstOrFail();
        $partnerUser = User::factory()->create();
        $partnerUser->assignRole('Partner');
        $partner = RecruitmentPartner::create(['user_id' => $partnerUser->id, 'name' => 'Assigned Partner', 'code' => 'assigned-partner', 'access_token' => str_repeat('e', 48), 'is_active' => true]);

        $this->actingAs($admin)->post(route('crm.student-referrals.store'), [
            'recruitment_partner_id' => $partner->id, 'student_name' => 'Admin Student', 'mobile' => '+201001234567',
            'email' => 'student@example.com', 'nationality' => 'Egypt', 'desired_program' => 'Engineering',
            'study_in_egypt_applied' => '0', 'consent' => '1', 'company_fax' => '',
        ])->assertRedirect(route('crm.student-referrals.index'));

        $referral = StudentReferral::where('email', 'student@example.com')->firstOrFail();
        $this->assertSame($partner->id, $referral->recruitment_partner_id);
        $this->assertFalse($referral->study_in_egypt_applied);
        $this->assertSame($admin->id, $referral->study_in_egypt_updated_by);
    }

    public function test_only_authorized_staff_can_manage_partners_and_referrals(): void
    {
        $admin = User::firstOrFail();
        $otherUser = User::factory()->create();

        $this->actingAs($admin)->get(route('crm.partners.index'))->assertOk();
        $this->actingAs($admin)->get(route('crm.student-referrals.index'))->assertOk();
        $this->actingAs($otherUser)->get(route('crm.partners.index'))->assertForbidden();
        $this->actingAs($otherUser)->get(route('crm.student-referrals.index'))->assertForbidden();
    }

    public function test_staff_can_open_partner_details_using_the_canonical_partner_url(): void
    {
        $admin = User::firstOrFail();
        $partnerUser = User::factory()->create();
        $partnerUser->assignRole('Partner');
        $partner = RecruitmentPartner::create(['user_id' => $partnerUser->id, 'name' => 'Partner', 'code' => 'partner-details', 'access_token' => str_repeat('f', 48), 'is_active' => true]);

        $this->actingAs($admin)->get(route('crm.partners.show', $partner))->assertOk();
    }

    public function test_staff_can_create_partner_account_from_full_application(): void
    {
        $admin = User::firstOrFail();
        $submission = CrmSubmission::create([
            'agency_name' => 'Global Education',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'contact_name' => 'Sara Ahmed',
            'mobile' => '+971 50 123 4567',
            'email' => 'partner@example.com',
            'password' => 'Password123!',
            'recruitment_countries' => ['Saudi Arabia'],
            'annual_students_range' => '26-50',
            'works_with_egyptian_universities' => true,
            'current_universities' => 'Example University',
            'expected_msa_students_range' => '11-25',
            'interested_programs' => ['Engineering'],
            'commission_type' => 'fixed_usd',
            'commission_value' => 500,
            'exclusive_discount_percent' => 5,
            'consent' => true,
        ]);

        $this->actingAs($admin)->from(route('crm.partners.index'))->post(route('crm.partners.store', $submission))
            ->assertRedirect(route('crm.partners.index'))
            ->assertSessionHas('success');

        $partner = RecruitmentPartner::firstWhere('crm_submission_id', $submission->id);

        $this->assertNotNull($partner);
        $this->assertSame('Global Education', $partner->name);
        $this->assertTrue($partner->user->hasRole('Partner'));
        auth()->logout();

        $this->post(route('partner.login.store'), ['email' => 'partner@example.com', 'password' => 'Password123!'])
            ->assertRedirect(route('partner.referrals.index'));
    }

    public function test_staff_can_set_password_when_creating_account_from_old_application(): void
    {
        $admin = User::firstOrFail();
        $submission = CrmSubmission::create([
            'agency_name' => 'Old Application Partner',
            'country' => 'Egypt',
            'contact_name' => 'Mona Ali',
            'mobile' => '+20 100 222 3333',
            'email' => 'old-partner@example.com',
            'recruitment_countries' => ['Egypt'],
            'annual_students_range' => '1-25',
            'works_with_egyptian_universities' => false,
            'expected_msa_students_range' => '1-10',
            'interested_programs' => ['Pharmacy'],
            'commission_type' => 'fixed_usd',
            'commission_value' => 250,
            'exclusive_discount_percent' => 5,
            'consent' => true,
        ]);

        $this->actingAs($admin)->from(route('crm.partners.index'))->post(route('crm.partners.store', $submission), [
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('crm.partners.index'));

        auth()->logout();

        $this->post(route('partner.login.store'), ['email' => 'old-partner@example.com', 'password' => 'Password123!'])
            ->assertRedirect(route('partner.referrals.index'));
    }
}
