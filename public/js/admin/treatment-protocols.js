// Modern Treatment Protocols JavaScript

// Print protocol functionality
function printProtocol(protocolName) {
    const activeTab = document.querySelector('.tab-pane-modern.show.active');
    if (!activeTab) return;

    const printContent = activeTab.cloneNode(true);
    
    // Remove action buttons from print content
    const actionButtons = printContent.querySelectorAll('.btn-protocol-action, .btn-expand-data, .btn-copy-data');
    actionButtons.forEach(btn => btn.remove());

    // Create print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Treatment Protocol: ${protocolName}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 2rem; color: #1f2937; }
                .protocol-content-modern { max-width: none; }
                .protocol-header-modern { margin-bottom: 2rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 1rem; }
                .protocol-title-section h4 { font-size: 2rem; margin: 0 0 1rem 0; }
                .protocol-badges { display: flex; gap: 0.5rem; flex-wrap: wrap; }
                .protocol-badge { padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; }
                .protocol-badge.evidence { background: #10b981; color: white; }
                .protocol-badge.who { background: #3b82f6; color: white; }
                .protocol-badge.pediatric { background: #f59e0b; color: white; }
                .protocol-overview { margin: 2rem 0; padding: 1.5rem; background: #f0f9ff; border-radius: 10px; }
                .overview-content h5 { margin: 0 0 1rem 0; color: #0c4a6e; }
                .protocol-sections { margin-top: 2rem; }
                .protocol-card-section { margin-bottom: 2rem; border: 1px solid #e5e7eb; border-radius: 10px; }
                .section-header-card { padding: 1rem; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
                .section-header-card h6 { margin: 0; font-size: 1.1rem; }
                .section-content-card { padding: 1.5rem; }
                .treatment-steps { list-style: decimal; padding-left: 2rem; }
                .treatment-steps li { margin-bottom: 1rem; }
                .population-specs, .spec-item { margin-bottom: 0.5rem; }
                .spec-label { font-weight: bold; }
                .raw-data-modern { display: none; }
                @media print { body { margin: 1rem; } }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>Clinical Treatment Protocol</h1>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
            </div>
            ${printContent.innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);

    showNotification('Protocol prepared for printing', 'success');
}

// Download protocol functionality
function downloadProtocol(protocolName) {
    const activeTab = document.querySelector('.tab-pane-modern.show.active');
    if (!activeTab) return;

    // Extract protocol data
    const protocolData = {
        name: protocolName,
        title: activeTab.querySelector('h4')?.textContent || protocolName,
        overview: activeTab.querySelector('.overview-content p')?.textContent || '',
        treatmentSteps: Array.from(activeTab.querySelectorAll('.treatment-steps li')).map(li => li.textContent.trim()),
        populationSpecs: Array.from(activeTab.querySelectorAll('.spec-item')).map(item => ({
            label: item.querySelector('.spec-label')?.textContent || '',
            value: item.querySelector('.spec-value')?.textContent || ''
        })),
        generatedDate: new Date().toISOString(),
        source: 'WHO Clinical Guidelines'
    };

    // Convert to JSON and download
    const jsonContent = JSON.stringify(protocolData, null, 2);
    downloadFile(jsonContent, `${protocolName}-protocol.json`, 'application/json');
    
    showNotification('Protocol downloaded successfully', 'success');
}

// Copy protocol data functionality
function copyProtocolData(index) {
    const preElement = document.getElementById(`protocolData${index}`);
    if (!preElement) return;

    const textContent = preElement.textContent;
    
    navigator.clipboard.writeText(textContent).then(() => {
        showNotification('Protocol data copied to clipboard', 'success');
        
        // Visual feedback
        const copyBtn = preElement.closest('.raw-data-modern').querySelector('.btn-copy-data');
        const originalIcon = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
        copyBtn.style.color = '#10b981';
        
        setTimeout(() => {
            copyBtn.innerHTML = originalIcon;
            copyBtn.style.color = '';
        }, 2000);
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = textContent;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        showNotification('Protocol data copied to clipboard', 'success');
    });
}

// Helper function to download files
function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Modern notification system
function showNotification(message, type = 'info', duration = 5000) {
    const existing = document.querySelector('.modern-notification');
    if (existing) existing.remove();

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
                position: fixed; top: 2rem; right: 2rem; z-index: 10000;
                min-width: 300px; max-width: 500px; background: white;
                border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                border-left: 4px solid; animation: slideInRight 0.3s ease-out;
            }
            .modern-notification.success { border-left-color: #10b981; }
            .modern-notification.error { border-left-color: #ef4444; }
            .modern-notification.info { border-left-color: #3b82f6; }
            .modern-notification.warning { border-left-color: #f59e0b; }
            .notification-content { display: flex; align-items: flex-start; gap: 1rem; padding: 1.5rem; }
            .notification-icon { width: 2rem; height: 2rem; border-radius: 50%; display: flex;
                align-items: center; justify-content: center; color: white; flex-shrink: 0; }
            .modern-notification.success .notification-icon { background: #10b981; }
            .modern-notification.error .notification-icon { background: #ef4444; }
            .modern-notification.info .notification-icon { background: #3b82f6; }
            .modern-notification.warning .notification-icon { background: #f59e0b; }
            .notification-message { flex: 1; color: #374151; line-height: 1.5; }
            .notification-close { background: none; border: none; color: #9ca3af; cursor: pointer;
                padding: 0; width: 1.5rem; height: 1.5rem; display: flex; align-items: center;
                justify-content: center; border-radius: 50%; transition: all 0.2s; }
            .notification-close:hover { background: #f3f4f6; color: #374151; }
            @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; } }
        `;
        document.head.appendChild(styles);
    }

    document.body.appendChild(notification);
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

// Enhanced tab functionality
function initializeModernTabs() {
    const tabButtons = document.querySelectorAll('.nav-tab-modern');
    const tabPanes = document.querySelectorAll('.tab-pane-modern');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-bs-target');
            
            // Remove active class from all tabs and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding pane
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
                
                // Animate the content
                targetPane.style.opacity = '0';
                targetPane.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    targetPane.style.transition = 'all 0.3s ease-out';
                    targetPane.style.opacity = '1';
                    targetPane.style.transform = 'translateY(0)';
                }, 50);
            }
        });
    });
}

// Protocol card animations
function initializeCardAnimations() {
    const cards = document.querySelectorAll('.metric-card-modern, .clinical-info-card, .protocol-card-section');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
}

// Expand/collapse animations for raw data
function initializeExpandAnimations() {
    const expandButtons = document.querySelectorAll('.btn-expand-data');
    
    expandButtons.forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                icon.style.transform = 'rotate(0deg)';
                this.querySelector('span').textContent = 'View Raw Protocol Data';
            } else {
                icon.style.transform = 'rotate(180deg)';
                this.querySelector('span').textContent = 'Hide Raw Protocol Data';
            }
        });
    });
}

// Smooth scroll to active tab
function scrollToActiveTab() {
    const activeTab = document.querySelector('.nav-tab-modern.active');
    if (activeTab) {
        activeTab.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest',
            inline: 'center'
        });
    }
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modern tabs if not using Bootstrap
    if (!window.bootstrap) {
        initializeModernTabs();
    }
    
    // Initialize animations
    initializeCardAnimations();
    initializeExpandAnimations();
    
    // Scroll to active tab on load
    setTimeout(scrollToActiveTab, 500);
    
    // Add stagger animation to metric cards
    const metricCards = document.querySelectorAll('.metric-card-modern');
    metricCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });
    
    // Add stagger animation to info cards
    const infoCards = document.querySelectorAll('.clinical-info-card');
    infoCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 800 + (index * 150));
    });
    
    console.log('🏥 Treatment Protocols page initialized with modern functionality');
});

// Handle window resize for responsive adjustments
window.addEventListener('resize', function() {
    // Adjust tab navigation on mobile
    const tabsContainer = document.querySelector('.nav-tabs-modern');
    if (tabsContainer && window.innerWidth < 768) {
        tabsContainer.scrollLeft = 0;
    }
});