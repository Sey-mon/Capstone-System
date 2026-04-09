/**
 * Admin Reports JavaScript - Main Controller
 */

// Load images for PDF header
let sanPedroLogo = null;
let bagongPilipinasLogo = null;

// Preload images
function preloadImages() {
    const sanPedroImg = new Image();
    sanPedroImg.src = '/img/san-pedro-logo.png';
    sanPedroImg.onload = function() {
        sanPedroLogo = sanPedroImg;
    };
    
    const bagongImg = new Image();
    bagongImg.src = '/img/bagong-pilipinas-logo.png';
    bagongImg.onload = function() {
        bagongPilipinasLogo = bagongImg;
    };
}

document.addEventListener('DOMContentLoaded', function() {
    preloadImages();
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
 * Handle CSV Export
 */
function setupCSVExportHandler() {
    const csvBtn = document.getElementById('csv-export-btn');
    if (csvBtn) {
        csvBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const link = document.createElement('a');
            link.href = '/admin/reports/malnutrition-cases/export-csv';
            link.click();
            
            showAlert('CSV file is being downloaded...', 'success', 2000);
        });
    }
}

/**
 * Handle Pagination for Severe Cases
 */
function setupSamPaginationHandlers() {
    let currentSamPage = 1;
    const samPrevBtn = document.querySelector('.sam-prev-page');
    const samNextBtn = document.querySelector('.sam-next-page');
    
    if (samNextBtn) {
        const totalPages = parseInt(samNextBtn.getAttribute('data-total-pages')) || 1;
        
        samNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentSamPage < totalPages) {
                currentSamPage++;
                loadSamCasesPage(currentSamPage, totalPages);
            }
        });
    }
    
    if (samPrevBtn) {
        samPrevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentSamPage > 1) {
                currentSamPage--;
                loadSamCasesPage(currentSamPage, parseInt(samNextBtn?.getAttribute('data-total-pages')) || 1);
            }
        });
    }
}

/**
 * Load SAM Cases Page via AJAX
 */
function loadSamCasesPage(page, totalPages) {
    const PAGINATION_THRESHOLD = 25;
    
    fetch(`/admin/reports/malnutrition-cases/severe/paginated?page=${page}&per_page=${PAGINATION_THRESHOLD}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const tbody = document.querySelector('h4:contains("Severe Acute Malnutrition (SAM)")') ? 
                              document.querySelector('h4:contains("Severe Acute Malnutrition (SAM)")').closest('.report-section').querySelector('tbody') : null;
                if (tbody) {
                    let rows = result.data.patients.map(patient => `
                        <tr>
                            <td>${patient.name}</td>
                            <td>${patient.barangay}</td>
                            <td style="text-align: center;">${patient.age || 'N/A'}</td>
                            <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.waz !== undefined && patient.waz !== null) ? patient.waz : 'N/A'}</td>
                            <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.haz !== undefined && patient.haz !== null) ? patient.haz : 'N/A'}</td>
                            <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.baz !== undefined && patient.baz !== null) ? patient.baz : 'N/A'}</td>
                            <td style="font-size: 0.813rem; color: #6b7280;">${patient.recovery_status || 'N/A'}</td>
                        </tr>
                    `).join('');
                    
                    tbody.innerHTML = rows;
                    
                    // Update pagination info
                    const pageInfo = document.getElementById('sam-page-info');
                    if (pageInfo) {
                        pageInfo.textContent = `Page ${page} of ${totalPages}`;
                    }
                    
                    // Update button states
                    const prevBtn = document.querySelector('.sam-prev-page');
                    const nextBtn = document.querySelector('.sam-next-page');
                    if (prevBtn) prevBtn.disabled = page === 1;
                    if (nextBtn) nextBtn.disabled = page === totalPages;
                }
            }
        })
        .catch(error => {
            console.error('Error loading SAM cases page:', error);
        });
}

/**
 * Handle Pagination for MAM Cases
 */
function setupMamPaginationHandlers() {
    let currentMamPage = 1;
    const mamPrevBtn = document.querySelector('.mam-prev-page');
    const mamNextBtn = document.querySelector('.mam-next-page');
    
    if (mamNextBtn) {
        const totalPages = parseInt(mamNextBtn.getAttribute('data-total-pages')) || 1;
        
        mamNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentMamPage < totalPages) {
                currentMamPage++;
                loadMamCasesPage(currentMamPage, totalPages);
            }
        });
    }
    
    if (mamPrevBtn) {
        mamPrevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentMamPage > 1) {
                currentMamPage--;
                loadMamCasesPage(currentMamPage, parseInt(mamNextBtn?.getAttribute('data-total-pages')) || 1);
            }
        });
    }
}

/**
 * Load MAM Cases Page via AJAX
 */
function loadMamCasesPage(page, totalPages) {
    const PAGINATION_THRESHOLD = 25;
    
    fetch(`/admin/reports/malnutrition-cases/malnourished/paginated?page=${page}&per_page=${PAGINATION_THRESHOLD}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Find MAM patients tbody
                const tableTitles = Array.from(document.querySelectorAll('h4'));
                const mamTitle = tableTitles.find(t => t.textContent.includes('Moderate Acute Malnutrition (MAM)'));
                const tbody = mamTitle?.closest('.report-section')?.querySelector('table tbody');
                
                if (tbody) {
                    let rows = result.data.patients.map(patient => `
                        <tr>
                            <td>${patient.name}</td>
                            <td>${patient.barangay}</td>
                            <td style="text-align: center;">${patient.age || 'N/A'}</td>
                            <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.waz !== undefined && patient.waz !== null) ? patient.waz : 'N/A'}</td>
                            <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.haz !== undefined && patient.haz !== null) ? patient.haz : 'N/A'}</td>
                            <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.baz !== undefined && patient.baz !== null) ? patient.baz : 'N/A'}</td>
                            <td style="font-size: 0.813rem; color: #6b7280;">${patient.recovery_status || 'N/A'}</td>
                        </tr>
                    `).join('');
                    
                    tbody.innerHTML = rows;
                    
                    // Update pagination info
                    const pageInfo = document.getElementById('mam-page-info');
                    if (pageInfo) {
                        pageInfo.textContent = `Page ${page} of ${totalPages}`;
                    }
                    
                    // Update button states
                    const prevBtn = document.querySelector('.mam-prev-page');
                    const nextBtn = document.querySelector('.mam-next-page');
                    if (prevBtn) prevBtn.disabled = page === 1;
                    if (nextBtn) nextBtn.disabled = page === totalPages;
                }
            }
        })
        .catch(error => {
            console.error('Error loading MAM cases page:', error);
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
    
    // Check if it's one of the essential reports that we generate on frontend
    const comprehensiveReports = [
        'malnutrition-cases', 'patient-progress', 'individual-patient', 'low-stock-alert', 'monthly-trends'
    ];
    
    if (comprehensiveReports.includes(reportType)) {
        // Generate comprehensive report locally
        setTimeout(() => {
            generateComprehensiveReport(reportType);
            button.innerHTML = originalText;
            button.disabled = false;
        }, 500);
        return;
    }
    
    // For existing reports, use API endpoint
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
 * Generate comprehensive report based on type
 */
function generateComprehensiveReport(reportType) {
    let title = '';
    let endpoint = '';
    
    switch(reportType) {
        case 'malnutrition-cases':
            title = 'Malnutrition Cases by Severity';
            endpoint = '/admin/reports/malnutrition-cases';
            break;
        case 'patient-progress':
            title = 'Patient Progress & Recovery Report';
            endpoint = '/admin/reports/patient-progress';
            break;
        case 'individual-patient':
            title = 'Individual Patient Report';
            // Special case - show patient selector first
            showIndividualPatientSelector();
            return;
        case 'low-stock-alert':
            title = 'Low Stock Alert Report';
            endpoint = '/admin/reports/low-stock-alert';
            break;
        case 'monthly-trends':
            title = 'Monthly Trends Analysis';
            endpoint = '/admin/reports/monthly-trends';
            break;
        default:
            showAlert('Unknown report type', 'error');
            return;
    }
    
    // Show loading state
    showReportModalWithContent(title, '<div style="text-align: center; padding: 3rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #2e7d32;"></i><p style="margin-top: 1rem; color: #6b7280;">Loading report data...</p></div>', reportType);
    
    // Fetch data from API
    fetch(endpoint)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                let content = '';
                switch(reportType) {
                    case 'malnutrition-cases':
                        // Sync fresh API data into global so PDF always uses current data
                        window.patientDistributionData = result.data;
                        content = generateMalnutritionCasesContent(result.data);
                        break;
                    case 'patient-progress':
                        // Sync fresh API data into global so PDF always uses current data
                        window.monthlyProgressData = result.data;
                        content = generatePatientProgressContent(result.data);
                        break;
                    case 'low-stock-alert':
                        content = generateLowStockAlertContent(result.data);
                        break;
                    case 'monthly-trends':
                        content = generateMonthlyTrendsContent(result.data);
                        break;
                }
                showReportModalWithContent(title, content, reportType);
            } else {
                showAlert('Error generating report: ' + (result.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching report:', error);
            showAlert('Error loading report: ' + error.message, 'error');
        });
}

/**
 * Generate inventory report content
 */
function generateInventoryContent(data) {
    const lowStockCount = (data.low_stock_items || []).length;
    const stockLevels = data.stock_levels || [];
    const goodStock = stockLevels.filter(item => item.status === 'Good').length;
    const mediumStock = stockLevels.filter(item => item.status === 'Medium').length;
    
    return `
        <div class="report-summary">
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-label">Total Items</div>
                    <div class="stat-value">${data.total_items || 0}</div>
                    <div class="stat-meta">In inventory</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Good Stock</div>
                    <div class="stat-value" style="color: #10b981">${goodStock}</div>
                    <div class="stat-meta">≥20 units</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Medium Stock</div>
                    <div class="stat-value" style="color: #f59e0b">${mediumStock}</div>
                    <div class="stat-meta">10-19 units</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Low Stock</div>
                    <div class="stat-value" style="color: #dc2626">${lowStockCount}</div>
                    <div class="stat-meta">< 10 units</div>
                </div>
            </div>
        </div>
        
        ${(() => {
            const usedItems = stockLevels.filter(item => (item.total_usage || 0) > 0);
            return usedItems.length > 0 ? `
                <div class="report-section">
                    <h4 style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chart-line" style="color: #2563eb;"></i>
                        Item Usage Summary
                    </h4>
                    <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 1.5rem; border-radius: 12px; border: 1px solid #bae6fd; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <i class="fas fa-info-circle" style="color: #0284c7;"></i>
                            <p style="font-size: 0.875rem; color: #0369a1; margin: 0; font-weight: 500;">
                                ${usedItems.length} items distributed/consumed • Showing items with usage only
                            </p>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem;">
                            ${usedItems.map(item => {
                                const usage = item.total_usage || 0;
                                // Color based on usage level
                                let bgColor = '#f0f9ff';
                                let borderColor = '#2563eb';
                                let textColor = '#1e40af';
                                
                                if (usage > 100) {
                                    bgColor = '#f0fdf4';
                                    borderColor = '#16a34a';
                                    textColor = '#15803d';
                                } else if (usage > 50) {
                                    bgColor = '#fefce8';
                                    borderColor = '#eab308';
                                    textColor = '#a16207';
                                }
                                
                                return `
                                    <div style="background: ${bgColor}; padding: 0.875rem; border-radius: 8px; border-left: 4px solid ${borderColor}; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                            <span style="font-size: 0.813rem; font-weight: 600; color: #1f2937; line-height: 1.2;">${item.item_name}</span>
                                            <i class="fas fa-arrow-trend-up" style="color: ${borderColor}; font-size: 0.75rem;"></i>
                                        </div>
                                        <div style="display: flex; align-items: baseline; gap: 0.375rem;">
                                            <span style="font-size: 1.5rem; font-weight: 700; color: ${textColor};">${usage}</span>
                                            <span style="font-size: 0.75rem; color: #6b7280; font-weight: 500;">${item.unit}</span>
                                        </div>
                                        <div style="font-size: 0.688rem; color: #9ca3af; margin-top: 0.25rem;">
                                            Distributed
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            ` : `
                <div class="report-section">
                    <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; text-align: center; border: 1px dashed #cbd5e1;">
                        <i class="fas fa-box-open" style="font-size: 2rem; color: #94a3b8; margin-bottom: 0.5rem;"></i>
                        <p style="color: #64748b; font-size: 0.875rem; margin: 0;">No items have been distributed yet</p>
                    </div>
                </div>
            `;
        })()}
        
        <div class="report-section">
            <h4>Complete Stock Levels</h4>
            ${stockLevels.length > 0 ? `
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Unit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${stockLevels.map(item => {
                                let statusClass = 'status-good';
                                let statusColor = '#10b981';
                                if (item.status === 'Low') {
                                    statusClass = 'status-low';
                                    statusColor = '#dc2626';
                                } else if (item.status === 'Medium') {
                                    statusClass = 'status-medium';
                                    statusColor = '#f59e0b';
                                }
                                return `
                                    <tr>
                                        <td><strong>${item.item_name || 'N/A'}</strong></td>
                                        <td>${item.category_name || 'Uncategorized'}</td>
                                        <td style="color: ${statusColor}; font-weight: 600;">${item.quantity}</td>
                                        <td>${item.unit}</td>
                                        <td><span class="status-badge ${statusClass}">${item.status}</span></td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            ` : `
                <p style="text-align: center; color: #6b7280; padding: 2rem;">No inventory items found.</p>
            `}
        </div>
        
        ${(data.items_by_category && Object.keys(data.items_by_category).length > 0) ? `
            <div class="report-section">
                <h4>Items by Category</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    ${Object.entries(data.items_by_category).map(([category, count]) => `
                        <div style="padding: 0.75rem; background: #f8fafc; border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #2e7d32;">${count}</div>
                            <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">${category}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : ''}
    `;
}

/**
 * Show report modal for API-based reports (user-activity, inventory)
 */
function showReportModal(reportType, data) {
    let title = '';
    let content = '';
    
    if (reportType === 'user-activity') {
        title = 'User Activity & System Usage Report';
        content = generateUserActivityContent(data);
        // Store data for PDF generation
        window.lastUserActivityData = data;
    } else if (reportType === 'inventory') {
        title = 'Complete Inventory Status Report';
        content = generateInventoryContent(data);
        // Store data for PDF generation
        window.lastInventoryData = data;
    } else {
        title = 'Report';
        content = '<p>Report data loaded successfully.</p>';
    }
    
    showReportModalWithContent(title, content, reportType);
}

/**
 * Generate user activity content
 */
function generateUserActivityContent(data) {
    // Get current date and 30 days ago as defaults
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30);
    
    const defaultStartDate = thirtyDaysAgo.toISOString().split('T')[0];
    const defaultEndDate = today.toISOString().split('T')[0];
    
    // Auto-apply filter after content loads
    setTimeout(() => applyUserActivityDateFilter(), 100);
    
    return `
        <div class="report-section">
            <h4><i class="fas fa-calendar-range" style="color: #2e7d32; margin-right: 0.5rem;"></i>Date Range Filter</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #f0f9ff; padding: 1rem; border-radius: 8px; border: 1px solid #bae6fd;">
                <div>
                    <label style="font-size: 0.875rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-calendar-day" style="color: #3b82f6;"></i> Start Date
                    </label>
                    <input type="date" id="userActivityStartDate" class="form-control" value="${defaultStartDate}"
                        onchange="applyUserActivityDateFilter()"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;">
                </div>
                <div>
                    <label style="font-size: 0.875rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-calendar-day" style="color: #3b82f6;"></i> End Date
                    </label>
                    <input type="date" id="userActivityEndDate" class="form-control" value="${defaultEndDate}"
                        onchange="applyUserActivityDateFilter()"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;">
                </div>
            </div>
        </div>
        
        <div id="userActivityResultsSection">
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3b82f6;"></i>
                <p style="margin-top: 1rem; color: #6b7280;">Loading user activity data...</p>
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
 * Add official header to PDF with logos
 */
function addPDFHeader(doc, reportTitle = '') {
    // Add San Pedro Logo (left)
    if (sanPedroLogo) {
        doc.addImage(sanPedroLogo, 'PNG', 15, 10, 25, 25);
    }
    
    // Add Bagong Pilipinas Logo (right)
    if (bagongPilipinasLogo) {
        doc.addImage(bagongPilipinasLogo, 'PNG', 170, 10, 25, 25);
    }
    
    // Center header text
    doc.setFontSize(9);
    doc.setTextColor(0, 0, 0);
    doc.setFont(undefined, 'normal');
    doc.text('Republic of the Philippines', 105, 14, { align: 'center' });
    
    doc.setFontSize(8);
    doc.text('Province of Laguna', 105, 18, { align: 'center' });
    
    // CITY OF SAN PEDRO - larger and bold
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(0, 51, 153); // Blue color
    doc.text('CITY OF SAN PEDRO', 105, 25, { align: 'center' });
    
    // CITY HEALTH OFFICE
    doc.setFontSize(11);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(64, 64, 64); // Dark gray
    doc.text('CITY HEALTH OFFICE', 105, 31, { align: 'center' });
    
    // Address and contact info - split into readable parts without emojis
    doc.setFontSize(7);
    doc.setFont(undefined, 'normal');
    doc.setTextColor(0, 0, 0);
    doc.text('4F, New City Hall Bldg, Brgy. Poblacion, San Pedro, Laguna | (02) 808 - 2020 local 302 |', 105, 36, { align: 'center' });
    doc.text('CHOsanpedro@gmail.com', 105, 40, { align: 'center' });
    
    // Add horizontal line separator
    doc.setDrawColor(0, 51, 153); // Blue line
    doc.setLineWidth(0.8);
    doc.line(15, 43, 195, 43);
    
    // Add report title below header if provided
    let yPos = 52;
    if (reportTitle) {
        doc.setFontSize(14);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(46, 125, 50);
        doc.text(reportTitle, 105, yPos, { align: 'center' });
        yPos += 8;
        
        // Add generation date
        doc.setFontSize(9);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(100, 100, 100);
        doc.text('Generated on: ' + new Date().toLocaleString(), 105, yPos, { align: 'center' });
        yPos += 10;
    }
    
    return yPos; // Return the Y position where content should start
}

/**
 * Generate PDF report using jsPDF
 */
function generatePDF(reportType, title, content) {
    Swal.fire({
        title: 'Generating PDF...',
        html: 'Please wait while we prepare your report.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Add official header with logos
            let yPos = addPDFHeader(doc, title);
            
            // Add report content based on type
            if (reportType === 'malnutrition-cases') {
                yPos = addMalnutritionCasesPDF(doc, yPos);
            } else if (reportType === 'patient-progress') {
                yPos = addPatientProgressPDF(doc, yPos);
            } else if (reportType === 'low-stock-alert') {
                yPos = addLowStockAlertPDF(doc, yPos);
            } else if (reportType === 'monthly-trends') {
                yPos = addMonthlyTrendsPDF(doc, yPos);
            } else if (reportType === 'inventory') {
                yPos = addInventoryPDF(doc, yPos);
            } else if (reportType === 'user-activity') {
                yPos = addUserActivityPDF(doc, yPos);
            } else if (reportType === 'individual-patient-detail') {
                // Close the loading dialog
                Swal.close();
                // Call the dedicated individual patient PDF function
                if (window.currentPatientData && typeof generateIndividualPatientPDF === 'function') {
                    generateIndividualPatientPDF();
                } else {
                    alert('Patient data not available. Please try again.');
                }
                return; // Exit early since generateIndividualPatientPDF handles everything
            } else {
                doc.setFontSize(12);
                doc.text('Report data is being prepared.', 20, yPos);
                doc.text('This report format is under development.', 20, yPos + 10);
            }
            
            // Add footer
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(150, 150, 150);
                doc.text(`Page ${i} of ${pageCount}`, 105, 285, { align: 'center' });
            }
            
            // Save PDF
            const fileName = `${reportType}_report_${new Date().getTime()}.pdf`;
            doc.save(fileName);
            
            Swal.fire({
                icon: 'success',
                title: 'PDF Generated!',
                text: 'Your report has been downloaded successfully.',
                confirmButtonColor: '#2e7d32'
            });
        } catch (error) {
            console.error('PDF generation error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to generate PDF. Please try again.',
                confirmButtonColor: '#dc2626'
            });
        }
    }, 500);
}

/**
 * Add Malnutrition Cases data to PDF
 */
function addMalnutritionCasesPDF(doc, startY) {
    const distData = window.patientDistributionData || {
        normal: { count: 0, percentage: 0, patients: [] },
        sam: { count: 0, percentage: 0, patients: [] },
        mam: { count: 0, percentage: 0, patients: [] },
        overweight: { count: 0, percentage: 0, patients: [] },
        obese: { count: 0, percentage: 0, patients: [] },
        barangay_breakdown: {}
    };
    
    doc.setFontSize(14);
    doc.setTextColor(0, 0, 0);
    doc.text('Malnutrition Cases by Severity', 20, startY);
    
    startY += 10;
    
    // Summary statistics
    const totalPatients = distData.normal.count + distData.overweight.count + 
                         distData.obese.count + distData.mam.count + distData.sam.count;
    const atRiskCount = distData.sam.count + distData.mam.count + distData.overweight.count + distData.obese.count;
    
    doc.setFontSize(12);
    doc.text('Overall Statistics:', 20, startY);
    startY += 8;
    
    doc.setFontSize(10);
    doc.text(`• Total Patients: ${totalPatients}`, 25, startY);
    doc.text(`• At Risk Patients: ${atRiskCount} (${totalPatients > 0 ? Math.round((atRiskCount/totalPatients)*100) : 0}%)`, 25, startY + 7);
    doc.text(`• SAM (Severe Acute Malnutrition): ${distData.sam.count}`, 25, startY + 14);
    doc.text(`• MAM (Moderate Acute Malnutrition): ${distData.mam.count}`, 25, startY + 21);
    doc.text(`• Overweight: ${distData.overweight.count}`, 25, startY + 28);
    doc.text(`• Obese: ${distData.obese.count}`, 25, startY + 35);
    
    startY += 50;
    
    // Distribution table
    doc.autoTable({
        startY: startY,
        head: [['Nutritional Status', 'Count', 'Percentage', 'Z-Score / BMI Range']],
        body: [
            ['Severe Acute Malnutrition (SAM)', distData.sam.count.toString(), `${distData.sam.percentage}%`, '≤ -3 SD'],
            ['Moderate Acute Malnutrition (MAM)', distData.mam.count.toString(), `${distData.mam.percentage}%`, '≥ -2 SD'],
            ['Overweight', distData.overweight.count.toString(), `${distData.overweight.percentage}%`, '+1 to +2 SD'],
            ['Obese', distData.obese.count.toString(), `${distData.obese.percentage}%`, '> +2 SD'],
            ['Normal Nutritional Status', distData.normal.count.toString(), `${distData.normal.percentage}%`, '-1 to +1 SD']
        ],
        theme: 'grid',
        headStyles: { fillColor: [46, 125, 50] },
        margin: { left: 20, right: 20 },
        styles: { fontSize: 9 }
    });
    
    startY = doc.lastAutoTable.finalY + 15;
    
    // Barangay breakdown
    if (distData.barangay_breakdown && Object.keys(distData.barangay_breakdown).length > 0) {
        doc.setFontSize(12);
        doc.text('Cases by Barangay:', 20, startY);
        startY += 8;
        
        const barangayData = Object.entries(distData.barangay_breakdown).map(([barangay, data]) => {
            const priority = data.sam > 0 ? 'Critical' : (data.mam > 0 ? 'High' : (data.obese > 0 ? 'Moderate' : 'Low'));
            return [
                barangay,
                data.sam.toString(),
                data.mam.toString(),
                data.overweight.toString(),
                data.obese.toString(),
                data.total.toString(),
                priority
            ];
        });
        
        doc.autoTable({
            startY: startY,
            head: [['Barangay', 'SAM', 'MAM', 'Overweight', 'Obese', 'Total', 'Priority']],
            body: barangayData,
            theme: 'striped',
            headStyles: { fillColor: [220, 38, 38] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 8 },
            columnStyles: {
                1: { halign: 'center', textColor: [153, 27, 27] },
                2: { halign: 'center', textColor: [239, 68, 68] },
                3: { halign: 'center', textColor: [245, 158, 11] },
                4: { halign: 'center', textColor: [217, 119, 6] },
                5: { halign: 'center', fontStyle: 'bold' }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    // SAM cases detail
    if (distData.sam.patients && distData.sam.patients.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(153, 27, 27);
        doc.text('Severe Acute Malnutrition (SAM) - Immediate Attention Required:', 20, startY);
        doc.setTextColor(0, 0, 0);
        startY += 8;
        
        const samPatients = distData.sam.patients.map(patient => [
            patient.name,
            patient.barangay,
            patient.age?.toString() || 'N/A',
            (patient.waz !== undefined && patient.waz !== null) ? patient.waz.toString() : 'N/A',
            (patient.haz !== undefined && patient.haz !== null) ? patient.haz.toString() : 'N/A',
            (patient.baz !== undefined && patient.baz !== null) ? patient.baz.toString() : 'N/A',
            patient.recovery_status || 'N/A'
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['Patient Name', 'Barangay', 'Age', 'WAZ', 'HAZ', 'BAZ', 'Status']],
            body: samPatients,
            theme: 'grid',
            headStyles: { fillColor: [153, 27, 27] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 7 },
            columnStyles: {
                2: { halign: 'center' },
                3: { halign: 'center', textColor: [153, 27, 27], fontStyle: 'bold' },
                4: { halign: 'center', textColor: [153, 27, 27], fontStyle: 'bold' },
                5: { halign: 'center', textColor: [153, 27, 27], fontStyle: 'bold' }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 10;
    }
    
    // MAM cases detail
    if (distData.mam.patients && distData.mam.patients.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(239, 68, 68);
        doc.text('Moderate Acute Malnutrition (MAM) - Urgent Intervention Required:', 20, startY);
        doc.setTextColor(0, 0, 0);
        startY += 8;
        
        const mamPatients = distData.mam.patients.map(patient => [
            patient.name,
            patient.barangay,
            patient.age?.toString() || 'N/A',
            (patient.waz !== undefined && patient.waz !== null) ? patient.waz.toString() : 'N/A',
            (patient.haz !== undefined && patient.haz !== null) ? patient.haz.toString() : 'N/A',
            (patient.baz !== undefined && patient.baz !== null) ? patient.baz.toString() : 'N/A',
            patient.recovery_status || 'N/A'
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['Patient Name', 'Barangay', 'Age', 'WAZ', 'HAZ', 'BAZ', 'Status']],
            body: mamPatients,
            theme: 'grid',
            headStyles: { fillColor: [239, 68, 68] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 7 },
            columnStyles: {
                2: { halign: 'center' },
                3: { halign: 'center', textColor: [239, 68, 68], fontStyle: 'bold' },
                4: { halign: 'center', textColor: [239, 68, 68], fontStyle: 'bold' },
                5: { halign: 'center', textColor: [239, 68, 68], fontStyle: 'bold' }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 10;
    }
    
    return startY;
}

/**
 * Add Patient Progress data to PDF
 */
function addPatientProgressPDF(doc, startY) {
    const monthlyData = window.monthlyProgressData || {
        months: [],
        assessments: [],
        recovered: [],
        total_assessments: 0,
        total_recovered: 0,
        patient_progress: [],
        barangay_progress: {}
    };
    
    // Get current filter values
    const barangayFilter = document.getElementById('barangayFilter')?.value || 'all';
    const searchFilter = document.getElementById('patientSearchInput')?.value || '';
    const trendFilter = document.getElementById('trendFilter')?.value || 'all';
    
    // Apply filters to patient data
    let patients = monthlyData.patient_progress || [];
    
    // Filter by barangay
    if (barangayFilter && barangayFilter !== 'all') {
        patients = patients.filter(p => p.barangay === barangayFilter);
    }
    
    // Filter by search term
    if (searchFilter && searchFilter.trim() !== '') {
        const search = searchFilter.toLowerCase().trim();
        patients = patients.filter(p => 
            p.name.toLowerCase().includes(search) || 
            p.barangay.toLowerCase().includes(search)
        );
    }
    
    // Filter by trend
    if (trendFilter && trendFilter !== 'all') {
        patients = patients.filter(p => p.progress_trend === trendFilter);
    }
    
    const improving = patients.filter(p => p.progress_trend === 'improving').length;
    const declining = patients.filter(p => p.progress_trend === 'declining').length;
    const stable = patients.filter(p => p.progress_trend === 'stable').length;
    
    doc.setFontSize(14);
    doc.text('Patient Progress & Recovery Tracking', 20, startY);
    startY += 10;
    
    // Add filter info if filters are active
    const activeFilters = [];
    if (barangayFilter !== 'all') activeFilters.push(`Barangay: ${barangayFilter}`);
    if (searchFilter.trim() !== '') activeFilters.push(`Search: "${searchFilter}"`);
    if (trendFilter !== 'all') activeFilters.push(`Trend: ${trendFilter}`);
    
    if (activeFilters.length > 0) {
        doc.setFontSize(9);
        doc.setTextColor(100, 100, 100);
        doc.text(`Filters Applied: ${activeFilters.join(' | ')}`, 20, startY);
        startY += 8;
        doc.setTextColor(0, 0, 0);
    }
    
    // Summary stats
    const recoveryRate = monthlyData.total_assessments > 0 
        ? Math.round((monthlyData.total_recovered / monthlyData.total_assessments) * 100) 
        : 0;
    
    doc.setFontSize(12);
    doc.text('Overall Statistics:', 20, startY);
    startY += 8;
    
    doc.setFontSize(10);
    doc.text(`• Total Patients Tracked: ${patients.length}`, 25, startY);
    doc.text(`• Improving: ${improving}`, 25, startY + 7);
    doc.text(`• Stable: ${stable}`, 25, startY + 14);
    doc.text(`• Declining: ${declining}`, 25, startY + 21);
    doc.text(`• Recovery Rate: ${recoveryRate}%`, 25, startY + 28);
    
    startY += 40;
    
    // Check if no patients match the filter
    if (patients.length === 0) {
        doc.setFontSize(11);
        doc.setTextColor(150, 150, 150);
        doc.text('No patients match the selected filters.', 20, startY);
        doc.setTextColor(0, 0, 0);
        return startY + 20;
    }
    
    // Patient progress table
    if (patients.length > 0) {
        doc.setFontSize(12);
        const displayText = patients.length > 20 
            ? `Patient Progress (showing top 20 of ${patients.length}):`
            : `Patient Progress (${patients.length} patients):`;
        doc.text(displayText, 20, startY);
        startY += 8;
        
        const patientData = patients.slice(0, 20).map(p => {
            const weightChange = p.weight_change ? `${p.weight_change > 0 ? '+' : ''}${p.weight_change}` : 'N/A';
            const bmiChange = p.bmi_change ? `${p.bmi_change > 0 ? '+' : ''}${p.bmi_change}` : 'N/A';
            const trend = p.progress_trend === 'improving' ? 'Up' : 
                         p.progress_trend === 'declining' ? 'Down' : 'Stable';
            
            return [
                p.name,
                p.barangay,
                p.total_assessments.toString(),
                weightChange,
                bmiChange,
                trend
            ];
        });
        
        doc.autoTable({
            startY: startY,
            head: [['Patient', 'Barangay', 'Visits', 'Weight Δ', 'BMI Δ', 'Trend']],
            body: patientData,
            theme: 'striped',
            headStyles: { fillColor: [46, 125, 50] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 8 },
            columnStyles: {
                2: { halign: 'center' },
                3: { halign: 'center' },
                4: { halign: 'center', fontStyle: 'bold' },
                5: { halign: 'center' }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    // Barangay progress table (filtered based on active filters)
    if (Object.keys(monthlyData.barangay_progress || {}).length > 0 && patients.length > 0) {
        doc.setFontSize(12);
        doc.text('Progress by Barangay:', 20, startY);
        startY += 8;
        
        let barangayEntries = Object.entries(monthlyData.barangay_progress);
        
        // Filter barangays based on filtered patients
        const filteredBarangays = [...new Set(patients.map(p => p.barangay))];
        barangayEntries = barangayEntries.filter(([barangay]) => filteredBarangays.includes(barangay));
        
        const barangayData = barangayEntries.map(([barangay, data]) => [
            barangay,
            data.total_patients.toString(),
            data.improving.toString(),
            data.stable.toString(),
            data.declining.toString(),
            data.recovered.toString()
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['Barangay', 'Total', 'Improving', 'Stable', 'Declining', 'Recovered']],
            body: barangayData,
            theme: 'grid',
            headStyles: { fillColor: [245, 158, 11] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 9 },
            columnStyles: {
                1: { halign: 'center' },
                2: { halign: 'center', textColor: [16, 185, 129] },
                3: { halign: 'center', textColor: [59, 130, 246] },
                4: { halign: 'center', textColor: [239, 68, 68] },
                5: { halign: 'center', fontStyle: 'bold', textColor: [46, 125, 50] }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    // Monthly breakdown table
    if (monthlyData.months.length > 0) {
        doc.setFontSize(12);
        doc.text('Monthly Trends:', 20, startY);
        startY += 8;
        
        const tableData = monthlyData.months.map((month, i) => {
            const rate = monthlyData.assessments[i] > 0 
                ? Math.round((monthlyData.recovered[i] / monthlyData.assessments[i]) * 100) 
                : 0;
            return [month, monthlyData.assessments[i].toString(), monthlyData.recovered[i].toString(), `${rate}%`];
        });
        
        doc.autoTable({
            startY: startY,
            head: [['Month', 'Assessments', 'Recovered', 'Rate']],
            body: tableData,
            theme: 'grid',
            headStyles: { fillColor: [46, 125, 50] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 9 }
        });
        
        startY = doc.lastAutoTable.finalY + 10;
    }
    
    return startY;
}

/**
 * Add Low Stock Alert data to PDF
 */
function addLowStockAlertPDF(doc, startY) {
    const stats = window.reportsStatsData || { low_stock_items: 0, low_stock_items_data: [] };
    const lowStockItems = stats.low_stock_items_data || [];
    
    doc.setFontSize(14);
    doc.setTextColor(0, 0, 0);
    doc.text('Low Stock Alert Report', 20, startY);
    startY += 10;
    
    if (lowStockItems.length > 0) {
        // Categorize items
        const expiredItems = lowStockItems.filter(i => i.is_expired);
        const bothIssues = lowStockItems.filter(i => i.alert_type === 'both');
        const criticalCount = lowStockItems.filter(i => i.quantity < 5 && !i.is_expired).length;
        const lowCount = lowStockItems.filter(i => i.quantity >= 5 && i.quantity < 10 && !i.is_expired).length;
        
        doc.setFontSize(12);
        doc.text('Summary:', 20, startY);
        startY += 8;
        
        doc.setFontSize(10);
        doc.text(`• Total Items Requiring Attention: ${stats.low_stock_items}`, 25, startY);
        doc.text(`• Expired Items: ${expiredItems.length}`, 25, startY + 7);
        doc.text(`• Critical Stock (< 5 units): ${criticalCount}`, 25, startY + 14);
        doc.text(`• Low Stock (5-9 units): ${lowCount}`, 25, startY + 21);
        doc.text(`• Items with Both Issues: ${bothIssues.length}`, 25, startY + 28);
        
        startY += 40;
        
        // Items table with expiry date
        const tableData = lowStockItems.map(item => {
            let status = [];
            if (item.is_expired) status.push('Expired');
            if (item.quantity < 5) status.push('Critical');
            else if (item.quantity < 10) status.push('Low Stock');
            
            return [
                item.item_name,
                item.category_name,
                item.quantity.toString(),
                item.unit,
                item.expiry_date || 'N/A',
                status.join(', ')
            ];
        });
        
        doc.autoTable({
            startY: startY,
            head: [['Item Name', 'Category', 'Stock', 'Unit', 'Expiry Date', 'Alert Type']],
            body: tableData,
            theme: 'grid',
            headStyles: { fillColor: [220, 38, 38] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 8 },
            columnStyles: {
                2: { halign: 'center', textColor: [220, 38, 38], fontStyle: 'bold' },
                4: { fontSize: 7 }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
        
        // Recommendations
        doc.setFontSize(12);
        doc.text('Recommended Actions:', 20, startY);
        startY += 8;
        
        doc.setFontSize(10);
        doc.text('• Remove and dispose of expired items immediately', 25, startY);
        doc.text('• Contact suppliers for critical items urgently', 25, startY + 7);
        doc.text('• Review consumption rates and adjust reorder points', 25, startY + 14);
        doc.text('• Implement FIFO system to prevent future expirations', 25, startY + 21);
        
        startY += 30;
    } else {
        doc.setFontSize(12);
        doc.setTextColor(16, 185, 129);
        doc.text('All inventory items are well stocked!', 20, startY);
        startY += 10;
        
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text('No items require immediate attention.', 20, startY);
        startY += 10;
    }
    
    return startY;
}

/**
 * Add Monthly Trends data to PDF
 */
function addMonthlyTrendsPDF(doc, startY) {
    const monthlyData = window.monthlyProgressData || {
        months: [],
        assessments: [],
        recovered: []
    };
    
    doc.setFontSize(14);
    doc.text('Monthly Trends Analysis', 20, startY);
    startY += 10;
    
    if (monthlyData.months.length > 0) {
        doc.autoTable({
            startY: startY,
            head: [['Month', 'Assessments', 'Recovered']],
            body: monthlyData.months.map((month, i) => [
                month, 
                monthlyData.assessments[i].toString(), 
                monthlyData.recovered[i].toString()
            ]),
            theme: 'grid',
            headStyles: { fillColor: [46, 125, 50] },
            margin: { left: 20, right: 20 }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    doc.setFontSize(10);
    doc.text('This analysis shows program effectiveness over the past 6 months.', 20, startY);
    
    return startY + 10;
}

/**
 * Add Inventory Report data to PDF
 */
function addInventoryPDF(doc, startY) {
    // Get stored data from last API call
    const inventoryData = window.lastInventoryData;
    
    if (!inventoryData) {
        doc.setFontSize(12);
        doc.text('No inventory data available', 20, startY);
        return startY + 10;
    }
    
    doc.setFontSize(14);
    doc.setTextColor(0, 0, 0);
    doc.text('Complete Inventory Status Report', 20, startY);
    startY += 10;
    
    // Summary statistics
    doc.setFontSize(12);
    doc.text('Inventory Summary:', 20, startY);
    startY += 8;
    
    doc.setFontSize(10);
    doc.text(`Total Items: ${inventoryData.total_items || 0}`, 25, startY);
    
    // Count by status
    const stockLevels = inventoryData.stock_levels || [];
    const goodStock = stockLevels.filter(item => item.status === 'Good').length;
    const mediumStock = stockLevels.filter(item => item.status === 'Medium').length;
    const lowStock = stockLevels.filter(item => item.status === 'Low').length;
    
    doc.text(`Good Stock (≥20): ${goodStock}`, 25, startY + 7);
    doc.text(`Medium Stock (10-19): ${mediumStock}`, 25, startY + 14);
    doc.text(`Low Stock (<10): ${lowStock}`, 25, startY + 21);
    
    startY += 33;
    
    // Item Usage Summary - Only items with usage
    const usedItems = stockLevels.filter(item => (item.total_usage || 0) > 0);
    
    if (usedItems.length > 0) {
        doc.setFontSize(12);
        doc.text(`Item Usage Summary (${usedItems.length} items distributed):`, 20, startY);
        startY += 8;
        
        const usageTableData = usedItems.map(item => [
            item.item_name || 'N/A',
            item.category_name || 'Uncategorized',
            (item.total_usage || 0).toString() + ' ' + item.unit
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['Item Name', 'Category', 'Total Distributed/Consumed']],
            body: usageTableData,
            theme: 'striped',
            headStyles: { fillColor: [37, 99, 235] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 9 },
            columnStyles: {
                2: { fontStyle: 'bold', textColor: [37, 99, 235] }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    } else {
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text('No items have been distributed yet.', 25, startY);
        startY += 15;
    }
    
    // Complete Items table
    if (stockLevels.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(0, 0, 0);
        doc.text('Complete Stock Levels:', 20, startY);
        startY += 8;
        
        const tableData = stockLevels.map(item => [
            item.item_name || 'N/A',
            item.category_name || 'Uncategorized',
            item.quantity.toString(),
            (item.total_usage || 0).toString(),
            item.unit,
            item.status
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['Item Name', 'Category', 'Current Stock', 'Total Usage', 'Unit', 'Status']],
            body: tableData,
            theme: 'grid',
            headStyles: { fillColor: [107, 114, 128] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 8 },
            didParseCell: function(data) {
                if (data.section === 'body' && data.column.index === 5) {
                    const status = data.cell.raw;
                    if (status === 'Low') {
                        data.cell.styles.textColor = [220, 38, 38];
                    } else if (status === 'Medium') {
                        data.cell.styles.textColor = [245, 158, 11];
                    } else {
                        data.cell.styles.textColor = [16, 185, 129];
                    }
                }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 10;
    }
    
    return startY;
}

/**
 * Add User Activity Report data to PDF
 */
function addUserActivityPDF(doc, startY) {
    // Get stored data from last API call
    const userData = window.lastUserActivityData;
    
    if (!userData) {
        doc.setFontSize(12);
        doc.text('No user activity data available', 20, startY);
        return startY + 10;
    }
    
    doc.setFontSize(14);
    doc.setTextColor(0, 0, 0);
    doc.text('User Activity & System Usage Report', 20, startY);
    startY += 10;
    
    // Date range
    doc.setFontSize(10);
    doc.setTextColor(100, 100, 100);
    doc.text(`Period: ${userData.start_display} to ${userData.end_display} (${userData.date_range_days} days)`, 20, startY);
    startY += 10;
    
    // User statistics
    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);
    doc.text('Overall Statistics:', 20, startY);
    startY += 8;
    
    doc.setFontSize(10);
    doc.text(`• Total System Users: ${userData.total_users || 0}`, 25, startY);
    doc.text(`• Active Users (in period): ${userData.active_users || 0}`, 25, startY + 7);
    doc.text(`• Total Assessments: ${userData.total_assessments || 0}`, 25, startY + 14);
    doc.text(`• Average per Day: ${userData.date_range_days > 0 ? Math.round(userData.total_assessments / userData.date_range_days) : 0}`, 25, startY + 21);
    
    startY += 33;
    
    // Users by role
    if (userData.users_by_role && Object.keys(userData.users_by_role).length > 0) {
        doc.setFontSize(12);
        doc.text('Users by Role:', 20, startY);
        startY += 8;
        
        const roleData = Object.entries(userData.users_by_role).map(([role, count]) => {
            const percentage = userData.total_users > 0 ? Math.round((count / userData.total_users) * 100) : 0;
            return [role, count.toString(), `${percentage}%`];
        });
        
        doc.autoTable({
            startY: startY,
            head: [['Role', 'Count', 'Percentage']],
            body: roleData,
            theme: 'grid',
            headStyles: { fillColor: [59, 130, 246] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 10 }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    // Top active users
    if (userData.assessments_by_user && userData.assessments_by_user.length > 0) {
        if (startY > 240) {
            doc.addPage();
            startY = addPDFHeader(doc);
        }
        
        doc.setFontSize(12);
        doc.text('Top 10 Active Users:', 20, startY);
        startY += 8;
        
        const userActivityData = userData.assessments_by_user.slice(0, 10).map(item => [
            item.user_name,
            item.role,
            item.count.toString()
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['User Name', 'Role', 'Assessments']],
            body: userActivityData,
            theme: 'grid',
            headStyles: { fillColor: [16, 185, 129] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 9 }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    // Recent assessments
    if (userData.recent_assessments && userData.recent_assessments.length > 0) {
        if (startY > 200) {
            doc.addPage();
            startY = addPDFHeader(doc);
        }
        
        doc.setFontSize(12);
        doc.text('Recent Assessments:', 20, startY);
        startY += 8;
        
        const assessmentData = userData.recent_assessments.map(assessment => [
            assessment.id.toString(),
            assessment.patient_name,
            `${assessment.user_name}\n(${assessment.user_role || 'Nutritionist'})`,
            assessment.recovery_status,
            assessment.date
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['ID', 'Patient', 'By User (Role)', 'Status', 'Date']],
            body: assessmentData,
            theme: 'grid',
            headStyles: { fillColor: [245, 158, 11] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 8, cellPadding: 3 },
            columnStyles: {
                2: { cellWidth: 40 } // Wider column for user name + role
            }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    return startY;
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
                    hoverRadius: 16
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
        sam: { count: 0, percentage: 0 },
        mam: { count: 0, percentage: 0 },
        overweight: { count: 0, percentage: 0 },
        obese: { count: 0, percentage: 0 }
    };
    
    const ctx = canvas.getContext('2d');
    window.patientDistributionPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Normal', 'SAM', 'MAM', 'Overweight', 'Obese'],
            datasets: [{
                data: [
                    distributionData.normal.count,
                    distributionData.sam.count,
                    distributionData.mam.count,
                    distributionData.overweight.count,
                    distributionData.obese.count
                ],
                backgroundColor: [
                    '#10b981',
                    '#991b1b',
                    '#ef4444',
                    '#f59e0b',
                    '#d97706'
                ],
                borderColor: [
                    '#059669',
                    '#7f1d1d',
                    '#dc2626',
                    '#f97316',
                    '#b45309'
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
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 16
                    },
                    formatter: function(value, context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                        return percentage > 0 ? percentage + '%' : '';
                    }
                }
            },
            cutout: '50%',
            animation: {
                animateRotate: true,
                duration: 1000
            }
        },
        plugins: [ChartDataLabels]
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

/**
 * Show report modal with custom content using SweetAlert2
 */
function showReportModalWithContent(title, content, reportType) {
    // Store current report data
    window.currentReportType = reportType;
    window.currentReportTitle = title;
    window.currentReportContent = content;
    
    Swal.fire({
        title: title,
        html: content,
        width: '1200px',
        padding: '2rem',
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-download"></i> Download PDF',
        cancelButtonText: 'Close',
        confirmButtonColor: '#2e7d32',
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'report-swal-popup',
            title: 'report-swal-title',
            htmlContainer: 'report-swal-content',
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        didOpen: () => {
            // Setup pagination and CSV export for malnutrition cases report
            if (reportType === 'malnutrition-cases') {
                setupCSVExportHandler();
                setupSamPaginationHandlers();
                setupMamPaginationHandlers();
            }
            
            // Re-initialize any charts in the modal
            if (content.includes('trendChart')) {
                setTimeout(() => {
                    const canvas = document.getElementById('trendChart');
                    if (canvas) {
                        const monthlyData = window.monthlyProgressData || {
                            months: [],
                            assessments: [],
                            recovered: []
                        };
                        
                        const ctx = canvas.getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: monthlyData.months,
                                datasets: [{
                                    label: 'Assessments',
                                    data: monthlyData.assessments,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }, {
                                    label: 'Recovered',
                                    data: monthlyData.recovered,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: { 
                                responsive: true, 
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top'
                                    }
                                }
                            }
                        });
                    }
                }, 200);
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            generatePDF(reportType, title, content);
        }
    });
}

// ===== COMPREHENSIVE REPORT GENERATION FUNCTIONS =====

function generateMalnutritionCasesContent(distData = null) {
    // Use provided data or fallback to window data
    if (!distData) {
        distData = window.patientDistributionData || {
            normal: { count: 0, percentage: 0, patients: [] },
            overweight: { count: 0, percentage: 0, patients: [] },
            obese: { count: 0, percentage: 0, patients: [] },
            mam: { count: 0, percentage: 0, patients: [] },
            sam: { count: 0, percentage: 0, patients: [] },
            barangay_breakdown: {}
        };
    }
    
    const totalAtRisk = distData.sam.count + distData.mam.count + distData.overweight.count + distData.obese.count;
    const totalPatients = totalAtRisk + distData.normal.count;
    const PAGINATION_THRESHOLD = 25;
    
    // Check if pagination is needed
    const samCaseCount = distData.sam.patients?.length || 0;
    const mamCaseCount = distData.mam.patients?.length || 0;
    const needSamPagination = samCaseCount > PAGINATION_THRESHOLD;
    const needMamPagination = mamCaseCount > PAGINATION_THRESHOLD;
    
    // Prepare barangay data
    let barangayRows = '';
    if (distData.barangay_breakdown && Object.keys(distData.barangay_breakdown).length > 0) {
        barangayRows = Object.entries(distData.barangay_breakdown)
            .map(([barangay, data]) => {
                const priorityLevel = data.sam > 0 ? 'Critical' : (data.mam > 0 ? 'High' : (data.obese > 0 ? 'Moderate' : 'Low'));
                const priorityColor = data.sam > 0 ? '#991b1b' : (data.mam > 0 ? '#dc2626' : (data.obese > 0 ? '#f59e0b' : '#3b82f6'));
                
                return `
                    <tr>
                        <td><strong>${barangay}</strong></td>
                        <td style="text-align: center; color: #991b1b; font-weight: bold;">${data.sam}</td>
                        <td style="text-align: center; color: #ef4444;">${data.mam}</td>
                        <td style="text-align: center; color: #f59e0b;">${data.overweight}</td>
                        <td style="text-align: center; color: #d97706;">${data.obese}</td>
                        <td style="text-align: center; font-weight: bold;">${data.total}</td>
                        <td style="text-align: center;">
                            <span style="color: ${priorityColor}; font-weight: 600;">${priorityLevel}</span>
                        </td>
                    </tr>
                `;
            }).join('');
    } else {
        barangayRows = '<tr><td colspan="7" style="text-align: center; color: #9ca3af;">No barangay data available</td></tr>';
    }
    
    // Prepare SAM patient rows (with or without pagination)
    let samPatientRows = '';
    let samPaginationHTML = '';
    
    if (distData.sam.patients && distData.sam.patients.length > 0) {
        if (needSamPagination) {
            // Show first page only
            samPatientRows = distData.sam.patients.slice(0, PAGINATION_THRESHOLD).map(patient => `
                <tr>
                    <td>${patient.name}</td>
                    <td>${patient.barangay}</td>
                    <td style="text-align: center;">${patient.age || 'N/A'}</td>
                    <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.waz !== undefined && patient.waz !== null) ? patient.waz : 'N/A'}</td>
                    <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.haz !== undefined && patient.haz !== null) ? patient.haz : 'N/A'}</td>
                    <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.baz !== undefined && patient.baz !== null) ? patient.baz : 'N/A'}</td>
                    <td style="font-size: 0.813rem; color: #6b7280;">${patient.recovery_status || 'N/A'}</td>
                </tr>
            `).join('');
            
            // Calculate total pages
            const totalPages = Math.ceil(samCaseCount / PAGINATION_THRESHOLD);
            samPaginationHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 1rem; padding: 0.75rem; background: #fee2e2; border-radius: 8px;">
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        Showing <strong>1-${Math.min(PAGINATION_THRESHOLD, samCaseCount)}</strong> of <strong>${samCaseCount}</strong> SAM cases
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-sm btn-outline-danger sam-prev-page" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <div id="sam-page-info" style="min-width: 60px; text-align: center; font-size: 0.875rem; font-weight: 600;">Page 1</div>
                        <button class="btn btn-sm btn-danger sam-next-page" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" data-total-pages="${totalPages}">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            `;
        } else {
            samPatientRows = distData.sam.patients.map(patient => `
                <tr>
                    <td>${patient.name}</td>
                    <td>${patient.barangay}</td>
                    <td style="text-align: center;">${patient.age || 'N/A'}</td>
                    <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.waz !== undefined && patient.waz !== null) ? patient.waz : 'N/A'}</td>
                    <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.haz !== undefined && patient.haz !== null) ? patient.haz : 'N/A'}</td>
                    <td style="text-align: center; color: #991b1b; font-weight: bold; font-size: 0.875rem;">${(patient.baz !== undefined && patient.baz !== null) ? patient.baz : 'N/A'}</td>
                    <td style="font-size: 0.813rem; color: #6b7280;">${patient.recovery_status || 'N/A'}</td>
                </tr>
            `).join('');
        }
    } else {
        samPatientRows = '<tr><td colspan="7" style="text-align: center; color: #10b981;">No SAM cases - excellent!</td></tr>';
    }
    
    // Prepare MAM patient rows (with or without pagination)
    let mamPatientRows = '';
    let mamPaginationHTML = '';
    
    if (distData.mam.patients && distData.mam.patients.length > 0) {
        if (needMamPagination) {
            // Show first page only
            mamPatientRows = distData.mam.patients.slice(0, PAGINATION_THRESHOLD).map(patient => `
                <tr>
                    <td>${patient.name}</td>
                    <td>${patient.barangay}</td>
                    <td style="text-align: center;">${patient.age || 'N/A'}</td>
                    <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.waz !== undefined && patient.waz !== null) ? patient.waz : 'N/A'}</td>
                    <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.haz !== undefined && patient.haz !== null) ? patient.haz : 'N/A'}</td>
                    <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.baz !== undefined && patient.baz !== null) ? patient.baz : 'N/A'}</td>
                    <td style="font-size: 0.813rem; color: #6b7280;">${patient.recovery_status || 'N/A'}</td>
                </tr>
            `).join('');
            
            // Calculate total pages
            const totalPages = Math.ceil(mamCaseCount / PAGINATION_THRESHOLD);
            mamPaginationHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 1rem; padding: 0.75rem; background: #fee2e2; border-radius: 8px;">
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        Showing <strong>1-${Math.min(PAGINATION_THRESHOLD, mamCaseCount)}</strong> of <strong>${mamCaseCount}</strong> MAM cases
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-sm btn-outline-warning mam-prev-page" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <div id="mam-page-info" style="min-width: 60px; text-align: center; font-size: 0.875rem; font-weight: 600;">Page 1</div>
                        <button class="btn btn-sm btn-warning mam-next-page" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" data-total-pages="${totalPages}">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            `;
        } else {
            mamPatientRows = distData.mam.patients.map(patient => `
                <tr>
                    <td>${patient.name}</td>
                    <td>${patient.barangay}</td>
                    <td style="text-align: center;">${patient.age || 'N/A'}</td>
                    <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.waz !== undefined && patient.waz !== null) ? patient.waz : 'N/A'}</td>
                    <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.haz !== undefined && patient.haz !== null) ? patient.haz : 'N/A'}</td>
                    <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 0.875rem;">${(patient.baz !== undefined && patient.baz !== null) ? patient.baz : 'N/A'}</td>
                    <td style="font-size: 0.813rem; color: #6b7280;">${patient.recovery_status || 'N/A'}</td>
                </tr>
            `).join('');
        }
    } else {
        mamPatientRows = '<tr><td colspan="7" style="text-align: center; color: #9ca3af;">No MAM cases</td></tr>';
    }
    
    return `
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; color: #1f2937;">Malnutrition Cases Report</h3>
            <button id="csv-export-btn" class="btn btn-success" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem;">
                <i class="fas fa-download"></i>
                Export CSV
            </button>
        </div>
        
        <div class="report-summary">
            <div class="stat-grid" style="grid-template-columns: repeat(5, 1fr);">
                <div class="stat-item">
                    <div class="stat-label">SAM Cases</div>
                    <div class="stat-value" style="color: #991b1b">${distData.sam.count}</div>
                    <div class="stat-meta">${distData.sam.percentage}% of total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">MAM Cases</div>
                    <div class="stat-value" style="color: #ef4444">${distData.mam.count}</div>
                    <div class="stat-meta">${distData.mam.percentage}% of total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Overweight</div>
                    <div class="stat-value" style="color: #f59e0b">${distData.overweight.count}</div>
                    <div class="stat-meta">${distData.overweight.percentage}% of total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Obese</div>
                    <div class="stat-value" style="color: #d97706">${distData.obese.count}</div>
                    <div class="stat-meta">${distData.obese.percentage}% of total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total At Risk</div>
                    <div class="stat-value" style="color: #dc2626">${totalAtRisk}</div>
                    <div class="stat-meta">${totalPatients > 0 ? Math.round((totalAtRisk/totalPatients)*100) : 0}% need intervention</div>
                </div>
            </div>
        </div>
        
        <div class="report-section">
            <h4><i class="fas fa-map-marked-alt" style="color: #2e7d32; margin-right: 0.5rem;"></i>Cases by Barangay</h4>
            <p style="margin-bottom: 1rem; color: #6b7280;">Distribution of at-risk patients across barangays, sorted by priority.</p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barangay</th>
                        <th style="text-align: center;">SAM</th>
                        <th style="text-align: center;">MAM</th>
                        <th style="text-align: center;">Overweight</th>
                        <th style="text-align: center;">Obese</th>
                        <th style="text-align: center;">Total</th>
                        <th style="text-align: center;">Priority</th>
                    </tr>
                </thead>
                <tbody>
                    ${barangayRows}
                </tbody>
            </table>
        </div>
        
        <div class="report-section">
            <h4><i class="fas fa-exclamation-triangle" style="color: #991b1b; margin-right: 0.5rem;"></i>Severe Acute Malnutrition (SAM) Cases</h4>
            <div class="alert alert-danger" style="background: #fee2e2; border-left: 4px solid #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <strong>Critical:</strong> These patients require immediate medical attention and intervention (Z-score ≤ -3) - Total: ${samCaseCount}
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Barangay</th>
                        <th style="text-align: center;">Age</th>
                        <th style="text-align: center;">WAZ<br><small style="font-weight: normal;">(Weight-for-Age)</small></th>
                        <th style="text-align: center;">HAZ<br><small style="font-weight: normal;">(Height-for-Age)</small></th>
                        <th style="text-align: center;">BAZ<br><small style="font-weight: normal;">(BMI-for-Age)</small></th>
                        <th style="text-align: center;">Recovery Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${samPatientRows}
                </tbody>
            </table>
            ${samPaginationHTML}
        </div>
        
        <div class="report-section">
            <h4><i class="fas fa-user-injured" style="color: #ef4444; margin-right: 0.5rem;"></i>Moderate Acute Malnutrition (MAM) Cases</h4>
            <p style="margin-bottom: 1rem; color: #6b7280;">Patients requiring urgent nutritional intervention (Z-score -2 to -2.99) - Total: ${mamCaseCount}</p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Barangay</th>
                        <th style="text-align: center;">Age</th>
                        <th style="text-align: center;">WAZ<br><small style="font-weight: normal;">(Weight-for-Age)</small></th>
                        <th style="text-align: center;">HAZ<br><small style="font-weight: normal;">(Height-for-Age)</small></th>
                        <th style="text-align: center;">BAZ<br><small style="font-weight: normal;">(BMI-for-Age)</small></th>
                        <th style="text-align: center;">Recovery Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${mamPatientRows}
                </tbody>
            </table>
            ${mamPaginationHTML}
        </div>
    `;
}

function generatePatientProgressContent(monthlyData = null) {
    // Use provided data or fallback to window data
    if (!monthlyData) {
        monthlyData = window.monthlyProgressData || {
            months: [],
            assessments: [],
            recovered: [],
            total_assessments: 0,
            total_recovered: 0,
            patient_progress: [],
            barangay_progress: {},
            barangays: []
        };
    }
    
    const recoveryRate = monthlyData.total_assessments > 0 
        ? Math.round((monthlyData.total_recovered / monthlyData.total_assessments) * 100) 
        : 0;
    
    const patients = monthlyData.patient_progress || [];
    const improving = patients.filter(p => p.progress_trend === 'improving').length;
    const declining = patients.filter(p => p.progress_trend === 'declining').length;
    const stable = patients.filter(p => p.progress_trend === 'stable').length;
    
    // Generate barangay options
    const barangays = monthlyData.barangays || [];
    const barangayOptions = barangays.map(b => `<option value="${b}">${b}</option>`).join('');
    
    return `
        <div class="report-summary">
            <div class="stat-grid" style="grid-template-columns: repeat(5, 1fr);">
                <div class="stat-item">
                    <div class="stat-label">Total Patients</div>
                    <div class="stat-value">${patients.length}</div>
                    <div class="stat-meta">Being tracked</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Improving</div>
                    <div class="stat-value" style="color: #10b981">${improving}</div>
                    <div class="stat-meta">BMI increasing</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Stable</div>
                    <div class="stat-value" style="color: #3b82f6">${stable}</div>
                    <div class="stat-meta">No change</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Declining</div>
                    <div class="stat-value" style="color: #ef4444">${declining}</div>
                    <div class="stat-meta">Needs attention</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Recovery Rate</div>
                    <div class="stat-value">${recoveryRate}%</div>
                    <div class="stat-meta">Last 6 months</div>
                </div>
            </div>
        </div>
        
        <div class="report-section">
            <h4><i class="fas fa-filter" style="color: #2e7d32; margin-right: 0.5rem;"></i>Filter Options</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <label style="font-size: 0.875rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-map-marker-alt" style="color: #f59e0b;"></i> Filter by Barangay
                    </label>
                    <select id="barangayFilter" class="form-control" onchange="applyProgressFilters()" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;">
                        <option value="all">All Barangays</option>
                        ${barangayOptions}
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.875rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-search" style="color: #3b82f6;"></i> Search Patient
                    </label>
                    <input type="text" id="patientSearchInput" class="form-control" placeholder="Type patient name..." 
                        style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;"
                        oninput="applyProgressFilters()">
                </div>
                <div>
                    <label style="font-size: 0.875rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-chart-line" style="color: #10b981;"></i> Progress Trend
                    </label>
                    <select id="trendFilter" class="form-control" onchange="applyProgressFilters()" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;">
                        <option value="all">All Trends</option>
                        <option value="improving">Improving</option>
                        <option value="stable">Stable</option>
                        <option value="declining">Declining</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="report-section" id="progressResultsSection">
            ${generateProgressResults(patients, null, null, null)}
        </div>
        
        <div class="report-section">
            <h4><i class="fas fa-chart-bar" style="color: #3b82f6; margin-right: 0.5rem;"></i>Monthly Trends</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th style="text-align: center;">Assessments</th>
                        <th style="text-align: center;">Recovered</th>
                        <th style="text-align: center;">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    ${monthlyData.months.map((month, i) => {
                        const rate = monthlyData.assessments[i] > 0 
                            ? Math.round((monthlyData.recovered[i] / monthlyData.assessments[i]) * 100) 
                            : 0;
                        return `
                            <tr>
                                <td><strong>${month}</strong></td>
                                <td style="text-align: center;">${monthlyData.assessments[i]}</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold;">${monthlyData.recovered[i]}</td>
                                <td style="text-align: center;">
                                    <span class="status-badge ${rate > 70 ? 'status-good' : rate > 40 ? 'status-medium' : 'status-low'}">${rate}%</span>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        </div>
        
        ${Object.keys(monthlyData.barangay_progress || {}).length > 0 ? `
        <div class="report-section">
            <h4><i class="fas fa-map-marked-alt" style="color: #f59e0b; margin-right: 0.5rem;"></i>Progress by Barangay</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barangay</th>
                        <th style="text-align: center;">Total</th>
                        <th style="text-align: center;">Improving</th>
                        <th style="text-align: center;">Stable</th>
                        <th style="text-align: center;">Declining</th>
                        <th style="text-align: center;">Recovered</th>
                    </tr>
                </thead>
                <tbody>
                    ${Object.entries(monthlyData.barangay_progress).map(([barangay, data]) => `
                        <tr>
                            <td><strong>${barangay}</strong></td>
                            <td style="text-align: center;">${data.total_patients}</td>
                            <td style="text-align: center; color: #10b981;">${data.improving}</td>
                            <td style="text-align: center; color: #3b82f6;">${data.stable}</td>
                            <td style="text-align: center; color: #ef4444;">${data.declining}</td>
                            <td style="text-align: center; color: #2e7d32; font-weight: bold;">${data.recovered}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        ` : ''}
    `;
}

function generateProgressResults(patients, barangay, searchTerm, trend) {
    let filteredPatients = patients;
    
    if (barangay && barangay !== 'all') {
        filteredPatients = filteredPatients.filter(p => p.barangay === barangay);
    }
    
    if (searchTerm && searchTerm.trim() !== '') {
        const search = searchTerm.toLowerCase().trim();
        filteredPatients = filteredPatients.filter(p => 
            p.name.toLowerCase().includes(search) || 
            p.barangay.toLowerCase().includes(search)
        );
    }
    
    if (trend && trend !== 'all') {
        filteredPatients = filteredPatients.filter(p => p.progress_trend === trend);
    }
    
    if (filteredPatients.length === 0) {
        return `
            <div style="text-align: center; padding: 2rem; color: #9ca3af;">
                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>No patients match the selected filters</p>
            </div>
        `;
    }
    
    return `
        <h4><i class="fas fa-users" style="color: #2e7d32; margin-right: 0.5rem;"></i>Patient Progress Details (${filteredPatients.length} patients)</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Barangay</th>
                    <th style="text-align: center;">Age</th>
                    <th style="text-align: center;">Assessments</th>
                    <th style="text-align: center;">Weight Change</th>
                    <th style="text-align: center;">BMI Change</th>
                    <th style="text-align: center;">Trend</th>
                    <th>Period</th>
                </tr>
            </thead>
            <tbody>
                ${filteredPatients.map(p => {
                    const trendIcon = p.progress_trend === 'improving' ? '↗' : 
                                     p.progress_trend === 'declining' ? '↘' : '→';
                    const trendColor = p.progress_trend === 'improving' ? '#10b981' : 
                                      p.progress_trend === 'declining' ? '#ef4444' : '#3b82f6';
                    const weightChangeDisplay = p.weight_change ? 
                        `${p.weight_change > 0 ? '+' : ''}${p.weight_change} kg` : 'N/A';
                    const bmiChangeDisplay = p.bmi_change ? 
                        `${p.bmi_change > 0 ? '+' : ''}${p.bmi_change}` : 'N/A';
                    
                    return `
                        <tr>
                            <td><strong>${p.name}</strong></td>
                            <td>${p.barangay}</td>
                            <td style="text-align: center;">${p.age || 'N/A'}</td>
                            <td style="text-align: center;">${p.total_assessments}</td>
                            <td style="text-align: center; color: ${p.weight_change > 0 ? '#10b981' : p.weight_change < 0 ? '#ef4444' : '#6b7280'}; font-weight: bold;">
                                ${weightChangeDisplay}
                            </td>
                            <td style="text-align: center; color: ${p.bmi_change > 0 ? '#10b981' : p.bmi_change < 0 ? '#ef4444' : '#6b7280'}; font-weight: bold;">
                                ${bmiChangeDisplay}
                            </td>
                            <td style="text-align: center;">
                                <span style="color: ${trendColor}; font-size: 1.5rem; font-weight: bold;">${trendIcon}</span>
                            </td>
                            <td style="font-size: 0.813rem; color: #6b7280;">
                                ${p.first_assessment_date} to ${p.last_assessment_date}
                            </td>
                        </tr>
                    `;
                }).join('')}
            </tbody>
        </table>
    `;
}

function applyProgressFilters() {
    const barangay = document.getElementById('barangayFilter')?.value;
    const searchTerm = document.getElementById('patientSearchInput')?.value;
    const trend = document.getElementById('trendFilter')?.value;
    
    const monthlyData = window.monthlyProgressData || { patient_progress: [] };
    const patients = monthlyData.patient_progress || [];
    
    const resultsSection = document.getElementById('progressResultsSection');
    if (resultsSection) {
        resultsSection.innerHTML = generateProgressResults(patients, barangay, searchTerm, trend);
    }
}

function generateLowStockAlertContent(data = null) {
    // Use provided data or fallback to window data
    let alertItems = [];
    if (data && data.alert_items) {
        alertItems = data.alert_items;
    } else {
        const stats = window.reportsStatsData || { low_stock_items: 0, low_stock_items_data: [] };
        alertItems = stats.low_stock_items_data || [];
    }
    
    // Separate items by alert type
    const expiredItems = alertItems.filter(item => item.is_expired);
    const lowStockOnly = alertItems.filter(item => !item.is_expired && item.is_low_stock);
    const bothIssues = alertItems.filter(item => item.is_expired && item.is_low_stock);
    
    let itemsTableHTML = '';
    if (alertItems.length > 0) {
        itemsTableHTML = `
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Expiry Date</th>
                        <th>Alert Type</th>
                    </tr>
                </thead>
                <tbody>
                    ${alertItems.map(item => {
                        let alertBadges = '';
                        let stockColor = '#6b7280';
                        
                        if (item.is_expired && item.is_low_stock) {
                            alertBadges = '<span class="status-badge status-low">Expired</span> <span class="status-badge status-low">Critical Stock</span>';
                            stockColor = '#dc2626';
                        } else if (item.is_expired) {
                            alertBadges = '<span class="status-badge status-low">Expired</span>';
                            stockColor = '#991b1b';
                        } else if (item.quantity < 5) {
                            alertBadges = '<span class="status-badge status-low">Critical Stock</span>';
                            stockColor = '#dc2626';
                        } else {
                            alertBadges = '<span class="status-badge status-medium">Low Stock</span>';
                            stockColor = '#f59e0b';
                        }
                        
                        return `
                            <tr>
                                <td><strong>${item.item_name}</strong></td>
                                <td>${item.category_name}</td>
                                <td style="color: ${stockColor}; font-weight: 600;">${item.quantity} ${item.unit}</td>
                                <td>${item.expiry_date || 'N/A'}</td>
                                <td>${alertBadges}</td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;
    } else {
        itemsTableHTML = `
            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.125rem; font-weight: 500;">All inventory items are well stocked!</p>
                <p>No items require immediate attention.</p>
            </div>
        `;
    }
    
    return `
        <div class="report-section">
            <h4>Inventory Alerts & Action Items</h4>
            ${alertItems.length > 0 ? `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Action Required:</strong> ${alertItems.length} item${alertItems.length !== 1 ? 's' : ''} require immediate attention.
                </div>
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-label">Expired Items</div>
                        <div class="stat-value" style="color: #991b1b">${expiredItems.length}</div>
                        <div class="stat-meta">Remove from stock</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Critical Stock</div>
                        <div class="stat-value" style="color: #dc2626">${lowStockOnly.filter(i => i.quantity < 5).length}</div>
                        <div class="stat-meta">< 5 units</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Low Stock</div>
                        <div class="stat-value" style="color: #f59e0b">${lowStockOnly.filter(i => i.quantity >= 5 && i.quantity < 10).length}</div>
                        <div class="stat-meta">5-9 units</div>
                    </div>
                    ${bothIssues.length > 0 ? `
                        <div class="stat-item">
                            <div class="stat-label">Both Issues</div>
                            <div class="stat-value" style="color: #7f1d1d">${bothIssues.length}</div>
                            <div class="stat-meta">Expired & Low</div>
                        </div>
                    ` : ''}
                </div>
            ` : ''}
            <div style="margin-top: 1.5rem;">
                ${itemsTableHTML}
            </div>
            ${alertItems.length > 0 ? `
                <div style="margin-top: 1.5rem; padding: 1rem; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 4px;">
                    <p style="margin: 0; color: #065f46;"><strong>Recommended Actions:</strong></p>
                    <ul style="margin: 0.5rem 0 0 1.5rem; color: #047857;">
                        ${expiredItems.length > 0 ? '<li>Remove expired items from inventory immediately</li>' : ''}
                        <li>Contact suppliers for critical items immediately</li>
                        <li>Review consumption rates and adjust reorder points</li>
                        <li>Monitor expiration dates closely to prevent waste</li>
                    </ul>
                </div>
            ` : ''}
        </div>
    `;
}

function generateMonthlyTrendsContent(monthlyData = null) {
    // Use provided data or fallback to window data
    if (!monthlyData) {
        monthlyData = window.monthlyProgressData || {
            months: [],
            assessments: [],
            recovered: [],
            total_assessments: 0,
            total_recovered: 0
        };
    }
    
    const totalAssessments = monthlyData.total_assessments || 0;
    const totalRecovered = monthlyData.total_recovered || 0;
    const recoveryRate = totalAssessments > 0 ? Math.round((totalRecovered / totalAssessments) * 100) : 0;
    
    return `
        <div class="report-section">
            <h4><i class="fas fa-chart-line" style="color: #2e7d32; margin-right: 0.5rem;"></i>Monthly Trends Overview</h4>
            <div class="alert alert-info" style="margin-bottom: 1rem;">
                <i class="fas fa-info-circle"></i> Showing last 6 months of assessment and recovery data
            </div>
            
            <div class="stat-grid" style="margin-bottom: 1.5rem;">
                <div class="stat-item">
                    <div class="stat-label">Total Assessments</div>
                    <div class="stat-value" style="color: #3b82f6">${totalAssessments}</div>
                    <div class="stat-meta">Last 6 months</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Recovered</div>
                    <div class="stat-value" style="color: #10b981">${totalRecovered}</div>
                    <div class="stat-meta">Successfully treated</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Recovery Rate</div>
                    <div class="stat-value" style="color: ${recoveryRate >= 50 ? '#10b981' : recoveryRate >= 25 ? '#f59e0b' : '#ef4444'}">${recoveryRate}%</div>
                    <div class="stat-meta">Success rate</div>
                </div>
            </div>
        </div>
        
        <div class="report-section">
            <h4>Trend Chart</h4>
            ${monthlyData.months.length > 0 ? `
                <div style="background: #f0f9ff; padding: 0.875rem; border-radius: 6px; border: 1px solid #bae6fd; margin-bottom: 1rem;">
                    <i class="fas fa-info-circle" style="color: #0284c7;"></i> Showing data from <strong>${monthlyData.months[0]}</strong> to <strong>${monthlyData.months[monthlyData.months.length - 1]}</strong>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 1.5rem;">
                    <canvas id="trendChart" style="max-height: 300px;"></canvas>
                </div>
            ` : `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No trend data available yet. Data will appear once assessments are recorded.
                </div>
            `}
            
        <div class="report-section">
            <h4>Monthly Breakdown</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th style="text-align: center;">Assessments</th>
                        <th style="text-align: center;">Recovered</th>
                        <th style="text-align: center;">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    ${monthlyData.months.length > 0 ? monthlyData.months.map((month, i) => {
                        const rate = monthlyData.assessments[i] > 0 
                            ? Math.round((monthlyData.recovered[i] / monthlyData.assessments[i]) * 100) 
                            : 0;
                        return `
                            <tr>
                                <td><strong>${month}</strong></td>
                                <td style="text-align: center;">${monthlyData.assessments[i]}</td>
                                <td style="text-align: center; color: #10b981;">${monthlyData.recovered[i]}</td>
                                <td style="text-align: center; color: ${rate >= 50 ? '#10b981' : rate >= 25 ? '#f59e0b' : '#ef4444'}; font-weight: bold;">${rate}%</td>
                            </tr>
                        `;
                    }).join('') : `
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 1.5rem; color: #6b7280;">
                                <i class="fas fa-inbox"></i> No monthly data available yet
                            </td>
                        </tr>
                    `}
                </tbody>
            </table>
        </div>
    `;
    
    // Initialize chart if data exists
    if (monthlyData.months.length > 0) {
        setTimeout(() => {
            const canvas = document.getElementById('trendChart');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: monthlyData.months,
                        datasets: [{
                            label: 'Assessments',
                            data: monthlyData.assessments,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2
                        }, {
                            label: 'Recovered',
                            data: monthlyData.recovered,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        }, 100);
    }
}

/**
 * Generate individual patient selector interface
 */
/**
 * Show patient selector by fetching list from API
 */
function showIndividualPatientSelector() {
    const title = 'Individual Patient Report - Select Patient';
    const loadingContent = '<div style="text-align: center; padding: 3rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #2e7d32;"></i><p style="margin-top: 1rem; color: #6b7280;">Loading patients list...</p></div>';
    
    showReportModalWithContent(title, loadingContent, 'individual-patient');
    
    // Fetch patients list from API
    fetch('/admin/reports/patients-list')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const content = generateIndividualPatientSelector(result.data);
                showReportModalWithContent(title, content, 'individual-patient');
            } else {
                showAlert('Error loading patients list: ' + (result.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching patients list:', error);
            showAlert('Error loading patients list: ' + error.message, 'error');
        });
}

function generateIndividualPatientSelector(patients = []) {
    if (patients.length === 0) {
        return `
            <div style="text-align: center; padding: 3rem; color: #9ca3af;">
                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.125rem; font-weight: 500;">No patient data available</p>
                <p style="font-size: 0.875rem;">Patient progress data will appear here once assessments are recorded.</p>
            </div>
        `;
    }
    
    // Store patients globally for filtering
    window.currentPatientsList = patients;
    
    return `
        <div class="report-section">
            <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #93c5fd;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <i class="fas fa-info-circle" style="color: #1e40af; font-size: 1.25rem;"></i>
                    <h4 style="margin: 0; color: #1e3a8a; font-size: 1rem;">Select a Patient</h4>
                </div>
                <p style="margin: 0; color: #1e40af; font-size: 0.875rem;">
                    <i class="fas fa-mouse-pointer" style="margin-right: 0.25rem;"></i> Click on a patient card below to view their detailed report.
                    Once opened, you can export the report as PDF.
                </p>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="font-size: 0.875rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-search" style="color: #3b82f6;"></i> Search Patient by Name or Barangay
                </label>
                <input type="text" id="individualPatientSearch" class="form-control" 
                    placeholder="Type patient name or barangay..." 
                    style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 0.875rem;"
                    oninput="filterIndividualPatients()">
            </div>
            
            <div id="patientSelectionList" style="max-height: 400px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb;">
                ${generatePatientSelectionList(patients)}
            </div>
        </div>
    `;
}

/**
 * Generate patient selection list
 */
function generatePatientSelectionList(patients, searchTerm = '') {
    let filteredPatients = patients;
    
    if (searchTerm && searchTerm.trim() !== '') {
        const search = searchTerm.toLowerCase().trim();
        filteredPatients = patients.filter(p => 
            p.name.toLowerCase().includes(search) || 
            p.barangay.toLowerCase().includes(search)
        );
    }
    
    if (filteredPatients.length === 0) {
        return `
            <div style="text-align: center; padding: 2rem; color: #9ca3af;">
                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <p>No patients found matching "${searchTerm}"</p>
            </div>
        `;
    }
    
    return filteredPatients.map((patient, index) => {
        const trendIcon = patient.progress_trend === 'improving' ? '↗' : 
                         patient.progress_trend === 'declining' ? '↘' : '→';
        const trendColor = patient.progress_trend === 'improving' ? '#10b981' : 
                          patient.progress_trend === 'declining' ? '#ef4444' : '#3b82f6';
        const trendText = patient.progress_trend === 'improving' ? 'Improving' :
                         patient.progress_trend === 'declining' ? 'Declining' : 'Stable';
        
        return `
            <div class="patient-selection-item" 
                onclick="showIndividualPatientReport(${patient.id})"
                style="padding: 1rem; border-bottom: 1px solid #e5e7eb; cursor: pointer; transition: all 0.2s; background: white;"
                onmouseover="this.style.background='#f0f9ff'; this.style.borderLeft='4px solid #3b82f6'"
                onmouseout="this.style.background='white'; this.style.borderLeft='none'">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <i class="fas fa-user" style="color: #6b7280; font-size: 0.875rem;"></i>
                            <strong style="font-size: 1rem; color: #1f2937;">${patient.name}</strong>
                        </div>
                        <div style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;">
                            <span style="font-size: 0.813rem; color: #6b7280;">
                                <i class="fas fa-map-marker-alt" style="color: #f59e0b;"></i> ${patient.barangay}
                            </span>
                            <span style="font-size: 0.813rem; color: #6b7280;">
                                <i class="fas fa-birthday-cake"></i> Age ${patient.age || 'N/A'}
                            </span>
                            <span style="font-size: 0.813rem; color: #6b7280;">
                                <i class="fas fa-clipboard-list"></i> ${patient.total_assessments} assessments
                            </span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="display: flex; align-items: center; gap: 0.25rem; justify-content: flex-end;">
                            <span style="color: ${trendColor}; font-size: 1.25rem; font-weight: bold;">${trendIcon}</span>
                            <span style="font-size: 0.813rem; color: ${trendColor}; font-weight: 600;">${trendText}</span>
                        </div>
                        ${patient.bmi_change ? `
                            <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                                BMI: ${patient.bmi_change > 0 ? '+' : ''}${patient.bmi_change}
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Filter individual patients based on search
 */
function filterIndividualPatients() {
    const searchTerm = document.getElementById('individualPatientSearch')?.value || '';
    const patients = window.currentPatientsList || [];
    
    const listContainer = document.getElementById('patientSelectionList');
    if (listContainer) {
        listContainer.innerHTML = generatePatientSelectionList(patients, searchTerm);
    }
}

/**
 * Show detailed report for individual patient
 */
function showIndividualPatientReport(patientId) {
    const patients = window.currentPatientsList || [];
    const patient = patients.find(p => p.id === patientId);
    
    if (!patient) {
        showAlert('Patient data not found', 'error');
        return;
    }
    
    // Store patient ID for PDF generation
    window.currentPatientId = patientId;
    
    const title = `Individual Patient Report: ${patient.name}`;
    const loadingContent = '<div style="text-align: center; padding: 3rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #2e7d32;"></i><p style="margin-top: 1rem; color: #6b7280;">Loading patient report...</p></div>';
    
    showReportModalWithContent(title, loadingContent, 'individual-patient-detail');
    
    // Fetch detailed patient report from API
    fetch(`/admin/reports/individual-patient/${patientId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const content = generateIndividualPatientReportContent(result.data);
                showReportModalWithContent(title, content, 'individual-patient-detail');
                
                // Store for PDF generation
                window.currentPatientData = result.data;
            } else {
                showAlert('Error loading patient report: ' + (result.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching patient report:', error);
            showAlert('Error loading patient report: ' + error.message, 'error');
        });
}

/**
 * Generate individual patient report content from API data
 */
function generateIndividualPatientReportContent(data) {
    const patient = data.patient;
    const assessments = data.assessments || [];
    const summary = data.summary;
    
    return `
        <div class="report-section">
            <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #bae6fd;">
                <h3 style="margin: 0 0 0.5rem 0; color: #1e3a8a; font-size: 1.25rem;">
                    <i class="fas fa-user-circle"></i> Patient Information
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-top: 1rem;">
                    <div>
                        <span style="color: #6b7280; font-size: 0.75rem; display: block;">Age</span>
                        <span style="color: #1e40af; font-weight: 600;">${data.summary.age_years || 'N/A'} years</span>
                    </div>
                    <div>
                        <span style="color: #6b7280; font-size: 0.75rem; display: block;">Sex</span>
                        <span style="color: #1e40af; font-weight: 600;">${patient.sex}</span>
                    </div>
                    <div>
                        <span style="color: #6b7280; font-size: 0.75rem; display: block;">Barangay</span>
                        <span style="color: #1e40af; font-weight: 600;">${patient.barangay}</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-grid" style="margin-bottom: 1.5rem;">
                <div class="stat-item">
                    <div class="stat-label">Total Assessments</div>
                    <div class="stat-value">${summary.total_assessments}</div>
                    <div class="stat-meta">Recorded visits</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Weight Change</div>
                    <div class="stat-value" style="color: ${summary.weight_change > 0 ? '#10b981' : summary.weight_change < 0 ? '#ef4444' : '#6b7280'}">
                        ${summary.weight_change !== null && summary.weight_change !== undefined ? (summary.weight_change > 0 ? '+' : '') + summary.weight_change.toFixed(1) + ' kg' : 'Only 1 assessment'}
                    </div>
                    <div class="stat-meta">${summary.initial_weight || 'N/A'} → ${summary.current_weight || 'N/A'} kg</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Height Change</div>
                    <div class="stat-value" style="color: ${summary.height_change > 0 ? '#10b981' : summary.height_change < 0 ? '#ef4444' : '#6b7280'}">
                        ${summary.height_change !== null && summary.height_change !== undefined ? (summary.height_change > 0 ? '+' : '') + summary.height_change.toFixed(1) + ' cm' : 'Only 1 assessment'}
                    </div>
                    <div class="stat-meta">${summary.initial_height || 'N/A'} → ${summary.current_height || 'N/A'} cm</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Status</div>
                    <div class="stat-value" style="color: ${summary.progress_trend === 'improving' ? '#10b981' : summary.progress_trend === 'declining' ? '#ef4444' : '#3b82f6'}; font-size: 1rem;">
                        ${summary.current_status}
                    </div>
                    <div class="stat-meta">${summary.progress_trend === 'improving' ? '↗ Improving' : summary.progress_trend === 'declining' ? '↘ Declining' : '→ Stable'}</div>
                </div>
            </div>
            
            <div class="report-section">
                <h4><i class="fas fa-calendar-alt" style="color: #3b82f6;"></i> Assessment Period</h4>
                <p style="color: #6b7280; font-size: 0.875rem;">
                    <strong>First Assessment:</strong> ${summary.first_assessment_date || 'N/A'}<br>
                    <strong>Latest Assessment:</strong> ${summary.latest_assessment_date || 'N/A'}
                </p>
            </div>
            
            ${assessments.length > 0 ? `
                <div class="report-section">
                    <h4><i class="fas fa-history" style="color: #2e7d32;"></i> Assessment History</h4>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Weight (kg)</th>
                                    <th>Height (cm)</th>
                                    <th>BMI</th>
                                    <th>MUAC (cm)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${assessments.map(assessment => `
                                    <tr>
                                        <td>${assessment.date}</td>
                                        <td>${assessment.weight || 'N/A'}</td>
                                        <td>${assessment.height || 'N/A'}</td>
                                        <td>${assessment.bmi || 'N/A'}</td>
                                        <td>${assessment.muac || 'N/A'}</td>
                                        <td>${assessment.recovery_status}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            ` : ''}
            
            <div class="alert alert-info" style="margin-top: 1rem;">
                <i class="fas fa-lightbulb"></i>
                <strong>Progress Summary:</strong> 
                ${summary.progress_trend === 'improving' 
                    ? `This patient shows positive progress with ${summary.bmi_change > 0 ? 'improving' : 'stable'} BMI trends. Continue current intervention plan.`
                    : summary.progress_trend === 'declining'
                    ? `This patient's progress shows decline. Review and adjust intervention plan as needed. Consider additional nutritional support.`
                    : `This patient maintains stable condition. Continue monitoring and maintain current care plan.`
                }
            </div>
        </div>
    `;
}

/**
 * Apply date range filter for user activity
 */
function applyUserActivityDateFilter() {
    const startDate = document.getElementById('userActivityStartDate')?.value;
    const endDate = document.getElementById('userActivityEndDate')?.value;
    
    if (!startDate || !endDate) {
        showAlert('Please select both start and end dates', 'warning');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        showAlert('Start date must be before end date', 'error');
        return;
    }
    
    // Show loading state
    const resultsSection = document.getElementById('userActivityResultsSection');
    if (resultsSection) {
        resultsSection.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3b82f6;"></i>
                <p style="margin-top: 1rem; color: #6b7280;">Loading user activity data...</p>
            </div>
        `;
    }
    
    // Fetch filtered data from server
    fetch(`/admin/reports/user-activity?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                throw new Error(result.message || 'Failed to load data');
            }
            
            const data = result.data;
            
            if (resultsSection) {
                resultsSection.innerHTML = `
                    <h4>User Activity Results</h4>
                    <p>Showing data from <strong>${data.start_display}</strong> to <strong>${data.end_display}</strong> (${data.date_range_days} days)</p>
                    
                    <div class="report-summary">
                        <div class="stat-grid">
                            <div class="stat-item">
                                <div class="stat-label">Total Users</div>
                                <div class="stat-value">${data.total_users || 0}</div>
                                <div class="stat-meta">System-wide</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Active Users</div>
                                <div class="stat-value" style="color: #10b981">${data.active_users || 0}</div>
                                <div class="stat-meta">In selected period</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Total Assessments</div>
                                <div class="stat-value" style="color: #3b82f6">${data.total_assessments || 0}</div>
                                <div class="stat-meta">Created</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Avg Per Day</div>
                                <div class="stat-value" style="color: #f59e0b">${data.date_range_days > 0 ? Math.round(data.total_assessments / data.date_range_days) : 0}</div>
                                <div class="stat-meta">Assessments/day</div>
                            </div>
                        </div>
                    </div>
                    
                    ${(data.users_by_role && Object.keys(data.users_by_role).length > 0) ? `
                        <div class="report-section">
                            <h4><i class="fas fa-users" style="color: #3b82f6; margin-right: 0.5rem;"></i>Users by Role</h4>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th style="text-align: center;">Count</th>
                                        <th style="text-align: center;">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${Object.entries(data.users_by_role).map(([role, count]) => {
                                        const percentage = data.total_users > 0 ? Math.round((count / data.total_users) * 100) : 0;
                                        return `
                                            <tr>
                                                <td><strong>${role}</strong></td>
                                                <td style="text-align: center;">${count}</td>
                                                <td style="text-align: center;">${percentage}%</td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : ''}
                    
                    ${(data.assessments_by_user && data.assessments_by_user.length > 0) ? `
                        <div class="report-section">
                            <h4><i class="fas fa-chart-bar" style="color: #10b981; margin-right: 0.5rem;"></i>Assessments by User (Top 10)</h4>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>User Name</th>
                                        <th>Role</th>
                                        <th style="text-align: center;">Assessments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.assessments_by_user.slice(0, 10).map(item => `
                                        <tr>
                                            <td><strong>${item.user_name}</strong></td>
                                            <td>${item.role}</td>
                                            <td style="text-align: center;"><span style="background: #dbeafe; padding: 0.25rem 0.75rem; border-radius: 12px; font-weight: 600; color: #1e40af;">${item.count}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : ''}
                    
                    ${(data.recent_assessments && data.recent_assessments.length > 0) ? `
                        <div class="report-section">
                            <h4><i class="fas fa-clock" style="color: #f59e0b; margin-right: 0.5rem;"></i>Recent Assessments</h4>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient</th>
                                        <th>By User (Role)</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.recent_assessments.map(assessment => `
                                        <tr>
                                            <td>${assessment.id}</td>
                                            <td>${assessment.patient_name}</td>
                                            <td>
                                                <strong>${assessment.user_name}</strong>
                                                <br>
                                                <span style="font-size: 0.85em; color: #6b7280;">${assessment.user_role || 'Nutritionist'}</span>
                                            </td>
                                            <td><span class="badge badge-${assessment.recovery_status === 'recovered' ? 'success' : 'info'}">${assessment.recovery_status}</span></td>
                                            <td>${assessment.date}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : '<div class="alert alert-info">No assessments found in this period</div>'}
                `;
            }
            
            // Store data for PDF generation
            window.lastUserActivityData = data;
        })
        .catch(error => {
            console.error('Error fetching user activity data:', error);
            if (resultsSection) {
                resultsSection.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error loading data:</strong> Unable to fetch user activity data for the selected period. Please try again.
                    </div>
                `;
            }
        });
}

/**
 * Generate PDF for individual patient
 */
function generateIndividualPatientPDF() {
    console.log('generateIndividualPatientPDF called');
    console.log('currentPatientData available:', !!window.currentPatientData);
    
    // Validation check
    if (!window.currentPatientData || !window.currentPatientData.patient) {
        console.error('Patient data not available:', window.currentPatientData);
        alert('Patient data not available. Please select a patient and try again.');
        return;
    }
    
    try {
        // Extract patient data from API response
        const apiData = window.currentPatientData;
        const firstAssessment = apiData.assessments && apiData.assessments.length > 0 ? apiData.assessments[apiData.assessments.length - 1] : null;
        const latestAssessment = apiData.assessments && apiData.assessments.length > 0 ? apiData.assessments[0] : null;
        
        const patient = {
            name: apiData.patient.name,
            barangay: apiData.patient.barangay,
            sex: apiData.patient.sex,
            age_years: apiData.patient.age_years,
            age_months: apiData.patient.age_months,
            first_assessment_date: apiData.summary.first_assessment_date,
            last_assessment_date: apiData.summary.latest_assessment_date,
            total_assessments: apiData.summary.total_assessments,
            progress_trend: apiData.summary.progress_trend,
            initial_weight: apiData.summary.initial_weight,
            current_weight: apiData.summary.current_weight,
            weight_change: apiData.summary.weight_change,
            initial_height: apiData.summary.initial_height,
            current_height: apiData.summary.current_height,
            height_change: apiData.summary.height_change,
            initial_bmi: apiData.summary.initial_bmi,
            current_bmi: apiData.summary.current_bmi,
            bmi_change: apiData.summary.bmi_change,
            initial_weight_for_age: apiData.summary.initial_weight_for_age,
            current_weight_for_age: apiData.summary.current_weight_for_age,
            weight_for_age_change: apiData.summary.weight_for_age_change,
            initial_height_for_age: apiData.summary.initial_height_for_age,
            current_height_for_age: apiData.summary.current_height_for_age,
            height_for_age_change: apiData.summary.height_for_age_change,
            initial_bmi_for_age: apiData.summary.initial_bmi_for_age,
            current_bmi_for_age: apiData.summary.current_bmi_for_age,
            bmi_for_age_change: apiData.summary.bmi_for_age_change,
            recovery_status: apiData.summary.current_status,
            current_diagnosis: apiData.summary.current_diagnosis || 'N/A'
        };
        
        console.log('Patient object created:', patient);
        
        // Check if jsPDF is loaded
        if (!window.jspdf || !window.jspdf.jsPDF) {
            console.error('jsPDF not loaded');
            alert('PDF library not loaded. Please refresh the page and try again.');
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Add official header with logos
        let yPos = addPDFHeader(doc, 'Individual Patient Report');
        
        // Patient Info Section
        yPos += 5;
        doc.setFontSize(16);
        doc.setTextColor(0, 0, 0);
        doc.setFont(undefined, 'bold');
        doc.text(`Patient: ${patient.name}`, 20, yPos);
        yPos += 10;
        
        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        doc.text(`Barangay: ${patient.barangay}`, 20, yPos);
        doc.text(`Sex: ${patient.sex}`, 100, yPos);
        yPos += 7;
        doc.text(`Age: ${patient.age_years || 'N/A'} years (${patient.age_months || 'N/A'} months)`, 20, yPos);
        yPos += 7;
        doc.text(`First Assessment Date: ${patient.first_assessment_date}`, 20, yPos);
        yPos += 7;
        doc.text(`Latest Assessment Date: ${patient.last_assessment_date}`, 20, yPos);
        yPos += 7;
        doc.text(`Total Assessments Completed: ${patient.total_assessments}`, 20, yPos);
        yPos += 12;
        
        // Progress Status
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text(`Diagnosis: ${patient.current_diagnosis}`, 20, yPos);
        doc.setFont(undefined, 'normal');
        yPos += 8;
        yPos += 7;
        
        // Weight and BMI Comparison Table
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text('Nutritional Assessment Data:', 20, yPos);
        yPos += 8;
    
    doc.autoTable({
        startY: yPos,
        head: [['Measurement', 'Initial', 'Current', 'Change']],
        body: [
            [
                'Weight (kg)',
                (patient.initial_weight != null && patient.initial_weight !== '') ? Number(patient.initial_weight).toFixed(1) : 'N/A',
                (patient.current_weight != null && patient.current_weight !== '') ? Number(patient.current_weight).toFixed(1) : 'N/A',
                (patient.weight_change != null && patient.weight_change !== '') ? (patient.weight_change > 0 ? '+' : '') + Number(patient.weight_change).toFixed(1) + ' kg' : 'N/A'
            ],
            [
                'Height (cm)',
                (patient.initial_height != null && patient.initial_height !== '') ? Number(patient.initial_height).toFixed(2) : 'N/A',
                (patient.current_height != null && patient.current_height !== '') ? Number(patient.current_height).toFixed(2) : 'N/A',
                (patient.height_change != null && patient.height_change !== '') ? (patient.height_change > 0 ? '+' : '') + Number(patient.height_change).toFixed(2) + ' cm' : 'N/A'
            ],
            [
                'Weight for Age (Z-score)',
                (patient.initial_weight_for_age != null) ? Number(patient.initial_weight_for_age).toFixed(2) : 'N/A',
                (patient.current_weight_for_age != null) ? Number(patient.current_weight_for_age).toFixed(2) : 'N/A',
                (patient.weight_for_age_change != null) ? (patient.weight_for_age_change >= 0 ? '+' : '') + Number(patient.weight_for_age_change).toFixed(2) : 'N/A'
            ],
            [
                'Height for Age (Z-score)',
                (patient.initial_height_for_age != null) ? Number(patient.initial_height_for_age).toFixed(2) : 'N/A',
                (patient.current_height_for_age != null) ? Number(patient.current_height_for_age).toFixed(2) : 'N/A',
                (patient.height_for_age_change != null) ? (patient.height_for_age_change >= 0 ? '+' : '') + Number(patient.height_for_age_change).toFixed(2) : 'N/A'
            ],
            [
                'BMI for Age (Z-score)',
                (patient.initial_bmi_for_age != null) ? Number(patient.initial_bmi_for_age).toFixed(2) : 'N/A',
                (patient.current_bmi_for_age != null) ? Number(patient.current_bmi_for_age).toFixed(2) : 'N/A',
                (patient.bmi_for_age_change != null) ? (patient.bmi_for_age_change >= 0 ? '+' : '') + Number(patient.bmi_for_age_change).toFixed(2) : 'N/A'
            ],
            [
                'Recovery Status',
                '-',
                patient.recovery_status ? patient.recovery_status.charAt(0).toUpperCase() + patient.recovery_status.slice(1) : 'Under Observation',
                '-'
            ]
        ],
        theme: 'grid',
        headStyles: { 
            fillColor: [46, 125, 50],
            fontSize: 10,
            fontStyle: 'bold'
        },
        columnStyles: {
            0: { fontStyle: 'bold', cellWidth: 50 },
            1: { halign: 'center', cellWidth: 40 },
            2: { halign: 'center', cellWidth: 40 },
            3: { 
                halign: 'center', 
                cellWidth: 40,
                textColor: function(data) {
                    if (data.row.index === 0 || data.row.index === 1) {
                        const changeText = data.cell.text[0];
                        if (changeText.includes('+')) return [16, 185, 129]; // Green for positive
                        if (changeText.includes('-')) return [239, 68, 68]; // Red for negative
                    }
                    return [0, 0, 0];
                }
            }
        },
        margin: { left: 20, right: 20 },
        styles: { fontSize: 9 }
    });
    
    yPos = doc.lastAutoTable.finalY + 15;
    
    // Add footer note
    doc.setFontSize(8);
    doc.setTextColor(100, 100, 100);
    doc.text(`Report Generated: ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`, 20, yPos);
    doc.text(`by City Health Office Nutrition Program`, 20, yPos + 4);
    
    // Save PDF
    try {
        const fileName = `Patient_Report_${patient.name.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`;
        console.log('Saving PDF as:', fileName);
        doc.save(fileName);
        console.log('PDF saved successfully');
        alert(`✓ PDF report generated for ${patient.name}`);
    } catch (error) {
        console.error('Error saving PDF:', error);
        alert('Error saving PDF: ' + error.message);
    }
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Error generating PDF: ' + error.message);
    }
}


