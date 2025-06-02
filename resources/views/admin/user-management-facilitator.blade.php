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
                <div class="col-md-3">
                    <select class="form-select" id="roleFilter">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="Facilitator">Facilitator</option>
                        <option value="barangay">Barangay</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
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
                            <th>Facilitator</th>
                            <th>Parent Facilitator</th>
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
                                <span class="badge bg-{{ $user->role === 'Facilitator' ? 'info' : 'success' }}">
                                    {{ $user->role === 'Facilitator' ? 'Facilitator' : ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @if($user->role === 'barangay' && $user->cluster)
                                    {{ $user->cluster->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($user->parentCluster)
                                    {{ $user->parentCluster->name }}
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
                        <label class="form-label">User ID</label>
                        <input type="text" class="form-control" name="user_id" required placeholder="e.g. BARANGAY19">
                        <div class="form-text">This will be used as the login username. Default password will be DILGBACOLOD + User ID.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="roleSelect" required>
                            <option value="">Select Role</option>
                            <option value="Facilitator">Facilitator</option>
                            <option value="barangay">Barangay</option>
                        </select>
                    </div>
                    <div class="mb-3" id="clusterSelectContainer" style="display: none;">
                        <label class="form-label">Assign to Facilitator</label>
                        <select class="form-select" name="cluster_id">
                            <option value="">Select Facilitator</option>
                            @foreach($clusters as $facilitator)
                                <option value="{{ $facilitator->id }}">{{ $facilitator->name }} (Facilitator)</option>
                            @endforeach
                        </select>
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
                        <button type="button" class="btn btn-warning" id="resetPasswordBtn" style="width:100%">Reset to Default Password</button>
                        <div class="form-text">Default password will be DILGBACOLOD + User ID (uppercase).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="editRoleSelect" required>
                            <option value="Facilitator">Facilitator</option>
                            <option value="barangay">Barangay</option>
                        </select>
                    </div>
                    <div class="mb-3" id="editClusterSelectContainer">
                        <label class="form-label">Assign to Facilitator</label>
                        <select class="form-select" name="cluster_id" id="editClusterSelect">
                            <option value="">Select Facilitator</option>
                            @foreach($clusters as $facilitator)
                                <option value="{{ $facilitator->id }}">{{ $facilitator->name }} (Facilitator)</option>
                            @endforeach
                        </select>
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
                <div class="mb-3" id="archiveReasonContainer" style="display: none;">
                    <label for="archive_reason" class="form-label">Reason for Deactivation</label>
                    <textarea class="form-control" id="archive_reason" name="archive_reason" rows="3" placeholder="Please provide a reason for deactivating this user"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="statusChangeForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="archive_reason" id="archive_reason_input">
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const clusterContainer = document.getElementById('clusterSelectContainer');
    if (roleSelect && clusterContainer) {
        roleSelect.addEventListener('change', function() {
            clusterContainer.style.display = (this.value === 'barangay') ? 'block' : 'none';
        });
        // Initial state
        clusterContainer.style.display = (roleSelect.value === 'barangay') ? 'block' : 'none';
    }
});
</script>
@endpush 