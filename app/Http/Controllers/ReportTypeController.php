<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportType;
use Illuminate\Support\Facades\Log;

class ReportTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportType::query();

        // Apply search filter if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Apply frequency filter if provided
        if ($request->has('frequency') && $request->frequency !== '') {
            $query->where('frequency', $request->frequency);
        }

        // Get paginated results
        $reportTypes = $query->orderBy('created_at', 'desc')
                           ->paginate(10)
                           ->withQueryString();

        return view('admin.create-report', compact('reportTypes'));
    }

    public function edit($id)
    {
        try {
            $reportType = ReportType::findOrFail($id);
            return response()->json([
                'success' => true,
                'report_type' => $reportType
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching report type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching report type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'frequency' => 'required|string|in:' . implode(',', array_keys(ReportType::frequencies())),
                'deadline' => 'required|date',
                'allowed_file_types' => 'nullable|array',
                'allowed_file_types.*' => 'string|in:' . implode(',', array_keys(ReportType::availableFileTypes()))
            ]);

            // Convert empty array to null for allowed_file_types
            if (empty($validated['allowed_file_types'])) {
                $validated['allowed_file_types'] = null;
            }

            $reportType = ReportType::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report type created successfully',
                    'data' => $reportType
                ]);
            }

            return redirect()->route('admin.create-report')
                ->with('success', 'Report type created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating report type: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating report type: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.create-report')
                ->with('error', 'Error creating report type. Please try again.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Updating report type', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            $reportType = ReportType::findOrFail($id);
            Log::info('Found report type', ['report_type' => $reportType->toArray()]);

            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'frequency' => 'required|string|in:' . implode(',', array_keys(ReportType::frequencies())),
                'deadline' => 'required|date',
                'allowed_file_types' => 'nullable|array',
                'allowed_file_types.*' => 'string|in:' . implode(',', array_keys(ReportType::availableFileTypes()))
            ]);

            Log::info('Validated data', ['validated' => $validated]);

            // Convert empty array to null for allowed_file_types
            if (empty($validated['allowed_file_types'])) {
                $validated['allowed_file_types'] = null;
            }

            // Update the report type
            $reportType->update($validated);

            Log::info('Update completed', ['updated_data' => $reportType->fresh()->toArray()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report type updated successfully',
                    'data' => $reportType->fresh()
                ]);
            }

            return redirect()->route('admin.create-report')
                ->with('success', 'Report type updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating report type', [
                'id' => $id,
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error: ' . collect($e->errors())->first()[0],
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating report type', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating report type: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.create-report')
                ->with('error', 'Error updating report type. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Deleting report type', ['id' => $id]);

            $reportType = ReportType::findOrFail($id);
            $reportType->delete();

            Log::info('Report type deleted successfully', ['id' => $id]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report type deleted successfully'
                ]);
            }

            return redirect()->route('admin.create-report')
                ->with('success', 'Report type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting report type', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting report type: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.create-report')
                ->with('error', 'Error deleting report type. Please try again.');
        }
    }
}

