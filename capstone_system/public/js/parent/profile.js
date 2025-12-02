// Advanced Parent Profile JavaScript
// ==========================================

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
        const text = stat.textContent.trim();
        const target = parseInt(text);
        
        // Skip if not a number or is a string like "Active"
        if (isNaN(target)) return;
        
        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                stat.textContent = target;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current);
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
