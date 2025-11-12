<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Slideshow Styles */
        .slideshow-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .slide {
            display: none;
            width: 100%;
            height: 100%;
            animation: fadeIn 1s ease-in-out;
        }

        .slide.active {
            display: block;
        }

        .slide-content {
            padding: 3rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .slide-icon {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .slide-icon i {
            font-size: 40px;
            color: var(--primary-color);
        }

        .slide h3 {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .slide p {
            font-size: 1.125rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.85);
        }

        .slide-indicators {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.75rem;
            z-index: 10;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .indicator.active {
            background: white;
            width: 32px;
            border-radius: 6px;
        }

        /* Navigation Arrows */
        .slide-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            backdrop-filter: blur(5px);
        }

        .slide-nav:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-50%) scale(1.1);
        }

        .slide-nav i {
            color: white;
            font-size: 20px;
        }

        .slide-nav.prev {
            left: 2rem;
        }

        .slide-nav.next {
            right: 2rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 968px) {
            .slide-content {
                padding: 2rem;
            }
            
            .slide h3 {
                font-size: 1.5rem;
            }
            
            .slide p {
                font-size: 1rem;
            }
        }

    </style>
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
                <a href="{{ route('login') }}">Parent Portal</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Login -->
    <section id="home" class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <div class="slideshow-container">
                    <!-- Slide 1 -->
                    <div class="slide active">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3>Secure Admin Access</h3>
                            <p>Manage your nutrition system with comprehensive administrative tools. Monitor users, track inventory, and oversee all system operations from a centralized dashboard.</p>
                        </div>
                    </div>

                    <!-- Slide 2 -->
                    <div class="slide">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <h3>Nutritionist Dashboard</h3>
                            <p>Access patient records, create meal plans, conduct assessments, and provide personalized nutrition guidance. Everything you need to deliver quality care.</p>
                        </div>
                    </div>

                    <!-- Slide 3 -->
                    <div class="slide">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <h3>Health Worker Portal</h3>
                            <p>Track community health metrics, manage patient data, and collaborate with healthcare teams to improve nutrition outcomes in your area.</p>
                        </div>
                    </div>

                    <!-- Slide 4 -->
                    <div class="slide">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>Real-Time Analytics</h3>
                            <p>Access comprehensive reports, visualize data trends, and make informed decisions with powerful analytics tools designed for healthcare professionals.</p>
                        </div>
                    </div>

                    <!-- Navigation Arrows -->
                    <button class="slide-nav prev" id="prevSlide">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="slide-nav next" id="nextSlide">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    <!-- Slide Indicators -->
                    <div class="slide-indicators">
                        <span class="indicator active" data-slide="0"></span>
                        <span class="indicator" data-slide="1"></span>
                        <span class="indicator" data-slide="2"></span>
                        <span class="indicator" data-slide="3"></span>
                    </div>
                </div>
            </div>

            <div class="login-card">
                <div class="login-header">
                    <h2>Staff Portal</h2>
                    <p class="subtitle">For Administrators, Nutritionists & Health Workers</p>
                </div>
            
                <!-- Display Error Messages -->
                @if($errors->any())
                    <div class="alert alert-error">
                        @foreach($errors->all() as $error)
                            <p><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('staff.login.post') }}" id="staffLoginForm" class="login-form">
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
                </form>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="{{ route('apply.nutritionist') }}" class="register-link">Apply as Staff</a></p>
                    <p class="staff-login-link">
                        <i class="fas fa-user"></i>
                        Parent? <a href="{{ route('login') }}">Login here</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Decorative Background -->
        <div class="hero-decoration">
            <div class="particle-network"></div>
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
        // Slideshow functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const indicators = document.querySelectorAll('.indicator');
        const totalSlides = slides.length;
        let autoSlideInterval;

        function showSlide(index) {
            // Hide all slides
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            // Show current slide
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
        }

        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(nextSlide, 5000);
        }

        // Auto advance slides every 5 seconds
        autoSlideInterval = setInterval(nextSlide, 5000);

        // Next button
        document.getElementById('nextSlide').addEventListener('click', () => {
            nextSlide();
            resetAutoSlide();
        });

        // Previous button
        document.getElementById('prevSlide').addEventListener('click', () => {
            prevSlide();
            resetAutoSlide();
        });

        // Click indicator to jump to slide
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
                resetAutoSlide();
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
    </script>
</body>
</html>
