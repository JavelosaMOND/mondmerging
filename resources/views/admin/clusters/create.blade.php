@extends('admin.layouts.app')

@section('title', 'Create Cluster')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="page-title">
                <i class="fas fa-layer-group"></i>
                Create New Cluster
            </h2>
        </div>
        <div class="col text-end">
            <a href="{{ route('admin.clusters.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Clusters
            </a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.clusters.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Cluster Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Assign Facilitators</label>
                    <div class="card">
                        <div class="card-body">
                            @if($facilitators->count() > 0)
                                @foreach($facilitators as $facilitator)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="facilitators[]" value="{{ $facilitator->id }}" id="facilitator{{ $facilitator->id }}" {{ in_array($facilitator->id, old('facilitators', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="facilitator{{ $facilitator->id }}">
                                        {{ $facilitator->name }} ({{ $facilitator->email }})
                                    </label>
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted">No facilitators available. <a href="{{ route('admin.user-management') }}">Create a facilitator</a> first.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Cluster
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
