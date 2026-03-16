<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@infotech.works', 'password' => 'Adm!n@2025#Sec', 'role' => 'admin'],
            ['name' => 'Tushar Jadhav', 'email' => 'tushar@infotech.works', 'password' => 'Tush@r#7491Jd', 'role' => 'interviewer'],
            ['name' => 'Sushant Patil', 'email' => 'sushant@infotech.works', 'password' => 'Sush@nt#3827Pt', 'role' => 'interviewer'],
            ['name' => 'Chinmayee Jakate', 'email' => 'chinmayee@infotech.works', 'password' => 'Ch!nm@y#6142Jk', 'role' => 'interviewer'],
            ['name' => 'Pooja Kolte', 'email' => 'pooja@infotech.works', 'password' => 'P00j@#9358Klt', 'role' => 'interviewer'],
            ['name' => 'Pratibha Singh', 'email' => 'pratibha@infotech.works', 'password' => 'Pr@t!bh#2764Sg', 'role' => 'interviewer'],
            ['name' => 'Manisha Kale', 'email' => 'manisha@infotech.works', 'password' => 'M@n!sh#5019Kle', 'role' => 'interviewer'],
            ['name' => 'Ankit Chaudhury', 'email' => 'ankit@infotech.works', 'password' => 'Ank!t#8473Chd', 'role' => 'interviewer'],
            ['name' => 'Manish Jangid', 'email' => 'manish@infotech.works', 'password' => 'M@n!sh#6295Jgd', 'role' => 'interviewer'],
            ['name' => 'Yash Malbhage', 'email' => 'yash@infotech.works', 'password' => 'Y@sh#4186Mlb', 'role' => 'interviewer'],
            ['name' => 'Nilesh Poman', 'email' => 'nilesh@infotech.works', 'password' => 'N!l3sh#7524Pmn', 'role' => 'interviewer'],
            ['name' => 'Bhumkesh Kale', 'email' => 'bhumkesh@infotech.works', 'password' => 'Bhum#3891Kle!', 'role' => 'interviewer'],
            ['name' => 'Madhura Pattekar', 'email' => 'madhura@infotech.works', 'password' => 'M@dh#5647Ptk!', 'role' => 'interviewer'],
            ['name' => 'Kamlesh Haswani', 'email' => 'kamlesh@infotech.works', 'password' => 'K@ml#2953Hsw!', 'role' => 'interviewer'],
            ['name' => 'Anuj Kumar', 'email' => 'anuj@infotech.works', 'password' => 'An!uj#8316Kmr', 'role' => 'interviewer'],
            ['name' => 'Devansh Sharma', 'email' => 'devansh@infotech.works', 'password' => 'D3v@n#4072Shm!', 'role' => 'interviewer'],
            ['name' => 'Ashish Kumar', 'email' => 'ashish@infotech.works', 'password' => 'Ash!sh#6738Kmr', 'role' => 'interviewer'],
            ['name' => 'Pankaj Pagar', 'email' => 'pankaj@infotech.works', 'password' => 'P@nk@j#1594Pgr', 'role' => 'interviewer'],
            ['name' => 'Vinay Poul', 'email' => 'vinay@infotech.works', 'password' => 'V!n@y#9261Pul', 'role' => 'interviewer'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
