@extends('layouts.barangay')

@section('title', 'Overdue Reports')
@section('page-title', 'Overdue Reports')

@section('content')
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-circle text-danger me-2"></i>
                            Overdue Reports
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="input-group">
                                <select class="form-select" id="frequencyFilter" style="width: auto; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                    <option value="">All Frequencies</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="semestral">Semestral</option>
                                    <option value="annual">Annual</option>
                                </select>
                                <button class="btn btn-outline-secondary" type="button" id="filterButton">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($reports->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: rgba(var(--success-rgb), 0.1); display: flex; align-items: center; justify-content: center; color: var(--success); margin: 0 auto;">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h5 class="mb-2">No overdue reports</h5>
                            <p class="text-muted">You have submitted all required reports on time.</p>
                            <a href="{{ route('barangay.dashboard') }}" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-home me-1"></i>
                                Return to Dashboard
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Report</th>
                                        <th>Frequency</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reports as $report)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2" style="width: 32px; height: 32px; border-radius: 8px; background: var(--primary-light); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                                        <i class="fas fa-file-alt fa-sm"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 500; color: var(--dark);">{{ $report->name }}</div>
                                                        <small class="text-muted">{{ ucfirst($report->frequency) }} Report</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge frequency-{{ $report->frequency }}">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    {{ ucfirst($report->frequency) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span>{{ \Carbon\Carbon::parse($report->deadline)->format('M d, Y') }}</span>
                                                    <small class="text-danger">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ \Carbon\Carbon::parse($report->deadline)->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge overdue">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    Overdue
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button"
                                                            class="btn btn-sm"
                                                            style="background: var(--warning-light); color: var(--warning); border: none;"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#submitModal{{ $report->id }}">
                                                        <i class="fas fa-upload me-1"></i>
                                                        Submit Report
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Submit Modal -->
                                        <div class="modal fade" id="submitModal{{ $report->id }}" tabindex="-1" aria-labelledby="submitModalLabel{{ $report->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="submitModalLabel{{ $report->id }}">
                                                            Submit {{ $report->name }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('barangay.submissions.store') }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="hidden" name="report_type_id" value="{{ $report->id }}">
                                                        <input type="hidden" name="report_type" value="{{ $report->frequency }}">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="file{{ $report->id }}" class="form-label">Upload Report File</label>
                                                                <input type="file"
                                                                       class="form-control"
                                                                       id="file{{ $report->id }}"
                                                                       name="file"
                                                                       accept=".pdf,.doc,.docx,.xlsx"
                                                                       required>
                                                                <div class="form-text">Accepted formats: PDF, DOC, DOCX, XLSX (Max: 2MB)</div>
                                                            </div>

                                                            @if($report->frequency === 'weekly')
                                                                <div class="mb-3">
                                                                    <label class="form-label">Number of Clean-up Sites</label>
                                                                    <input type="number" class="form-control" name="num_of_clean_up_sites" min="0" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Number of Participants</label>
                                                                    <input type="number" class="form-control" name="num_of_participants" min="0" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Number of Barangays</label>
                                                                    <input type="number" class="form-control" name="num_of_barangays" min="0" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Total Volume</label>
                                                                    <input type="number" class="form-control" name="total_volume" min="0" step="0.01" required>
                                                                </div>
                                                            @elseif($report->frequency === 'monthly')
                                                                <div class="mb-3">
                                                                    <label class="form-label">Month</label>
                                                                    <select class="form-select" name="month" required>
                                                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                                            <option value="{{ $month }}">{{ $month }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            @elseif($report->frequency === 'quarterly')
                                                                <div class="mb-3">
                                                                    <label class="form-label">Quarter</label>
                                                                    <select class="form-select" name="quarter_number" required>
                                                                        <option value="1">First Quarter</option>
                                                                        <option value="2">Second Quarter</option>
                                                                        <option value="3">Third Quarter</option>
                                                                        <option value="4">Fourth Quarter</option>
                                                                    </select>
                                                                </div>
                                                            @elseif($report->frequency === 'semestral')
                                                                <div class="mb-3">
                                                                    <label class="form-label">Semester</label>
                                                                    <select class="form-select" name="sem_number" required>
                                                                        <option value="1">First Semester</option>
                                                                        <option value="2">Second Semester</option>
                                                                    </select>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-upload me-1"></i>
                                                                Submit Report
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-muted">
                                    Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} entries
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="perPageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ $reports->perPage() }} per page
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="perPageDropdown">
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 10]) }}">10 per page</a></li>
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 25]) }}">25 per page</a></li>
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 50]) }}">50 per page</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($reports->hasPages())
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm mb-0">
                                            {{-- Previous Page Link --}}
                                            @if($reports->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $reports->previousPageUrl() }}" rel="prev">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            @endif

                                            {{-- Pagination Elements --}}
                                            @foreach($reports->getUrlRange(1, $reports->lastPage()) as $page => $url)
                                                @if($page == $reports->currentPage())
                                                    <li class="page-item active">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                            {{-- Next Page Link --}}
                                            @if($reports->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $reports->nextPageUrl() }}" rel="next">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                    <span class="page-link">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Table styles */
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        .table > :not(caption) > * > * {
            padding: 1rem;
        }
        .table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Status badge styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1;
        }

        .status-badge.submitted {
            background-color: rgba(var(--success-rgb), 0.1);
            color: var(--success);
        }

        .status-badge.pending {
            background-color: rgba(var(--info-rgb), 0.1);
            color: var(--info);
        }

        .status-badge.approved {
            background-color: rgba(var(--success-rgb), 0.1);
            color: var(--success);
        }

        .status-badge.rejected {
            background-color: rgba(var(--danger-rgb), 0.1);
            color: var(--danger);
        }

        .status-badge.overdue {
            background-color: rgba(var(--danger-rgb), 0.1);
            color: var(--danger);
        }

        /* Frequency badge styles */
        .status-badge.frequency-weekly {
            background-color: rgba(var(--info-rgb), 0.1);
            color: var(--info);
        }

        .status-badge.frequency-monthly {
            background-color: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
        }

        .status-badge.frequency-quarterly {
            background-color: rgba(var(--success-rgb), 0.1);
            color: var(--success);
        }

        .status-badge.frequency-semestral {
            background-color: rgba(var(--warning-rgb), 0.1);
            color: var(--warning);
        }

        .status-badge.frequency-annual {
            background-color: rgba(var(--danger-rgb), 0.1);
            color: var(--danger);
        }

        /* Pagination styles */
        .pagination {
            margin-bottom: 0;
        }
        .pagination .page-link {
            padding: 0.375rem 0.75rem;
            color: #6c757d;
            background-color: #fff;
            border: 1px solid #dee2e6;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }

        /* Form and dropdown styles */
        .form-select {
            border-radius: 0.375rem;
        }
        .dropdown-menu {
            min-width: 8rem;
        }
        .dropdown-item {
            padding: 0.5rem 1rem;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        /* Modal styles */
        .modal-content {
            border-radius: 0.5rem;
        }
        .modal-header {
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        .modal-footer {
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .card-header .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }
            .card-header .d-flex > * {
                width: 100% !important;
                margin-bottom: 0.5rem;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
            .d-flex.justify-content-between > * {
                width: 100%;
            }
            .pagination {
                justify-content: center;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Filter functionality
            const frequencyFilter = document.getElementById('frequencyFilter');
            const table = document.querySelector('table');

            if (table) {
                const rows = table.getElementsByTagName('tr');

                function filterTable() {
                    const frequencyValue = frequencyFilter.value.toLowerCase();

                    for (let i = 1; i < rows.length; i++) {
                        const row = rows[i];
                        const cells = row.getElementsByTagName('td');
                        if (cells.length > 1) {
                            const frequency = cells[1].textContent.toLowerCase();

                            if (!frequencyValue || frequency.includes(frequencyValue)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    }
                }

                // Apply filter when the filter button is clicked
                const filterButton = document.getElementById('filterButton');
                if (filterButton) {
                    filterButton.addEventListener('click', filterTable);
                }

                // Also apply filter when the frequency filter changes
                frequencyFilter.addEventListener('change', filterTable);
            }

            // File input validation
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    const allowedTypes = ['.pdf', '.doc', '.docx', '.xlsx'];

                    if (file) {
                        // Check file size
                        if (file.size > maxSize) {
                            alert('File size must be less than 2MB');
                            this.value = '';
                            return;
                        }

                        // Check file type
                        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                        if (!allowedTypes.includes(fileExtension)) {
                            alert('Only PDF, DOC, DOCX, and XLSX files are allowed');
                            this.value = '';
                            return;
                        }
                    }
                });
            });

            // Handle form submission with loading state
            const submitForms = document.querySelectorAll('form[action*="submissions.store"]');
            submitForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        // Disable button and show loading state
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Submitting...';
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
