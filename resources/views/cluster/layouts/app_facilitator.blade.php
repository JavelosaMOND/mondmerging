<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Facilitator Panel')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary: #3b82f6;
            --primary-light: rgba(59, 130, 246, 0.1);
            --secondary: #64748b;
            --success: #22c55e;
            --success-light: rgba(34, 197, 94, 0.1);
            --danger: #ef4444;
            --danger-light: rgba(239, 68, 68, 0.1);
            --warning: #f59e0b;
            --warning-light: rgba(245, 158, 11, 0.1);
            --info: #8b5cf6;
            --info-light: rgba(139, 92, 246, 0.1);
            --dark: #1e293b;
            --gray-100: #f8fafc;
            --gray-200: #f1f5f9;
            --gray-300: #e2e8f0;
            --gray-400: #cbd5e1;
            --gray-500: #94a3b8;
            --gray-600: #64748b;
            --gray-700: #475569;
            --gray-800: #334155;
            --gray-900: #1e293b;
            --shadow-sm: 0 2px 12px rgba(0,0,0,0.04);
            --shadow-md: 0 5px 15px rgba(0,0,0,0.08);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }
        body {
            background-color: var(--gray-100);
            color: var(--gray-800);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        .sidebar {
            background: var(--gray-200);
            min-height: 100vh;
            box-shadow: var(--shadow-sm);
            padding: 2rem 1rem 1rem 1rem;
        }
        .sidebar-header img {
            border-radius: 50%;
        }
        .sidebar-header h4 {
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link {
            color: var(--gray-700);
            font-weight: 500;
            margin-bottom: 0.5rem;
            border-radius: var(--radius-sm);
            transition: background 0.2s, color 0.2s;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: var(--primary-light);
            color: var(--primary);
        }
        .main-content {
            padding: 2rem 2rem 2rem 2rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                min-height: auto;
                width: 100%;
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-header text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Facilitator Icon" width="48" class="mb-2">
                    <h4>Facilitator Panel</h4>
                    <small>Control Center</small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('facilitator.dashboard') ? 'active' : '' }}" href="{{ route('facilitator.dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link {{ request()->routeIs('facilitator.reports*') ? 'active' : '' }}" href="{{ route('facilitator.reports') }}">
                        <i class="fas fa-inbox"></i> View Submissions
                    </a>
                    <a class="nav-link {{ request()->routeIs('facilitator.barangays*') ? 'active' : '' }}" href="{{ route('facilitator.barangays') }}">
                        <i class="fas fa-building"></i> Barangays
                    </a>
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="fas fa-user-cog"></i> Profile
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </nav>
            </div>
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="profileModalLabel">Profile Settings</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="text" class="form-control" value="{{ auth()->user()->email }}" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">Role</label>
              <input type="text" class="form-control" value="{{ ucfirst(auth()->user()->role) }}" readonly>
            </div>
            @if(auth()->user()->cluster)
            <div class="mb-3">
              <label class="form-label">Cluster</label>
              <input type="text" class="form-control" value="{{ 'Cluster ' . (\App\Models\User::where('role', 'Facilitator')->get()->search(fn($c) => $c->id === auth()->user()->cluster_id) + 1) }}" readonly>
            </div>
            @endif
            <hr>
            <h6>Change Password</h6>
            <form id="changePasswordForm">
              @csrf
              <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="current_password" required>
              </div>
              <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="new_password_confirmation" required>
              </div>
              <div class="mb-3 d-flex align-items-center">
                <input type="text" class="form-control me-2" name="otp" placeholder="Enter OTP" required>
                <button type="button" class="btn btn-secondary" id="requestOtpBtn">Request OTP</button>
              </div>
              <button type="submit" class="btn btn-primary w-100">Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ... existing JS code ...
    </script>
    @stack('scripts')
</body>
</html> 