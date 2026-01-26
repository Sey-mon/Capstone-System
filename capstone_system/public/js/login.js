document.addEventListener("DOMContentLoaded", function () {
    console.log("Login page loaded successfully!");
    
    // Initialize all functionality
    initializeLoginForm();
    initializePasswordToggle();
    initializeFormValidation();
    initializeAnimations();
    initializeHoneypotProtection();
    initializeSmoothScrolling();
    initializeNavbarScrollEffect();
    initializeScrollAnimations();
    initializeRippleEffects();
});

// Honeypot protection - prevents bot submissions
function initializeHoneypotProtection() {
    const form = document.getElementById('loginForm');
    const honeypot = document.getElementById('website');
    
    if (form && honeypot) {
        form.addEventListener('submit', function(e) {
            if (honeypot.value) {
                e.preventDefault();
                console.log('Bot detected via honeypot');
                return false;
            }
        });
    }
}

// Smooth scrolling for navigation links
function initializeSmoothScrolling() {
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
}

// Navbar scroll effect
function initializeNavbarScrollEffect() {
    window.addEventListener('scroll', function() {
        const nav = document.querySelector('.main-nav');
        if (nav) {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        }
    });
}

// Scroll Animation Observer
function initializeScrollAnimations() {
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
}

// Add ripple effect to all interactive buttons
function initializeRippleEffects() {
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
}

// Password visibility toggle
function initializePasswordToggle() {
    const toggleButton = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (toggleButton && passwordInput) {
        toggleButton.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            
            const icon = toggleButton.querySelector('i');
            if (icon) {
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            }
            
            // Update aria-label for accessibility
            toggleButton.setAttribute('aria-label', 
                type === 'password' ? 'Show password' : 'Hide password'
            );
            
            // Brief focus management
            passwordInput.focus();
        });
    }
}

// Form submission with loading state
function initializeLoginForm() {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginBtn');
    
    // Check if login form elements exist (not on registration page)
    if (!form || !submitBtn) {
        return;
    }
    
    const btnText = submitBtn.querySelector('.btn-text');
    const loadingSpinner = submitBtn.querySelector('.loading-spinner');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            btnText.style.opacity = '0';
            loadingSpinner.style.display = 'block';
            
            // Update screen reader status
            const statusElement = document.getElementById('login-status');
            if (statusElement) {
                statusElement.textContent = 'Signing in, please wait...';
            }
            
            // Add a minimum delay to show the loading state
            setTimeout(() => {
                // The form will continue with its normal submission
                // This timeout just ensures the loading state is visible
            }, 500);
        });
        
        // Reset button state if form submission fails
        window.addEventListener('pageshow', function() {
            submitBtn.disabled = false;
            btnText.style.opacity = '1';
            loadingSpinner.style.display = 'none';
        });
    }
}

// Enhanced form validation
function initializeFormValidation() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('loginBtn');
    
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function validateForm() {
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        
        const isEmailValid = validateEmail(email);
        const isPasswordValid = password.length >= 1;
        
        // Update submit button state only
        if (submitBtn && submitBtn.type === 'submit') {
            if (isEmailValid && isPasswordValid) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
            }
        }
        
        return isEmailValid && isPasswordValid;
    }
    
    // Real-time validation
    if (emailInput && passwordInput && submitBtn) {
        emailInput.addEventListener('input', validateForm);
        passwordInput.addEventListener('input', validateForm);
        
        // Initial validation
        validateForm();
        
        // Enhanced visual feedback
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !validateEmail(email)) {
                this.style.borderColor = 'var(--error-color)';
                showInputError(this, 'Please enter a valid email address');
            } else {
                this.style.borderColor = '';
                hideInputError(this);
            }
        });
        
        emailInput.addEventListener('focus', function() {
            this.style.borderColor = '';
            hideInputError(this);
        });
    }
}

// Show input error
function showInputError(input, message) {
    hideInputError(input); // Remove existing error first
    
    const errorElement = document.createElement('span');
    errorElement.className = 'error-text dynamic-error';
    errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    input.parentNode.parentNode.appendChild(errorElement);
}

// Hide input error
function hideInputError(input) {
    const existingError = input.parentNode.parentNode.querySelector('.dynamic-error');
    if (existingError) {
        existingError.remove();
    }
}

// Initialize animations and interactions
function initializeAnimations() {
    // Add focus/blur animations to input fields
    const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentNode.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentNode.classList.remove('focused');
            }
        });
        
        // Check if input has value on page load
        if (input.value) {
            input.parentNode.classList.add('focused');
        }
    });
    
    // Add smooth transitions to alert messages
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.animation = 'slideIn 0.3s ease-out';
    });
    
    // Keyboard navigation enhancement
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const submitBtn = document.getElementById('loginBtn');
            if (submitBtn && !submitBtn.disabled) {
                // Let the form handle the submission naturally
            }
        }
    });
}

// Auto-hide alerts after a delay
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.3s ease-out';
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.style.display = 'none';
        }, 300);
    });
}, 5000); // Hide after 5 seconds

// Add some interactive feedback
document.addEventListener('click', function(e) {
    // Add ripple effect to buttons (but not to links)
    if (e.target.matches('.btn-primary') && e.target.tagName === 'BUTTON') {
        createRipple(e.target, e);
    }
});

// Create ripple effect
function createRipple(button, event) {
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    button.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}
