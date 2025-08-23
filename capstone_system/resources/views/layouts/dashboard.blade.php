<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Nutrition System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @stack('styles')
    
    <link rel="stylesheet" href="{{ asset('css/dashboard-modal.css') }}">
    
    <!-- Ensure Bootstrap JS is loaded before any other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="{{ asset('js/dashboard-modal.js') }}"></script>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span class="logo-text">NutriCare</span>
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
            <!-- Top Header -->
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="header-title">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <p class="header-subtitle">@yield('page-subtitle', '')</p>
                </div>
                
                <div class="header-actions">
                    <div class="notifications">
                        <button class="notification-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                    </div>
                </div>
            </header>
            
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
    @yield('scripts')
    @stack('scripts')
</body>
</html>
