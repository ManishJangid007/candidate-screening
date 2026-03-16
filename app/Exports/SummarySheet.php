<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    private Collection $candidates;

    public function __construct(Collection $candidates)
    {
        $this->candidates = $candidates;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $total = $this->candidates->count();
        $pending = $this->candidates->where('status', 'pending')->count();
        $onHold = $this->candidates->where('status', 'on_hold')->count();
        $selected = $this->candidates->where('status', 'selected')->count();
        $rejected = $this->candidates->where('status', 'rejected')->count();

        $round1 = $this->candidates->where('current_round', 1)->count();
        $round2 = $this->candidates->where('current_round', 2)->count();
        $round3 = $this->candidates->where('current_round', 3)->count();

        $avgAptitude = round($this->candidates->whereNotNull('aptitude_score')->avg('aptitude_score'), 1);
        $avgTest = round($this->candidates->whereNotNull('test_score')->avg('test_score'), 1);
        $avgVideo = round($this->candidates->whereNotNull('video_score')->avg('video_score'), 1);

        // Interviewer workload
        $interviewerStats = $this->candidates
            ->whereNotNull('interviewer')
            ->groupBy('interviewer')
            ->map(function ($group, $name) {
                return [
                    'name' => $name,
                    'assigned' => $group->count(),
                    'pending' => $group->where('status', 'pending')->count(),
                    'selected' => $group->where('status', 'selected')->count(),
                    'rejected' => $group->where('status', 'rejected')->count(),
                ];
            })
            ->sortByDesc('assigned')
            ->values();

        $rows = [];

        // Title
        $rows[] = ['CANDIDATE SCREENING REPORT'];
        $rows[] = ['Generated on: ' . now()->format('d M Y, h:i A')];
        $rows[] = [''];

        // Status summary
        $rows[] = ['STATUS OVERVIEW', ''];
        $rows[] = ['Total Candidates', $total];
        $rows[] = ['Pending', $pending];
        $rows[] = ['On Hold', $onHold];
        $rows[] = ['Selected', $selected];
        $rows[] = ['Rejected', $rejected];
        $rows[] = [''];

        // Round distribution
        $rows[] = ['ROUND DISTRIBUTION', ''];
        $rows[] = ['Round 1', $round1];
        $rows[] = ['Round 2', $round2];
        $rows[] = ['Round 3', $round3];
        $rows[] = [''];

        // Score averages
        $rows[] = ['AVERAGE SCORES', ''];
        $rows[] = ['Aptitude', $avgAptitude ?: 'N/A'];
        $rows[] = ['Test', $avgTest ?: 'N/A'];
        $rows[] = ['Video', $avgVideo ?: 'N/A'];
        $rows[] = [''];

        // Interviewer workload
        $rows[] = ['INTERVIEWER WORKLOAD', 'Assigned', 'Pending', 'Selected', 'Rejected'];

        foreach ($interviewerStats as $stat) {
            $rows[] = [$stat['name'], $stat['assigned'], $stat['pending'], $stat['selected'], $stat['rejected']];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 14,
            'C' => 14,
            'D' => 14,
            'E' => 14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Title styling
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E293B']],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '64748B']],
                ]);

                // Section headers
                $sectionRows = [4, 11, 16, 21];
                foreach ($sectionRows as $row) {
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1E293B'],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(28);
                }

                // Data rows styling
                $dataRanges = [[5, 9], [12, 14], [17, 19]];
                foreach ($dataRanges as [$start, $end]) {
                    $sheet->getStyle("A{$start}:B{$end}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D1D5DB'],
                            ],
                        ],
                    ]);
                    $sheet->getStyle("A{$start}:A{$end}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F1F5F9'],
                        ],
                    ]);
                    $sheet->getStyle("B{$start}:B{$end}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Interviewer table header
                $intHeaderRow = 21;
                $lastIntRow = $intHeaderRow + $sheet->getHighestRow() - $intHeaderRow;

                // Style interviewer data rows if they exist
                if ($lastIntRow > $intHeaderRow) {
                    $intDataStart = $intHeaderRow + 1;
                    $intDataEnd = $sheet->getHighestRow();
                    $sheet->getStyle("A{$intDataStart}:E{$intDataEnd}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D1D5DB'],
                            ],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("B{$intDataStart}:E{$intDataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    for ($row = $intDataStart; $row <= $intDataEnd; $row++) {
                        if (($row - $intDataStart) % 2 === 1) {
                            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8FAFC'],
                                ],
                            ]);
                        }
                    }
                }

                // Color highlights for status counts
                $statusColors = [
                    6 => 'FEF3C7', // Pending - yellow
                    7 => 'EDE9FE', // On Hold - purple
                    8 => 'DCFCE7', // Selected - green
                    9 => 'FEE2E2', // Rejected - red
                ];
                foreach ($statusColors as $row => $color) {
                    $sheet->getStyle("B{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $color],
                        ],
                        'font' => ['bold' => true],
                    ]);
                }
            },
        ];
    }
}
