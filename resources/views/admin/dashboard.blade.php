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
        max-height: 450px;
        overflow-y: auto;
        padding: 0.5rem;
    }

    .recent-activity::-webkit-scrollbar {
        width: 6px;
    }

    .recent-activity::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .recent-activity::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 10px;
    }

    .recent-activity::-webkit-scrollbar-thumb:hover {
        background: #ccc;
    }

    .activity-item {
        padding: 1.25rem;
        margin-bottom: 1rem;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .activity-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 4px;
        background: var(--primary);
    }

    .activity-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .activity-item.late::before {
        background: var(--danger);
    }

    .activity-item.ontime::before {
        background: var(--success);
    }

    .activity-meta {
        font-size: 0.8125rem;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .activity-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--gray-800);
        font-size: 1rem;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1;
        transition: all 0.2s ease;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .status-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .status-pill i {
        margin-right: 0.375rem;
        font-size: 0.75rem;
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-status {
        margin-left: 1rem;
    }

    .chart-container {
        position: relative;
        height: 260px;
    }

    .stat-items {
        margin-top: 1.5rem;
    }

    .stat-item {
        transition: all 0.2s ease;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .stat-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .stat-icon-wrapper {
        transition: all 0.2s ease;
    }

    .stat-item:hover .stat-icon-wrapper {
        transform: scale(1.1);
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--gray-700);
        font-weight: 500;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: 600;
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
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-history me-2" style="color: var(--primary);"></i>
                    Recent Submissions
                </h5>
                <a href="{{ route('admin.view.submissions') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-eye me-1"></i> View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="recent-activity">
                    @forelse($recentSubmissions as $submission)
                    <div class="activity-item {{ $submission->is_late ? 'late' : 'ontime' }}">
                        <div class="d-flex justify-content-between">
                            <div class="activity-content">
                                <h6 class="activity-title">{{ $submission->report_type }}</h6>
                                <div class="activity-meta">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="d-flex align-items-center">
                                            <i class="fas fa-user-circle me-1"></i>
                                            {{ $submission->submitter }}
                                        </span>
                                        <span class="d-flex align-items-center">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            {{ \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="activity-status d-flex align-items-center gap-2">
                                @php
                                    // Status badge configuration
                                    $statusConfig = [
                                        'submitted' => [
                                            'icon' => 'fa-check-circle',
                                            'color' => 'var(--success)',
                                            'bgColor' => 'rgba(25, 135, 84, 0.1)'
                                        ],
                                        'no submission' => [
                                            'icon' => 'fa-times-circle',
                                            'color' => 'var(--danger)',
                                            'bgColor' => 'rgba(220, 53, 69, 0.1)'
                                        ],
                                        'pending' => [
                                            'icon' => 'fa-clock',
                                            'color' => 'var(--warning)',
                                            'bgColor' => 'rgba(255, 193, 7, 0.1)'
                                        ]
                                    ];

                                    // Default values if status is not in the config
                                    $statusData = $statusConfig[$submission->status] ?? [
                                        'icon' => 'fa-info-circle',
                                        'color' => 'var(--gray-600)',
                                        'bgColor' => 'rgba(108, 117, 125, 0.1)'
                                    ];

                                    // Timeliness configuration
                                    $timelinessData = $submission->is_late
                                        ? [
                                            'icon' => 'fa-exclamation-circle',
                                            'text' => 'Late',
                                            'color' => 'var(--danger)',
                                            'bgColor' => 'rgba(220, 53, 69, 0.1)'
                                        ]
                                        : [
                                            'icon' => 'fa-check-circle',
                                            'text' => 'On Time',
                                            'color' => 'var(--success)',
                                            'bgColor' => 'rgba(25, 135, 84, 0.1)'
                                        ];
                                @endphp

                                <div class="status-pill" style="background-color: {{ $statusData['bgColor'] }}; color: {{ $statusData['color'] }}">
                                    <i class="fas {{ $statusData['icon'] }}"></i>
                                    {{ ucfirst($submission->status) }}
                                </div>

                                <div class="status-pill" style="background-color: {{ $timelinessData['bgColor'] }}; color: {{ $timelinessData['color'] }}">
                                    <i class="fas {{ $timelinessData['icon'] }}"></i>
                                    {{ $timelinessData['text'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <div class="mb-3" style="color: var(--gray-300);">
                            <i class="fas fa-inbox fa-3x"></i>
                        </div>
                        <p class="text-muted mb-0">No recent submissions</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Statistics -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-pie me-2" style="color: var(--primary);"></i>
                    Submission Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container mb-4">
                    <canvas id="submissionChart"></canvas>
                </div>
                <div class="mt-4">
                    @php
                        $reportTypes = [
                            [
                                'name' => 'Weekly Reports',
                                'count' => $weeklyCount,
                                'icon' => 'fa-clock',
                                'color' => 'var(--primary)'
                            ],
                            [
                                'name' => 'Monthly Reports',
                                'count' => $monthlyCount,
                                'icon' => 'fa-calendar-day',
                                'color' => 'var(--danger)'
                            ],
                            [
                                'name' => 'Quarterly Reports',
                                'count' => $quarterlyCount,
                                'icon' => 'fa-chart-pie',
                                'color' => 'var(--warning)'
                            ],
                            [
                                'name' => 'Annual Reports',
                                'count' => $annualCount,
                                'icon' => 'fa-chart-line',
                                'color' => 'var(--success)'
                            ]
                        ];
                    @endphp

                    <div class="stat-items">
                        @foreach($reportTypes as $type)
                        <div class="stat-item d-flex justify-content-between align-items-center p-3 mb-3 rounded"
                            style="background-color: {{ $type['color'] }}08; border-left: 3px solid {{ $type['color'] }}">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-wrapper me-3 d-flex align-items-center justify-content-center rounded-circle"
                                    style="background-color: {{ $type['color'] }}15; width: 36px; height: 36px;">
                                    <i class="fas {{ $type['icon'] }}" style="color: {{ $type['color'] }}"></i>
                                </div>
                                <span class="stat-label">{{ $type['name'] }}</span>
                            </div>
                            <span class="stat-value fw-bold" style="color: {{ $type['color'] }}">{{ $type['count'] }}</span>
                        </div>
                        @endforeach
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
                    'rgba(13, 110, 253, 0.8)',  // Primary (blue)
                    'rgba(220, 53, 69, 0.8)',   // Danger (red)
                    'rgba(255, 193, 7, 0.8)',   // Warning (yellow)
                    'rgba(25, 135, 84, 0.8)'    // Success (green)
                ],
                borderColor: [
                    'rgba(13, 110, 253, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(25, 135, 84, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#666',
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    boxPadding: 6,
                    usePointStyle: true
                }
            },
            elements: {
                arc: {
                    borderWidth: 2
                }
            }
        }
    });
});
</script>
@endpush
@endsection
