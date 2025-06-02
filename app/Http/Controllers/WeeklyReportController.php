<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklyReport;
use App\Models\ReportType;
use Illuminate\Support\Facades\Auth;

class WeeklyReportController extends Controller
{
    public function create()
    {
        // $reportType = ReportType::where('name', 'weekly')->firstOrFail();
        // return view('barangay.submissions', compact('reportType'));
        return view('barangay.submissions');
    }

    public function store(Request $request)
    {
        $reportType = ReportType::where('name', 'weekly')->firstOrFail(); // Get Report Type ID

        $request->validate([
            'month' => 'required|string',
            'week_number' => 'required|integer|min:1|max:5',
            'num_of_clean_up_sites' => 'required|integer|min:0',
            'num_of_participants' => 'required|integer|min:0',
            'num_of_barangays' => 'required|integer|min:0',
            'total_volume' => 'required|integer|min:0',
            'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        // Handle File Upload
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('reports', $fileName, 'public');

        // Store Weekly Report
        WeeklyReport::create([
            'user_id' => Auth::id(),
            'report_type_id' => $reportType->id, // Use correct ID
            'month' => $request->month,
            'week_number' => $request->week_number,
            'num_of_clean_up_sites' => $request->num_of_clean_up_sites,
            'num_of_participants' => $request->num_of_participants,
            'num_of_barangays' => $request->num_of_barangays,
            'total_volume' => $request->total_volume,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'status' => 'pending',
        ]);

        return redirect()->route('barangay.submissions')->with('success', 'Weekly report submitted successfully.');
    }
}
