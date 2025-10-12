/**
 * Admin Reports JavaScript - Main Controller
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializeEventListeners();
});

/**
 * Initialize event listeners for report buttons and chart controls
 */
function initializeEventListeners() {
    // Report generation buttons
    document.querySelectorAll('[data-report-type]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const reportType = this.getAttribute('data-report-type');
            generateReport(reportType, this);
        });
    });



    // Distribution view toggle buttons
    document.querySelectorAll('.toggle-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const view = this.getAttribute('data-view');
            toggleDistributionView(view);
        });
    });
}

/**
 * Generate and display report
 */
function generateReport(reportType, button) {
    if (!button) {
        console.error('Button element is required');
        return;
    }
    
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
 * Initialize charts
 */
function initializeCharts() {
    // Check if Chart.js is available and if we have data
    if (typeof Chart !== 'undefined') {
        initMonthlyProgressChart();
        initPatientDistributionChart();
    }
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

/**
 * Initialize monthly progress chart
 */
function initMonthlyProgressChart() {
    const canvas = document.getElementById('monthlyProgressChart');
    if (!canvas) return;
    
    // Get data from PHP (passed via data attributes or global variables)
    const monthlyData = window.monthlyProgressData || {
        months: ['May 2025', 'Jun 2025', 'Jul 2025', 'Aug 2025', 'Sep 2025', 'Oct 2025'],
        assessments: [0, 0, 0, 0, 0, 0],
        recovered: [0, 0, 0, 0, 0, 0]
    };
    
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.months,
            datasets: [{
                label: 'Assessments',
                data: monthlyData.assessments,
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Recovered',
                data: monthlyData.recovered,
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    radius: 6,
                    hoverRadius: 8
                }
            }
        }
    });
}

/**
 * Initialize patient distribution pie chart
 */
function initPatientDistributionChart() {
    const canvas = document.getElementById('patientDistributionChart');
    if (!canvas) return;
    
    // Get data from PHP (passed via global variables)
    const distributionData = window.patientDistributionData || {
        normal: { count: 0, percentage: 0 },
        underweight: { count: 0, percentage: 0 },
        malnourished: { count: 0, percentage: 0 },
        severe_malnourishment: { count: 0, percentage: 0 }
    };
    
    const ctx = canvas.getContext('2d');
    window.patientDistributionPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Normal Weight', 'Underweight', 'Malnourished', 'Severe Malnourishment'],
            datasets: [{
                data: [
                    distributionData.normal.count,
                    distributionData.underweight.count,
                    distributionData.malnourished.count,
                    distributionData.severe_malnourishment.count
                ],
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#991b1b'
                ],
                borderColor: [
                    '#059669',
                    '#f97316',
                    '#dc2626',
                    '#7f1d1d'
                ],
                borderWidth: 2,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // We'll use custom legend
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} patients (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '50%',
            animation: {
                animateRotate: true,
                duration: 1000
            }
        }
    });
}

/**
 * Toggle between distribution views (bars/pie chart)
 */
function toggleDistributionView(view) {
    // Update button states
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-view="${view}"]`).classList.add('active');
    
    // Update view visibility
    document.querySelectorAll('.distribution-view').forEach(viewEl => {
        viewEl.classList.remove('active');
    });
    
    if (view === 'bars') {
        document.getElementById('barsView').classList.add('active');
    } else if (view === 'pie') {
        document.getElementById('pieView').classList.add('active');
        
        // Ensure pie chart is rendered when view becomes visible
        if (window.patientDistributionPieChart) {
            setTimeout(() => {
                window.patientDistributionPieChart.resize();
            }, 100);
        }
    }
}
