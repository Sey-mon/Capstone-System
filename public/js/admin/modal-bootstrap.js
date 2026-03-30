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
        console.log('Bootstrap modal initialized');
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
 * Show report modal with Bootstrap
 */
function showReportModal(reportType, data) {
    const modal = document.getElementById('reportModal');
    const title = document.getElementById('reportModalLabel');
    const content = document.getElementById('reportModalContent');
    
    if (!modal || !title || !content) {
        if (typeof showAlert === 'function') {
            showAlert('Modal elements not found. Please refresh the page.', 'error');
        } else {
            alert('Modal elements not found. Please refresh the page.');
        }
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
        console.error('Error generating report content:', error);
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
 * Download current report as PDF
 */
function downloadReport() {
    if (!window.currentReportData) {
        if (typeof showAlert === 'function') {
            showAlert('No report data available for download', 'error');
        } else {
            alert('No report data available for download');
        }
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
 * Initialize modal when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeModal();
    
    // Set up download button event
    const downloadBtn = document.getElementById('downloadReportBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            downloadReport();
        });
    }
    
    console.log('Bootstrap modal system initialized');
});
