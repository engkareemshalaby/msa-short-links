<?php

namespace Tests\Feature;

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

    public function test_partner_link_opens_and_registers_a_student(): void
    {
        Storage::fake('local');
        $partner = RecruitmentPartner::create([
            'name' => 'Global Education',
            'code' => 'global-education',
            'access_token' => str_repeat('a', 48),
            'is_active' => true,
        ]);

        $this->get(route('student-referrals.create', $partner->access_token))
            ->assertOk()->assertSee('Global Education');

        $response = $this->post(route('student-referrals.store', $partner->access_token), [
            'student_name' => 'Ahmed Ali',
            'mobile' => '+20 100 123 4567',
            'nationality' => 'Egyptian',
            'desired_program' => 'Engineering',
            'passport' => UploadedFile::fake()->image('passport.jpg'),
            'consent' => '1',
            'company_fax' => '',
        ]);

        $referral = StudentReferral::firstOrFail();
        $response->assertRedirect(route('student-referrals.thank-you', $partner->access_token));
        $this->assertSame($partner->id, $referral->recruitment_partner_id);
        $this->assertStringStartsWith('MSA-', $referral->reference_code);
        $this->assertSame('new', $referral->status);
        Storage::disk('local')->assertExists($referral->passport_path);
    }

    public function test_inactive_or_unknown_partner_link_cannot_be_used(): void
    {
        $partner = RecruitmentPartner::create([
            'name' => 'Inactive Partner',
            'code' => 'inactive-partner',
            'access_token' => str_repeat('b', 48),
            'is_active' => false,
        ]);

        $this->get(route('student-referrals.create', $partner->access_token))->assertNotFound();
        $this->post(route('student-referrals.store', $partner->access_token), [
            'student_name' => 'Ahmed Ali',
            'mobile' => '+20 100 123 4567',
            'nationality' => 'Egyptian',
            'desired_program' => 'Engineering',
            'consent' => '1',
            'company_fax' => '',
        ])->assertNotFound();
        $this->get(route('student-referrals.create', str_repeat('x', 48)))->assertNotFound();
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
}
