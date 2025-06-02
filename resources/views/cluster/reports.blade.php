<!-- @if(request()->is('facilitator/*')) -->
    @extends('cluster.layouts.app_facilitator')
<!-- @else
    @extends('cluster.layouts.app')
@endif -->

@section('title', 'Report Submissions')

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
    .status-badge.pending {
        background-color: var(--warning-light);
        color: var(--warning);
    }
    .status-badge.approved {
        background-color: var(--success-light);
        color: var(--success);
    }
    .status-badge.rejected {
        background-color: var(--danger-light);
        color: var(--danger);
    }
    .action-btn {
        min-width: 90px;
        margin-bottom: 0.25rem;
    }
    .search-box {
        border-radius: 0.375rem;
    }
    .search-box:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
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
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-alt me-2" style="color: var(--primary);"></i>
            All Submissions
        </h5>
        <form id="filterForm" class="d-flex gap-2" method="GET" action="">
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control search-box" id="searchInput" placeholder="Search...">
            </div>
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-filter"></i>
                </span>
                <select class="form-select" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="annual">Annual</option>
                </select>
            </div>
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-filter"></i>
                </span>
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-clock"></i>
                </span>
                <select class="form-select" id="timelinessFilter">
                    <option value="">All Submissions</option>
                    <option value="late">Late</option>
                    <option value="ontime">On Time</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                <i class="fas fa-filter"></i>
                Apply Filters
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Report Type</th>
                        <th>Submitted By</th>
                        <th>Submitted At</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="width: 40px; height: 40px; border-radius: 10px; background: var(--primary-light); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: var(--dark);">{{ $report->reportType->name }}</div>
                                    <div class="text-muted" style="font-size: 0.9em;">
                                        {{ ucfirst($report->reportType->frequency) }} Report
                                        @if($report->file_path)
                                            <span class="ms-2"><i class="fas fa-file"></i> {{ strtoupper(pathinfo($report->file_name, PATHINFO_EXTENSION)) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $report->user->name }}</td>
                        <td>{{ $report->created_at->format('M d, Y h:i A') }}</td>
                        <td>{{ $report->reportType->deadline ? \Carbon\Carbon::parse($report->reportType->deadline)->format('M d, Y h:i A') : 'N/A' }}</td>
                        <td>
                            <span class="status-badge {{ $report->status }}">{{ ucfirst($report->status) }}</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary action-btn" data-bs-toggle="modal" data-bs-target="#viewReportModal{{ $report->id }}">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info action-btn" data-bs-toggle="modal" data-bs-target="#updateReportModal{{ $report->id }}">
                                <i class="fas fa-edit"></i> Update
                            </button>
                        </td>
                    </tr>
                    <!-- View Modal -->
                    <div class="modal fade" id="viewReportModal{{ $report->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-file-alt me-2" style="color: var(--primary);"></i>View Submission</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2"><strong>Report Type</strong><br>{{ $report->reportType->name }}</div>
                                    <div class="mb-2"><strong>Submitted By</strong><br>{{ $report->user->name }}</div>
                                    <div class="mb-2"><strong>Submitted At</strong><br>{{ $report->created_at->format('M d, Y h:i A') }}
                                        @php
                                            $deadline = $report->reportType->deadline ? \Carbon\Carbon::parse($report->reportType->deadline) : null;
                                            $isLate = $deadline && $report->created_at->gt($deadline);
                                            $fileExt = strtolower(pathinfo($report->file_name, PATHINFO_EXTENSION));
                                        @endphp
                                        @if($deadline)
                                            <span class="badge ms-2" style="background: {{ $isLate ? 'var(--danger-light)' : 'var(--success-light)' }}; color: {{ $isLate ? 'var(--danger)' : 'var(--success)' }};">
                                                {{ $isLate ? 'Late' : 'On Time' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mb-2"><strong>Deadline</strong><br>{{ $deadline ? $deadline->format('M d, Y h:i A') : 'N/A' }}</div>
                                    <div class="mb-2"><strong>Status</strong><br>
                                        <span class="status-badge {{ $report->status }}">{{ ucfirst($report->status) }}</span>
                                    </div>
                                    <div class="mb-2"><strong>File</strong><br>
                                        <button type="button" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#filePreviewModal{{ $report->id }}">
                                            <i class="fas fa-eye"></i> View File
                                        </button>
                                        <a href="{{ route('cluster.files.download', $report->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> Download File
                                        </a>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- File Preview Modal -->
                    <div class="modal fade" id="filePreviewModal{{ $report->id }}" tabindex="-1">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-file-alt me-2" style="color: var(--primary);"></i>{{ $report->file_name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" style="min-height: 70vh;">
                                    @if($fileExt === 'pdf')
                                        <iframe src="{{ asset('storage/' . $report->file_path) }}" width="100%" height="600px" style="border: none;"></iframe>
                                    @elseif(in_array($fileExt, ['docx','xlsx']))
                                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode(asset('storage/' . $report->file_path)) }}" width="100%" height="600px" frameborder="0"></iframe>
                                    @else
                                        <div class="alert alert-info">Preview not available for this file type. Please download to view.</div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ route('cluster.files.download', $report->id) }}" class="btn btn-info">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Update Modal -->
                    <div class="modal fade" id="updateReportModal{{ $report->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-edit me-2" style="color: var(--primary);"></i>Update Report Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="updateForm{{ $report->id }}" action="{{ auth()->user()->role === 'Facilitator' ? route('facilitator.reports.update', $report->id) : route('cluster.reports.update', $report->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select status-select" name="status" required>
                                                <option value="approved" {{ $report->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="resubmit" {{ $report->status == 'resubmit' ? 'selected' : '' }}>Resubmit</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea class="form-control" name="remarks">{{ $report->remarks }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var statusSelect = document.querySelector('#updateReportModal{{ $report->id }} .status-select');
                        var freqFields = document.getElementById('frequencyFields{{ $report->id }}');
                        if (!statusSelect || !freqFields) return;
                        function toggleFreqFields() {
                            if (statusSelect.value === 'resubmit') {
                                freqFields.style.display = '';
                            } else {
                                freqFields.style.display = 'none';
                            }
                        }
                        statusSelect.addEventListener('change', toggleFreqFields);
                        toggleFreqFields();
                    });
                    </script>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No reports found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    });
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        const status = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            if (!status) {
                row.style.display = '';
                return;
            }
            const statusCell = row.querySelector('td:nth-child(5) .status-badge');
            const reportStatus = statusCell ? statusCell.textContent.trim().toLowerCase() : '';
            row.style.display = reportStatus === status ? '' : 'none';
        });
    });
    // Type filter
    document.getElementById('typeFilter').addEventListener('change', function() {
        const type = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const typeCell = row.querySelector('td:nth-child(1) .text-muted');
            const reportType = typeCell ? typeCell.textContent.trim().toLowerCase() : '';
            row.style.display = !type || reportType.includes(type) ? '' : 'none';
        });
    });
    // Timeliness filter (dummy, for UI match)
    document.getElementById('timelinessFilter').addEventListener('change', function() {
        // You can implement actual logic if you have timeliness data
    });
    // Apply Filters button (just triggers all filters)
    document.getElementById('applyFiltersBtn').addEventListener('click', function(e) {
        document.getElementById('searchInput').dispatchEvent(new Event('keyup'));
        document.getElementById('statusFilter').dispatchEvent(new Event('change'));
        document.getElementById('typeFilter').dispatchEvent(new Event('change'));
        document.getElementById('timelinessFilter').dispatchEvent(new Event('change'));
    });
</script>
@endpush 