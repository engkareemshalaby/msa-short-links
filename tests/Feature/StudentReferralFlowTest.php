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
