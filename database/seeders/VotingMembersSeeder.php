<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voting\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class VotingMembersSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Voter', 'guard_name' => 'web']);

        // Temporary demo data. Replace before the real vote and rotate all passwords.
        $names = [
            'Ahmed Mahmoud', 'Mohamed Samy', 'Ali Hassan', 'Omar Ibrahim', 'Youssef Khaled', 'Karim Mostafa',
            'Mahmoud Ashraf', 'Khaled Yasser', 'Mostafa Adel', 'Ibrahim Tarek', 'Hassan Mohamed', 'Samy Ahmed',
            'Tarek Nabil', 'Yasser Fouad', 'Ashraf Walid', 'Adel Sherif', 'Nabil Hossam', 'Fouad Ramy',
            'Mariam Ahmed', 'Sara Mohamed', 'Nour Mahmoud', 'Yasmine Ali', 'Fatma Hassan', 'Malak Ibrahim',
            'Hana Khaled', 'Aya Mostafa', 'Dina Ashraf', 'Basma Yasser', 'Shahd Adel', 'Nada Tarek',
            'Rahma Samy', 'Iman Karim', 'Laila Nabil', 'Menna Fouad', 'Reem Walid', 'Salma Sherif',
        ];

        $members = array_map(static fn (string $name, int $index): array => [
            'name' => 'Dr. '.$name,
            'email' => sprintf('voter%02d@example.test', $index + 1),
            'staff_id' => sprintf('%05d', 12345 + $index),
            'password' => sprintf('DemoVote@%03d!', $index + 1),
        ], $names, array_keys($names));

        foreach ($members as $member) {
            $user = User::updateOrCreate(
                ['email' => $member['email']],
                ['name' => $member['name'], 'password' => Hash::make($member['password'])]
            );
            $user->assignRole($role);
            Member::updateOrCreate(['user_id' => $user->id], ['staff_id' => $member['staff_id']]);
        }
    }
}
