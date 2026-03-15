<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SampleCandidatesExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['ID', 'Student Name', 'Aptitude Score', 'Test Score', 'Video Score'];
    }

    public function array(): array
    {
        return [
            ['101', 'John Doe', 85, 78, 90],
            ['102', 'Jane Smith', 92, 88, 76],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
