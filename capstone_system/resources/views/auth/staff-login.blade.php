<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    
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
            padding: 3rem 5rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-width: 90%;
            margin: 0 auto;
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
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.4);
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
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-50%) scale(1.1);
        }

        .slide-nav i {
            color: white;
            font-size: 18px;
        }

        .slide-nav.prev {
            left: 1rem;
        }

        .slide-nav.next {
            right: 1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 968px) {
            .slide-content {
                padding: 2rem 3rem;
            }
            
            .slide h3 {
                font-size: 1.5rem;
            }
            
            .slide p {
                font-size: 1rem;
            }

            .slide-nav {
                width: 40px;
                height: 40px;
            }

            .slide-nav i {
                font-size: 16px;
            }

            .slide-nav.prev {
                left: 0.5rem;
            }

            .slide-nav.next {
                right: 0.5rem;
            }
        }

        /* CAPTCHA Styling */
        .cf-turnstile {
            margin: 0 auto;
            display: flex;
            justify-content: center;
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #94a3b8;
        }

        .security-info {
            text-align: center;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .security-info i {
            color: #10b981;
            margin-right: 0.25rem;
        }

        /* Google reCAPTCHA Styling */
        .g-recaptcha {
            margin: 0 auto;
            display: flex;
            justify-content: center;
            margin-bottom: 0.75rem;
        }

    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" style="height: 45px; width: auto;">
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
                    <!-- Slide 1: Dashboard Overview -->
                    <div class="slide active">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <h3>Step 1: Dashboard Overview</h3>
                            <p>After logging in, you'll see your Dashboard with real-time statistics of children in your barangay. The dashboard displays total patients, recent screenings, nutrition status breakdown with colorful charts, and quick access to urgent cases. You can view trends and monitor which children need immediate attention at a glance.</p>
                        </div>
                    </div>

                    <!-- Slide 2: Patient Management -->
                    <div class="slide">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-user-injured"></i>
                            </div>
                            <h3>Step 2: Manage Patients</h3>
                            <p>Click "Patients" to view all children assigned to your barangay. You can add new patients, update their information (name, birthdate, gender, parent contact), search and filter by status or age, and archive inactive cases. Each patient record includes their complete profile, screening history, and generated meal plans all in one place.</p>
                        </div>
                    </div>

                    <!-- Slide 3: Nutrition Screening -->
                    <div class="slide">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h3>Step 3: Conduct Nutrition Screenings</h3>
                            <p>Click "Screenings" to perform nutrition assessments. Select a child from the Patients list, then enter their current weight, height, and MUAC (Mid-Upper Arm Circumference). The AI-powered system instantly calculates BMI, weight-for-age, height-for-age, and diagnoses their nutrition status (Normal, Underweight, Wasted, Stunted, Overweight). Results are automatically saved with timestamps.</p>
                        </div>
                    </div>

                    <!-- Slide 4: Meal Plan Generation -->
                    <div class="slide">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <h3>Step 4: Generate AI Meal Plans</h3>
                            <p>Visit "Meal Plans" to create personalized nutrition programs. Select a child, specify available local foods, dietary restrictions, and budget. The AI generates a complete weekly meal plan with breakfast, lunch, dinner, and snacks tailored to the child's age, weight, and nutritional needs. You can also create feeding programs for groups and download meal plans as PDF.</p>
                        </div>
                    </div>

                    <!-- Slide 5: Reports & Food Database -->
                    <div class="slide">
                        <div class="slide-content">
                            <div class="slide-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <h3>Step 5: Reports & Food Database</h3>
                            <p>Access "Reports" to generate and download PDF summaries including Children Monitoring Reports, Assessment Summaries, and Monthly Progress Reports for your barangay. The "Food Database" lets you browse nutritional information for hundreds of Filipino foods—search by name or category to view calories, protein, vitamins, and minerals to help recommend the best foods for children's meal plans.</p>
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
                        <span class="indicator" data-slide="4"></span>
                    </div>
                </div>
            </div>

            <div class="login-card">
                <div class="login-header">
                    <h2>Staff Portal</h2>
                    <p class="subtitle">For Barangay Nutrition Scholars</p>
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

                <form method="POST" action="{{ route('staff.login.post') }}" id="staffLoginForm" class="login-form">
                    @csrf

                    <!-- Honeypot Field (Hidden trap for bots) -->
                    <input type="text" name="website" id="website" style="display:none !important;" tabindex="-1" autocomplete="off">

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
                        <div class="loading-spinner" style="display: none;">
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
            <div class="footer-grid">
                <!-- About Section -->
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" style="height: 60px; width: auto;">
                    </div>
                    <p class="footer-about">
                        Smart Health and Recommender System for San Pedro City's Nutrition Program - Building a healthier, food-secure future for all San Pedrenses.
                    </p>

                </div>

                <div class="footer-section">
                    <h4>Connect With Us</h4>
                    <div class="footer-social" style="display: flex; flex-direction: column; gap: 10px; align-items: flex-start;">
                        <a href="https://www.facebook.com/profile.php?id=61555362010142" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-list">
                        <li><a href="#home">Home</a></li>
                        <li><a href="{{ route('apply.nutritionist') }}">Register</a></li>
                    </ul>
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

    <script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}"></script>
    <script>
        // reCAPTCHA v3 - Generate token on form submit
        const staffLoginForm = document.getElementById('staffLoginForm');
        if (staffLoginForm) {
            staffLoginForm.addEventListener('submit', function(e) {
                const honeypot = document.getElementById('website').value;
                if (honeypot) {
                    e.preventDefault();
                    console.log('Bot detected via honeypot');
                    return false;
                }
            });
        }

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
    </script>
</body>
</html>
