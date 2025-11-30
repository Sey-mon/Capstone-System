<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SHARES</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @stack('styles')
    
    <link rel="stylesheet" href="{{ asset('css/dashboard-modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-backdrop-fix.css') }}">
    
    <!-- jQuery (required for some legacy scripts) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Ensure Bootstrap JS is loaded before any other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Modal cleanup utility -->
    <script src="{{ asset('js/modal-cleanup.js') }}"></script>
    <script src="{{ asset('js/dashboard-modal.js') }}"></script>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo" style="padding: 0; margin: 0; border: none; display: flex; align-items: center; flex: 1;">
                    <div class="logo-container" style="padding: 0; margin: 0; border: none; width: 100%; max-width: 160px; height: auto;">
                        <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" class="logo-img" style="padding: 0; margin: 0; border: none; width: 100%; height: auto; display: block;">
                    </div>
                </div>
                <button class="sidebar-toggle desktop-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="sidebar-content">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                        <div class="user-role">{{ Auth::user()->role->role_name ?? 'User' }}</div>
                    </div>
                    <a href="{{ route(Auth::user()->role->role_name === 'Admin' ? 'admin.profile' : (Auth::user()->role->role_name === 'Nutritionist' ? 'nutritionist.profile' : 'parent.profile')) }}" class="profile-btn" title="Profile">
                        <i class="fas fa-user-cog"></i>
                    </a>
                </div>
                
                <nav class="sidebar-nav">
                    @yield('navigation')
                </nav>
            </div>
            
            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Content -->
            <div class="page-content">
                <!-- Floating Menu Button (shown when sidebar is hidden) -->
                <button class="floating-menu-btn" id="floatingMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                
                @yield('content')
            </div>
        </main>
    </div>
    
    <script src="{{ asset('js/dashboard.js') }}"></script>
    
    <!-- Session Timeout Warning Script -->
    <script>
        let inactivityTimer;
        let warningTimer;
        let isWarningShown = false;
        
        // Session timeout in milliseconds (from Laravel config)
        const SESSION_TIMEOUT = {{ config('session.lifetime') * 60 * 1000 }};
        const WARNING_TIME = 5 * 60 * 1000; // Show warning 5 minutes before timeout
        
        function showTimeoutWarning() {
            if (isWarningShown) return;
            isWarningShown = true;
            
            if (confirm('Your session will expire in 5 minutes due to inactivity. Click OK to stay logged in or Cancel to logout now.')) {
                // User wants to stay logged in, make a keep-alive request
                fetch('{{ route('dashboard') }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(() => {
                    isWarningShown = false;
                    resetActivityTimer();
                }).catch(() => {
                    // If request fails, redirect to login
                    window.location.href = '{{ route('login') }}';
                });
            } else {
                // User chose to logout
                window.location.href = '{{ route('logout') }}';
            }
        }
        
        function resetActivityTimer() {
            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);
            
            // Set warning timer (5 minutes before actual timeout)
            warningTimer = setTimeout(showTimeoutWarning, SESSION_TIMEOUT - WARNING_TIME);
            
            // Set logout timer (full timeout)
            inactivityTimer = setTimeout(() => {
                alert('You have been logged out due to inactivity.');
                window.location.href = '{{ route('login') }}';
            }, SESSION_TIMEOUT);
        }
        
        // Reset timer on user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, resetActivityTimer, true);
        });
        
        // Start the timer when page loads
        resetActivityTimer();
        
        // Handle AJAX errors for session expiry
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept all AJAX requests to handle session expiry
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args).then(response => {
                    if (response.status === 401) {
                        alert('Your session has expired. Please log in again.');
                        window.location.href = '{{ route('login') }}';
                    }
                    return response;
                });
            };
        });
    </script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>
