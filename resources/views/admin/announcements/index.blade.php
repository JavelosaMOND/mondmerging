@extends('admin.layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="page-title">
            <i class="fas fa-bullhorn"></i>
            Announcements
        </h2>
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Announcement
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Schedule</th>
                        <th>Priority</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($announcement->image_path)
                                        <div class="me-3">
                                            <img src="{{ asset('storage/' . $announcement->image_path) }}" alt="{{ $announcement->title }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $announcement->title }}</div>
                                        <div class="small text-muted">{{ Str::limit(strip_tags($announcement->content), 50) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($announcement->category == 'recognition')
                                    <span class="badge bg-success">
                                        <i class="fas fa-award me-1"></i> Recognition
                                    </span>
                                @elseif($announcement->category == 'important_update')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-bell me-1"></i> Important Update
                                    </span>
                                @elseif($announcement->category == 'upcoming_event')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-calendar me-1"></i> Upcoming Event
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-info-circle me-1"></i> Announcement
                                    </span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.announcements.toggle-status', $announcement) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm {{ $announcement->is_active ? 'btn-success' : 'btn-secondary' }}">
                                        {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="small">
                                    @if($announcement->starts_at)
                                        <div><i class="fas fa-calendar-day me-1"></i> Start: {{ $announcement->starts_at->format('M d, Y') }}</div>
                                    @endif
                                    @if($announcement->ends_at)
                                        <div><i class="fas fa-calendar-check me-1"></i> End: {{ $announcement->ends_at->format('M d, Y') }}</div>
                                    @endif
                                    @if(!$announcement->starts_at && !$announcement->ends_at)
                                        <span class="text-muted">No schedule</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $announcement->priority }}</span>
                            </td>
                            <td>
                                <div class="small">
                                    {{ $announcement->created_at->format('M d, Y') }}
                                    <div class="text-muted">
                                        {{ $announcement->creator ? 'by ' . $announcement->creator->name : '' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-info-circle me-2"></i>No announcements found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 d-flex justify-content-center">
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection 