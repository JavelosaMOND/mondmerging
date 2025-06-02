@extends('layouts.barangay')

@section('title', 'Test Resubmit Form')
@section('page-title', 'Test Resubmit Form')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Test Resubmit Form</h5>
                </div>
                <div class="card-body">
                    <form action="{{ url('/barangay/submissions/quarterly_2/resubmit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="report_type_id" value="14">
                        <input type="hidden" name="report_type" value="quarterly">
                        
                        <div class="mb-3">
                            <label for="quarter_number" class="form-label">Quarter Number</label>
                            <select class="form-select" name="quarter_number" id="quarter_number" required>
                                <option value="1">Q1 (Jan-Mar)</option>
                                <option value="2" selected>Q2 (Apr-Jun)</option>
                                <option value="3">Q3 (Jul-Sep)</option>
                                <option value="4">Q4 (Oct-Dec)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">Upload File (Optional)</label>
                            <input type="file" class="form-control" id="file" name="file">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Submit Test Form</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
