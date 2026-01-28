// Modern Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const floatingMenuBtn = document.getElementById('floatingMenuBtn');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Initialize sidebar state
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
    }
    
    // Function to toggle sidebar
    function toggleSidebar() {
        const isDesktop = window.innerWidth > 768;
        
        if (isDesktop) {
            // Add animation class
            sidebar.classList.add('toggling');
            
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            // Remove animation class after animation completes
            setTimeout(() => {
                sidebar.classList.remove('toggling');
            }, 300);
            
            // Dispatch custom event for other components to listen
            window.dispatchEvent(new CustomEvent('sidebarToggle', {
                detail: { collapsed: isCollapsed }
            }));
        }
    }
    
    // Desktop sidebar toggle - toggles collapsed state
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Floating menu button - shows sidebar when hidden
    if (floatingMenuBtn) {
        floatingMenuBtn.addEventListener('click', function() {
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Mobile behavior - show sidebar with overlay
                sidebar.classList.add('mobile-active');
                mobileOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                // Desktop behavior - show sidebar by removing collapsed class
                sidebar.classList.remove('collapsed');
                localStorage.setItem('sidebarCollapsed', false);
                
                window.dispatchEvent(new CustomEvent('sidebarToggle', {
                    detail: { collapsed: false }
                }));
            }
        });
    }
    
    // Mobile menu toggle
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Mobile behavior - show/hide sidebar with overlay
                const isActive = sidebar.classList.contains('mobile-active');
                
                if (isActive) {
                    // Close sidebar
                    sidebar.classList.remove('mobile-active');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                } else {
                    // Check if sidebar is collapsed, if so expand it first
                    if (sidebar.classList.contains('collapsed')) {
                        sidebar.classList.remove('collapsed');
                    }
                    // Open sidebar
                    sidebar.classList.add('mobile-active');
                    mobileOverlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            } else {
                // Desktop behavior - toggle collapsed state
                toggleSidebar();
            }
        });
    }
    
    // Mobile overlay click
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Handle window resize with debounce
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            handleResponsiveLayout();
        }, 150);
    });
    
    function handleResponsiveLayout() {
        const isMobile = window.innerWidth <= 768;
        const isDesktop = window.innerWidth > 768;
        
        if (isMobile) {
            // Mobile view - ensure sidebar starts hidden
            sidebar.classList.remove('mobile-active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Remove collapsed class on mobile to show full sidebar when active
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
            }
        } else {
            // Desktop view - restore collapsed state
            sidebar.classList.remove('mobile-active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Restore collapsed state on desktop
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }
    }
    
    // Initialize responsive layout
    handleResponsiveLayout();
    
    // Set active navigation link
    function setActiveNavLink() {
        const currentPath = window.location.pathname;
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            
            // Check if current path matches the link href
            const linkPath = new URL(link.href, window.location.origin).pathname;
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
            
            // Close mobile menu if open and on mobile
            const isMobile = window.innerWidth <= 768;
            if (isMobile && sidebar.classList.contains('mobile-active')) {
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
                item.setAttribute('title', text.textContent.trim());
            }
        });
        
        // Also add tooltip for logout button
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            const span = logoutBtn.querySelector('span');
            if (span) {
                logoutBtn.setAttribute('title', span.textContent.trim());
            }
        }
    }
    
    initializeTooltips();
    
    // Update tooltips when sidebar state changes
    window.addEventListener('sidebarToggle', function() {
        // Small delay to allow transition to complete
        setTimeout(initializeTooltips, 100);
    });
    
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
            const href = this.getAttribute('href');
            // Skip if href is just '#' or if link has other handlers (like sort links)
            if (href === '#' || href.length <= 1 || this.classList.contains('sort-link')) {
                return;
            }
            e.preventDefault();
            const target = document.querySelector(href);
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
