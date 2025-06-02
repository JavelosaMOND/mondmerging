<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resubmit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            padding: 20px;
            color: white;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .nav-link {
            color: rgba(255,255,255,.75);
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="sidebar">
        <h4 class="mb-4">Report Management</h4>
        <nav class="nav flex-column">
            <a class="nav-link" href="{{ route('reports.submit') }}">
                <i class="bi bi-plus-circle"></i> Submit New Report
            </a>
            <a class="nav-link" href="{{ route('reports.view') }}">
                <i class="bi bi-list-ul"></i> View Reports
            </a>
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-house"></i> Dashboard
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="form-container">
                <h2 class="mb-4">Resubmit Report</h2>

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Current Report Details</h5>
                        <p class="mb-1"><strong>Report Type:</strong> {{ $report->reportType->name }}</p>
                        <p class="mb-1"><strong>Current File:</strong> {{ $report->file_name }}</p>
                        <p class="mb-1"><strong>Status:</strong>
                            <span class="badge bg-{{ $report->status === 'approved' ? 'success' : ($report->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </p>
                        @if($report->remarks)
                            <p class="mb-1"><strong>Remarks:</strong> {{ $report->remarks }}</p>
                        @endif
                    </div>
                </div>

                <form action="{{ route('reports.resubmit', $report->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="file" class="form-label">Upload New Report File</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="form-text">Accepted formats: PDF, DOC, DOCX, XLSX (Max size: 2MB)</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('reports.view') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Resubmit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
