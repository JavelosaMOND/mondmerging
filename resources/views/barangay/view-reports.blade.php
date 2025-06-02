@extends('layouts.barangay')

@section('title', 'View Reports')
@section('page-title', 'View Reports')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Reports</h5>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="width: 300px;">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search reports...">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                    <li><a class="dropdown-item" href="#" data-filter="all">All Reports</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="approved">Approved</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="pending">Pending</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="rejected">Rejected</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($reports->isEmpty())
                        <p class="text-muted">No reports found</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Report Type</th>
                                        <th>Submission Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports as $report)
                                        <tr class="report-row" data-status="{{ $report->status }}">
                                            <td>{{ $report->reportType->name }}</td>
                                            <td>{{ $report->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $report->status === 'approved' ? 'success' : ($report->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($report->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($report->file_path)
                                                    <a href="{{ route('barangay.files.download', $report->id) }}" class="btn btn-sm btn-info" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                                @if($report->remarks)
                                                    <button class="btn btn-sm btn-secondary" title="View Remarks" data-bs-toggle="modal" data-bs-target="#remarksModal{{ $report->id }}">
                                                        <i class="fas fa-comment"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($report->remarks)
                                            <div class="modal fade" id="remarksModal{{ $report->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Remarks</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{ $report->remarks }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ ($reports->currentPage() - 1) * $reports->perPage() + 1 }} to {{ min($reports->currentPage() * $reports->perPage(), $reports->total()) }} of {{ $reports->total() }} entries
                            </div>
                            <div>
                                {{ $reports->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const filterLinks = document.querySelectorAll('[data-filter]');
            const reportRows = document.querySelectorAll('.report-row');

            function filterReports() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeFilter = document.querySelector('.dropdown-item.active')?.dataset.filter || 'all';

                reportRows.forEach(row => {
                    const reportText = row.textContent.toLowerCase();
                    const reportStatus = row.dataset.status;
                    const matchesSearch = reportText.includes(searchTerm);
                    const matchesFilter = activeFilter === 'all' || reportStatus === activeFilter;

                    row.style.display = matchesSearch && matchesFilter ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterReports);
            searchButton.addEventListener('click', filterReports);

            filterLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    filterLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    filterReports();
                });
            });
        });
    </script>
    @endpush
@endsection
