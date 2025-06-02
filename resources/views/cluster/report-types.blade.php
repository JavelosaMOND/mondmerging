@extends('cluster.layouts.app')

@section('title', 'Report Types')

@push('styles')
<style>
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
    }
    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
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
    .is-invalid {
        border-color: var(--danger) !important;
    }
    .invalid-feedback {
        display: block;
        color: var(--danger);
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="page-title">
            <i class="fas fa-file-alt"></i>
            Report Types Management
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
            Report Types
        </h5>
        <div class="d-flex gap-2">
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control search-box" id="reportTypeSearch" placeholder="Search...">
            </div>
            <div class="input-group" style="width: 200px;">
                <span class="input-group-text">
                    <i class="fas fa-filter"></i>
                </span>
                <select class="form-select" id="frequencyFilter">
                    <option value="">All Frequencies</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="annual">Annual</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createReportTypeModal">
                <i class="fas fa-plus"></i>
                <span>Add Report Type</span>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Frequency</th>
                        <th>Deadline</th>
                        <th>Allowed File Types</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportTypes as $reportType)
                    <tr>
                        <td>{{ $reportType->name }}</td>
                        <td>{{ ucfirst($reportType->frequency) }}</td>
                        <td>{{ $reportType->deadline ? \Carbon\Carbon::parse($reportType->deadline)->format('M d, Y h:i A') : 'N/A' }}</td>
                        <td>
                            @if($reportType->allowed_file_types)
                                @foreach($reportType->allowed_file_types as $type)
                                    <span class="badge me-1" style="background: var(--primary-light); color: var(--primary);">
                                        {{ strtoupper($type) }}
                                    </span>
                                @endforeach
                            @else
                                <span style="color: var(--gray-600);">No restrictions</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editReportTypeModal{{ $reportType->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="{{ route('cluster.report-types.destroy', $reportType->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this report type?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editReportTypeModal{{ $reportType->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-edit me-2" style="color: var(--primary);"></i>Edit Report Type</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('cluster.report-types.update', $reportType->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" name="name" value="{{ $reportType->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description">{{ $reportType->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Frequency</label>
                                            <select class="form-select" name="frequency" required>
                                                <option value="weekly" {{ $reportType->frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                <option value="monthly" {{ $reportType->frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                <option value="quarterly" {{ $reportType->frequency == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                <option value="annual" {{ $reportType->frequency == 'annual' ? 'selected' : '' }}>Annual</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Deadline</label>
                                            <input type="datetime-local" class="form-control" name="deadline" value="{{ $reportType->deadline ? \Carbon\Carbon::parse($reportType->deadline)->format('Y-m-d\TH:i') : '' }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Allowed File Types</label>
                                            <select class="form-select" name="allowed_file_types[]" multiple>
                                                <option value="pdf" {{ is_array($reportType->allowed_file_types) && in_array('pdf', $reportType->allowed_file_types) ? 'selected' : '' }}>PDF</option>
                                                <option value="docx" {{ is_array($reportType->allowed_file_types) && in_array('docx', $reportType->allowed_file_types) ? 'selected' : '' }}>DOCX</option>
                                                <option value="xlsx" {{ is_array($reportType->allowed_file_types) && in_array('xlsx', $reportType->allowed_file_types) ? 'selected' : '' }}>XLSX</option>
                                            </select>
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Create Modal -->
<div class="modal fade" id="createReportTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2" style="color: var(--primary);"></i>Add Report Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cluster.report-types.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Frequency</label>
                        <select class="form-select" name="frequency" required>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="datetime-local" class="form-control" name="deadline" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Allowed File Types</label>
                        <select class="form-select" name="allowed_file_types[]" multiple>
                            <option value="pdf">PDF</option>
                            <option value="docx">DOCX</option>
                            <option value="xlsx">XLSX</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Search functionality
    document.getElementById('reportTypeSearch').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const name = row.querySelector('td:first-child').textContent.toLowerCase();
            row.style.display = name.includes(searchText) ? '' : 'none';
        });
    });
    // Frequency filter
    document.getElementById('frequencyFilter').addEventListener('change', function() {
        const freq = this.value;
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const frequency = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
            if (!freq || frequency === freq) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush 