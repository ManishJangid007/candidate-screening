<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CandidatesOverviewSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    private Collection $candidates;

    public function __construct(Collection $candidates)
    {
        $this->candidates = $candidates;
    }

    public function title(): string
    {
        return 'Candidates Overview';
    }

    public function headings(): array
    {
        return [
            'Candidate ID',
            'Student Name',
            'GitHub Profile',
            'Aptitude Score',
            'Test Score',
            'Video Score',
            'Total Score',
            'Current Round',
            'Status',
            'Assigned Interviewer',
            'R1 Result',
            'R1 Interviewer',
            'R2 Result',
            'R2 Interviewer',
            'R3 Result',
            'R3 Interviewer',
            'Last Updated',
        ];
    }

    public function array(): array
    {
        return $this->candidates->map(function ($c) {
            $rounds = $c->interviewRounds->keyBy('round_number');

            $aptitude = $c->aptitude_score;
            $test = $c->test_score;
            $video = $c->video_score;
            $total = ($aptitude !== null || $test !== null || $video !== null)
                ? ($aptitude ?? 0) + ($test ?? 0) + ($video ?? 0)
                : null;

            return [
                $c->candidate_id,
                $c->student_name,
                $c->github_profile ?? '-',
                $aptitude ?? '-',
                $test ?? '-',
                $video ?? '-',
                $total ?? '-',
                'Round ' . $c->current_round,
                $c->formatted_status,
                $c->interviewer ?? 'Not Assigned',
                isset($rounds[1]) ? $rounds[1]->formatted_result : '-',
                isset($rounds[1]) ? $rounds[1]->interviewer : '-',
                isset($rounds[2]) ? $rounds[2]->formatted_result : '-',
                isset($rounds[2]) ? $rounds[2]->interviewer : '-',
                isset($rounds[3]) ? $rounds[3]->formatted_result : '-',
                isset($rounds[3]) ? $rounds[3]->interviewer : '-',
                $c->updated_at->format('d M Y, h:i A'),
            ];
        })->toArray();
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 22,
            'C' => 30,
            'D' => 14,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 14,
            'I' => 14,
            'J' => 20,
            'K' => 14,
            'L' => 18,
            'M' => 14,
            'N' => 18,
            'O' => 14,
            'P' => 18,
            'Q' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->candidates->count() + 1;

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->candidates->count() + 1;
                $lastCol = 'Q';

                // Header row height
                $sheet->getRowDimension(1)->setRowHeight(30);

                // All data cells alignment and borders
                $dataRange = "A1:{$lastCol}{$lastRow}";
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Center align score and round columns (D-H now, shifted by GitHub col C)
                $sheet->getStyle("D2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Alternating row colors
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8FAFC'],
                            ],
                        ]);
                    }
                }

                // Color-code status column (I)
                $statusColors = [
                    'Pending' => 'FEF3C7',
                    'On Hold' => 'EDE9FE',
                    'Selected' => 'DCFCE7',
                    'Rejected' => 'FEE2E2',
                ];

                for ($row = 2; $row <= $lastRow; $row++) {
                    $status = $sheet->getCell("I{$row}")->getValue();
                    if (isset($statusColors[$status])) {
                        $sheet->getStyle("I{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $statusColors[$status]],
                            ],
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }

                // Color-code round results (K, M, O, Q)
                $resultColors = [
                    'Cleared' => 'DCFCE7',
                    'Not Cleared' => 'FEE2E2',
                    'On Hold' => 'EDE9FE',
                ];

                foreach (['K', 'M', 'O'] as $col) {
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $result = $sheet->getCell("{$col}{$row}")->getValue();
                        if (isset($resultColors[$result])) {
                            $sheet->getStyle("{$col}{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $resultColors[$result]],
                                ],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);
                        }
                    }
                }

                // Freeze header row
                $sheet->freezePane('A2');

                // Auto filter
                $sheet->setAutoFilter("A1:{$lastCol}1");
            },
        ];
    }
}
