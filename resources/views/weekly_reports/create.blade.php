@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-lg font-semibold mb-4">Submit Weekly Report</h2>

    <form action="{{ route('weekly_reports.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="block text-sm font-medium">Report Type</label>
            <select name="report_type_id" class="w-full border p-2 rounded-md" required>
                @foreach($reportTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium">Text Input</label>
            <textarea name="text_input" class="w-full border p-2 rounded-md" required></textarea>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium">Attach File</label>
            <input type="file" name="file" class="w-full border p-2 rounded-md" accept=".pdf,.docx,.xlsx" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium">Deadline</label>
            <input type="date" name="deadline" class="w-full border p-2 rounded-md" required>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Submit Report</button>
    </form>
</div>
@endsection
