@extends('admin.layouts.app')

@section('title', 'View Submissions')

@push('styles')
    <style>
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

    .timeliness-badge {
        font-size: 0.75rem;
        padding: 0.25em 0.5em;
        border-radius: 0.25rem;
        margin-left: 0.5rem;
    }

    .timeliness-badge.late {
        background-color: var(--danger-light);
        color: var(--danger);
    }

    .timeliness-badge.ontime {
        background-color: var(--success-light);
        color: var(--success);
    }

    .status-badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
        border-radius: 0.375rem;
    }

    .status-badge.submitted {
        background-color: var(--success-light);
        color: var(--success);
    }

    .status-badge.no-submission {
        background-color: var(--danger-light);
        color: var(--danger);
    }

    /* Modal Styles */
    .modal-content {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        border-top: none;
        padding: 1rem 2rem 2rem;
    }

    .modal .btn {
        padding: 0.5rem 2rem;
        border-radius: 0.5rem;
    }

    .modal .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .modal .btn-danger {
        background-color: var(--danger);
        border-color: var(--danger);
    }

    .modal .btn-light {
        background-color: var(--light);
        border-color: var(--border-color);
    }

    .modal .text-success {
        color: var(--success) !important;
    }

    .modal .text-warning {
        color: var(--warning) !important;
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

                        const newTableBody = tempContainer.querySelector('.table tbody');
                        if (newTableBody) {
                            const oldContent = tableBody.innerHTML;
                            const newContent = newTableBody.innerHTML;

                            // Only update if content is different
                            if (oldContent !== newContent) {
                                tableBody.innerHTML = newContent;

                                // Re-initialize any interactive elements
                                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                                    new bootstrap.Tooltip(el);
                                });
                            }
                        }
                        // Restore opacity
                        tableBody.style.opacity = '1';
                    })
                    .catch(error => {
                        console.error('Search request failed:', error);
                        tableBody.style.opacity = '1';
                    });
                };

                // Add event listener for search input
                searchInput.addEventListener('input', function() {
                    const currentValue = this.value;
                    handleAjaxRequest({
                        search: currentValue,
                        type: document.querySelector('select[name="type"]').value,
                        status: document.querySelector('select[name="status"]').value,
                        timeliness: document.querySelector('select[name="timeliness"]').value,
                        ajax: true
                    });
                });

                // Add event listeners for all select elements
                filterSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        handleAjaxRequest({
                            search: searchInput.value.trim(),
                            type: document.querySelector('select[name="type"]').value,
                            status: document.querySelector('select[name="status"]').value,
                            timeliness: document.querySelector('select[name="timeliness"]').value,
                            ajax: true
                        });
                    });
                });
            });
        </script>
        @endpush

        <form id="filterForm" class="d-flex gap-2" method="GET" action="{{ route('admin.view.submissions') }}">
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control search-box" name="search" value="{{ request('search') }}" placeholder="Search...">
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
                    <option value="semestral" {{ request('type') == 'semestral' ? 'selected' : '' }}>Semestral</option>
                    <option value="annual" {{ request('type') == 'annual' ? 'selected' : '' }}>Annual</option>
                </select>
            </div>
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-filter"></i>
                </span>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="no submission" {{ request('status') == 'no submission' ? 'selected' : '' }}>No Submission</option>
                </select>
            </div>
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-clock"></i>
                </span>
                <select class="form-select" name="timeliness">
                    <option value="">All Submissions</option>
                    <option value="late" {{ request('timeliness') == 'late' ? 'selected' : '' }}>Late</option>
                    <option value="ontime" {{ request('timeliness') == 'ontime' ? 'selected' : '' }}>On Time</option>
                </select>
            </div>
            @if(request()->hasAny(['search', 'type', 'status', 'timeliness']))
                <a href="{{ route('admin.view.submissions') }}" class="btn btn-light">
                    <i class="fas fa-times"></i>
                    Clear Filters
                </a>
            @endif
        </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                <table class="table">
                <thead>
                    <tr>
                        <th>Report</th>
                        <th>User</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 32px; height: 32px; border-radius: 8px; background: var(--primary-light); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                    @php
                                        $extension = strtolower(pathinfo($submission['file_name'], PATHINFO_EXTENSION));
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
                                    <div style="font-weight: 500; color: var(--dark);">{{ $submission['report_type'] }}</div>
                                    <small class="text-muted">{{ ucfirst($submission['type']) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $submission['submitted_by'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($submission['submitted_at'])->format('M d, Y') }}</td>
                        <td>
                            @php
                                $statusColor = match($submission['status']) {
                                    'submitted' => 'var(--success)',
                                    'no submission' => 'var(--danger)',
                                    default => 'var(--gray)'
                                };
                            @endphp
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 8px; height: 8px; border-radius: 50%; background: {{ $statusColor }};"></div>
                                {{ ucfirst($submission['status']) }}
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm" style="background: var(--primary-light); color: var(--primary); border: none;" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal{{ $submission['id'] }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm" style="background: var(--info-light); color: var(--info); border: none;" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $submission['id'] }}">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- View Submission Modal -->
                    <div class="modal fade" id="viewSubmissionModal{{ $submission['id'] }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        @php
                                            $extension = strtolower(pathinfo($submission['file_name'], PATHINFO_EXTENSION));
                                            $icon = match($extension) {
                                                'pdf' => 'fa-file-pdf',
                                                'doc', 'docx' => 'fa-file-word',
                                                'xls', 'xlsx' => 'fa-file-excel',
                                                'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image',
                                                'txt' => 'fa-file-alt',
                                                default => 'fa-file'
                                            };
                                        @endphp
                                        <i class="fas {{ $icon }} me-2" style="color: var(--primary);"></i>
                                        View Submission
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Report Type</label>
                                        <p class="form-control-static">{{ $submission['report_type'] }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Submitted By</label>
                                        <p class="form-control-static">{{ $submission['submitted_by'] }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Submitted At</label>
                                        <p class="form-control-static">
                                            {{ \Carbon\Carbon::parse($submission['submitted_at'])->format('M d, Y h:i A') }}
                                            <span class="timeliness-badge {{ $submission['is_late'] ? 'late' : 'ontime' }}">
                                                {{ $submission['is_late'] ? 'Late' : 'On Time' }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Deadline</label>
                                        <p class="form-control-static">{{ \Carbon\Carbon::parse($submission['deadline'])->format('M d, Y h:i A') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <p class="form-control-static">
                                            <span class="status-badge {{ $submission['status'] }}">
                                                {{ ucfirst($submission['status']) }}
                                            </span>
                                        </p>
                                    </div>
                                    @if($submission['remarks'])
                                    <div class="mb-3">
                                        <label class="form-label">Remarks</label>
                                        <p class="form-control-static">{{ $submission['remarks'] }}</p>
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label">File</label>
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                    class="btn btn-sm"
                                                    style="background: var(--primary-light); color: var(--primary); border: none;"
                                                    onclick="previewFile('{{ route('admin.files.download', ['id' => $submission['id']]) }}', '{{ $submission['file_name'] }}')">
                                                <i class="fas fa-eye"></i>
                                                <span>View File</span>
                                            </button>
                                            <a href="{{ route('admin.files.download', ['id' => $submission['id'], 'download' => true]) }}"
                                               class="btn btn-sm"
                                               style="background: var(--info-light); color: var(--info); border: none;">
                                                <i class="fas fa-download"></i>
                                                <span>Download File</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                                            <!-- Update Status Modal -->
                    <div class="modal fade" id="updateStatusModal{{ $submission['id'] }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit me-2" style="color: var(--primary);"></i>
                                        Update Status
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                <form action="{{ route('admin.update.report', $submission['id']) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="type" value="{{ $submission['type'] }}">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="submitted" {{ $submission['status'] == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                                <option value="no submission" {{ $submission['status'] == 'no submission' ? 'selected' : '' }}>No Submission</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="3">{{ $submission['remarks'] }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i>
                                            <span>Save Changes</span>
                                        </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                    <!-- File Preview Modal -->
                    <div class="modal fade" id="filePreviewModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-file-alt me-2"></i>
                                        <span id="previewFileName"></span>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="previewContainer" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="#" id="downloadLink" class="btn btn-primary">
                                        <i class="fas fa-download"></i>
                                        Download
                                    </a>
                                </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-inbox fa-3x mb-3" style="color: var(--gray-400);"></i>
                                <p class="mb-0" style="color: var(--gray-600);">No submissions found</p>
                            </div>
                        </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
        @if($submissions->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="pagination-info">
                Showing {{ $submissions->firstItem() ?? 0 }} to {{ $submissions->lastItem() ?? 0 }} of {{ $submissions->total() }} entries
                        </div>
            <div class="pagination-container">
                {{ $submissions->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">Success!</h5>
                <p class="text-muted mb-0" id="successMessage">The operation was completed successfully.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">Confirm Deletion</h5>
                <p class="text-muted mb-0">Are you sure you want to delete this report type? This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt me-2"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Success Modal -->
<div class="modal fade" id="statusUpdateSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">Status Updated</h5>
                <p class="text-muted mb-0">The report status has been updated successfully.</p>
                    </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewFile(url, fileName) {
    // Set the file name in the modal
    document.getElementById('previewFileName').textContent = fileName;

    // Set the download link
    const downloadLink = document.getElementById('downloadLink');
    downloadLink.href = url + '?download=true';

    // Show loading spinner
    const previewContainer = document.getElementById('previewContainer');
    previewContainer.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
    modal.show();

    // Get file extension
    const extension = fileName.split('.').pop().toLowerCase();

    // Fetch the file
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('File not found or access denied');
            }
            const contentType = response.headers.get('content-type');
            console.log('File content type:', contentType);
            return response.blob().then(blob => ({ blob, contentType }));
        })
        .then(({ blob, contentType }) => {
            const fileUrl = URL.createObjectURL(blob);
            console.log('File URL created:', fileUrl);

            // Create preview based on content type and extension
            if (contentType.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                // Image preview
                previewContainer.innerHTML = `
                    <div class="text-center">
                        <img src="${fileUrl}" class="img-fluid" alt="${fileName}" style="max-height: 70vh;">
                    </div>`;
            } else if (contentType === 'application/pdf' || extension === 'pdf') {
                // PDF preview
                previewContainer.innerHTML = `
                    <div style="height: 70vh;">
                        <iframe src="${fileUrl}"
                                style="width: 100%; height: 100%; border: none;"
                                title="${fileName}">
                        </iframe>
                    </div>`;
            } else if (contentType.startsWith('text/') || ['txt', 'csv', 'html'].includes(extension)) {
                // Text preview
                fetch(fileUrl)
                    .then(response => response.text())
                    .then(text => {
                        previewContainer.innerHTML = `
                            <div style="max-height: 70vh; overflow-y: auto;">
                                <pre class="text-start p-3 bg-light rounded">${text}</pre>
                            </div>`;
                    });
            } else if (['doc', 'docx', 'xls', 'xlsx'].includes(extension)) {
                // Office documents - use Google Docs Viewer
                const googleDocsUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(window.location.origin + url)}&embedded=true`;
                previewContainer.innerHTML = `
                    <div style="height: 70vh;">
                        <iframe src="${googleDocsUrl}"
                                style="width: 100%; height: 100%; border: none;"
                                title="${fileName}">
                        </iframe>
                    </div>`;
            } else {
                // Unsupported file type
                previewContainer.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This file type cannot be previewed. Please download to view.
                        <div class="mt-2">
                            <small class="text-muted">
                                File type: ${contentType}<br>
                                Extension: ${extension}
                            </small>
                        </div>
                    </div>`;
            }
        })
        .catch(error => {
            console.error('Preview error:', error);
            previewContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${error.message}
                    <div class="mt-2">
                        <small class="text-muted">
                            File: ${fileName}<br>
                            Extension: ${extension}
                        </small>
                    </div>
                </div>`;
        });
}

// Function to show success modal
function showSuccessModal(message) {
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    document.getElementById('successMessage').textContent = message;
    successModal.show();
}

// Function to show status update success modal
function showStatusUpdateSuccessModal() {
    const statusUpdateSuccessModal = new bootstrap.Modal(document.getElementById('statusUpdateSuccessModal'));
    statusUpdateSuccessModal.show();
}

// Function to show delete confirmation modal
function showDeleteConfirmationModal(reportTypeId) {
    const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    document.getElementById('confirmDeleteBtn').onclick = function() {
        // Handle delete action here
        deleteConfirmationModal.hide();
    };
    deleteConfirmationModal.show();
}
</script>
@endpush
@endsection
