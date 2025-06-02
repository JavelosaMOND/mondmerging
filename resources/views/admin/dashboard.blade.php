@extends('admin.layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .stat-card {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0.5rem 0;
    }

    .stat-label {
        color: var(--gray-600);
        font-size: 0.875rem;
    }

    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-item {
        padding: 1rem;
        border-left: 3px solid var(--primary);
        margin-bottom: 1rem;
        background: var(--light);
        border-radius: 0.5rem;
    }

    .activity-item.late {
        border-left-color: var(--danger);
    }

    .activity-item.ontime {
        border-left-color: var(--success);
    }

    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="page-title">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </h2>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: var(--primary-light); color: var(--primary);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-value">{{ $totalSubmissions }}</div>
                        <div class="stat-label">Total Submissions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-value">{{ $submittedReports }}</div>
                        <div class="stat-label">Submitted Reports</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-value">{{ $noSubmissionReports }}</div>
                        <div class="stat-label">No Submission Reports</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: var(--danger-light); color: var(--danger);">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-value">{{ $lateSubmissions }}</div>
                        <div class="stat-label">Late Submissions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Submissions -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2" style="color: var(--primary);"></i>
                    Recent Submissions
                </h5>
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    @forelse($recentSubmissions as $submission)
                    <div class="activity-item {{ $submission->is_late ? 'late' : 'ontime' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $submission->report_type }}</h6>
                                <p class="mb-1 text-muted">
                                    Submitted by {{ $submission->submitter }}
                                </p>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y h:i A') }}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $submission->status === 'submitted' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($submission->status) }}
                                </span>
                                @if($submission->is_late)
                                    <span class="badge bg-danger ms-2">Late</span>
                                @else
                                    <span class="badge bg-success ms-2">On Time</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x mb-3" style="color: var(--gray-400);"></i>
                        <p class="text-muted">No recent submissions</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Statistics -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2" style="color: var(--primary);"></i>
                    Submission Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="submissionChart"></canvas>
                </div>
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Weekly Reports</span>
                        <span class="fw-bold">{{ $weeklyCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Monthly Reports</span>
                        <span class="fw-bold">{{ $monthlyCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Quarterly Reports</span>
                        <span class="fw-bold">{{ $quarterlyCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Annual Reports</span>
                        <span class="fw-bold">{{ $annualCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Submission Statistics Chart
    const ctx = document.getElementById('submissionChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Weekly', 'Monthly', 'Quarterly', 'Annual'],
            datasets: [{
                data: [
                    {{ $weeklyCount }},
                    {{ $monthlyCount }},
                    {{ $quarterlyCount }},
                    {{ $annualCount }}
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
@endsection
