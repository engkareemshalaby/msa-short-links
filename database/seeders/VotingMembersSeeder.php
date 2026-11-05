<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voting\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Role;

class VotingMembersSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Voter', 'guard_name' => 'web']);
        $advisors = json_decode(
            file_get_contents(__DIR__.'/data/academic_advisors.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $usedEmails = [];
        $credentials = [];

        $removedMember = Member::where('staff_id', '11111')->where('club_name', 'Lebaladna')->first();
        if ($removedMember) {
            $hasVotes = DB::table('voting_ballots')->where('voter_id', $removedMember->id)->exists()
                || DB::table('voting_choices')->where('candidate_id', $removedMember->id)->exists();
            if ($hasVotes) {
                throw new RuntimeException('Cannot remove Engy Mansour: voting records are linked to this member.');
            }

            $removedMember->user->removeRole($role);
            $removedMember->delete();
        }

        // Each advisor has one voting record, including advisors assigned to several clubs.
        foreach (collect($advisors)->groupBy('id') as $staffId => $clubs) {
            $advisor = $clubs->first();
            $email = $advisor['email'];
            if (isset($usedEmails[$email]) && $usedEmails[$email] !== (string) $staffId) {
                $email = 'advisor-'.$staffId.'@example.test';
            }
            $usedEmails[$advisor['email']] = (string) $staffId;

            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $advisor['academic_advisor'], 'password' => (string) $staffId]
            );
            $user->assignRole($role);
            $credentials[] = [$advisor['academic_advisor'], (string) $staffId, $email];

            Member::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'staff_id' => (string) $staffId,
                    'club_name' => $clubs->pluck('club_name')->unique()->implode(', '),
                ]
            );
        }

        $this->command?->table(['Member', 'Staff ID', 'Email'], $credentials);
    }
}
