@extends('cluster.layouts.app_facilitator')

@section('title', 'Barangays')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="page-title">
                <i class="fas fa-building"></i>
                Assigned Barangays
            </h2>
        </div>
    </div>

    <!-- Barangays Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Barangay List</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Barangay Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Last Report</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($barangays as $barangay)
                                <tr>
                                    <td>{{ $barangay->name }}</td>
                                    <td>{{ $barangay->email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $barangay->is_active ? 'success' : 'danger' }}">
                                            {{ $barangay->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $barangay->last_login_at ? $barangay->last_login_at->diffForHumans() : 'Never' }}</td>
                                    <td>
                                        @php
                                            $lastReport = $barangay->weeklyReports->concat($barangay->monthlyReports)
                                                ->concat($barangay->quarterlyReports)
                                                ->concat($barangay->annualReports)
                                                ->sortByDesc('created_at')->first();
                                        @endphp
                                        {{ $lastReport ? $lastReport->created_at->format('M d, Y') : 'No reports yet' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('cluster.barangays.show', $barangay->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No barangays assigned to this cluster.</td>
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

            const statusCell = row.querySelector('td:nth-child(3)');
            const isActive = statusCell.textContent.trim().toLowerCase() === 'active';
            row.style.display = (status === 'active' && isActive) || (status === 'inactive' && !isActive) ? '' : 'none';
        });
    });
</script>
@endpush 