<?php

namespace App\Http\Controllers;

use App\Models\BarangayFile;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\{WeeklyReport, MonthlyReport, QuarterlyReport, SemestralReport, AnnualReport};

class BarangayFileController extends Controller {
    public function store(Request $request) {
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'file' => 'required|mimes:pdf,doc,docx,xlsx|max:2048',
        ]);

        $filePath = $request->file('file')->store('barangay_files', 'public');
        BarangayFile::create([
            'barangay_id' => auth()->id(),
            'report_id' => $request->report_id,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $filePath,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'File submitted successfully.');
    }

    public function download($id)
    {
        // Try to find the report in each table
        $report = WeeklyReport::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            $report = MonthlyReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = QuarterlyReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = SemestralReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = AnnualReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            return back()->with('error', 'Report not found.');
        }

        if (!Storage::disk('public')->exists($report->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download(
            $report->file_path,
            $report->file_name
        );
    }

    public function destroy($id)
    {
        // Try to find the report in each table
        $report = WeeklyReport::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            $report = MonthlyReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = QuarterlyReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = SemestralReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = AnnualReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            return back()->with('error', 'Report not found.');
        }

        if (Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }

        $report->update([
            'file_path' => null,
            'file_name' => null
        ]);

        return back()->with('success', 'File deleted successfully.');
    }
}
