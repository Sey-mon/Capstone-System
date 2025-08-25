/**
 * Admin Reports JavaScript - Main Controller
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

/**
 * Generate and display report
 */
function generateReport(reportType) {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    button.disabled = true;
    
    fetch(`/admin/reports/${reportType}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showReportModal(reportType, data.data);
            } else {
                showAlert('Error generating report: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            showAlert('Error generating report: ' + error.message, 'error');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

/**
 * Generate inventory report content
 */
function generateInventoryContent(data) {
    return `
        <div class="report-summary">
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-label">Total Items</div>
                    <div class="stat-value">${data.total_items}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Value</div>
                    <div class="stat-value">₱${(data.total_value || 0).toLocaleString()}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Low Stock Items</div>
                    <div class="stat-value">${(data.low_stock_items || []).length}</div>
                </div>
            </div>
        </div>
        
        <div class="report-section">
            <h4>Stock Levels</h4>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(data.stock_levels || []).map(item => `
                            <tr>
                                <td>${item.name}</td>
                                <td>${item.quantity}</td>
                                <td>${item.unit}</td>
                                <td><span class="status-badge status-${item.status.toLowerCase()}">${item.status}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

/**
 * Generate assessment trends report content
 */
function generateAssessmentTrendsContent(data) {
    return `
        <div class="report-summary">
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-label">Total Assessments</div>
                    <div class="stat-value">${data.total_assessments}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Completed</div>
                    <div class="stat-value">${data.completed_assessments}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">${data.pending_assessments}</div>
                </div>
            </div>
        </div>
        
        <div class="report-section">
            <h4>Assessments by Month</h4>
            <div class="data-grid">
                ${Object.entries(data.assessments_by_month || {}).map(([month, count]) => `
                    <div class="data-item">
                        <span>${month}</span>
                        <span class="data-value">${count}</span>
                    </div>
                `).join('')}
            </div>
        </div>
        
        <div class="report-section">
            <h4>Assessments by Barangay</h4>
            <div class="data-grid">
                ${Object.entries(data.assessments_by_barangay || {}).map(([barangay, count]) => `
                    <div class="data-item">
                        <span>${barangay}</span>
                        <span class="data-value">${count}</span>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

/**
 * Generate low stock report content
 */
function generateLowStockContent(data) {
    return `
        <div class="report-summary">
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-label">Critical Items</div>
                    <div class="stat-value">${(data.critical_items || []).length}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Low Items</div>
                    <div class="stat-value">${(data.low_items || []).length}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Affected Value</div>
                    <div class="stat-value">₱${(data.total_affected_value || 0).toLocaleString()}</div>
                </div>
            </div>
        </div>
        
        <div class="report-section">
            <h4>Restock Recommendations</h4>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Current Stock</th>
                            <th>Recommended Order</th>
                            <th>Estimated Cost</th>
                            <th>Urgency</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(data.recommendations || []).map(rec => `
                            <tr>
                                <td>${rec.item}</td>
                                <td>${rec.current_stock}</td>
                                <td>${rec.recommended_order}</td>
                                <td>₱${rec.estimated_cost?.toLocaleString()}</td>
                                <td><span class="urgency-badge urgency-${rec.urgency.toLowerCase()}">${rec.urgency}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

/**
 * Close report modal
 */
function closeReportModal() {
    const modal = document.getElementById('reportModal');
    modal.style.display = 'none';
    window.currentReportData = null;
}

/**
 * Download current report
 */
function downloadReport() {
    if (!window.currentReportData) {
        showAlert('No report data available for download', 'error');
        return;
    }
    
    const { type, data } = window.currentReportData;
    
    // Create download URL for PDF
    const downloadUrl = `/admin/reports/${type}/download`;
    
    // Show loading state
    const downloadBtn = document.querySelector('.modal-footer .btn-primary');
    const originalText = downloadBtn.innerHTML;
    downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
    downloadBtn.disabled = true;
    
    // Create a form to submit the report data for PDF generation
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = downloadUrl;
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Add report data
    const dataInput = document.createElement('input');
    dataInput.type = 'hidden';
    dataInput.name = 'report_data';
    dataInput.value = JSON.stringify(data);
    form.appendChild(dataInput);
    
    // Submit form
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    // Restore button state after a delay
    setTimeout(() => {
        downloadBtn.innerHTML = originalText;
        downloadBtn.disabled = false;
        showAlert('PDF download started', 'success');
    }, 1000);
}

/**
 * Update chart period
 */
function updateChartPeriod(period) {
    // Update button states
    document.querySelectorAll('.card-header button').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-secondary');
    });
    
    event.target.classList.remove('btn-secondary');
    event.target.classList.add('btn-primary');
    
    // TODO: Update chart data based on period
    console.log('Updating chart for period:', period);
}

/**
 * Initialize charts
 */
function initializeCharts() {
    // Check if Chart.js is available and if we have data
    if (typeof Chart !== 'undefined') {
        initTrendsChart();
    }
}

/**
 * Initialize trends chart
 */
function initTrendsChart() {
    const canvas = document.getElementById('trendsChart');
    if (!canvas) return;
    
    // Sample data - replace with actual data from backend
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Assessments',
                data: [12, 19, 3, 5, 2, 3, 7],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button type="button" class="close" onclick="this.parentElement.remove()">
            <span>&times;</span>
        </button>
    `;
    
    // Add to page
    document.body.insertBefore(alert, document.body.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('reportModal');
    if (event.target === modal) {
        closeReportModal();
    }
}
