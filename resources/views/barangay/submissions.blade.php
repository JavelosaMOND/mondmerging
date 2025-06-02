@extends('layouts.barangay')

@section('title', 'My Submissions')
@section('page-title', 'My Submissions')

@section('content')
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-lg me-2"></i>
            <strong>{{ session('success') }}</strong>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-file-alt text-primary me-2"></i>Submitted Reports
                        </h5>
                        @if(!$reports->isEmpty() && (!empty($search) || !empty($frequency) || ($sortBy ?? 'newest') != 'newest'))
                            <a href="{{ route('barangay.submissions') }}" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times me-1"></i> Clear Filters
                            </a>
                        @endif
                    </div>

                    @if(!$reports->isEmpty())
                    <form id="filterForm" class="d-flex flex-wrap gap-2" method="GET" action="{{ route('barangay.submissions') }}">
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control search-box" name="search" id="searchInput" value="{{ $search ?? '' }}" placeholder="Search...">
                        </div>

                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fas fa-filter"></i>
                            </span>
                            <select class="form-select filter-select" name="frequency" id="frequencyFilter">
                                <option value="">All Frequencies</option>
                                <option value="weekly" {{ ($frequency ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ ($frequency ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ ($frequency ?? '') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="semestral" {{ ($frequency ?? '') == 'semestral' ? 'selected' : '' }}>Semestral</option>
                                <option value="annual" {{ ($frequency ?? '') == 'annual' ? 'selected' : '' }}>Annual</option>
                            </select>
                        </div>

                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fas fa-sort"></i>
                            </span>
                            <select class="form-select filter-select" name="sort_by" id="sortBy">
                                <option value="newest" {{ ($sortBy ?? 'newest') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ ($sortBy ?? '') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="type" {{ ($sortBy ?? '') == 'type' ? 'selected' : '' }}>Report Type</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary d-none" id="submitFilterBtn">
                            <i class="fas fa-search me-1"></i> Apply Filters
                        </button>

                        @if(!empty($search) || !empty($frequency) || ($sortBy ?? 'newest') != 'newest')
                            <a href="{{ route('barangay.submissions') }}" class="btn btn-light">
                                <i class="fas fa-times"></i>
                                Clear Filters
                            </a>
                        @endif
                    </form>
                    @endif
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

                    @if ($reports->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            @if(!empty($search) || !empty($frequency) || ($sortBy ?? 'newest') != 'newest')
                                <h5 class="text-muted">No reports match the selected filters</h5>
                            @else
                                <h5 class="text-muted">No reports have been assigned yet</h5>
                                <p class="text-muted small">Reports will be assigned by the admin for upcoming submission deadlines</p>
                            @endif
                            <a href="{{ route('barangay.submissions') }}" class="btn btn-outline-primary mt-3">
                                <i class="fas fa-times me-2"></i>Clear Filters
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Report</th>
                                        <th>Frequency</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reports as $report)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2" style="width: 32px; height: 32px; border-radius: 8px; background: var(--primary-light); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                                        @php
                                                            $extension = strtolower(pathinfo($report->file_path, PATHINFO_EXTENSION));
                                                            $icon = match($extension) {
                                                                'pdf' => 'fa-file-pdf',
                                                                'doc', 'docx' => 'fa-file-word',
                                                                'xls', 'xlsx' => 'fa-file-excel',
                                                                'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image',
                                                                'txt' => 'fa-file-alt',
                                                                default => 'fa-file'
                                                            };
                                                        @endphp
                                                        <i class="fas {{ $icon }} fa-sm"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 500; color: var(--dark);">{{ $report->reportType->name }}</div>
                                                        <small class="text-muted">{{ ucfirst(str_replace('Report', '', class_basename($report->model_type))) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $frequencyClass = 'info'; // Default for weekly

                                                    if ($report->reportType->frequency === 'monthly') {
                                                        $frequencyClass = 'primary';
                                                    } elseif ($report->reportType->frequency === 'quarterly') {
                                                        $frequencyClass = 'success';
                                                    } elseif ($report->reportType->frequency === 'semestral') {
                                                        $frequencyClass = 'warning';
                                                    } elseif ($report->reportType->frequency === 'annual') {
                                                        $frequencyClass = 'danger';
                                                    }
                                                @endphp
                                                <span class="status-badge frequency-{{ $report->reportType->frequency }}">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    {{ ucfirst($report->reportType->frequency) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-nowrap">{{ $report->created_at->format('M d, Y') }}</span>
                                                    <small class="text-muted">{{ $report->created_at->format('h:i A') }}</small>
                                                    @if($report->updated_at && $report->updated_at->ne($report->created_at))
                                                    <small class="status-success mt-1">
                                                        <i class="fas fa-sync-alt me-1"></i> Updated
                                                    </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = 'warning'; // Default for pending
                                                    $iconClass = 'fa-clock';

                                                    if ($report->status === 'approved') {
                                                        $statusClass = 'primary';
                                                        $iconClass = 'fa-check-circle';
                                                    } elseif ($report->status === 'rejected') {
                                                        $statusClass = 'danger';
                                                        $iconClass = 'fa-times-circle';
                                                    } elseif ($report->status === 'submitted') {
                                                        $statusClass = 'success'; // Green for submitted
                                                        $iconClass = 'fa-check';
                                                    }
                                                @endphp
                                                <span class="status-badge {{ $report->status }}">
                                                    <i class="fas {{ $iconClass }}"></i>
                                                    {{ ucfirst($report->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($report->remarks)
                                                    <button type="button"
                                                            class="btn btn-link btn-sm p-0 text-decoration-none"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="{{ $report->remarks }}">
                                                        <i class="fas fa-comment-alt text-primary"></i>
                                                        <small>View</small>
                                                    </button>
                                                @else
                                                    <small class="text-muted">None</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end gap-1">
                                                    @php
                                                        // Store the report ID in a variable for consistency
                                                        // Use the unique_id if available, otherwise fall back to regular id
                                                        $reportId = $report->unique_id ?? $report->id;
                                                    @endphp
                                                    <button type="button"
                                                            class="btn btn-sm"
                                                            style="background: var(--primary-light); color: var(--primary); border: none;"
                                                            onclick="previewFile('{{ route('barangay.direct.files.download', $reportId) }}', '{{ basename($report->file_path) }}')"
                                                            data-bs-toggle="tooltip"
                                                            title="View/Download">
                                                        <i class="fas fa-eye me-1"></i>
                                                        View
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm"
                                                            style="background: {{ $report->status === 'rejected' ? 'var(--warning-light)' : 'var(--info-light)' }};
                                                                   color: {{ $report->status === 'rejected' ? 'var(--warning)' : 'var(--info)' }};
                                                                   border: none;"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#resubmitModal{{ $reportId }}"
                                                            title="{{ $report->status === 'rejected' ? 'Resubmit (Required)' : 'Update Report' }}"
                                                            {{ $report->status === 'approved' ? 'disabled' : '' }}>
                                                        <i class="fas {{ $report->status === 'rejected' ? 'fa-redo' : 'fa-upload' }} me-1"></i>
                                                        {{ $report->status === 'rejected' ? 'Resubmit' : 'Update' }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Resubmit/Update Modal -->
                                        <div class="modal fade" id="resubmitModal{{ $reportId }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-light py-2">
                                                        <div class="d-flex align-items-center">
                                                            @if($report->status === 'rejected')
                                                            <div class="me-2 p-2 rounded-circle" style="background-color: rgba(var(--warning-rgb), 0.1);">
                                                                <i class="fas fa-redo text-warning"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title mb-0 fw-bold">Resubmit Report</h5>
                                                                <div class="text-muted small">{{ $report->reportType->name }}</div>
                                                            </div>
                                                            @else
                                                            <div class="me-2 p-2 rounded-circle" style="background-color: rgba(var(--info-rgb), 0.1);">
                                                                <i class="fas fa-upload text-info"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title mb-0 fw-bold">Update Report</h5>
                                                                <div class="text-muted small">{{ $report->reportType->name }}</div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-3">
                                                        @if($report->status === 'rejected' && $report->remarks)
                                                        <div class="alert alert-warning py-2 px-3 mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                <strong>Admin Remarks:</strong> <span class="ms-2">{{ $report->remarks }}</span>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <!-- File Comparison Section -->
                                                        <div class="card border-0 bg-light-subtle mb-4">
                                                            <div class="card-body">
                                                                <h6 class="card-title mb-3">
                                                                    <i class="fas fa-exchange-alt me-2 text-primary"></i>
                                                                    Replace Current File
                                                                </h6>
                                                                <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                                    @php
                                                                        $fileExtension = pathinfo($report->file_path, PATHINFO_EXTENSION);
                                                                        $iconClass = 'fa-file';
                                                                        $colorClass = 'primary';

                                                                        if ($fileExtension == 'pdf') {
                                                                            $iconClass = 'fa-file-pdf';
                                                                            $colorClass = 'danger';
                                                                        } elseif (in_array($fileExtension, ['doc', 'docx'])) {
                                                                            $iconClass = 'fa-file-word';
                                                                            $colorClass = 'primary';
                                                                        } elseif (in_array($fileExtension, ['xls', 'xlsx'])) {
                                                                            $iconClass = 'fa-file-excel';
                                                                            $colorClass = 'success';
                                                                        }
                                                                    @endphp
                                                                    <div class="me-3 p-2 rounded" style="background-color: rgba(var(--{{ $colorClass }}-rgb), 0.1);">
                                                                        <i class="fas {{ $iconClass }} fa-lg text-{{ $colorClass }}"></i>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <p class="mb-0 fw-medium">{{ basename($report->file_path) }}</p>
                                                                        <small class="text-muted">Submitted on {{ $report->created_at->format('M d, Y h:i A') }}</small>
                                                                    </div>
                                                                    <div>
                                                                        <a href="{{ route('barangay.direct.files.download', $reportId) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                            <i class="fas fa-eye me-1"></i> View
                                                                        </a>
                                                                        <a href="{{ route('barangay.direct.files.download', $reportId) }}?download=true" class="btn btn-sm btn-outline-secondary ms-1">
                                                                            <i class="fas fa-download me-1"></i> Download
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <form action="{{ route('barangay.submissions.resubmit', $reportId) }}" method="POST" enctype="multipart/form-data" id="resubmitForm{{ $reportId }}" onsubmit="console.log('Form submitted: {{ $reportId }}'); return true;">
                                                            @csrf
                                                            <input type="hidden" name="report_type_id" value="{{ $report->report_type_id }}">



                                                            <!-- Report Type Specific Fields -->
                                                            @php
                                                                // Get the model type from the report
                                                                $reportClassName = $report->model_type ?? class_basename(get_class($report));

                                                                // Determine the report type based on the model class
                                                                if ($reportClassName === 'WeeklyReport') {
                                                                    $reportType = 'weekly';
                                                                } elseif ($reportClassName === 'MonthlyReport') {
                                                                    $reportType = 'monthly';
                                                                } elseif ($reportClassName === 'QuarterlyReport') {
                                                                    $reportType = 'quarterly';
                                                                } elseif ($reportClassName === 'SemestralReport') {
                                                                    $reportType = 'semestral';
                                                                } elseif ($reportClassName === 'AnnualReport') {
                                                                    $reportType = 'annual';
                                                                } else {
                                                                    $reportType = strtolower($report->reportType->frequency);
                                                                }

                                                                // Ensure report type is lowercase for consistent comparison
                                                                $reportType = strtolower($reportType);
                                                            @endphp
                                                            <input type="hidden" name="report_type" value="{{ $reportType }}">
                                                            <div id="reportFields{{ $reportId }}" data-report-type="{{ $reportType }}" data-report-id="{{ $reportId }}"></div>

                                                            @if($reportType == 'weekly')
                                                            <div class="card mb-3" style="background-color: rgba(var(--primary-rgb), 0.03);">
                                                                <div class="card-body p-2">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                                                        <h6 class="card-title mb-0 small">Report Details</h6>
                                                                    </div>
                                                                    <div class="row g-2">
                                                                        <div class="col-md-3 mb-2">
                                                                            <label class="form-label small">Month</label>
                                                                            <select class="form-select form-select-sm" name="month" required>
                                                                                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                                                    <option value="{{ $month }}" {{ $report->month === $month ? 'selected' : '' }}>{{ $month }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-3 mb-2">
                                                                            <label class="form-label small">Week Number</label>
                                                                            <input type="number" class="form-control form-control-sm" name="week_number" min="1" max="52" required value="{{ $report->week_number }}">
                                                                        </div>
                                                                        <div class="col-md-3 mb-2">
                                                                            <label class="form-label small">Clean-up Sites</label>
                                                                            <input type="number" class="form-control form-control-sm" name="num_of_clean_up_sites" min="0" required value="{{ $report->num_of_clean_up_sites }}">
                                                                        </div>
                                                                        <div class="col-md-3 mb-2">
                                                                            <label class="form-label small">Participants</label>
                                                                            <input type="number" class="form-control form-control-sm" name="num_of_participants" min="0" required value="{{ $report->num_of_participants }}">
                                                                        </div>
                                                                        <div class="col-md-3 mb-2">
                                                                            <label class="form-label small">Barangays</label>
                                                                            <input type="number" class="form-control form-control-sm" name="num_of_barangays" min="0" required value="{{ $report->num_of_barangays }}">
                                                                        </div>
                                                                        <div class="col-md-3 mb-2">
                                                                            <label class="form-label small">Total Volume (m³)</label>
                                                                            <input type="number" class="form-control form-control-sm" name="total_volume" min="0" step="0.01" required value="{{ $report->total_volume }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @elseif($reportType == 'monthly')
                                                            <div class="card mb-3" style="background-color: rgba(var(--primary-rgb), 0.03);">
                                                                <div class="card-body p-2">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                                                        <h6 class="card-title mb-0 small">Report Details</h6>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label small">Month</label>
                                                                            <select class="form-select form-select-sm" name="month" required>
                                                                                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                                                    <option value="{{ $month }}" {{ $report->month === $month ? 'selected' : '' }}>{{ $month }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @elseif($reportType == 'quarterly')
                                                            <div class="card mb-3" style="background-color: rgba(var(--primary-rgb), 0.03);">
                                                                <div class="card-body p-2">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                                                        <h6 class="card-title mb-0 small">Report Details</h6>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label small">Quarter</label>
                                                                            <select class="form-select form-select-sm" name="quarter_number" required>
                                                                                <option value="1" {{ $report->quarter_number == 1 ? 'selected' : '' }}>Q1 (Jan-Mar)</option>
                                                                                <option value="2" {{ $report->quarter_number == 2 ? 'selected' : '' }}>Q2 (Apr-Jun)</option>
                                                                                <option value="3" {{ $report->quarter_number == 3 ? 'selected' : '' }}>Q3 (Jul-Sep)</option>
                                                                                <option value="4" {{ $report->quarter_number == 4 ? 'selected' : '' }}>Q4 (Oct-Dec)</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @elseif($reportType == 'semestral')
                                                            <div class="card mb-3" style="background-color: rgba(var(--primary-rgb), 0.03);">
                                                                <div class="card-body p-2">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                                                        <h6 class="card-title mb-0 small">Report Details</h6>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label small">Semester</label>
                                                                            <select class="form-select form-select-sm" name="sem_number" required>
                                                                                <option value="1" {{ $report->sem_number == 1 ? 'selected' : '' }}>1st Sem (Jan-Jun)</option>
                                                                                <option value="2" {{ $report->sem_number == 2 ? 'selected' : '' }}>2nd Sem (Jul-Dec)</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @elseif($reportType == 'annual')
                                                            <div class="card mb-3" style="background-color: rgba(var(--primary-rgb), 0.03);">
                                                                <div class="card-body p-2">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                                                        <h6 class="card-title mb-0 small">Report Details</h6>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="text-muted small mb-2">
                                                                            <i class="fas fa-info-circle me-1"></i>
                                                                            Report will be submitted for the current year.
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            <div class="mb-3">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <i class="fas fa-upload text-primary me-2"></i>
                                                                    <h6 class="mb-0 small">Upload New Report</h6>
                                                                </div>
                                                                <div class="file-upload-container" id="dropZone{{ $reportId }}">
                                                                    <input type="file" name="file" class="d-none" id="fileInput{{ $reportId }}" accept=".pdf,.doc,.docx,.xlsx">
                                                                    <div class="p-2 border rounded" style="background-color: rgba(var(--primary-rgb), 0.03);">
                                                                        <div class="d-flex align-items-center">
                                                                            <div>
                                                                                <button type="button" class="btn btn-sm btn-primary py-1 px-2" onclick="document.getElementById('fileInput{{ $reportId }}').click()">
                                                                                    <i class="fas fa-folder-open me-1"></i> Browse
                                                                                </button>
                                                                                <small class="d-block mt-1 text-muted" style="font-size: 0.7rem;">PDF, DOC, DOCX, XLSX (Max: 2MB)</small>
                                                                            </div>
                                                                            <div id="fileInfo{{ $reportId }}" class="d-none ms-2 flex-grow-1">
                                                                                <div class="d-flex align-items-center">
                                                                                    <i class="fas fa-file-alt text-primary me-1"></i>
                                                                                    <div>
                                                                                        <p class="mb-0 small"><span id="fileName{{ $reportId }}"></span></p>
                                                                                    </div>
                                                                                    <button type="button" class="btn btn-sm btn-link text-danger ms-auto p-0" onclick="clearFile({{ $reportId }})">
                                                                                        <i class="fas fa-times"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="d-flex justify-content-end gap-2 mt-3">
                                                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-sm {{ $report->status === 'rejected' ? 'btn-warning' : 'btn-primary' }}" id="submitBtn{{ $reportId }}">
                                                                    <i class="fas {{ $report->status === 'rejected' ? 'fa-redo' : 'fa-upload' }} me-1"></i>
                                                                    {{ $report->status === 'rejected' ? 'Resubmit' : 'Update' }}
                                                                </button>
                                                            </div>

                                                            <!-- Add a hidden field to ensure the form is properly submitted -->
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted">
                                    {{ $reports->firstItem() ?? 0 }}-{{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }}
                                </small>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle py-0 px-2" type="button" id="perPageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ $reports->perPage() }}
                                    </button>
                                    <ul class="dropdown-menu shadow-sm" aria-labelledby="perPageDropdown">
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 10]) }}">10 per page</a></li>
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 25]) }}">25 per page</a></li>
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 50]) }}">50 per page</a></li>
                                    </ul>
                                </div>
                            </div>
                            @if($reports->hasPages())
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        <li class="page-item {{ $reports->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ $reports->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>

                                        {{-- Pagination Elements --}}
                                        @php
                                            $currentPage = $reports->currentPage();
                                            $lastPage = $reports->lastPage();
                                            $range = 2; // Show 2 pages before and after current page

                                            $startPage = max($currentPage - $range, 1);
                                            $endPage = min($currentPage + $range, $lastPage);

                                            // Always show first page
                                            if ($startPage > 1) {
                                                echo '<li class="page-item"><a class="page-link" href="'.$reports->url(1).'">1</a></li>';

                                                // Add ellipsis if needed
                                                if ($startPage > 2) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                            }

                                            // Main page range
                                            for ($i = $startPage; $i <= $endPage; $i++) {
                                                if ($i == $currentPage) {
                                                    echo '<li class="page-item active"><span class="page-link">'.$i.'</span></li>';
                                                } else {
                                                    echo '<li class="page-item"><a class="page-link" href="'.$reports->url($i).'">'.$i.'</a></li>';
                                                }
                                            }

                                            // Always show last page
                                            if ($endPage < $lastPage) {
                                                // Add ellipsis if needed
                                                if ($endPage < $lastPage - 1) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }

                                                echo '<li class="page-item"><a class="page-link" href="'.$reports->url($lastPage).'">'.$lastPage.'</a></li>';
                                            }
                                        @endphp

                                        {{-- Next Page Link --}}
                                        <li class="page-item {{ !$reports->hasMorePages() ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ $reports->nextPageUrl() }}" rel="next" aria-label="Next">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Define CSS variables for status colors */
        :root {
            --primary: #0d6efd; /* Trustworthy blue - monthly reports */
            --primary-light: rgba(13, 110, 253, 0.1);
            --primary-rgb: 13, 110, 253;

            --success: #198754; /* Forest green - quarterly reports and submitted status */
            --success-light: rgba(25, 135, 84, 0.1);
            --success-rgb: 25, 135, 84;

            --warning: #ffc107; /* Amber - semestral reports and pending items */
            --warning-light: rgba(255, 193, 7, 0.1);
            --warning-rgb: 255, 193, 7;

            --danger: #dc3545; /* Red - annual reports and rejected status */
            --danger-light: rgba(220, 53, 69, 0.1);
            --danger-rgb: 220, 53, 69;

            --info: #0dcaf0; /* Light blue - weekly reports */
            --info-light: rgba(13, 202, 240, 0.1);
            --info-rgb: 13, 202, 240;

            --secondary: #6c757d;
            --secondary-light: rgba(108, 117, 125, 0.1);
            --secondary-rgb: 108, 117, 125;

            /* Status Colors */
            --submitted: var(--success);
            --submitted-light: var(--success-light);
            --submitted-rgb: var(--success-rgb);

            --neutral: #f8f9fa; /* Light gray - clean, professional background */
            --neutral-light: #f9fafb; /* Very light gray - for subtle backgrounds */
            --neutral-dark: #e9ecef; /* Slightly darker neutral for contrast */
            --text-primary: #495057; /* Dark gray - easy to read but less harsh than black */
            --text-secondary: #6c757d; /* Medium gray - secondary information */
            --border-color: #dee2e6; /* Light gray border - subtle separation */
        }

        body {
            color: var(--text-primary);
        }

        /* Compact Design */
        .card {
            border-radius: 0.375rem;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: var(--neutral);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
        }

        .card-body {
            padding: 0.75rem 1rem;
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            background: var(--light);
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .table td {
            vertical-align: middle;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .table tr:hover {
            background-color: rgba(var(--primary-rgb), 0.03);
        }

        /* Badge Styles - Color Psychology */
        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
            border-radius: 0.25rem;
            font-size: 0.75rem;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8125rem;
            line-height: 1;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .status-badge i {
            font-size: 0.875rem;
        }

        .status-badge.submitted {
            background-color: var(--success-light);
            color: var(--success);
            border: 1px solid rgba(var(--success-rgb), 0.2);
        }

        .status-badge.pending {
            background-color: var(--warning-light);
            color: var(--warning);
            border: 1px solid rgba(var(--warning-rgb), 0.2);
        }

        .status-badge.approved {
            background-color: var(--primary-light);
            color: var(--primary);
            border: 1px solid rgba(var(--primary-rgb), 0.2);
        }

        .status-badge.rejected {
            background-color: var(--secondary-light);
            color: var(--secondary);
            border: 1px solid rgba(var(--secondary-rgb), 0.2);
        }

        /* Frequency Badge Styles */
        .frequency-weekly {
            background-color: var(--info-light) !important;
            color: var(--info);
            border: 1px solid rgba(var(--info-rgb), 0.3);
        }

        .frequency-monthly {
            background-color: var(--primary-light) !important;
            color: var(--primary);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
        }

        .frequency-quarterly {
            background-color: var(--success-light) !important;
            color: var(--success);
            border: 1px solid rgba(var(--success-rgb), 0.3);
        }

        .frequency-semestral {
            background-color: var(--warning-light) !important;
            color: var(--warning);
            border: 1px solid rgba(var(--warning-rgb), 0.3);
        }

        .frequency-annual {
            background-color: var(--danger-light) !important;
            color: var(--danger);
            border: 1px solid rgba(var(--danger-rgb), 0.3);
        }

        /* Status Colors */
        .status-submitted {
            color: var(--success);
        }

        .status-approved {
            color: var(--primary);
        }

        .status-rejected {
            color: var(--danger);
        }

        .status-pending {
            color: var(--warning);
        }

        /* Button Styles - Color Psychology */
        .btn {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }

        .btn-warning {
            background-color: var(--warning);
            border-color: var(--warning);
        }

        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
        }

        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: #fff;
        }

        .btn-outline-danger {
            border-color: var(--danger);
            color: var(--danger);
        }

        .btn-outline-danger:hover {
            background-color: var(--danger);
            color: #fff;
        }

        .btn-outline-info {
            border-color: var(--info);
            color: var(--info);
        }

        .btn-outline-info:hover {
            background-color: var(--info);
            color: #fff;
        }

        /* Form Styles - Compact */
        .form-select, .form-control {
            border-radius: 0.25rem;
            border: 1px solid var(--border-color);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            height: calc(1.5em + 0.75rem + 2px);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        }

        /* Filter Styles */
        .filter-active {
            background-color: rgba(var(--primary-rgb), 0.1);
            border-color: var(--primary);
        }

        /* Pagination Styles - Compact */
        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            color: var(--primary);
            background-color: #fff;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .pagination .page-link:hover {
            background-color: var(--neutral-dark);
            border-color: var(--border-color);
            color: var(--primary);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .pagination .page-item.disabled .page-link {
            color: var(--text-secondary);
            pointer-events: none;
            background-color: #fff;
            border-color: var(--border-color);
        }

        /* Dropdown Styles - Compact */
        .dropdown-menu {
            min-width: 8rem;
            border-radius: 0.25rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 0.875rem;
            padding: 0.25rem 0;
        }

        .dropdown-item {
            padding: 0.375rem 0.75rem;
            transition: background-color 0.15s ease-in-out;
        }

        .dropdown-item:hover {
            background-color: var(--neutral);
        }

        /* Modal Styles - Compact */
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
            padding: 1rem;
        }

        .modal-header {
            padding: 0.75rem 1rem;
        }

        .modal-footer {
            padding: 0.75rem 1rem;
        }

        /* File Upload Styles */
        .file-upload-container {
            position: relative;
            border: 2px dashed var(--border-color);
            border-radius: 0.375rem;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            background-color: var(--neutral);
        }

        .file-upload-container.dragover {
            border-color: var(--primary);
            background-color: rgba(var(--primary-rgb), 0.05);
        }

        .file-upload-container:hover {
            border-color: var(--primary);
        }

        /* Input Group Styles */
        .input-group {
            margin-bottom: 0;
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group .btn {
            border-left: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group-text {
            background-color: #fff;
            border-right: 0;
        }

        .search-box {
            border-radius: 0.375rem;
        }

        .search-box:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        }

        /* Enhanced Filter Section */
        .filter-container {
            background-color: var(--neutral-light);
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
        }

        .filter-header {
            color: var(--text-primary);
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(var(--primary-rgb), 0.1);
            padding-bottom: 0.5rem;
            margin-bottom: 0.75rem;
            font-weight: 500;
        }

        .filter-group {
            margin-bottom: 0.75rem;
        }

        .filter-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
            display: block;
        }

        .filter-container .form-control,
        .filter-container .form-select {
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
            font-size: 0.85rem;
            padding: 0.375rem 0.75rem;
            height: calc(1.5em + 0.75rem + 2px);
        }

        .filter-container .form-control:focus,
        .filter-container .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.15rem rgba(var(--primary-rgb), 0.15);
        }

        .filter-container .input-group .btn {
            border-top-right-radius: 0.25rem !important;
            border-bottom-right-radius: 0.25rem !important;
            height: calc(1.5em + 0.75rem + 2px);
        }

        .filter-active {
            border-color: var(--primary) !important;
            background-color: rgba(var(--primary-rgb), 0.05);
        }

        .filter-active-btn {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        .active-filters {
            padding-top: 0.75rem;
            border-top: 1px dashed rgba(var(--primary-rgb), 0.2);
            margin-top: 0.75rem;
        }

        .filter-badge {
            font-size: 0.7rem;
            padding: 0.35em 0.65em;
            background-color: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            border-radius: 0.25rem;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }

        /* Status Colors */
        .status-submitted {
            color: var(--info);
        }

        .status-approved {
            color: var(--success);
        }

        .status-rejected {
            color: var(--danger);
        }

        .status-pending {
            color: var(--warning);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .card-header .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }
            .card-header .d-flex > * {
                width: 100% !important;
                margin-bottom: 0.5rem;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 0.5rem;
            }
            .d-flex.justify-content-between > * {
                width: 100%;
            }
            .pagination {
                justify-content: center;
            }

            .table td, .table th {
                padding: 0.4rem 0.5rem;
                font-size: 0.8rem;
            }

            /* Improved filter container for mobile */
            .filter-container {
                padding: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .filter-header {
                font-size: 0.8rem;
                padding-bottom: 0.5rem;
                margin-bottom: 0.75rem;
            }

            .filter-container .row > div {
                margin-bottom: 0.75rem;
            }

            .filter-group {
                margin-bottom: 0.5rem;
            }

            .filter-label {
                margin-bottom: 0.25rem;
                font-size: 0.7rem;
            }

            .filter-container .form-control,
            .filter-container .form-select {
                font-size: 0.8rem;
                height: calc(1.5em + 0.75rem + 2px);
                padding: 0.375rem 0.5rem;
            }

            .filter-container .input-group .btn {
                height: calc(1.5em + 0.75rem + 2px);
            }

            .active-filters {
                padding-top: 0.5rem;
                margin-top: 0.5rem;
            }

            .filter-badge {
                margin-bottom: 0.25rem;
                font-size: 0.65rem;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to all filter selects
            const filterSelects = document.querySelectorAll('.filter-select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            // Add debounced event listener to search input
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        document.getElementById('filterForm').submit();
                    }, 500);
                });
            }

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    container: 'body'
                });
            });
        });
    </script>
    @endpush

    @if(!$reports->isEmpty())
    <!-- File Preview Modal -->
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <div class="d-flex align-items-center">
                        <div id="fileTypeIcon" class="me-3 p-2 rounded-circle" style="background-color: rgba(var(--primary-rgb), 0.1);">
                            <i class="fas fa-file fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold">
                                <span id="previewFileName"></span>
                            </h5>
                            <div class="text-muted small">Document Preview</div>
                        </div>
                    </div>
                    <div>
                        <a id="downloadLink" href="#" class="btn btn-primary me-2">
                            <i class="fas fa-download me-1"></i>
                            Download
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 bg-light">
                    <div id="previewContainer" class="d-flex justify-content-center align-items-center p-4" style="min-height: 70vh; background-color: #f8f9fa;">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">Loading document preview...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <div class="d-flex align-items-center text-muted me-auto small">
                        <i class="fas fa-info-circle me-2"></i>
                        <span>If the document doesn't load correctly, please use the download button.</span>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white justify-content-center">
                    <h5 class="modal-title text-center mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Success
                    </h5>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="success-icon mb-3">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h4 class="mb-3" id="successModalTitle">Report Updated Successfully</h4>
                        <p class="text-muted" id="successModalMessage">Your report has been successfully updated and will be reviewed by the admin.</p>
                        <div class="mt-3">
                            <small class="text-muted">This message will close automatically in <span id="countdown">2</span> seconds</small>
                        </div>
                    </div>
                </div>
                <!-- No footer with close button -->
            </div>
        </div>
    </div>

    @endif
@endsection
