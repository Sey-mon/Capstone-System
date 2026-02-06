<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Login to SHARES - Smart Health and Recommender System for Excellence in Nutrition. Access your San Pedro City nutrition management account.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/cho.png') }}?v={{ filemtime(public_path('img/cho.png')) }}">
    
    <!-- Preconnect to external resources for better performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://www.google.com">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body>  
    <!-- Navigation Header -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="{{ asset('img/shares-logo.png') }}?v={{ filemtime(public_path('img/shares-logo.png')) }}" alt="SHARES Logo">
            </div>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#mission">Mission</a>
                <a href="#vision">Vision</a>
                <a href="#features">Features</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Login -->
    <section id="home" class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="title-line">WELCOME TO</span><br>
                    <span class="title-highlight">SHARES</span>
                </h1>
                <p class="hero-subtitle">
                    Smart Health and Recommender System for Excellence in Nutrition - Transforming San Pedro City into a nutrition-smart community through data-driven insights and intelligent health recommendations
                </p>
                <a href="#about" class="learn-more-btn">
                    Learn More
                    <i class="fas fa-arrow-down"></i>
                    <h2>it works</h2>
                </a>
            </div>

            <div class="login-card">
                <div class="login-header">
                    <h2>Sign In</h2>
                    <p class="subtitle">Access your account</p>
                </div>
            
                <!-- Display Success Messages -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Display Error Messages -->
                @if($errors->any())
                    <div class="alert alert-error">
                        @foreach($errors->all() as $error)
                            <p><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" id="loginForm" class="login-form">
                    @csrf

                    <!-- Honeypot Field (Hidden trap for bots) -->
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" id="email" 
                                   placeholder="Enter your email" 
                                   value="{{ old('email') }}"
                                   required autofocus
                                   autocomplete="email">
                        </div>
                        @error('email')
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="password" 
                                   placeholder="Enter your password" 
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="password-toggle" id="togglePassword" 
                                    aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="forgot-password">Forgot Password?</a>
                    </div>

                    <!-- Cloudflare Turnstile Widget -->
                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                    @error('cf-turnstile-response')
                        <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror

                    <button type="submit" class="btn-primary" id="loginBtn">
                        <span class="btn-text">Sign In</span>
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>

                    <div class="divider">
                        <span>or</span>
                    </div>

                    <div class="extra-links">
                        <a href="{{ route('support.report') }}" class="contact-admin-btn">
                            <i class="fas fa-exclamation-circle"></i>
                            Report a Problem
                        </a>
                    </div>
                </form>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="{{ route('register') }}" class="register-link">Create one here</a></p>
                    <p class="staff-login-link">
                        <i class="fas fa-user-shield"></i>
                        Staff member? <a href="{{ route('staff.login') }}">Login here</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Decorative Background -->
        <div class="hero-decoration">
            <div class="particle-network"></div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="content-section dark-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ asset('img/city-hall.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed;">
        <div class="section-container">
            <div class="section-content">
                <div class="content-text">
                    <h2 class="section-title">About San Pedro City</h2>
                    <p class="section-description">
                        San Pedro City, located in the province of Laguna, Philippines, is a rapidly growing urban center in the CALABARZON region. As part of the greater Metro Manila area, San Pedro is committed to becoming a nutrition-smart city through innovative healthcare solutions and comprehensive nutrition management systems. Our platform supports the city's healthcare professionals and community health workers in their mission to improve nutritional outcomes for all San Pedrenses, particularly children and vulnerable populations. By leveraging technology and data-driven approaches, we aim to contribute to San Pedro City's transformation into a model of nutrition excellence in the Philippines.
                    </p>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3>CALABARZON</h3>
                            <p>Region in Laguna Province</p>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <h3>348,968</h3>
                            <p>Population (2024 Census)</p>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-city"></i>
                            <h3>Smart City</h3>
                            <p>Nutrition-Smart Goal by 2032</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section id="mission" class="content-section light-section">
        <div class="section-container">
            <div class="feature-cards">
                <div class="feature-card">
                    <div class="feature-icon mission-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>To lead San Pedro City toward becoming a nutrition-smart city by 2032—where innovation, data, and inclusive systems drive improved nutritional outcomes for all. Through the effective implementation of the Philippine Plan of Action for Nutrition, the city will promote evidence-based strategies, digital solutions, and multisectoral coordination to ensure food safety, eliminate malnutrition—including micronutrient deficiencies—and guarantee year-round access to safe, nutritious, and affordable food. By integrating nutrition into the core of its smart city agenda, San Pedro commits to building a healthier, more resilient, and food-secure population.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision Section -->
    <section id="vision" class="content-section dark-section">
        <div class="section-container">
            <div class="feature-cards">
                <div class="feature-card">
                    <div class="feature-icon vision-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Our Vision</h3>
                    <p>By 2032, San Pedro City will be recognized smart city in Calabarzon where 100% of househols have year-around access to safe, nutritious, and affordable food; all children under five are free from stunting and wasting; and every San Pedrense adopts healthy, sustanable diets supported by digital innovations, resilient food systems, and inclusive nutrition services-contributing to a healthier, more productive, and food-secure urban population.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="content-section light-section">
        <div class="section-container">
            <h2 class="section-title centered">Why Choose San Pedro CHO Nutrition Office</h2>
            <p class="section-subtitle centered">Empowering San Pedrenses with innovative nutrition solutions</p>
            
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Data-Driven Nutrition Monitoring</h4>
                    <p>Track malnutrition cases and nutritional outcomes across all barangays in San Pedro City</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-baby"></i>
                    </div>
                    <h4>Child Nutrition Focus</h4>
                    <p>Dedicated programs to eliminate stunting and wasting in children under five years old</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4>Mobile-Friendly Access</h4>
                    <p>Barangay health workers can update patient records anytime, anywhere in San Pedro</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h4>Food Security Programs</h4>
                    <p>Monitor and ensure year-round access to safe, nutritious, and affordable food for all households</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h4>Multi-Sectoral Coordination</h4>
                    <p>Seamless collaboration between CHO staff, barangay officials, and community health workers</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h4>PPAN Implementation</h4>
                    <p>Aligned with Philippine Plan of Action for Nutrition for evidence-based interventions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="section-container">
            <div class="cta-content">
                <h2>Are You a Healthcare Professional?</h2>
                <p>Join San Pedro CHO staff in delivering better nutrition outcomes for our community</p>
                <a href="{{ route('staff.login') }}" class="cta-button">
                    Access Staff Portal
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- About Section -->
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="{{ asset('img/shares-logo.png') }}?v={{ filemtime(public_path('img/shares-logo.png')) }}" alt="SHARES Logo">
                    </div>
                    <p class="footer-about">
                        Smart Health and Recommender System for San Pedro City's Nutrition Program - Building a healthier, food-secure future for all San Pedrenses.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-list">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#mission">Mission</a></li>
                        <li><a href="#vision">Vision</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="{{ route('register') }}">Register</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="footer-section">
                    <h4>Connect With Us</h4>
                    <div class="footer-social" style="display: flex; flex-direction: column; gap: 10px; align-items: flex-start;">
                        <a href="https://www.facebook.com/profile.php?id=61555362010142" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>3rd Floor New City Hall Building<br>Brgy. Poblacion, City of San Pedro, Laguna</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>(02) 8808 - 2020 Loc 302 </span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>chonutrition.spl@gmail.com </span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Mon - Fri: 8:00 AM - 5:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p class="footer-copyright">© 2025 San Pedro City Health Office - Nutrition Program. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="{{ route('privacy') }}">Privacy Policy</a>
                    <span>|</span>
                    <a href="{{ route('terms') }}">Terms of Service</a>
                    <span>|</span>
                    <a href="{{ route('support.report') }}">Report a Problem</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Prevent back button caching - reload page once if loaded from cache
        (function() {
            // Use sessionStorage to prevent infinite reload loop
            const navigationEntry = performance.getEntriesByType('navigation')[0];
            
            if (navigationEntry && navigationEntry.type === 'back_forward') {
                // Check if we haven't already reloaded
                if (!sessionStorage.getItem('pageReloaded')) {
                    sessionStorage.setItem('pageReloaded', 'true');
                    window.location.reload();
                } else {
                    // Clear the flag so next back button press works
                    sessionStorage.removeItem('pageReloaded');
                }
            } else {
                // Normal navigation, clear the flag
                sessionStorage.removeItem('pageReloaded');
            }
        })();

        // Auto-hide alerts after 10 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 10000);
            });
        });
    </script>
    
    <script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}"></script>
</body>
</html>
