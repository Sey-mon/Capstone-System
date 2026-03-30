// Advanced Nutritionist Profile JavaScript
// ==========================================
// Modal functions are now handled by SweetAlert2 in the blade file

// Enhanced Notification Function
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds with fade out
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Smooth Scroll for Quick Actions
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth reveal animations on page load
    const cards = document.querySelectorAll('.content-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });
    
    // Animate quick stats on load
    const quickStats = document.querySelectorAll('.quick-stat');
    quickStats.forEach((stat, index) => {
        stat.style.opacity = '0';
        stat.style.transform = 'scale(0.9)';
        setTimeout(() => {
            stat.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            stat.style.opacity = '1';
            stat.style.transform = 'scale(1)';
        }, 50 * index);
    });
    
    // Animate stat numbers
    animateNumbers();
});

// Animate counting numbers
function animateNumbers() {
    const statValues = document.querySelectorAll('.quick-stat-value');
    statValues.forEach(stat => {
        const target = parseInt(stat.textContent);
        if (isNaN(target)) return;
        
        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                stat.textContent = target + (stat.textContent.includes('%') ? '%' : '');
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current) + (stat.textContent.includes('%') ? '%' : '');
            }
        }, 30);
    });
}

// Add ripple effect to buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn') || 
        e.target.classList.contains('action-btn') ||
        e.target.closest('.btn') ||
        e.target.closest('.action-btn')) {
        
        const button = e.target.classList.contains('btn') || e.target.classList.contains('action-btn') 
            ? e.target 
            : e.target.closest('.btn') || e.target.closest('.action-btn');
        
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
});

// Add CSS for ripple effect dynamically
const style = document.createElement('style');
style.textContent = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Password strength validation function
function validatePasswordStrength(password) {
    const strengthIndicator = document.getElementById('password-strength');
    const strengthText = document.getElementById('strength-text');
    const strengthBars = document.querySelectorAll('.strength-bar');
    
    if (!password) {
        strengthIndicator.style.display = 'none';
        return;
    }
    
    strengthIndicator.style.display = 'block';
    
    let strength = 0;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };
    
    // Update requirements checklist
    updateRequirement('req-length', requirements.length);
    updateRequirement('req-uppercase', requirements.uppercase);
    updateRequirement('req-lowercase', requirements.lowercase);
    updateRequirement('req-number', requirements.number);
    updateRequirement('req-special', requirements.special);
    
    // Calculate strength
    Object.values(requirements).forEach(met => {
        if (met) strength++;
    });
    
    // Reset bars
    strengthBars.forEach(bar => {
        bar.style.background = '#e5e7eb';
    });
    
    // Update strength indicator
    if (strength <= 2) {
        strengthText.textContent = 'Weak';
        strengthText.style.color = '#ef4444';
        strengthBars[0].style.background = '#ef4444';
    } else if (strength === 3) {
        strengthText.textContent = 'Fair';
        strengthText.style.color = '#f59e0b';
        strengthBars[0].style.background = '#f59e0b';
        strengthBars[1].style.background = '#f59e0b';
    } else if (strength === 4) {
        strengthText.textContent = 'Good';
        strengthText.style.color = '#3b82f6';
        strengthBars[0].style.background = '#3b82f6';
        strengthBars[1].style.background = '#3b82f6';
        strengthBars[2].style.background = '#3b82f6';
    } else if (strength === 5) {
        strengthText.textContent = 'Strong';
        strengthText.style.color = '#10b981';
        strengthBars.forEach(bar => {
            bar.style.background = '#10b981';
        });
    }
}

// Update requirement item
function updateRequirement(id, met) {
    const element = document.getElementById(id);
    if (!element) return;
    
    const icon = element.querySelector('i');
    if (met) {
        element.style.color = '#10b981';
        icon.className = 'fas fa-check-circle';
        icon.style.color = '#10b981';
        icon.style.fontSize = '12px';
    } else {
        element.style.color = '#6b7280';
        icon.className = 'fas fa-circle';
        icon.style.color = '#d1d5db';
        icon.style.fontSize = '6px';
    }
}

// Password match validation
function validatePasswordMatch(password, confirmPassword) {
    const matchIndicator = document.getElementById('password-match');
    const mismatchIndicator = document.getElementById('password-mismatch');
    
    if (!confirmPassword) {
        matchIndicator.style.display = 'none';
        mismatchIndicator.style.display = 'none';
        return;
    }
    
    if (password === confirmPassword) {
        matchIndicator.style.display = 'block';
        mismatchIndicator.style.display = 'none';
    } else {
        matchIndicator.style.display = 'none';
        mismatchIndicator.style.display = 'block';
    }
}

// Toggle password visibility
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.style.color = '#10b981';
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.style.color = '#6b7280';
    }
}
