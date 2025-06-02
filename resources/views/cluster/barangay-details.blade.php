@extends('cluster.layouts.app')

@section('title', 'Barangay Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">
                    <i class="fas fa-building"></i>
                    {{ $barangay->name }}
                </h2>
                <a href="{{ route('cluster.barangays') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Barangays
                </a>
            </div>
        </div>
    </div>

    <!-- Barangay Info Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Barangay Information</h5>
                    <div class="mb-3">
                        <label class="text-muted">Email</label>
                        <p class="mb-0">{{ $barangay->email }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted">Status</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $barangay->is_active ? 'success' : 'danger' }}">
                                {{ $barangay->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="text-muted">Last Login</label>
                        <p class="mb-0">{{ $barangay->last_login_at ? $barangay->last_login_at->diffForHumans() : 'Never' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Submitted Reports</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Submitted Date</th>
                                    <th>Status</th>
                                    <th>File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                <tr>
                                    <td>{{ $report->reportType->name }}</td>
                                    <td>{{ $report->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $report->status === 'approved' ? 'success' : ($report->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('cluster.files.view', $report->id) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('cluster.files.download', $report->id) }}" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </td>
                                    <td>
                                        @if($report->status === 'pending')
                                        <form action="{{ route('cluster.reports.update', $report->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('cluster.reports.update', $report->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No reports submitted yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 