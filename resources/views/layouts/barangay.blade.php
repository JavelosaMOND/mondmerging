<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Barangay Portal')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            background: white;
            min-height: 100vh;
            padding: 1.5rem 0;
            position: fixed;
            width: 280px;
            box-shadow: var(--shadow-sm);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 1rem;
        }

        .sidebar-header h4 {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .sidebar-header small {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .nav-link {
            color: var(--gray-700);
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 1rem;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
            text-decoration: none;
        }

        .nav-link:hover {
            background: var(--gray-100);
            color: var(--primary);
        }

        .nav-link.active {
            background: var(--primary-light);
            color: var(--primary);
        }

        .nav-link i {
            width: 1.5rem;
            font-size: 1.1rem;
            margin-right: 0.75rem;
        }

        /* Main Content Styles */
        .main-content {
            padding: 2rem;
            margin-left: 280px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Card Styles */
        .card {
            background: white;
            border: none;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            padding: 1.25rem;
            border-radius: var(--radius-md) var(--radius-md) 0 0 !important;
        }

        .card-header h5 {
            color: var(--dark);
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Form Styles */
        .form-control, .form-select {
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            padding: 0.625rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-label {
            color: var(--gray-700);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            color: var(--gray-600);
        }

        /* Button Styles */
        .btn {
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
            width: 100%;
        }

        .table th {
            background: var(--gray-100);
            color: var(--gray-700);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            white-space: nowrap;
        }

        .table td {
            color: var(--gray-800);
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-200);
        }

        /* Badge Styles */
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: var(--radius-sm);
        }

        .badge-primary {
            background: var(--primary-light);
            color: var(--primary);
        }

        .badge-success {
            background: var(--success-light);
            color: var(--success);
        }

        .badge-danger {
            background: var(--danger-light);
            color: var(--danger);
        }

        .badge-warning {
            background: var(--warning-light);
            color: var(--warning);
        }

        .badge-info {
            background: var(--info-light);
            color: var(--info);
        }

        /* Drop Zone Styles */
        .drop-zone {
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-md);
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .drop-zone:hover {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        .drop-zone.dragover {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        .drop-zone__thumb {
            padding: 1rem;
            background: white;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: var(--radius-sm);
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success);
        }

        .alert-danger {
            background: var(--danger-light);
            color: var(--danger);
        }

        .alert-warning {
            background: var(--warning-light);
            color: var(--warning);
        }

        .alert-info {
            background: var(--info-light);
            color: var(--info);
        }

        /* Page Title */
        .page-title {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .table-responsive {
                margin: 0 -1rem;
            }
        }

        /* Loading Spinner */
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--gray-200);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-auto">
                <div class="sidebar">
                    <div class="sidebar-header">
                        <h4>Barangay Portal</h4>
                        <small>{{ auth()->user()->name }}</small>
                    </div>
                    <nav>
                        <a href="{{ route('barangay.dashboard') }}" class="nav-link {{ request()->routeIs('barangay.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('barangay.submissions') }}" class="nav-link {{ request()->routeIs('barangay.submissions') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            My Submissions
                        </a>
                        <a href="{{ route('barangay.overdue-reports') }}" class="nav-link {{ request()->routeIs('barangay.overdue-reports') ? 'active' : '' }}">
                            <i class="fas fa-exclamation-circle"></i>
                            Overdue Reports
                        </a>
                        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i class="fas fa-user-cog"></i>
                            Profile
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col">
                <div class="main-content">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

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
            @if(auth()->user()->role === 'barangay' && auth()->user()->cluster)
            <div class="mb-3">
              <label class="form-label">Cluster</label>
              <input type="text" class="form-control" value="{{ auth()->user()->cluster->name }}" readonly>
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

    <script>
    $(function() {
        $('#requestOtpBtn').on('click', function() {
            $.post('/request-otp', {}, function(response) {
                let message = response.message;
                if (response.debug_otp) {
                    message += '<br><br><strong>Debug OTP: ' + response.debug_otp + '</strong><br><small>(This is only for development)</small>';
                }
                Swal.fire({
                    title: 'OTP Sent',
                    html: message,
                    icon: 'success'
                });
            }).fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to send OTP', 'error');
            });
        });
        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            $.ajax({
                url: '/verify-otp-change-password',
                method: 'POST',
                data: data,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    Swal.fire('Success', response.message, 'success');
                    form[0].reset();
                },
                error: function(xhr) {
                    let msg = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    });
    </script>
</body>
</html>
