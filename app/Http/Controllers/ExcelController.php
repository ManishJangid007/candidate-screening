<?php

namespace App\Http\Controllers;

use App\Exports\SampleCandidatesExport;
use App\Imports\CandidatesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function sample()
    {
        return Excel::download(new SampleCandidatesExport(), 'sample_candidates.xlsx');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new CandidatesImport();
        Excel::import($import, $request->file('file'));

        $count = $import->getRowCount();

        return redirect()->route('candidates.index')
            ->with('success', $count . ' candidates imported successfully.');
    }
}
