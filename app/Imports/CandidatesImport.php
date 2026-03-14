<?php

namespace App\Imports;

use App\Models\Candidate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class CandidatesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    private int $rowCount = 0;

    public function model(array $row)
    {
        $this->rowCount++;

        $candidate = Candidate::updateOrCreate(
            ['candidate_id' => $row['id']],
            [
                'student_name' => $row['student_name'],
                'aptitude_score' => $row['aptitude_score'] ?? null,
                'test_score' => $row['test_score'] ?? null,
                'video_score' => $row['video_score'] ?? null,
            ]
        );

        return null;
    }

    public function rules(): array
    {
        return [
            'id' => ['required'],
            'student_name' => ['required'],
            'aptitude_score' => ['nullable', 'integer'],
            'test_score' => ['nullable', 'integer'],
            'video_score' => ['nullable', 'integer'],
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
