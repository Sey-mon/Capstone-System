// Modern Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Initialize sidebar state - start visible by default
    const sidebarHidden = localStorage.getItem('sidebarHidden') === 'true';
    if (sidebarHidden) {
        // Hide sidebar if previously hidden
        sidebar.classList.add('mobile-hidden');
    } else {
        // Sidebar starts visible
        sidebar.classList.remove('mobile-hidden');
    }
    
    // Desktop sidebar toggle - toggles visibility
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-hidden');
            
            // Save state to localStorage
            const isHidden = sidebar.classList.contains('mobile-hidden');
            localStorage.setItem('sidebarHidden', isHidden);
            
            // Dispatch custom event for other components to listen
            window.dispatchEvent(new CustomEvent('sidebarToggle', {
                detail: { hidden: isHidden }
            }));
        });
    }
    
    // Mobile menu toggle
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-hidden');
            
            // Save state to localStorage
            const isHidden = sidebar.classList.contains('mobile-hidden');
            localStorage.setItem('sidebarHidden', isHidden);
        });
    }
    
    // Mobile overlay click
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.add('mobile-hidden');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Save state to localStorage
            localStorage.setItem('sidebarHidden', true);
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop view
            sidebar.classList.remove('mobile-active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Restore collapsed state on desktop
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            }
        } else {
            // Mobile view - remove collapsed class
            sidebar.classList.remove('collapsed');
        }
    });
    
    // Set active navigation link
    function setActiveNavLink() {
        const currentPath = window.location.pathname;
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            
            // Check if current path matches the link href
            const linkPath = new URL(link.href).pathname;
            if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
                link.classList.add('active');
            }
        });
    }
    
    // Initialize active nav link
    setActiveNavLink();
    
    // Handle navigation clicks
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Close mobile menu if open
            if (window.innerWidth <= 768 && sidebar.classList.contains('mobile-active')) {
                sidebar.classList.remove('mobile-active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Tooltip functionality for collapsed sidebar
    function initializeTooltips() {
        const navItems = document.querySelectorAll('.nav-link');
        
        navItems.forEach(item => {
            const text = item.querySelector('.nav-text');
            if (text) {
                item.setAttribute('title', text.textContent);
            }
        });
    }
    
    initializeTooltips();
    
    // Animation for stat cards
    function animateStatCards() {
        const statCards = document.querySelectorAll('.stat-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, {
            threshold: 0.1
        });
        
        statCards.forEach(card => {
            observer.observe(card);
        });
    }
    
    animateStatCards();
    
    // Auto-hide notifications
    function initializeNotifications() {
        const notifications = document.querySelectorAll('.notification');
        
        notifications.forEach(notification => {
            if (notification.dataset.autoHide !== 'false') {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 5000);
            }
        });
    }
    
    initializeNotifications();
    
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
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
    
    // Handle keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Toggle sidebar with Ctrl/Cmd + B
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            if (window.innerWidth > 768) {
                sidebarToggle.click();
            } else {
                mobileMenuBtn.click();
            }
        }
        
        // Close mobile menu with Escape
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-active')) {
            sidebar.classList.remove('mobile-active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Performance: Debounce resize handler
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Re-initialize tooltips if needed
            if (sidebar.classList.contains('collapsed')) {
                initializeTooltips();
            }
        }, 250);
    });
});

// Utility functions
window.DashboardUtils = {
    // Show notification
    showNotification: function(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Position notification
        const notifications = document.querySelectorAll('.notification');
        const offset = (notifications.length - 1) * 70;
        notification.style.top = `${20 + offset}px`;
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.removeNotification(notification);
            }, duration);
        }
        
        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.removeNotification(notification);
        });
        
        return notification;
    },
    
    // Remove notification
    removeNotification: function(notification) {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    },
    
    // Get notification icon
    getNotificationIcon: function(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    },
    
    // Format numbers
    formatNumber: function(num, decimals = 0) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    },
    
    // Format currency
    formatCurrency: function(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },
    
    // Animate counter
    animateCounter: function(element, target, duration = 1000) {
        const start = parseInt(element.textContent) || 0;
        const increment = (target - start) / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= target) || (increment < 0 && current <= target)) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 16);
    }
};
