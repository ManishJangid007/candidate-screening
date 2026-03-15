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

class InterviewHistorySheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    private Collection $candidates;
    private int $totalRows = 0;

    public function __construct(Collection $candidates)
    {
        $this->candidates = $candidates;
    }

    public function title(): string
    {
        return 'Interview History';
    }

    public function headings(): array
    {
        return [
            'Candidate ID',
            'Student Name',
            'Round',
            'Interviewer',
            'Result',
            'Remarks',
            'Evaluated On',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->candidates as $candidate) {
            if ($candidate->interviewRounds->isEmpty()) {
                $rows[] = [
                    $candidate->candidate_id,
                    $candidate->student_name,
                    '-',
                    '-',
                    'No evaluation yet',
                    '-',
                    '-',
                ];
                continue;
            }

            foreach ($candidate->interviewRounds as $round) {
                $rows[] = [
                    $candidate->candidate_id,
                    $candidate->student_name,
                    'Round ' . $round->round_number,
                    $round->interviewer,
                    $round->formatted_result,
                    $round->remarks ?? '-',
                    $round->created_at->format('d M Y, h:i A'),
                ];
            }
        }

        $this->totalRows = count($rows);
        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 22,
            'C' => 12,
            'D' => 20,
            'E' => 16,
            'F' => 45,
            'G' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
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
                $lastRow = $this->totalRows + 1;
                $lastCol = 'G';

                $sheet->getRowDimension(1)->setRowHeight(30);

                // Borders
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // Center align ID, Round, Result columns
                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Alternating rows
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

                // Color-code results
                $resultColors = [
                    'Cleared' => 'DCFCE7',
                    'Not Cleared' => 'FEE2E2',
                    'On Hold' => 'EDE9FE',
                ];

                for ($row = 2; $row <= $lastRow; $row++) {
                    $result = $sheet->getCell("E{$row}")->getValue();
                    if (isset($resultColors[$result])) {
                        $sheet->getStyle("E{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $resultColors[$result]],
                            ],
                            'font' => ['bold' => true],
                        ]);
                    }
                }

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastCol}1");
            },
        ];
    }
}
