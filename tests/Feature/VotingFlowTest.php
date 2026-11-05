<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voting\Member;
use App\Models\Voting\Setting;
use Database\Seeders\VotingMembersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VotingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_36_unique_members(): void
    {
        $this->seed(VotingMembersSeeder::class);
        $this->assertDatabaseCount('voting_members', 36);
        $this->assertSame(36, Member::distinct()->count('staff_id'));
        $this->assertSame('12345', Member::orderBy('id')->first()->staff_id);
        $this->assertSame('Dr. Ahmed Mahmoud', Member::orderBy('id')->first()->user->name);
        $this->assertTrue(Member::with('user')->get()->every(fn ($member) => str_starts_with($member->user->name, 'Dr. ')));
        $this->assertSame(36, User::whereIn('id', Member::pluck('user_id'))->distinct()->count('email'));
        $this->assertTrue(Member::with('user')->get()->every(fn ($member) => Hash::check($member->staff_id, $member->user->password)));
    }

    public function test_admin_voting_page_follows_dashboard_language(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)->withSession(['locale' => 'en'])
            ->get(route('voting.admin.index'))
            ->assertOk()->assertSee('Voting management')->assertSee('Results & settings')
            ->assertSee('role="tablist"', false)->assertSee('id="vote-panel-choices"', false);
        $this->withSession(['locale' => 'ar'])
            ->get(route('voting.admin.index'))
            ->assertOk()->assertSee('إدارة التصويت')->assertSee('النتائج والإعدادات');
    }

    public function test_voting_login_is_english(): void
    {
        $this->withSession(['locale' => 'ar'])->get(route('voting.login'))
            ->assertOk()->assertSee('Sign in to vote')->assertSee('Email address')->assertSee('Staff ID');
    }

    public function test_member_can_sign_in_with_email_and_staff_id_and_cast_exactly_one_ballot(): void
    {
        $role = Role::create(['name' => 'Voter', 'guard_name' => 'web']);
        $members = collect(range(1, 9))->map(function ($number) use ($role) {
            $user = User::factory()->create(['email' => "vote{$number}@example.com", 'password' => (string) (12344 + $number)]);
            $user->assignRole($role);
            return Member::create(['user_id' => $user->id, 'staff_id' => (string) (12344 + $number)]);
        });
        Setting::create(['id' => 1, 'is_open' => true]);

        $this->post(route('voting.login.store'), ['email' => 'vote1@example.com', 'staff_id' => '12345'])
            ->assertRedirect(route('voting.index'));
        $this->post(route('voting.store'), ['choices' => $members->skip(1)->pluck('id')->all()])
            ->assertRedirect(route('voting.index'));
        $this->assertDatabaseCount('voting_ballots', 1);
        $this->assertDatabaseCount('voting_choices', 8);
        $this->post(route('voting.store'), ['choices' => $members->skip(1)->pluck('id')->all()])
            ->assertSessionHasErrors('choices');
        $this->assertDatabaseCount('voting_ballots', 1);
    }

    public function test_voting_login_requires_matching_email_and_staff_id(): void
    {
        $this->seed(VotingMembersSeeder::class);
        $members = Member::with('user')->orderBy('id')->take(2)->get();

        $this->post(route('voting.login.store'), [
            'email' => $members[0]->user->email,
            'staff_id' => $members[1]->staff_id,
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('voting.login.store'), [
            'email' => $members[0]->user->email,
            'staff_id' => $members[0]->staff_id,
        ])->assertRedirect(route('voting.index'));
    }

    public function test_member_cannot_select_self_or_view_hidden_results(): void
    {
        $role = Role::create(['name' => 'Voter', 'guard_name' => 'web']);
        $members = collect(range(1, 9))->map(function ($number) use ($role) {
            $user = User::factory()->create();
            $user->assignRole($role);
            return Member::create(['user_id' => $user->id, 'staff_id' => (string) (12344 + $number)]);
        });
        Setting::create(['id' => 1, 'is_open' => true, 'show_results_to_members' => false]);
        $this->actingAs($members[0]->user)
            ->post(route('voting.store'), ['choices' => $members->take(8)->pluck('id')->all()])
            ->assertSessionHasErrors('choices');
        $this->get(route('voting.admin.index'))->assertForbidden();
        $this->get(route('voting.index'))->assertOk()->assertSee('Choose your')->assertDontSee('STAFF ID')->assertDontSee('<h2>Results</h2>', false);
        $this->assertDatabaseCount('voting_ballots', 0);
    }
}
