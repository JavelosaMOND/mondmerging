@extends('admin.layouts.app')

@section('title', 'Cluster Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="page-title">
                <i class="fas fa-layer-group"></i>
                Cluster Management
            </h2>
        </div>
        <div class="col text-end">
            <a href="{{ route('admin.clusters.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Cluster
            </a>
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

    <!-- Clusters Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Facilitators</th>
                            <th>Barangays</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clusters as $cluster)
                        <tr>
                            <td>{{ $cluster->name }}</td>
                            <td>{{ Str::limit($cluster->description, 50) }}</td>
                            <td>
                                <span class="badge bg-info">{{ $cluster->facilitators->count() }}</span>
                                @if($cluster->facilitators->count() > 0)
                                    <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#facilitatorsModal{{ $cluster->id }}">
                                        View
                                    </button>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $cluster->barangays->count() }}</span>
                                @if($cluster->barangays->count() > 0)
                                    <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#barangaysModal{{ $cluster->id }}">
                                        View
                                    </button>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $cluster->is_active ? 'success' : 'danger' }}">
                                    {{ $cluster->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.clusters.edit', $cluster) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete({{ $cluster->id }}, '{{ $cluster->name }}')">
                                        <i class="fas fa-trash"></i>
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

<!-- Facilitators Modals -->
@foreach($clusters as $cluster)
<div class="modal fade" id="facilitatorsModal{{ $cluster->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Facilitators for {{ $cluster->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    @foreach($cluster->facilitators as $facilitator)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $facilitator->name }}
                        <span class="badge bg-{{ $facilitator->is_active ? 'success' : 'danger' }} rounded-pill">
                            {{ $facilitator->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Barangays Modals -->
@foreach($clusters as $cluster)
<div class="modal fade" id="barangaysModal{{ $cluster->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Barangays in {{ $cluster->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    @foreach($cluster->barangays as $barangay)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $barangay->name }}
                        <span class="badge bg-{{ $barangay->is_active ? 'success' : 'danger' }} rounded-pill">
                            {{ $barangay->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(clusterId, clusterName) {
        const modal = document.getElementById('deleteModal');
        const message = document.getElementById('deleteMessage');
        const form = document.getElementById('deleteForm');

        message.textContent = `Are you sure you want to delete the cluster "${clusterName}"?`;
        form.action = "{{ route('admin.clusters.destroy', '') }}/" + clusterId;

        new bootstrap.Modal(modal).show();
    }
</script>
@endpush
@endsection
