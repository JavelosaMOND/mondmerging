@extends('facilitator.layouts.app')

@section('title', 'Barangay Management')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2 class="page-title">
            <i class="fas fa-users"></i>
            Barangay Management
        </h2>
    </div>
    <div class="col text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBarangayModal">
            <i class="fas fa-plus"></i> Add Barangay
        </button>
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
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search barangays...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="clusterFilter">
                    <option value="">All Clusters</option>
                    @foreach($clusters as $cluster)
                        <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Cluster</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangays as $barangay)
                    <tr data-cluster="{{ $barangay->cluster_id }}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                    {{ substr($barangay->name, 0, 1) }}
                                </div>
                                {{ $barangay->name }}
                            </div>
                        </td>
                        <td>{{ $barangay->email }}</td>
                        <td>{{ $barangay->cluster->name ?? 'None' }}</td>
                        <td>
                            <span class="badge bg-{{ $barangay->is_active ? 'success' : 'danger' }}">
                                {{ $barangay->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editBarangayModal" 
                                        data-barangay="{{ json_encode($barangay) }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-{{ $barangay->is_active ? 'danger' : 'success' }}" 
                                        onclick="confirmStatusChange({{ $barangay->id }}, {{ $barangay->is_active ? 'false' : 'true' }})">
                                    <i class="fas fa-{{ $barangay->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No barangays found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Barangay Modal -->
<div class="modal fade" id="addBarangayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('facilitator.barangays.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Barangay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign to Cluster</label>
                        <select class="form-select" name="cluster_id" required>
                            <option value="">Select Cluster</option>
                            @foreach($clusters as $cluster)
                                <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Barangay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Barangay Modal -->
<div class="modal fade" id="editBarangayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBarangayForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barangay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign to Cluster</label>
                        <select class="form-select" name="cluster_id" id="editClusterSelect" required>
                            <option value="">Select Cluster</option>
                            @foreach($clusters as $cluster)
                                <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Barangay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Change Confirmation Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="statusChangeMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="statusChangeForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        filterTable();
    });
    
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        filterTable();
    });
    
    // Cluster filter
    document.getElementById('clusterFilter').addEventListener('change', function() {
        filterTable();
    });
    
    function filterTable() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        const clusterId = document.getElementById('clusterFilter').value;
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const statusCell = row.querySelector('td:nth-child(4)');
            const isActive = statusCell.textContent.trim().toLowerCase() === 'active';
            const rowClusterId = row.getAttribute('data-cluster');
            
            let showRow = text.includes(searchText);
            
            if (status && showRow) {
                showRow = (status === 'active' && isActive) || (status === 'inactive' && !isActive);
            }
            
            if (clusterId && showRow) {
                showRow = rowClusterId === clusterId;
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    // Edit barangay modal handling
    document.getElementById('editBarangayModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const barangay = JSON.parse(button.getAttribute('data-barangay'));
        const form = this.querySelector('form');
        
        form.action = "{{ route('facilitator.barangays.update', '') }}/" + barangay.id;
        document.getElementById('editName').value = barangay.name;
        document.getElementById('editEmail').value = barangay.email;
        document.getElementById('editClusterSelect').value = barangay.cluster_id;
    });
    
    // Status change confirmation
    function confirmStatusChange(barangayId, newStatus) {
        const modal = document.getElementById('statusChangeModal');
        const message = document.getElementById('statusChangeMessage');
        const form = document.getElementById('statusChangeForm');
        
        message.textContent = `Are you sure you want to ${newStatus ? 'activate' : 'deactivate'} this barangay?`;
        form.action = "{{ route('facilitator.barangays.toggle-status', '') }}/" + barangayId;
        
        new bootstrap.Modal(modal).show();
    }
</script>
@endpush
@endsection
