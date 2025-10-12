// Modern API Management JavaScript

// Get route from DOM
const apiManagementStatusRoute = document.getElementById('apiManagementStatusRoute')?.value || '';

// Modern notification system
function showNotification(message, type = 'info', duration = 5000) {
    // Remove existing notification
    const existing = document.querySelector('.modern-notification');
    if (existing) {
        existing.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `modern-notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <div class="notification-icon">
                <i class="fas ${getIconForType(type)}"></i>
            </div>
            <div class="notification-message">${message}</div>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    // Add styles if not already present
    if (!document.querySelector('#notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .modern-notification {
                position: fixed;
                top: 2rem;
                right: 2rem;
                z-index: 10000;
                min-width: 300px;
                max-width: 500px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                border-left: 4px solid;
                animation: slideInRight 0.3s ease-out;
            }
            .modern-notification.success { border-left-color: #10b981; }
            .modern-notification.error { border-left-color: #ef4444; }
            .modern-notification.info { border-left-color: #3b82f6; }
            .modern-notification.warning { border-left-color: #f59e0b; }
            .notification-content {
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                padding: 1.5rem;
            }
            .notification-icon {
                width: 2rem;
                height: 2rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                flex-shrink: 0;
            }
            .modern-notification.success .notification-icon { background: #10b981; }
            .modern-notification.error .notification-icon { background: #ef4444; }
            .modern-notification.info .notification-icon { background: #3b82f6; }
            .modern-notification.warning .notification-icon { background: #f59e0b; }
            .notification-message {
                flex: 1;
                color: #374151;
                line-height: 1.5;
            }
            .notification-close {
                background: none;
                border: none;
                color: #9ca3af;
                cursor: pointer;
                padding: 0;
                width: 1.5rem;
                height: 1.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.2s;
            }
            .notification-close:hover {
                background: #f3f4f6;
                color: #374151;
            }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }

    document.body.appendChild(notification);

    // Auto remove after duration
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, duration);
}

function getIconForType(type) {
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-triangle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-circle'
    };
    return icons[type] || 'fa-info-circle';
}

// Enhanced API status check with modern UI
async function checkApiStatus() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.innerHTML = `
        <i class="fas fa-spinner fa-spin"></i>
        Checking...
    `;
    button.disabled = true;

    try {
        const response = await fetch(apiManagementStatusRoute);
        const data = await response.json();
        
        if (data.success) {
            const status = data.data.status;
            const message = data.data.message || 'API is running normally';
            
            showNotification(
                `<strong>API Status:</strong> ${status.toUpperCase()}<br><small>${message}</small>`,
                status === 'healthy' ? 'success' : 'warning'
            );
            
            // Update page status if needed
            updatePageStatus(data.data);
        } else {
            showNotification(
                `<strong>API Check Failed:</strong><br>${data.error}`,
                'error'
            );
        }
    } catch (error) {
        showNotification(
            `<strong>Connection Error:</strong><br>Unable to check API status. Please try again.`,
            'error'
        );
        console.error('API Status Check Error:', error);
    } finally {
        // Restore button
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.disabled = false;
        }, 1000);
    }
}

// Copy to clipboard functionality
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showNotification(
            `<strong>Copied to clipboard!</strong><br><code>${text}</code>`,
            'success',
            3000
        );
    } catch (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        showNotification(
            `<strong>Copied to clipboard!</strong><br><code>${text}</code>`,
            'success',
            3000
        );
    }
}

// Update page status dynamically
function updatePageStatus(statusData) {
    const statusCards = document.querySelectorAll('.stat-card-modern');
    statusCards.forEach(card => {
        if (card.querySelector('.stat-title').textContent === 'API Status') {
            const isHealthy = statusData.status === 'healthy';
            card.className = `stat-card-modern ${isHealthy ? 'success' : 'danger'}`;
            card.querySelector('.stat-value').textContent = statusData.status.charAt(0).toUpperCase() + statusData.status.slice(1);
            card.querySelector('.stat-description').textContent = statusData.message || 'API is running normally';
            card.querySelector('.stat-badge').textContent = isHealthy ? 'ONLINE' : 'OFFLINE';
        }
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scroll behavior to action cards
    const actionCards = document.querySelectorAll('.action-card-modern');
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    // Add interactive effects to config cards
    const configCards = document.querySelectorAll('.config-card-modern');
    configCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.01)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    console.log('🚀 API Management page initialized with modern design');
});
