<table class="table">
    <thead>
        <tr>
            <th>BARANGAY</th>
            <th>REPORT TYPE</th>
            <th>FREQUENCY</th>
            <th>SUBMITTED</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reports as $report)
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <span class="me-2 text-primary">
                        <i class="fas fa-building"></i>
                    </span>
                    {{ $report->user->name }}
                </div>
            </td>
            <td>{{ $report->reportType->name }}</td>
            <td>
                <span class="badge bg-{{ $report->type == 'weekly' ? 'primary' : ($report->type == 'monthly' ? 'success' : ($report->type == 'quarterly' ? 'warning' : 'danger')) }}">
                    {{ ucfirst($report->type) }}
                </span>
            </td>
            <td>{{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y') }}</td>
            <td>
                @php
                    $displayStatus = 'Pending';
                    $badgeClass = 'bg-warning';
                    if ($report->status === 'approved') {
                        $displayStatus = 'Approved';
                        $badgeClass = 'bg-success';
                    } elseif ($report->status === 'resubmit') {
                        $displayStatus = 'Resubmit';
                        $badgeClass = 'bg-info';
                    } elseif ($report->status === 'resubmitted') {
                        $displayStatus = 'Resubmitted';
                        $badgeClass = 'bg-warning';
                    }
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $displayStatus }}</span>
            </td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#viewReportModal" 
                            data-report="{{ json_encode($report) }}"
                            data-type="{{ $report->type }}"
                            title="View Report">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addRemarksModal" 
                            data-report="{{ json_encode($report) }}"
                            data-type="{{ $report->type }}"
                            title="Add Remarks">
                        <i class="fas fa-comment"></i>
                    </button>
                    <a href="{{ route('admin.files.download', $report->id) }}" 
                       class="btn btn-sm btn-outline-success"
                       title="Download Files"
                       target="_blank">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-folder-open text-muted mb-2" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0">No submissions found.</p>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
