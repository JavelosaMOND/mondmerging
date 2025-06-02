@extends('admin.layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="page-title">
                <i class="fas fa-users"></i>
                User Management
            </h2>
        </div>
        <div class="col text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus"></i> Add User
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

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                    </div>
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Cluster</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-2">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'cluster' ? 'info' : 'success' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @if(($user->role === 'barangay' || $user->user_type === 'barangay') && $user->cluster)
                                    {{ $user->cluster->name }}
                                @elseif(($user->role === 'facilitator' || $user->user_type === 'facilitator'))
                                    @php
                                        $assignedClusters = $user->assignedClusters()->pluck('name')->toArray();
                                    @endphp
                                    @if(count($assignedClusters) > 0)
                                        <span class="badge bg-info" data-bs-toggle="tooltip" data-bs-placement="top"
                                              title="{{ implode(', ', $assignedClusters) }}">
                                            {{ count($assignedClusters) }} cluster(s)
                                        </span>
                                    @else
                                        <span class="badge bg-warning">No clusters</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal"
                                            data-user="{{ json_encode($user) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-{{ $user->is_active ? 'danger' : 'success' }}"
                                            onclick="confirmStatusChange({{ $user->id }}, {{ $user->is_active ? 'false' : 'true' }})">
                                        <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
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
                        <label class="form-label">Role</label>
                        <select class="form-select" name="user_type" id="userTypeSelect" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="facilitator">Facilitator</option>
                            <option value="barangay">Barangay</option>
                        </select>
                    </div>
                    <!-- Barangay Cluster Assignment -->
                    <div class="mb-3" id="barangayClusterContainer" style="display: none;">
                        <label class="form-label">Assign to Cluster <span class="text-danger">*</span></label>
                        <select class="form-select" name="cluster_id" id="clusterSelect">
                            <option value="">Select Cluster</option>
                            @foreach(App\Models\Cluster::where('is_active', true)->get() as $cluster)
                                <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-danger">Required for Barangay users</div>
                    </div>

                    <!-- Facilitator Cluster Assignments -->
                    <div class="mb-3" id="facilitatorClustersContainer" style="display: none;">
                        <label class="form-label">Assign to Clusters <span class="text-danger">*</span></label>
                        <div class="card">
                            <div class="card-body">
                                @foreach(App\Models\Cluster::where('is_active', true)->get() as $cluster)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="clusters[]" value="{{ $cluster->id }}" id="cluster_{{ $cluster->id }}">
                                    <label class="form-check-label" for="cluster_{{ $cluster->id }}">
                                        {{ $cluster->name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-text text-danger">Select at least one cluster for Facilitator users</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
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
                        <label class="form-label">Role</label>
                        <select class="form-select" name="user_type" id="editUserTypeSelect" required>
                            <option value="admin">Admin</option>
                            <option value="facilitator">Facilitator</option>
                            <option value="barangay">Barangay</option>
                        </select>
                    </div>
                    <!-- Barangay Cluster Assignment -->
                    <div class="mb-3" id="editBarangayClusterContainer" style="display: none;">
                        <label class="form-label">Assign to Cluster <span class="text-danger">*</span></label>
                        <select class="form-select" name="cluster_id" id="editClusterSelect">
                            <option value="">Select Cluster</option>
                            @foreach(App\Models\Cluster::where('is_active', true)->get() as $cluster)
                                <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-danger">Required for Barangay users</div>
                    </div>

                    <!-- Facilitator Cluster Assignments -->
                    <div class="mb-3" id="editFacilitatorClustersContainer" style="display: none;">
                        <label class="form-label">Assign to Clusters <span class="text-danger">*</span></label>
                        <div class="card">
                            <div class="card-body">
                                @foreach(App\Models\Cluster::where('is_active', true)->get() as $cluster)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="clusters[]" value="{{ $cluster->id }}" id="edit_cluster_{{ $cluster->id }}">
                                    <label class="form-check-label" for="edit_cluster_{{ $cluster->id }}">
                                        {{ $cluster->name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-text text-danger">Select at least one cluster for Facilitator users</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
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

@push('styles')
<style>
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

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
        const status = this.value;
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            if (!status) {
                row.style.display = '';
                return;
            }

            const statusCell = row.querySelector('td:nth-child(5)');
            const isActive = statusCell.textContent.trim().toLowerCase() === 'active';
            row.style.display = (status === 'active' && isActive) || (status === 'inactive' && !isActive) ? '' : 'none';
        });
    });

    // User type selection handling
    const userTypeSelect = document.getElementById('userTypeSelect');
    const barangayClusterContainer = document.getElementById('barangayClusterContainer');
    const facilitatorClustersContainer = document.getElementById('facilitatorClustersContainer');

    // Function to update container visibility based on selected user type
    function updateContainerVisibility(userType) {
        // Hide all containers first
        barangayClusterContainer.style.display = 'none';
        facilitatorClustersContainer.style.display = 'none';

        // Show the appropriate container based on user type
        if (userType === 'barangay') {
            barangayClusterContainer.style.display = 'block';
        } else if (userType === 'facilitator') {
            facilitatorClustersContainer.style.display = 'block';
        }
    }

    // Initialize container visibility based on current selection
    updateContainerVisibility(userTypeSelect.value);

    // Add change event listener
    userTypeSelect.addEventListener('change', function() {
        updateContainerVisibility(this.value);
    });

    // Edit user modal handling
    document.getElementById('editUserModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const user = JSON.parse(button.getAttribute('data-user'));
        console.log('User data:', user);
        const form = this.querySelector('form');

        // Set the correct form action URL using the route name
        form.action = "{{ route('admin.users.update', '') }}/" + user.id;

        document.getElementById('editName').value = user.name;
        document.getElementById('editEmail').value = user.email;

        // Set user type (use user_type if available, otherwise fall back to role for backward compatibility)
        const userType = user.user_type || user.role;
        document.getElementById('editUserTypeSelect').value = userType;

        const barangayClusterContainer = document.getElementById('editBarangayClusterContainer');
        const facilitatorClustersContainer = document.getElementById('editFacilitatorClustersContainer');
        const clusterSelect = document.getElementById('editClusterSelect');

        // Hide all containers first
        barangayClusterContainer.style.display = 'none';
        facilitatorClustersContainer.style.display = 'none';

        // Show the appropriate container based on user type
        if (userType === 'barangay') {
            barangayClusterContainer.style.display = 'block';

            // Reset and set cluster select value if user is a barangay
            clusterSelect.value = '';
            if (user.cluster_id) {
                clusterSelect.value = user.cluster_id;
            }
        } else if (userType === 'facilitator') {
            facilitatorClustersContainer.style.display = 'block';

            // Reset all cluster checkboxes
            const clusterCheckboxes = facilitatorClustersContainer.querySelectorAll('input[type="checkbox"]');
            clusterCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Check the appropriate cluster checkboxes if user is a facilitator
            if (user.assigned_clusters) {
                // Convert to array if it's not already
                const clusterIds = Array.isArray(user.assigned_clusters)
                    ? user.assigned_clusters
                    : Object.values(user.assigned_clusters);

                console.log('Cluster IDs:', clusterIds);

                clusterIds.forEach(clusterId => {
                    const checkbox = document.getElementById('edit_cluster_' + clusterId);
                    console.log('Checking checkbox for cluster ID:', clusterId, checkbox);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        }

        // Remove existing event listeners to prevent duplicates
        const editUserTypeSelect = document.getElementById('editUserTypeSelect');
        const oldEditUserTypeSelect = editUserTypeSelect.cloneNode(true);
        editUserTypeSelect.parentNode.replaceChild(oldEditUserTypeSelect, editUserTypeSelect);

        // Function to update edit form container visibility
        function updateEditContainerVisibility(userType) {
            // Hide all containers first
            barangayClusterContainer.style.display = 'none';
            facilitatorClustersContainer.style.display = 'none';

            // Get the edit form containers
            const editBarangayContainer = document.getElementById('editBarangayClusterContainer');
            const editFacilitatorContainer = document.getElementById('editFacilitatorClustersContainer');

            // Hide all edit form containers
            editBarangayContainer.style.display = 'none';
            editFacilitatorContainer.style.display = 'none';

            // Show the appropriate container based on user type
            if (userType === 'barangay') {
                editBarangayContainer.style.display = 'block';
            } else if (userType === 'facilitator') {
                editFacilitatorContainer.style.display = 'block';
            }

            console.log('Updated container visibility for user type:', userType);
        }

        // Add event listener to handle user type change in edit form
        document.getElementById('editUserTypeSelect').addEventListener('change', function() {
            updateEditContainerVisibility(this.value);
        });
    });

    // Status change confirmation
    function confirmStatusChange(userId, newStatus) {
        const modal = document.getElementById('statusChangeModal');
        const message = document.getElementById('statusChangeMessage');
        const form = document.getElementById('statusChangeForm');

        message.textContent = `Are you sure you want to ${newStatus ? 'activate' : 'deactivate'} this user?`;

        // Set the correct form action URL using the route name
        form.action = "{{ route('admin.users.destroy', '') }}/" + userId;

        new bootstrap.Modal(modal).show();
    }
</script>
@endpush
@endsection
