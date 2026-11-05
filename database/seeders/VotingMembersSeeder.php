<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voting\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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

        // Each advisor has one voting record, including advisors assigned to several clubs.
        foreach (collect($advisors)->groupBy('id') as $staffId => $clubs) {
            $advisor = $clubs->first();
            $email = $advisor['email'];
            if (isset($usedEmails[$email]) && $usedEmails[$email] !== (string) $staffId) {
                $email = 'advisor-'.$staffId.'@example.test';
            }
            $usedEmails[$advisor['email']] = (string) $staffId;

            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $advisor['academic_advisor'], 'password' => Str::random(40)]
            );
            $user->update(['name' => $advisor['academic_advisor']]);
            $user->assignRole($role);

            Member::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'staff_id' => (string) $staffId,
                    'club_name' => $clubs->pluck('club_name')->unique()->implode(', '),
                ]
            );
        }
    }
}
