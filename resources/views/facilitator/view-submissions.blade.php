@extends('facilitator.layouts.app')

@section('title', 'View Submissions')

@push('styles')
<style>
/* Define CSS variables for status colors */
:root {
    --success-rgb: 25, 135, 84;
    --danger-rgb: 220, 53, 69;
    --warning-rgb: 255, 193, 7;
    --primary-rgb: 13, 110, 253;
    --secondary-rgb: 108, 117, 125;
    --info-rgb: 13, 202, 240;
}

.table th {
    background: var(--light);
    font-weight: 600;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}

.search-box {
    border-radius: 0.375rem;
}

.search-box:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
}

/* Pagination Styles */
.pagination {
    margin: 0;
    padding: 1rem 0;
}

.pagination .page-item .page-link {
    color: var(--primary);
    border: 1px solid var(--border-color);
    padding: 0.5rem 1rem;
    margin: 0 0.2rem;
    border-radius: 0.375rem;
}

.pagination .page-item.active .page-link {
    background-color: var(--primary);
    border-color: var(--primary);
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: var(--gray-500);
    border-color: var(--border-color);
}

.pagination .page-item .page-link:hover {
    background-color: var(--primary-light);
    border-color: var(--primary);
    color: var(--primary);
}

.pagination .page-item.active .page-link:hover {
    background-color: var(--primary);
    color: white;
}

.pagination-info {
    color: var(--gray-600);
    font-size: 0.875rem;
}

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
    position: relative;
    overflow: hidden;
}

.status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.status-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.status-badge:hover::before {
    transform: translateX(100%);
}

.status-badge i {
    font-size: 0.875rem;
}

.status-badge.submitted {
    background-color: var(--success-light);
    color: var(--success);
    border: 1px solid rgba(var(--success-rgb), 0.2);
}

.status-badge.no-submission {
    background-color: var(--danger-light);
    color: var(--danger);
    border: 1px solid rgba(var(--danger-rgb), 0.2);
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
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="page-title">
            <i class="fas fa-file-alt"></i>
            Report Submissions
        </h2>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-alt me-2" style="color: var(--primary);"></i>
            All Submissions
        </h5>

        <form id="filterForm" class="d-flex flex-wrap gap-2" method="GET" action="{{ route('facilitator.view-submissions') }}">
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control search-box" name="search" value="{{ request('search') }}" placeholder="Search...">
            </div>

            <!-- Barangay Filter -->
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text">
                    <i class="fas fa-building"></i>
                </span>
                <select class="form-select" name="barangay_id">
                    <option value="">All Barangays</option>
                    @foreach($barangays as $barangay)
                        <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>
                            {{ $barangay->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-filter"></i>
                </span>
                <select class="form-select" name="type">
                    <option value="">All Types</option>
                    <option value="weekly" {{ request('type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ request('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarterly" {{ request('type') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                    <option value="annual" {{ request('type') == 'annual' ? 'selected' : '' }}>Annual</option>
                </select>
            </div>

            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-filter"></i>
                </span>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            @if(request()->hasAny(['search', 'barangay_id', 'type', 'status']))
                <a href="{{ route('facilitator.view-submissions') }}" class="btn btn-light">
                    <i class="fas fa-times"></i>
                    Clear Filters
                </a>
            @endif
        </form>
    </div>
    <div class="card-body">
        @if(isset($selectedBarangay) && $selectedBarangay)
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i>
            Showing submissions for <strong>{{ $selectedBarangay->name }}</strong>
        </div>
        @endif

        <div class="table-responsive">
            @include('facilitator.partials.reports-table')
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2 text-primary"></i>
                    View Report Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-building text-primary me-2"></i>
                                    <span class="fw-bold">Barangay:</span>
                                    <span id="viewBarangay" class="ms-2"></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-file-alt text-primary me-2"></i>
                                    <span class="fw-bold">Report Type:</span>
                                    <span id="viewReportType" class="ms-2"></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <span class="fw-bold">Frequency:</span>
                                    <span id="viewFrequency" class="ms-2"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <span class="fw-bold">Submitted:</span>
                                    <span id="viewSubmitted" class="ms-2"></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    <span class="fw-bold">Status:</span>
                                    <span id="viewStatus" class="ms-2"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Report Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="reportDetails"></div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-paperclip me-2 text-primary"></i>
                            Attached Files
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="attachedFiles"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-comment me-2 text-primary"></i>
                            Remarks
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="viewRemarks"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRemarksModal" id="openRemarksFromView">
                    <i class="fas fa-comment me-1"></i> Add Remarks
                </button>
                <form id="approveForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success ms-2">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                </form>
                <form id="rejectForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning ms-2">
                        <i class="fas fa-redo me-1"></i> Resubmit
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Remarks Modal -->
<div class="modal fade" id="addRemarksModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addRemarksForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" id="remarkReportType">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-comment me-2 text-primary"></i>
                        Add Remarks
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-building text-primary me-2"></i>
                                <span class="fw-bold">Barangay:</span>
                                <span id="remarkBarangay" class="ms-2"></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                <span class="fw-bold">Report Type:</span>
                                <span id="remarkReportName" class="ms-2"></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" id="remarkText" rows="5" placeholder="Enter your remarks here..." required></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            These remarks will be visible to the barangay.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Remarks
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all filter elements
        const filterForm = document.getElementById('filterForm');
        const searchInput = document.querySelector('input[name="search"]');
        const filterSelects = document.querySelectorAll('select');
        const tableBody = document.querySelector('.table tbody');

        // Function to handle AJAX requests
        const handleAjaxRequest = (params) => {
            // Show loading state
            tableBody.style.opacity = '0.5';

            fetch(`${filterForm.action}?${new URLSearchParams(params).toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = html;

                // Replace the table body with the new content
                tableBody.innerHTML = tempContainer.innerHTML;

                // Restore opacity
                tableBody.style.opacity = '1';
            })
            .catch(error => {
                console.error('Search request failed:', error);
                tableBody.style.opacity = '1';
            });
        };

        // Function to get all filter values
        const getFilterValues = () => {
            return {
                search: searchInput.value.trim(),
                barangay_id: document.querySelector('select[name="barangay_id"]').value,
                type: document.querySelector('select[name="type"]').value,
                status: document.querySelector('select[name="status"]').value,
                ajax: true
            };
        };

        // Add event listener for search input
        searchInput.addEventListener('input', function() {
            handleAjaxRequest(getFilterValues());
        });

        // Add event listeners for all select elements
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                handleAjaxRequest(getFilterValues());
            });
        });
    });

    // Store the current report data
    let currentReport = null;
    let currentReportType = null;

    // View report modal handling
    document.getElementById('viewReportModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const report = JSON.parse(button.getAttribute('data-report'));
        const type = button.getAttribute('data-type');

        // Store the current report data for use with the "Add Remarks" button
        currentReport = report;
        currentReportType = type;

        document.getElementById('viewBarangay').textContent = report.user.name;
        document.getElementById('viewReportType').textContent = report.report_type.name;
        document.getElementById('viewFrequency').textContent = type.charAt(0).toUpperCase() + type.slice(1);
        document.getElementById('viewSubmitted').textContent = new Date(report.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

        const statusElement = document.getElementById('viewStatus');
        if (report.remarks) {
            statusElement.innerHTML = '<span class="badge bg-success">Reviewed</span>';
        } else {
            statusElement.innerHTML = '<span class="badge bg-warning">Pending</span>';
        }

        // Display report details based on type
        const detailsElement = document.getElementById('reportDetails');
        let detailsHTML = '';

        // Common fields for all report types
        detailsHTML += `
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-heading text-primary me-2"></i>
                <span class="fw-bold">Title:</span>
                <span class="ms-2">${report.title || 'N/A'}</span>
            </div>
        `;

        // Type-specific fields
        if (type === 'weekly' || type === 'monthly') {
            detailsHTML += `
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-week text-primary me-2"></i>
                    <span class="fw-bold">Week/Month:</span>
                    <span class="ms-2">${report.period || 'N/A'}</span>
                </div>
            `;
        } else if (type === 'quarterly') {
            detailsHTML += `
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    <span class="fw-bold">Quarter:</span>
                    <span class="ms-2">${report.quarter || 'N/A'}</span>
                </div>
            `;
        } else if (type === 'annual') {
            detailsHTML += `
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    <span class="fw-bold">Year:</span>
                    <span class="ms-2">${report.year || 'N/A'}</span>
                </div>
            `;
        }

        detailsHTML += `
            <div class="d-flex align-items-start">
                <i class="fas fa-align-left text-primary me-2 mt-1"></i>
                <span class="fw-bold mt-1">Description:</span>
                <div class="ms-2">${report.description || 'N/A'}</div>
            </div>
        `;

        detailsElement.innerHTML = detailsHTML;

        // Display attached files
        const filesElement = document.getElementById('attachedFiles');
        if (report.files && report.files.length > 0) {
            let filesHTML = '<div class="list-group">';
            report.files.forEach(file => {
                filesHTML += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file text-primary me-2"></i>
                            ${file.original_name}
                        </div>
                        <a href="{{ route('admin.files.download', '') }}/${file.id}" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    </div>
                `;
            });
            filesHTML += '</div>';
            filesElement.innerHTML = filesHTML;
        } else {
            filesElement.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-file-alt text-muted mb-2" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0">No files attached to this report.</p>
                </div>
            `;
        }

        // Display remarks
        const remarksElement = document.getElementById('viewRemarks');
        if (report.remarks) {
            remarksElement.innerHTML = `
                <div class="d-flex">
                    <i class="fas fa-quote-left text-primary me-2 mt-1"></i>
                    <div>${report.remarks}</div>
                </div>
            `;
        } else {
            remarksElement.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-comment-slash text-muted mb-2" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0">No remarks added yet.</p>
                </div>
            `;
        }

        // Set up the approve/reject forms
        document.getElementById('approveForm').action = "/facilitator/reports/" + report.id + "/approve";
        document.getElementById('rejectForm').action = "/facilitator/reports/" + report.id + "/resubmit";
    });

    // Handle the "Add Remarks" button in the view modal
    document.getElementById('openRemarksFromView').addEventListener('click', function() {
        // Hide the view modal when opening the remarks modal
        $('#viewReportModal').modal('hide');

        // Set up the remarks modal with the current report data
        const form = document.getElementById('addRemarksForm');
        form.action = "/facilitator/reports/" + currentReport.id + "/remarks";
        document.getElementById('remarkReportType').value = currentReportType;
        document.getElementById('remarkBarangay').textContent = currentReport.user.name;
        document.getElementById('remarkReportName').textContent = currentReport.report_type.name;
        document.getElementById('remarkText').value = currentReport.remarks || '';
    });

    // Add remarks modal handling
    document.getElementById('addRemarksModal').addEventListener('show.bs.modal', function(event) {
        // Only set up the form if the event was triggered by a button with data attributes
        // (not by our "Add Remarks" button in the view modal)
        if (event.relatedTarget && event.relatedTarget.hasAttribute('data-report')) {
            const button = event.relatedTarget;
            const report = JSON.parse(button.getAttribute('data-report'));
            const type = button.getAttribute('data-type');
            const form = this.querySelector('form');

            form.action = "/facilitator/reports/" + report.id + "/remarks";
            document.getElementById('remarkReportType').value = type;
            document.getElementById('remarkBarangay').textContent = report.user.name;
            document.getElementById('remarkReportName').textContent = report.report_type.name;
            document.getElementById('remarkText').value = report.remarks || '';
        }
    });
</script>
@endpush
@endsection
