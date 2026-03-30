/**
 * Bootstrap Modal Management JavaScript
 */

// Global modal instance
let reportModalInstance = null;

/**
 * Initialize Bootstrap modal
 */
function initializeModal() {
    const modalElement = document.getElementById('reportModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        reportModalInstance = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
    }
}

/**
 * Close report modal using Bootstrap
 */
function closeReportModal() {
    console.log('closeReportModal() called');
    try {
        if (reportModalInstance) {
            console.log('Closing Bootstrap modal instance');
            reportModalInstance.hide();
        } else {
            // Fallback: try to get instance
            const modalElement = document.getElementById('reportModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const instance = bootstrap.Modal.getInstance(modalElement);
                if (instance) {
                    console.log('Found existing Bootstrap modal instance, closing');
                    instance.hide();
                } else {
                    console.log('No Bootstrap instance found, creating and closing');
                    const newInstance = new bootstrap.Modal(modalElement);
                    newInstance.hide();
                }
            }
        }
        
        window.currentReportData = null;
        console.log('Modal closed successfully');
    } catch (error) {
        console.error('Error in closeReportModal:', error);
        // Fallback to force close
        const modal = document.getElementById('reportModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
        }
    }
}

/**
 * Download current report as PDF
 */
function downloadReport() {
    if (!window.currentReportData) {
        showAlert('No report data available for download', 'error');
        return;
    }
    
    const { type, data } = window.currentReportData;
    const downloadUrl = `/admin/reports/${type}/download`;
    
    const downloadBtn = document.getElementById('downloadReportBtn');
    if (downloadBtn) {
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
        downloadBtn.disabled = true;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = downloadUrl;
        form.style.display = 'none';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }
        
        const dataInput = document.createElement('input');
        dataInput.type = 'hidden';
        dataInput.name = 'report_data';
        dataInput.value = JSON.stringify(data);
        form.appendChild(dataInput);
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        setTimeout(() => {
            downloadBtn.innerHTML = originalText;
            downloadBtn.disabled = false;
            showAlert('PDF download started', 'success');
        }, 1000);
    }
}

/**
 * Initialize modal when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeModal();
    
    // Set up download button event
    const downloadBtn = document.getElementById('downloadReportBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', downloadReport);
    }
    
    console.log('Bootstrap modal initialized');
});
function downloadReport() {
    if (!window.currentReportData) {
        alert('No report data available for download');
        return;
    }
    
    const { type, data } = window.currentReportData;
    const downloadUrl = `/admin/reports/${type}/download`;
    
    const downloadBtn = document.getElementById('downloadReportBtn');
    if (downloadBtn) {
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
        downloadBtn.disabled = true;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = downloadUrl;
        form.style.display = 'none';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }
        
        const dataInput = document.createElement('input');
        dataInput.type = 'hidden';
        dataInput.name = 'report_data';
        dataInput.value = JSON.stringify(data);
        form.appendChild(dataInput);
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        setTimeout(() => {
            downloadBtn.innerHTML = originalText;
            downloadBtn.disabled = false;
            if (typeof showAlert === 'function') {
                showAlert('PDF download started', 'success');
            } else {
                alert('PDF download started');
            }
        }, 1000);
    }
}

/**
 * Show report modal with data
 */
/**
 * Show report modal with Bootstrap
 */
function showReportModal(reportType, data) {
    const modal = document.getElementById('reportModal');
    const title = document.getElementById('reportModalLabel');
    const content = document.getElementById('reportModalContent');
    
    if (!modal || !title || !content) {
        showAlert('Modal elements not found. Please refresh the page.', 'error');
        return;
    }
    
    const titles = {
        'user-activity': 'User Activity Report',
        'inventory': 'Inventory Report',
        'assessment-trends': 'Assessment Trends Report',
        'low-stock': 'Low Stock Alert Report'
    };
    
    title.textContent = titles[reportType] || 'Report Results';
    
    try {
        const reportContent = generateReportContent(reportType, data);
        content.innerHTML = reportContent;
    } catch (error) {
        content.innerHTML = '<div class="empty-state">Error generating report content. Please try again.</div>';
    }
    
    window.currentReportData = { type: reportType, data: data };
    
    // Show modal using Bootstrap
    if (reportModalInstance) {
        reportModalInstance.show();
    } else {
        // Initialize and show
        initializeModal();
        if (reportModalInstance) {
            reportModalInstance.show();
        } else {
            // Fallback
            if (typeof bootstrap !== 'undefined') {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
        }
    }
}

/**
 * Event listeners for modal
 */
document.addEventListener('DOMContentLoaded', function() {
    // Set up close button events with multiple approaches
    setupModalCloseEvents();
});

function setupModalCloseEvents() {
    console.log('Setting up modal close events...');
    const closeBtn = document.getElementById('closeModalBtn');
    const closeFooterBtn = document.getElementById('closeModalFooterBtn');
    const downloadBtn = document.getElementById('downloadReportBtn');
    
    console.log('Close button (X):', closeBtn);
    console.log('Footer close button:', closeFooterBtn);
    console.log('Download button:', downloadBtn);
    
    // Close button (X)
    if (closeBtn) {
        console.log('Adding event listeners to close button (X)');
        closeBtn.onclick = function(e) {
            console.log('Close button (X) clicked via onclick');
            e.preventDefault();
            e.stopPropagation();
            closeReportModal();
            return false;
        };
        closeBtn.addEventListener('click', function(e) {
            console.log('Close button (X) clicked via addEventListener');
            e.preventDefault();
            closeReportModal();
        });
    } else {
        console.error('Close button (X) not found!');
    }
    
    // Footer close button
    if (closeFooterBtn) {
        console.log('Adding event listeners to footer close button');
        closeFooterBtn.onclick = function(e) {
            console.log('Footer close button clicked via onclick');
            e.preventDefault();
            e.stopPropagation();
            closeReportModal();
            return false;
        };
        closeFooterBtn.addEventListener('click', function(e) {
            console.log('Footer close button clicked via addEventListener');
            e.preventDefault();
            closeReportModal();
        });
    } else {
        console.error('Footer close button not found!');
    }
    
    // Download button
    if (downloadBtn) {
        downloadBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            downloadReport();
            return false;
        };
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            downloadReport();
        });
    }
    
    // Click outside to close
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('reportModal');
        if (event.target === modal) {
            closeReportModal();
        }
    });

    // Escape key to close
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('reportModal');
            if (modal && (modal.style.display === 'flex' || modal.classList.contains('show'))) {
                closeReportModal();
            }
        }
    });
    
    // Event delegation for close buttons (works even if buttons are dynamically created)
    document.addEventListener('click', function(event) {
        if (event.target.id === 'closeModalBtn' || 
            event.target.id === 'closeModalFooterBtn' ||
            event.target.classList.contains('close')) {
            console.log('Close button clicked via event delegation');
            event.preventDefault();
            event.stopPropagation();
            closeReportModal();
        }
    });
}
