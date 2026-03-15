<?php

namespace App\Exports;

use App\Models\Candidate;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CandidatesExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $candidates = Candidate::with('interviewRounds')->orderBy('candidate_id')->get();

        return [
            new CandidatesOverviewSheet($candidates),
            new InterviewHistorySheet($candidates),
            new SummarySheet($candidates),
        ];
    }
}
