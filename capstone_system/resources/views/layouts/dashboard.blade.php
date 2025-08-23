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
    
    <!-- Modal Styling -->
    <style>
        /* Center modals perfectly on screen - ONLY when shown */
        .modal.show {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
        }
        
        .modal.show .modal-dialog {
            margin: 0 !important;
            max-width: 90vw !important;
            max-height: 90vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        /* Hidden modal should not interfere */
        .modal:not(.show) {
            display: none !important;
        }
        
        /* White and Green Color Scheme */
        .modal-content {
            background-color: white !important;
            border: 2px solid #28a745 !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3) !important;
            overflow: hidden !important;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            color: white !important;
            border-bottom: none !important;
            padding: 1.5rem !important;
        }
        
        .modal-title {
            color: white !important;
            font-weight: 600 !important;
            margin: 0 !important;
        }
        
        .btn-close,
        .btn-close-white {
            filter: brightness(0) invert(1) !important;
            opacity: 0.8 !important;
        }
        
        .btn-close:hover,
        .btn-close-white:hover {
            opacity: 1 !important;
        }
        
        .modal-body {
            background-color: white !important;
            padding: 2rem !important;
            color: #333 !important;
        }
        
        .modal-footer {
            background-color: #f8f9fa !important;
            border-top: 1px solid #e9ecef !important;
            padding: 1.5rem !important;
        }
        
        /* Form styling inside modals */
        .modal .form-label {
            color: #28a745 !important;
            font-weight: 600 !important;
        }
        
        .modal .form-control,
        .modal .form-select {
            border: 2px solid #e9ecef !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
        }
        
        .modal .form-control:focus,
        .modal .form-select:focus {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25) !important;
            outline: none !important;
        }
        
        /* Button styling in modals */
        .modal .btn-primary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            border: none !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
        }
        
        .modal .btn-primary:hover {
            background: linear-gradient(135deg, #218838 0%, #17a2b8 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4) !important;
        }
        
        .modal .btn-success {
            background: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
        }
        
        .modal .btn-success:hover {
            background: #218838 !important;
            border-color: #1e7e34 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4) !important;
        }
        
        .modal .btn-secondary {
            background: #6c757d !important;
            border-color: #6c757d !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
        }
        
        .modal .btn-secondary:hover {
            background: #5a6268 !important;
            border-color: #545b62 !important;
            transform: translateY(-2px) !important;
        }
        
        /* Modal backdrop */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.6) !important;
        }
        
        /* Special styling for assessment results */
        .modal .result-item.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
            border-left: 4px solid #28a745 !important;
            color: #155724 !important;
        }
        
        .modal .result-item.warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
            border-left: 4px solid #ffc107 !important;
            color: #856404 !important;
        }
        
        .modal .result-item.danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%) !important;
            border-left: 4px solid #dc3545 !important;
            color: #721c24 !important;
        }
        
        .modal .result-item.info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%) !important;
            border-left: 4px solid #17a2b8 !important;
            color: #0c5460 !important;
        }
        
        /* Alert styling in modals */
        .modal .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%) !important;
            border: 1px solid #f5c6cb !important;
            color: #721c24 !important;
        }
        
        .modal .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
            border: 1px solid #ffeaa7 !important;
            color: #856404 !important;
        }
        
        .modal .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
            border: 1px solid #c3e6cb !important;
            color: #155724 !important;
        }
        
        /* Text styling */
        .modal .text-muted {
            color: #6c757d !important;
        }
        
        .modal .text-danger {
            color: #dc3545 !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .modal-dialog {
                max-width: 95vw !important;
                margin: 1rem !important;
            }
            
            .modal-body {
                padding: 1.5rem !important;
            }
            
            .modal-header,
            .modal-footer {
                padding: 1rem !important;
            }
        }
    </style>
    
    <!-- Ensure Bootstrap JS is loaded before any other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Global Modal Functions Fallback -->
    <script>
        // Fallback functions to prevent "undefined" errors
        window.modalFallbacks = {
            openModal: function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    if (typeof bootstrap !== 'undefined') {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    } else {
                        modal.style.display = 'block';
                        modal.classList.add('show');
                        document.body.style.overflow = 'hidden';
                        
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'modalBackdrop';
                        document.body.appendChild(backdrop);
                    }
                }
            },
            
            closeModal: function(modalId) {
                const modal = document.getElementById(modalId);
                const backdrop = document.getElementById('modalBackdrop');
                
                if (modal) {
                    if (typeof bootstrap !== 'undefined') {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    } else {
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        document.body.style.overflow = '';
                        
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                }
            }
        };
        
        // Global fallback functions for specific modals
        if (typeof window.openQuickAssessmentModal === 'undefined') {
            window.openQuickAssessmentModal = function() {
                console.log('Using fallback for Quick Assessment Modal');
                window.modalFallbacks.openModal('quickAssessmentModal');
            };
        }
        
        if (typeof window.openAddPatientModal === 'undefined') {
            window.openAddPatientModal = function() {
                console.log('Using fallback for Add Patient Modal');
                window.modalFallbacks.openModal('patientModal');
            };
        }
        
        // Check Bootstrap loading
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof bootstrap !== 'undefined') {
                console.log('✅ Bootstrap loaded successfully');
            } else {
                console.error('❌ Bootstrap failed to load');
            }
        });
    </script>
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
