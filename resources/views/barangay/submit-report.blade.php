@extends('layouts.barangay')

@section('title', 'Submit Report')
@section('page-title', 'Submit Report')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    Submit Report
                </h5>
            </div>

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Already Submitted Reports Section -->
                @if(count($submittedReportTypeIds) > 0)
                    <div class="mb-4">
                        <h6 class="mb-3">
                            <i class="fas fa-history me-2"></i>
                            Already Submitted Reports
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Report Type</th>
                                        <th>Frequency</th>
                                        <th>Status</th>
                                        <th>Submitted Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allReportTypes as $reportType)
                                        @if($submittedReportTypeIds->contains($reportType->id))
                                            @php
                                                $report = $submittedReportsByFrequency[$reportType->frequency]->firstWhere('report_type_id', $reportType->id);
                                            @endphp
                                            <tr>
                                                <td>{{ $reportType->name }}</td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ ucfirst($reportType->frequency) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $report->status === 'approved' ? 'success' : ($report->status === 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($report->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $report->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    @if($report->status === 'rejected')
                                                        <a href="{{ route('barangay.submissions.resubmit', $report->id) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-redo me-1"></i>
                                                            Resubmit
                                                        </a>
                                                    @else
                                                        <button class="btn btn-sm btn-secondary" disabled>
                                                            <i class="fas fa-check me-1"></i>
                                                            Submitted
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- New Report Submission Form -->
                @if(count($reportTypes) > 0)
                    <form method="POST" action="{{ route('barangay.submissions.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="frequency" class="form-label fw-bold">
                                        <i class="fas fa-tasks me-2"></i>
                                        Frequency
                                    </label>
                                    <select id="frequency" class="form-select" name="frequency">
                                        <option value="">Select Frequency</option>
                                        @foreach($frequencies as $frequency)
                                            <option value="{{ $frequency }}">{{ $frequency }}</option>
                                        @endforeach
                                    </select>
                                    @error('frequency')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_type" class="form-label fw-bold">
                                        <i class="fas fa-tasks me-2"></i>
                                        Report Type
                                    </label>
                                    <select id="report_type" class="form-select form-select-lg @error('report_type_id') is-invalid @enderror" name="report_type_id" required>
                                        <option value="">Select Report Type</option>
                                        @foreach($reportTypes as $reportType)
                                            <option value="{{ $reportType->id }}" data-frequency="{{ $reportType->frequency }}">
                                                {{ $reportType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('report_type_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file" class="form-label fw-bold">
                                        <i class="fas fa-file-upload me-2"></i>
                                        Upload File
                                    </label>
                                    <div class="drop-zone" id="dropZone">
                                        <div class="drop-zone__prompt">
                                            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                                            <p class="mb-2">Drag and drop your file here or click to browse</p>
                                            <small class="text-muted">Accepted formats: PDF, DOC, DOCX, XLSX (Max: 2MB)</small>
                                        </div>
                                        <input type="file" name="file" id="file" class="drop-zone__input" accept=".pdf,.doc,.docx,.xlsx" required>
                                    </div>
                                    @error('file')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Report Fields -->
                        <div id="weekly-fields" class="report-fields" style="display: none;">
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-4">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Report Period
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Month</label>
                                            <select class="form-select" name="month" required>
                                                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                    <option value="{{ $month }}">{{ $month }}</option>
                                                @endforeach
                                            </select>
                                            @error('month')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Week Number</label>
                                            <input type="number" class="form-control" name="week_number" min="1" max="52" required>
                                            @error('week_number')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-4">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Report Details
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Number of Clean-up Sites</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="num_of_clean_up_sites" min="0" required>
                                                <span class="input-group-text">sites</span>
                                            </div>
                                            @error('num_of_clean_up_sites')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Number of Participants</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="num_of_participants" min="0" required>
                                                <span class="input-group-text">people</span>
                                            </div>
                                            @error('num_of_participants')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Number of Barangays</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="num_of_barangays" min="0" required>
                                                <span class="input-group-text">barangays</span>
                                            </div>
                                            @error('num_of_barangays')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Total Volume</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="total_volume" min="0" step="0.01" required>
                                                <span class="input-group-text">m³</span>
                                            </div>
                                            @error('total_volume')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Report Fields -->
                        <div id="monthly-fields" class="report-fields" style="display: none;">
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-4">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Report Period
                                    </h6>
                                    <div class="mb-3">
                                        <label class="form-label">Month</label>
                                        <select class="form-select" name="month" required>
                                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                <option value="{{ $month }}">{{ $month }}</option>
                                            @endforeach
                                        </select>
                                        @error('month')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quarterly Report Fields -->
                        <div id="quarterly-fields" class="report-fields" style="display: none;">
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-4">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Report Period
                                    </h6>
                                    <div class="mb-3">
                                        <label class="form-label">Quarter</label>
                                        <select class="form-select" name="quarter_number" required>
                                            <option value="">Select Quarter</option>
                                            <option value="1">First Quarter (January - March)</option>
                                            <option value="2">Second Quarter (April - June)</option>
                                            <option value="3">Third Quarter (July - September)</option>
                                            <option value="4">Fourth Quarter (October - December)</option>
                                        </select>
                                        @error('quarter_number')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Semestral Report Fields -->
                        <div id="semestral-fields" class="report-fields" style="display: none;">
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-4">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Report Period
                                    </h6>
                                    <div class="mb-3">
                                        <label class="form-label">Semester</label>
                                        <select class="form-select" name="sem_number" required>
                                            <option value="">Select Semester</option>
                                            <option value="1">First Semester (January - June)</option>
                                            <option value="2">Second Semester (July - December)</option>
                                        </select>
                                        @error('sem_number')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('barangay.dashboard') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Submit Report
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="mb-3">All Reports Submitted</h5>
                        <p class="text-muted mb-4">You have submitted all available reports. Check the table above for any reports that need resubmission.</p>
                        <a href="{{ route('barangay.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Drop Zone */
    .drop-zone {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .drop-zone:hover {
        border-color: var(--accent-color);
        background-color: rgba(var(--accent-color-rgb), 0.05);
    }

    .drop-zone.dragover {
        border-color: var(--accent-color);
        background-color: rgba(var(--accent-color-rgb), 0.1);
    }

    .drop-zone__thumb {
        padding: 1rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Form Controls */
    .form-control:focus, .form-select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 0.2rem rgba(var(--accent-color-rgb), 0.25);
    }

    .input-group:focus-within .input-group-text {
        border-color: var(--accent-color);
        background-color: var(--accent-color);
        color: white;
    }

    /* Submit Button Animation */
    @keyframes submitPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .btn-primary:active {
        animation: submitPulse 0.3s ease;
    }

    /* Loading Spinner */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading .fa-spinner {
        animation: spin 1s linear infinite;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportTypeSelect = document.getElementById('report_type');
    const reportFields = document.querySelectorAll('.report-fields');
    const dropZone = document.getElementById('dropZone');
    const inputElement = document.getElementById('file');
    const submitBtn = document.getElementById('submitBtn');

    // Function to hide all report fields
    function hideAllFields() {
        reportFields.forEach(field => {
            field.style.display = 'none';
        });
    }

    // Handle report type change
    reportTypeSelect.addEventListener('change', function() {
        hideAllFields();
        const frequency = this.options[this.selectedIndex].dataset.frequency;
        if (frequency) {
            document.getElementById(`${frequency}-fields`).style.display = 'block';
        }
    });

    // Handle file drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drop-zone--over');
    });

    ['dragleave', 'dragend'].forEach(type => {
        dropZone.addEventListener(type, function() {
            this.classList.remove('drop-zone--over');
        });
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drop-zone--over');

        if (e.dataTransfer.files.length) {
            inputElement.files = e.dataTransfer.files;
            updateThumbnail(this, e.dataTransfer.files[0]);
        }
    });

    dropZone.addEventListener('click', function() {
        inputElement.click();
    });

    inputElement.addEventListener('change', function() {
        if (this.files.length) {
            updateThumbnail(dropZone, this.files[0]);
        }
    });

    function updateThumbnail(dropZoneElement, file) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function() {
                dropZoneElement.style.backgroundImage = `url('${reader.result}')`;
            };
        } else {
            dropZoneElement.style.backgroundImage = '';
        }
    }

    // Form submission handling
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Validate required fields
        const reportType = reportTypeSelect.value;
        const file = inputElement.files[0];

        if (!reportType) {
            e.preventDefault();
            alert('Please select a report type');
            return;
        }

        if (!file) {
            e.preventDefault();
            alert('Please upload a file');
            return;
        }

        // Check file size (2MB limit)
        if (file.size > 2 * 1024 * 1024) {
            e.preventDefault();
            alert('File size must be less than 2MB');
            return;
        }

        // Check file type
        const allowedTypes = ['.pdf', '.doc', '.docx', '.xlsx'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExtension)) {
            e.preventDefault();
            alert('Invalid file type. Please upload a PDF, DOC, DOCX, or XLSX file');
            return;
        }

        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';

        // Disable the frequency and report type fields
        const freq = form.querySelector('[name="frequency"], #frequency');
        const reportType = form.querySelector('[name="report_type_id"], #report_type');
        if (freq) freq.disabled = true;
        if (reportType) reportType.disabled = true;

        // Let the form submit naturally
        return true;
    });
});
</script>
@endpush
@endsection
