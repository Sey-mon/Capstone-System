<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Header -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-heartbeat"></i>
                <span>Nutrition System</span>
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
                <h1 class="hero-title">Welcome to<br>Nutrition Management System</h1>
                <p class="hero-subtitle">Empowering healthier communities through intelligent nutrition tracking and comprehensive health management</p>
                <a href="#about" class="learn-more-btn">
                    Learn More
                    <i class="fas fa-arrow-down"></i>
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

                    <button type="submit" class="btn-primary" id="loginBtn">
                        <span class="btn-text">Sign In</span>
                        <div class="loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>

                    <div class="divider">
                        <span>or</span>
                    </div>

                    <div class="extra-links">
                        <a href="{{ route('contact.admin') }}" class="contact-admin-btn">
                            <i class="fas fa-headset"></i>
                            Contact Admin
                        </a>
                    </div>
                </form>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="{{ route('register') }}" class="register-link">Create one here</a></p>
                </div>
            </div>
        </div>

        <!-- Decorative Background -->
        <div class="hero-decoration">
            <div class="particle-network"></div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="content-section dark-section">
        <div class="section-container">
            <div class="section-content">
                <div class="content-text">
                    <h2 class="section-title">Transform Healthcare Through Data</h2>
                    <p class="section-description">
                        Our comprehensive nutrition management system helps healthcare professionals track, assess, and improve patient nutrition outcomes with powerful analytics and comprehensive monitoring tools. We are dedicated to transforming healthcare through intelligent nutrition management.
                    </p>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <h3>1000+</h3>
                            <p>Healthcare Professionals</p>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-hospital"></i>
                            <h3>50+</h3>
                            <p>Partner Clinics</p>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-chart-line"></i>
                            <h3>10K+</h3>
                            <p>Patients Monitored</p>
                        </div>
                    </div>
                </div>
                <div class="content-image">
                    <div class="image-placeholder">
                        <i class="fas fa-heartbeat"></i>
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
                    <p>To empower communities by providing comprehensive nutrition management tools that promote healthier lifestyles and combat malnutrition through innovative technology and data-driven insights.</p>
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
                    <p>To be the leading platform in nutrition and health management, creating a world where every individual has access to personalized nutrition guidance and support for optimal health and well-being.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="content-section light-section">
        <div class="section-container">
            <h2 class="section-title centered">Why Choose Us</h2>
            <p class="section-subtitle centered">Comprehensive features designed for healthcare excellence</p>
            
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Advanced Analytics</h4>
                    <p>Real-time data visualization and comprehensive reporting tools</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Secure & Private</h4>
                    <p>Enterprise-grade security with encrypted data storage</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4>Easy to Use</h4>
                    <p>Intuitive interface designed for healthcare professionals</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4>24/7 Access</h4>
                    <p>Access your data anytime, anywhere, from any device</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h4>Team Collaboration</h4>
                    <p>Work seamlessly with your healthcare team</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-small">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4>Smart Notifications</h4>
                    <p>Stay updated with automated alerts and reminders</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="section-container">
            <div class="cta-content">
                <h2>Ready to Transform Your Healthcare Practice?</h2>
                <p>Join thousands of healthcare professionals using our platform</p>
                <a href="{{ route('register') }}" class="cta-button">
                    Get Started Now
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-logo">
                <i class="fas fa-heartbeat"></i>
                <span>Nutrition System</span>
            </div>
            <p class="footer-text">© 2025 Nutrition Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="{{ asset('js/login.js') }}"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.main-nav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Scroll Animation Observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);

        // Observe all content sections
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                observer.observe(section);
            });

            // Add stagger animation to feature boxes when they come into view
            const featureBoxObserver = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const boxes = entry.target.querySelectorAll('.feature-box');
                        boxes.forEach((box, index) => {
                            setTimeout(() => {
                                box.style.opacity = '1';
                                box.style.transform = 'translateY(0)';
                            }, index * 100);
                        });
                    }
                });
            }, observerOptions);

            const featuresSection = document.querySelector('#features');
            if (featuresSection) {
                featureBoxObserver.observe(featuresSection);
            }

            // Parallax effect for hero section
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const parallax = document.querySelector('.hero-decoration');
                if (parallax) {
                    parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
                }
            });

            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.btn-primary, .cta-button, .learn-more-btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const rect = button.getBoundingClientRect();
                    const ripple = document.createElement('span');
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');

                    button.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
    </script>
</body>
</html>
