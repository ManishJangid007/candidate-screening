<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = [
            ['candidate_id' => '101', 'student_name' => 'Ayaan Shaikh', 'aptitude_score' => 78, 'test_score' => 81, 'video_score' => 72],
            ['candidate_id' => '102', 'student_name' => 'Meera Joshi', 'aptitude_score' => 88, 'test_score' => 91, 'video_score' => 85],
            ['candidate_id' => '103', 'student_name' => 'Rohan Patil', 'aptitude_score' => 69, 'test_score' => 74, 'video_score' => 80],
            ['candidate_id' => '104', 'student_name' => 'Sana Khan', 'aptitude_score' => 92, 'test_score' => 89, 'video_score' => 90],
            ['candidate_id' => '105', 'student_name' => 'Arjun Deshmukh', 'aptitude_score' => 85, 'test_score' => 78, 'video_score' => 82],
            ['candidate_id' => '106', 'student_name' => 'Priya Menon', 'aptitude_score' => 74, 'test_score' => 86, 'video_score' => 79],
            ['candidate_id' => '107', 'student_name' => 'Vikrant Rao', 'aptitude_score' => 91, 'test_score' => 94, 'video_score' => 88],
            ['candidate_id' => '108', 'student_name' => 'Neha Kulkarni', 'aptitude_score' => 67, 'test_score' => 71, 'video_score' => 65],
            ['candidate_id' => '109', 'student_name' => 'Aman Verma', 'aptitude_score' => 83, 'test_score' => 80, 'video_score' => 77],
            ['candidate_id' => '110', 'student_name' => 'Divya Nair', 'aptitude_score' => 95, 'test_score' => 92, 'video_score' => 91],
            ['candidate_id' => '111', 'student_name' => 'Kunal Bhatt', 'aptitude_score' => 72, 'test_score' => 68, 'video_score' => 70],
            ['candidate_id' => '112', 'student_name' => 'Snehal Pawar', 'aptitude_score' => 80, 'test_score' => 83, 'video_score' => 76],
            ['candidate_id' => '113', 'student_name' => 'Rahul Iyer', 'aptitude_score' => 87, 'test_score' => 90, 'video_score' => 84],
            ['candidate_id' => '114', 'student_name' => 'Tanvi Sharma', 'aptitude_score' => 76, 'test_score' => 79, 'video_score' => 73],
            ['candidate_id' => '115', 'student_name' => 'Ishaan Gupta', 'aptitude_score' => 90, 'test_score' => 87, 'video_score' => 86],
            ['candidate_id' => '116', 'student_name' => 'Anjali Reddy', 'aptitude_score' => 63, 'test_score' => 66, 'video_score' => 60],
            ['candidate_id' => '117', 'student_name' => 'Farhan Qureshi', 'aptitude_score' => 81, 'test_score' => 84, 'video_score' => 78],
            ['candidate_id' => '118', 'student_name' => 'Kavya Pillai', 'aptitude_score' => 89, 'test_score' => 93, 'video_score' => 87],
            ['candidate_id' => '119', 'student_name' => 'Sahil Thakur', 'aptitude_score' => 70, 'test_score' => 75, 'video_score' => 69],
            ['candidate_id' => '120', 'student_name' => 'Riya Choudhary', 'aptitude_score' => 86, 'test_score' => 88, 'video_score' => 83],
        ];

        foreach ($candidates as $data) {
            Candidate::updateOrCreate(
                ['candidate_id' => $data['candidate_id']],
                $data
            );
        }
    }
}
