/**
 * Admin Reports JavaScript
 * Handles report generation and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Reports page specific JavaScript
    console.log('Reports page loaded');
    
    // Initialize report generation functionality
    initializeReportGeneration();
    
    // Initialize date pickers if available
    initializeDatePickers();
});

function initializeReportGeneration() {
    // Quick report buttons
    const quickReportButtons = document.querySelectorAll('.quick-reports-actions .btn');
    quickReportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reportType = this.textContent.trim();
            generateQuickReport(reportType);
        });
    });
    
    // Custom report form
    const customReportForm = document.querySelector('.custom-reports-form');
    if (customReportForm) {
        const generateButton = customReportForm.querySelector('.btn-primary');
        if (generateButton) {
            generateButton.addEventListener('click', function(e) {
                e.preventDefault();
                generateCustomReport();
            });
        }
    }
}

function initializeDatePickers() {
    // Initialize date inputs with proper formatting
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            // Set default date range (last 30 days)
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            
            if (input.name === 'start_date') {
                input.value = thirtyDaysAgo.toISOString().split('T')[0];
            } else if (input.name === 'end_date') {
                input.value = today.toISOString().split('T')[0];
            }
        }
    });
}

function generateQuickReport(reportType) {
    // Show loading state
    showLoadingState();
    
    // Simulate report generation (replace with actual API call)
    setTimeout(() => {
        hideLoadingState();
        showSuccessMessage(`${reportType} generated successfully!`);
    }, 2000);
}

function generateCustomReport() {
    const form = document.querySelector('.custom-reports-form');
    const formData = new FormData(form);
    
    // Validate form data
    if (!validateCustomReportForm(formData)) {
        return;
    }
    
    // Show loading state
    showLoadingState();
    
    // Simulate report generation (replace with actual API call)
    setTimeout(() => {
        hideLoadingState();
        showSuccessMessage('Custom report generated successfully!');
    }, 3000);
}

function validateCustomReportForm(formData) {
    const reportType = formData.get('report_type');
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    
    if (!reportType) {
        showErrorMessage('Please select a report type.');
        return false;
    }
    
    if (!startDate || !endDate) {
        showErrorMessage('Please select both start and end dates.');
        return false;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        showErrorMessage('Start date cannot be after end date.');
        return false;
    }
    
    return true;
}

function showLoadingState() {
    // Add loading spinner or disable buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    });
}

function hideLoadingState() {
    // Remove loading state and restore buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.disabled = false;
        // Restore original button text (you might want to store this)
        if (button.querySelector('.fa-spinner')) {
            button.innerHTML = button.innerHTML.replace('<i class="fas fa-spinner fa-spin"></i> Generating...', 'Generate Report');
        }
    });
}

function showSuccessMessage(message) {
    // Create and show success notification
    const notification = createNotification(message, 'success');
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function showErrorMessage(message) {
    // Create and show error notification
    const notification = createNotification(message, 'error');
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function createNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius);
        color: white;
        font-weight: 500;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
        background: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
    `;
    notification.textContent = message;
    
    return notification;
}
