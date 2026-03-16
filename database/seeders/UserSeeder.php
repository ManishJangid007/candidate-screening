<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@infotech.works'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin@01'),
                'role' => 'admin',
            ]
        );

        $interviewers = config('interviewers');
        foreach ($interviewers as $name) {
            $firstName = explode(' ', $name)[0];
            $email = strtolower($firstName) . '@infotech.works';
            $password = strtolower($firstName) . '@01';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'role' => 'interviewer',
                ]
            );
        }
    }
}
