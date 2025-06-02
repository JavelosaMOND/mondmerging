@extends('facilitator.layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="page-title">
    <i class="fas fa-tachometer-alt"></i> Facilitator Dashboard
</h1>

<div class="row">
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <span class="fa-stack fa-2x">
                        <i class="fas fa-circle fa-stack-2x text-primary-light"></i>
                        <i class="fas fa-users fa-stack-1x text-primary"></i>
                    </span>
                </div>
                <h5 class="card-title">Barangays</h5>
                <p class="card-text fs-2 fw-bold">{{ $barangays->count() }}</p>
                <a href="{{ route('facilitator.view-submissions') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> View Reports
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <span class="fa-stack fa-2x">
                        <i class="fas fa-circle fa-stack-2x text-success-light"></i>
                        <i class="fas fa-file-alt fa-stack-1x text-success"></i>
                    </span>
                </div>
                <h5 class="card-title">Recent Submissions</h5>
                <p class="card-text fs-2 fw-bold">{{ $recentReports->count() }}</p>
                <a href="{{ route('facilitator.view-submissions') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-list"></i> View All
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <span class="fa-stack fa-2x">
                        <i class="fas fa-circle fa-stack-2x text-warning-light"></i>
                        <i class="fas fa-clock fa-stack-1x text-warning"></i>
                    </span>
                </div>
                <h5 class="card-title">Upcoming Deadlines</h5>
                <p class="card-text fs-2 fw-bold">{{ count($upcomingDeadlines) }}</p>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#deadlinesModal">
                    <i class="fas fa-calendar"></i> View Deadlines
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <span class="fa-stack fa-2x">
                        <i class="fas fa-circle fa-stack-2x text-info-light"></i>
                        <i class="fas fa-layer-group fa-stack-1x text-info"></i>
                    </span>
                </div>
                <h5 class="card-title">My Clusters</h5>
                <p class="card-text fs-2 fw-bold">{{ Auth::user()->assignedClusters()->count() }}</p>
                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#clustersModal">
                    <i class="fas fa-th-large"></i> View Clusters
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Recent Report Submissions</h5>
                <a href="{{ route('facilitator.view-submissions') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-list"></i> View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>BARANGAY</th>
                                <th>REPORT TYPE</th>
                                <th>SUBMITTED</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentReports as $report)
                            <tr>
                                <td>{{ $report->barangay_name }}</td>
                                <td>{{ $report->report_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y') }}</td>
                                <td>
                                    @if($report->remarks)
                                        <span class="badge bg-success">Reviewed</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('facilitator.view-submissions') }}?barangay={{ $report->user_id }}&type={{ $report->type }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No recent submissions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Assigned Barangays</h5>
                <a href="{{ route('facilitator.view-submissions') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> View Reports
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($barangays->take(5) as $barangay)
                    <a href="{{ route('facilitator.view-submissions') }}?barangay={{ $barangay->id }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-building text-primary me-2"></i>
                            {{ $barangay->name }}
                        </div>
                        <span class="badge bg-{{ $barangay->is_active ? 'success' : 'danger' }}">
                            {{ $barangay->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </a>
                    @empty
                    <div class="list-group-item text-center py-4">
                        <p class="text-muted mb-0">No barangays found.</p>
                    </div>
                    @endforelse
                </div>

                @if($barangays->count() > 5)
                <div class="card-footer text-center">
                    <a href="{{ route('facilitator.view-submissions') }}" class="text-primary">
                        View all {{ $barangays->count() }} barangays
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Deadlines Modal -->
<div class="modal fade" id="deadlinesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upcoming Deadlines</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    @forelse($upcomingDeadlines as $deadline)
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">{{ $deadline['report_type'] }}</h5>
                            <small class="text-{{ $deadline['days_remaining'] <= 3 ? 'danger' : ($deadline['days_remaining'] <= 7 ? 'warning' : 'success') }}">
                                {{ $deadline['days_remaining'] }} days left
                            </small>
                        </div>
                        <p class="mb-1">Frequency: {{ ucfirst($deadline['frequency']) }}</p>
                        <small>Due on: {{ $deadline['deadline']->format('F d, Y') }}</small>
                    </div>
                    @empty
                    <div class="text-center py-3">
                        <p class="text-muted">No upcoming deadlines found.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Clusters Modal -->
<div class="modal fade" id="clustersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">My Assigned Clusters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    @forelse(Auth::user()->assignedClusters as $cluster)
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">{{ $cluster->name }}</h5>
                            <span class="badge bg-{{ $cluster->is_active ? 'success' : 'danger' }}">
                                {{ $cluster->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="mb-1">{{ $cluster->description }}</p>
                        <small>Barangays: {{ $cluster->barangays->count() }}</small>
                    </div>
                    @empty
                    <div class="text-center py-3">
                        <p class="text-muted">No clusters assigned to you.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
