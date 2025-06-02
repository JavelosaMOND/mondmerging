@extends('cluster.layouts.app')

@section('title', 'Cluster Details')

@section('content')
<div class="container-fluid">
    <h2 class="page-title mb-4">
        <i class="fas fa-building"></i> Cluster: {{ $cluster->name }}
    </h2>
    <div class="card mb-4">
        <div class="card-body">
            <h5>Cluster Information</h5>
            <ul>
                <li><strong>Name:</strong> {{ $cluster->name }}</li>
                <li><strong>Email:</strong> {{ $cluster->email }}</li>
                <li><strong>Parent Cluster:</strong> {{ $cluster->parentCluster ? $cluster->parentCluster->name : '-' }}</li>
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h5>Barangays Under This Cluster (including child clusters)</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Assigned Cluster</th>
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
                            <td>{{ $barangay->cluster ? $barangay->cluster->name : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No barangays found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 