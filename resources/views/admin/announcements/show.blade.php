@extends('admin.layouts.app')

@section('title', 'Announcement Details')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="page-title">
            <i class="fas fa-bullhorn"></i>
            Announcement Details
        </h2>
        <div>
            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Announcement Content</h5>
            </div>
            <div class="card-body">
                <h3 class="mb-3">{{ $announcement->title }}</h3>
                
                <div class="mb-3">
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
                </div>
                
                <div class="mb-4">
                    {!! $announcement->content !!}
                </div>
                
                @if($announcement->button_text && $announcement->button_link)
                    <div class="mb-3">
                        <strong>Button:</strong>
                        <a href="{{ $announcement->button_link }}" class="btn btn-primary btn-sm ms-2" target="_blank">
                            {{ $announcement->button_text }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Preview</h5>
            </div>
            <div class="card-body">
                <div class="border rounded" style="background-color: {{ $announcement->background_color }}">
                    <div class="container py-4">
                        <div class="row align-items-center">
                            @if($announcement->image_path)
                                <div class="col-md-6">
                                    <div class="p-4">
                                        <h3 class="fw-bold">{{ $announcement->title }}</h3>
                                        <div class="my-3">
                                            {!! $announcement->content !!}
                                        </div>
                                        @if($announcement->button_text && $announcement->button_link)
                                            <a href="{{ $announcement->button_link }}" class="btn btn-primary" target="_blank">
                                                {{ $announcement->button_text }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <img src="{{ asset('storage/' . $announcement->image_path) }}" class="img-fluid rounded" alt="{{ $announcement->title }}">
                                </div>
                            @else
                                <div class="col-12 text-center">
                                    <div class="p-4">
                                        <h3 class="fw-bold">{{ $announcement->title }}</h3>
                                        <div class="my-3">
                                            {!! $announcement->content !!}
                                        </div>
                                        @if($announcement->button_text && $announcement->button_link)
                                            <a href="{{ $announcement->button_link }}" class="btn btn-primary mt-3" target="_blank">
                                                {{ $announcement->button_text }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Status Information</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Status:</span>
                    <span class="badge {{ $announcement->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Priority:</span>
                    <span class="badge bg-primary">{{ $announcement->priority }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Background Color:</span>
                    <span class="d-inline-flex align-items-center">
                        <span class="me-2">{{ $announcement->background_color }}</span>
                        <span style="width: 20px; height: 20px; display: inline-block; background-color: {{ $announcement->background_color }}; border-radius: 4px; border: 1px solid #ddd;"></span>
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>Start Date:</strong>
                    <div>{{ $announcement->starts_at ? $announcement->starts_at->format('F j, Y - g:i A') : 'Not set' }}</div>
                </div>
                
                <div class="mb-3">
                    <strong>End Date:</strong>
                    <div>{{ $announcement->ends_at ? $announcement->ends_at->format('F j, Y - g:i A') : 'Not set' }}</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Metadata</h5>
            </div>
            <div class="card-body">
                @if($announcement->image_path)
                    <div class="mb-3">
                        <strong>Image:</strong>
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $announcement->image_path) }}" alt="{{ $announcement->title }}" class="img-fluid rounded border">
                        </div>
                    </div>
                @endif
                
                <div class="mb-3">
                    <strong>Created:</strong>
                    <div>{{ $announcement->created_at->format('F j, Y - g:i A') }}</div>
                    @if($announcement->creator)
                        <small class="text-muted">by {{ $announcement->creator->name }}</small>
                    @endif
                </div>
                
                <div class="mb-3">
                    <strong>Last Updated:</strong>
                    <div>{{ $announcement->updated_at->format('F j, Y - g:i A') }}</div>
                    @if($announcement->updater)
                        <small class="text-muted">by {{ $announcement->updater->name }}</small>
                    @endif
                </div>
                
                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash"></i> Delete Announcement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 