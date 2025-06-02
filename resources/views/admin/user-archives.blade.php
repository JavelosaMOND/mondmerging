@extends('admin.layouts.app')

@section('title', 'Archived Users')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="page-title">
                <i class="fas fa-archive"></i>
                Archived Users
            </h2>
        </div>
        <div class="col text-end">
            <a href="{{ route('admin.user-management') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to User Management
            </a>
        </div>
    </div>
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
                            <th>Archived At</th>
                            <th>Archived By</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archives as $archive)
                        <tr>
                            <td>{{ $archive->name }}</td>
                            <td>{{ $archive->email }}</td>
                            <td>{{ ucfirst($archive->role) }}</td>
                            <td>{{ $archive->cluster ? $archive->cluster->name : '-' }}</td>
                            <td>{{ $archive->archived_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $archive->archived_by }}</td>
                            <td>{{ $archive->archive_reason }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No archived users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 