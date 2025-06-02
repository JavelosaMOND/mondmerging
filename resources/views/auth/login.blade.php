<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DILG - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #003366;
            --primary-light: #0055a4;
            --primary-dark: #002244;
            --secondary: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
        }
        
        body {
            background-color: #f8f9fa;
            height: 100vh;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
        }
        
        .login-container {
            height: 100vh;
            max-width: 100vw;
            overflow: hidden;
        }
        
        .announcement-side {
            height: 100%;
            padding: 0;
            overflow: hidden;
            position: relative;
            color: white;
        }
        
        .login-side {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            background-color: #ffffff;
            position: relative;
        }
        
        .logo-container {
            position: absolute;
            top: 30px;
            left: 40px;
            z-index: 100;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .logo-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            filter: drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.1));
        }
        
        .logo-text {
            margin-left: 15px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            text-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .login-form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 0 20px;
        }
        
        .login-form {
            width: 100%;
            max-width: 380px;
            padding: 40px;
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .login-title {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .login-subtitle {
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-label {
            color: #4b5563;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .form-control {
            height: 50px;
            border-radius: 12px;
            box-shadow: none;
            margin-bottom: 20px;
            padding-left: 15px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
            background-color: #fff;
            transform: translateY(-1px);
        }
        
        .login-btn {
            height: 50px;
            border-radius: 12px;
            font-weight: 600;
            background-color: var(--primary);
            border: none;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.2);
        }
        
        .carousel-item {
            height: 100vh;
        }
        
        .announcement-content {
            padding: 40px;
        }
        
        .announcement-content-overlay {
            width: 100%;
        }
        
        .announcement-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
            line-height: 1.2;
        }
        
        .announcement-text {
            font-size: 15px;
            opacity: 0.9;
            margin-bottom: 25px;
            color: white;
            max-height: 200px;
            overflow-y: auto;
            line-height: 1.5;
        }
        
        /* Custom scrollbar for announcement text */
        .announcement-text::-webkit-scrollbar {
            width: 4px;
        }
        
        .announcement-text::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
        }
        
        .announcement-text::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .announcement-badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .footer-text {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }
        
        .remember-me-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me-container input {
            margin-right: 10px;
        }
        
        .remember-me-container label {
            font-size: 14px;
            color: #6c757d;
            cursor: pointer;
        }
        
        .forgot-password {
            font-size: 14px;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .announcement-side {
                display: none;
            }
            
            .login-form {
                padding: 30px;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 login-container">
            <!-- Left side: Announcements Carousel -->
            <div class="col-md-7 announcement-side">
                <a href="/" class="logo-container">
                    <img src="{{ asset('images/dilg.png') }}" alt="DILG Logo" class="logo-img">
                    <div class="logo-text">Department of Interior and Local Government</div>
                </a>
                
                @php
                    $announcements = [];
                    try {
                        $announcements = \App\Models\Announcement::getActiveAnnouncements();
                    } catch (\Exception $e) {
                        // Silently handle database errors
                    }
                @endphp
                
                @if(count($announcements) > 0)
                    <x-announcement-carousel :announcements="$announcements" />
                @else
                    <div class="d-flex align-items-center justify-content-center h-100" style="background: linear-gradient(135deg, #003366 0%, #001a33 100%);">
                        <div class="announcement-content text-center">
                            <div class="announcement-badge">
                                <i class="fas fa-star me-2"></i> Official Government Platform
                            </div>
                            <h1 class="announcement-title">Department of the Interior and Local Government</h1>
                            <p class="announcement-text">
                                Welcome to the DILG Barangay Reporting and Monitoring System. 
                                This platform manages submissions, tracks performance, and 
                                facilitates communication between government offices.
                            </p>
                            <div class="mt-4">
                                <img src="{{ asset('images/dilg.png') }}" alt="DILG Logo" style="width: 120px; height: auto; opacity: 0.9;">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Right side: Login form -->
            <div class="col-md-5 login-side">
                <div class="login-form-container">
                    <div class="login-form">
                        <h2 class="login-title">Sign In</h2>
                        <p class="login-subtitle">Enter your credentials to access your account</p>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required autocomplete="email" autofocus placeholder="Enter your email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="remember-me-container">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                
                                @if (Route::has('password.request'))
                                    <a class="forgot-password" href="{{ route('password.request') }}">
                                        Forgot Password?
                                    </a>
                                @endif
                            </div>
                            
                            <button type="submit" class="btn btn-primary login-btn w-100">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </form>
                    </div>
                    
                    <div class="footer-text">
                        <p>Department of the Interior and Local Government</p>
                        <p>© {{ date('Y') }} DILG. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
