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
    let content = '';
    let title = '';
    
    switch(reportType) {
        case 'malnutrition-cases':
            title = 'Malnutrition Cases by Severity';
            content = generateMalnutritionCasesContent();
            break;
        case 'patient-progress':
            title = 'Patient Progress & Recovery Report';
            content = generatePatientProgressContent();
            break;
        case 'individual-patient':
            title = 'Individual Patient Report';
            content = generateIndividualPatientSelector();
            break;
        case 'low-stock-alert':
            title = 'Low Stock Alert Report';
            content = generateLowStockAlertContent();
            break;
        case 'monthly-trends':
            title = 'Monthly Trends Analysis';
            content = generateMonthlyTrendsContent();
            break;
        default:
            content = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Report generation in progress...</div>';
    }
    
    showReportModalWithContent(title, content, reportType);
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
    return `
        <div class="report-summary">
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value">${data.total_users || 0}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Active Users (30 days)</div>
                    <div class="stat-value" style="color: #10b981">${data.active_users_30_days || 0}</div>
                </div>
            </div>
        </div>
        
        ${(data.users_by_role && Object.keys(data.users_by_role).length > 0) ? `
            <div class="report-section">
                <h4>Users by Role</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Object.entries(data.users_by_role).map(([role, count]) => `
                            <tr>
                                <td><strong>${role}</strong></td>
                                <td>${count}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : ''}
        
        ${(data.recent_assessments && data.recent_assessments.length > 0) ? `
            <div class="report-section">
                <h4>Recent Assessments (Last 10)</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>By User</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.recent_assessments.map(assessment => {
                            const patientName = assessment.patient 
                                ? `${assessment.patient.first_name} ${assessment.patient.last_name}` 
                                : 'N/A';
                            const userName = assessment.user ? assessment.user.name : 'N/A';
                            const date = assessment.created_at ? new Date(assessment.created_at).toLocaleDateString() : 'N/A';
                            return `
                                <tr>
                                    <td>${assessment.id}</td>
                                    <td>${patientName}</td>
                                    <td>${userName}</td>
                                    <td>${date}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        ` : ''}
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
            
            // Add header
            doc.setFontSize(20);
            doc.setTextColor(46, 125, 50);
            doc.text('Nutrition Management System', 105, 20, { align: 'center' });
            
            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text(title, 105, 35, { align: 'center' });
            
            // Add generation date
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`Generated on: ${new Date().toLocaleString()}`, 105, 45, { align: 'center' });
            
            let yPos = 60;
            
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
        underweight: { count: 0, percentage: 0, patients: [] },
        malnourished: { count: 0, percentage: 0, patients: [] },
        severe_malnourishment: { count: 0, percentage: 0, patients: [] },
        barangay_breakdown: {}
    };
    
    doc.setFontSize(14);
    doc.setTextColor(0, 0, 0);
    doc.text('Malnutrition Cases by Severity', 20, startY);
    
    startY += 10;
    
    // Summary statistics
    const totalPatients = distData.normal.count + distData.underweight.count + 
                         distData.malnourished.count + distData.severe_malnourishment.count;
    const atRiskCount = distData.malnourished.count + distData.severe_malnourishment.count + distData.underweight.count;
    
    doc.setFontSize(12);
    doc.text('Overall Statistics:', 20, startY);
    startY += 8;
    
    doc.setFontSize(10);
    doc.text(`• Total Patients: ${totalPatients}`, 25, startY);
    doc.text(`• At Risk Patients: ${atRiskCount} (${totalPatients > 0 ? Math.round((atRiskCount/totalPatients)*100) : 0}%)`, 25, startY + 7);
    doc.text(`• Severe Cases: ${distData.severe_malnourishment.count}`, 25, startY + 14);
    doc.text(`• Malnourished: ${distData.malnourished.count}`, 25, startY + 21);
    doc.text(`• Underweight: ${distData.underweight.count}`, 25, startY + 28);
    
    startY += 40;
    
    // Distribution table
    doc.autoTable({
        startY: startY,
        head: [['Nutritional Status', 'Count', 'Percentage', 'BMI Range']],
        body: [
            ['Severe Malnourishment', distData.severe_malnourishment.count.toString(), `${distData.severe_malnourishment.percentage}%`, '< 16'],
            ['Malnourished', distData.malnourished.count.toString(), `${distData.malnourished.percentage}%`, '16-18.5'],
            ['Underweight', distData.underweight.count.toString(), `${distData.underweight.percentage}%`, '< 17'],
            ['Normal Weight', distData.normal.count.toString(), `${distData.normal.percentage}%`, '≥ 18.5']
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
            const priority = data.severe > 0 ? 'Critical' : (data.malnourished > 0 ? 'High' : 'Medium');
            return [
                barangay,
                data.severe.toString(),
                data.malnourished.toString(),
                data.underweight.toString(),
                data.total.toString(),
                priority
            ];
        });
        
        doc.autoTable({
            startY: startY,
            head: [['Barangay', 'Severe', 'Malnourished', 'Underweight', 'Total', 'Priority']],
            body: barangayData,
            theme: 'striped',
            headStyles: { fillColor: [220, 38, 38] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 8 },
            columnStyles: {
                1: { halign: 'center', textColor: [153, 27, 27] },
                2: { halign: 'center', textColor: [239, 68, 68] },
                3: { halign: 'center', textColor: [245, 158, 11] },
                4: { halign: 'center', fontStyle: 'bold' }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    // Severe cases detail
    if (distData.severe_malnourishment.patients && distData.severe_malnourishment.patients.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(153, 27, 27);
        doc.text('Severe Cases - Immediate Attention Required:', 20, startY);
        doc.setTextColor(0, 0, 0);
        startY += 8;
        
        const severePatients = distData.severe_malnourishment.patients.slice(0, 15).map(patient => [
            patient.name,
            patient.barangay,
            patient.age?.toString() || 'N/A',
            patient.bmi?.toString() || 'N/A',
            patient.last_assessment
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['Patient Name', 'Barangay', 'Age', 'BMI', 'Last Assessment']],
            body: severePatients,
            theme: 'grid',
            headStyles: { fillColor: [153, 27, 27] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 8 },
            columnStyles: {
                2: { halign: 'center' },
                3: { halign: 'center', textColor: [153, 27, 27], fontStyle: 'bold' }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 10;
        
        if (distData.severe_malnourishment.patients.length > 15) {
            doc.setFontSize(9);
            doc.setTextColor(107, 114, 128);
            doc.text(`... and ${distData.severe_malnourishment.patients.length - 15} more severe cases`, 25, startY);
            startY += 10;
        }
    }
    
    // Recommendations
    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);
    doc.text('Recommended Actions:', 20, startY);
    startY += 8;
    
    doc.setFontSize(10);
    doc.text('Priority 1 - Severe Cases:', 25, startY);
    doc.setFontSize(9);
    doc.text('  - Immediate medical referral and hospitalization', 30, startY + 6);
    doc.text('  - Daily monitoring and emergency food assistance', 30, startY + 12);
    
    startY += 20;
    
    doc.setFontSize(10);
    doc.text('Priority 2 - High-Risk Barangays:', 25, startY);
    doc.setFontSize(9);
    doc.text('  - Conduct household visits and assessments', 30, startY + 6);
    doc.text('  - Implement community feeding programs', 30, startY + 12);
    
    startY += 20;
    
    doc.setFontSize(10);
    doc.text('General Recommendations:', 25, startY);
    doc.setFontSize(9);
    doc.text('  - Weekly weight monitoring for all at-risk patients', 30, startY + 6);
    doc.text('  - Monthly progress assessments and family counseling', 30, startY + 12);
    
    startY += 25;
    
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
    
    // User statistics
    doc.setFontSize(12);
    doc.text('User Statistics:', 20, startY);
    startY += 8;
    
    doc.setFontSize(10);
    doc.text(`Total Active Users: ${userData.total_users || 0}`, 25, startY);
    doc.text(`Recent Assessments: ${userData.recent_assessments || 0}`, 25, startY + 7);
    
    startY += 20;
    
    // Roles breakdown
    if (userData.by_role && userData.by_role.length > 0) {
        doc.setFontSize(12);
        doc.text('Users by Role:', 20, startY);
        startY += 8;
        
        const roleData = userData.by_role.map(role => [
            role.name,
            role.count.toString()
        ]);
        
        doc.autoTable({
            startY: startY,
            head: [['Role', 'Count']],
            body: roleData,
            theme: 'grid',
            headStyles: { fillColor: [59, 130, 246] },
            margin: { left: 20, right: 20 },
            styles: { fontSize: 10 },
            columnStyles: {
                0: { cellWidth: 100 },
                1: { cellWidth: 50, halign: 'center' }
            }
        });
        
        startY = doc.lastAutoTable.finalY + 15;
    }
    
    // Recent activity summary
    if (userData.recent_assessments > 0) {
        doc.setFontSize(12);
        doc.text('Activity Summary:', 20, startY);
        startY += 8;
        
        doc.setFontSize(10);
        doc.text('System is actively being used for patient assessments.', 25, startY);
        doc.text('Regular monitoring is recommended to ensure optimal performance.', 25, startY + 7);
        
        startY += 20;
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
        width: '900px',
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

function generateMalnutritionCasesContent() {
    const distData = window.patientDistributionData || {
        normal: { count: 0, percentage: 0, patients: [] },
        underweight: { count: 0, percentage: 0, patients: [] },
        malnourished: { count: 0, percentage: 0, patients: [] },
        severe_malnourishment: { count: 0, percentage: 0, patients: [] },
        barangay_breakdown: {}
    };
    
    const totalAtRisk = distData.severe_malnourishment.count + distData.malnourished.count + distData.underweight.count;
    const totalPatients = totalAtRisk + distData.normal.count;
    
    // Prepare barangay data
    let barangayRows = '';
    if (distData.barangay_breakdown && Object.keys(distData.barangay_breakdown).length > 0) {
        barangayRows = Object.entries(distData.barangay_breakdown)
            .map(([barangay, data]) => {
                const priorityLevel = data.severe > 0 ? 'Critical' : (data.malnourished > 0 ? 'High' : 'Medium');
                const priorityColor = data.severe > 0 ? '#991b1b' : (data.malnourished > 0 ? '#dc2626' : '#f59e0b');
                
                return `
                    <tr>
                        <td><strong>${barangay}</strong></td>
                        <td style="text-align: center; color: #991b1b; font-weight: bold;">${data.severe}</td>
                        <td style="text-align: center; color: #ef4444;">${data.malnourished}</td>
                        <td style="text-align: center; color: #f59e0b;">${data.underweight}</td>
                        <td style="text-align: center; font-weight: bold;">${data.total}</td>
                        <td style="text-align: center;">
                            <span style="color: ${priorityColor}; font-weight: 600;">${priorityLevel}</span>
                        </td>
                    </tr>
                `;
            }).join('');
    } else {
        barangayRows = '<tr><td colspan="6" style="text-align: center; color: #9ca3af;">No barangay data available</td></tr>';
    }
    
    // Prepare detailed patient lists
    let severePatientRows = '';
    if (distData.severe_malnourishment.patients && distData.severe_malnourishment.patients.length > 0) {
        severePatientRows = distData.severe_malnourishment.patients.map(patient => `
            <tr>
                <td>${patient.name}</td>
                <td>${patient.barangay}</td>
                <td style="text-align: center;">${patient.age || 'N/A'}</td>
                <td style="text-align: center; color: #991b1b; font-weight: bold;">${patient.bmi || 'N/A'}</td>
                <td style="font-size: 0.813rem; color: #6b7280;">${patient.last_assessment}</td>
            </tr>
        `).join('');
    } else {
        severePatientRows = '<tr><td colspan="5" style="text-align: center; color: #10b981;">No severe cases - excellent!</td></tr>';
    }
    
    let malnourishedPatientRows = '';
    if (distData.malnourished.patients && distData.malnourished.patients.length > 0) {
        malnourishedPatientRows = distData.malnourished.patients.slice(0, 10).map(patient => `
            <tr>
                <td>${patient.name}</td>
                <td>${patient.barangay}</td>
                <td style="text-align: center;">${patient.age || 'N/A'}</td>
                <td style="text-align: center; color: #ef4444; font-weight: bold;">${patient.bmi || 'N/A'}</td>
                <td style="font-size: 0.813rem; color: #6b7280;">${patient.last_assessment}</td>
            </tr>
        `).join('');
        
        if (distData.malnourished.patients.length > 10) {
            malnourishedPatientRows += `<tr><td colspan="5" style="text-align: center; font-style: italic; color: #6b7280;">... and ${distData.malnourished.patients.length - 10} more patients</td></tr>`;
        }
    } else {
        malnourishedPatientRows = '<tr><td colspan="5" style="text-align: center; color: #9ca3af;">No malnourished patients</td></tr>';
    }
    
    return `
        <div class="report-summary">
            <div class="stat-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-item">
                    <div class="stat-label">Severe Cases</div>
                    <div class="stat-value" style="color: #991b1b">${distData.severe_malnourishment.count}</div>
                    <div class="stat-meta">${distData.severe_malnourishment.percentage}% of total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Malnourished</div>
                    <div class="stat-value" style="color: #ef4444">${distData.malnourished.count}</div>
                    <div class="stat-meta">${distData.malnourished.percentage}% of total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Underweight</div>
                    <div class="stat-value" style="color: #f59e0b">${distData.underweight.count}</div>
                    <div class="stat-meta">${distData.underweight.percentage}% of total</div>
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
                        <th style="text-align: center;">Severe</th>
                        <th style="text-align: center;">Malnourished</th>
                        <th style="text-align: center;">Underweight</th>
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
            <h4><i class="fas fa-exclamation-triangle" style="color: #991b1b; margin-right: 0.5rem;"></i>Severe Cases - Immediate Attention Required</h4>
            <div class="alert alert-warning" style="background: #fef3c7; border-left: 4px solid #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <strong>Critical:</strong> These patients require immediate medical attention and intervention (BMI < 16)
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Barangay</th>
                        <th style="text-align: center;">Age</th>
                        <th style="text-align: center;">BMI</th>
                        <th>Last Assessment</th>
                    </tr>
                </thead>
                <tbody>
                    ${severePatientRows}
                </tbody>
            </table>
        </div>
        
        <div class="report-section">
            <h4><i class="fas fa-user-injured" style="color: #ef4444; margin-right: 0.5rem;"></i>Malnourished Patients (Top 10)</h4>
            <p style="margin-bottom: 1rem; color: #6b7280;">Patients requiring nutritional intervention (BMI < 18.5)</p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Barangay</th>
                        <th style="text-align: center;">Age</th>
                        <th style="text-align: center;">BMI</th>
                        <th>Last Assessment</th>
                    </tr>
                </thead>
                <tbody>
                    ${malnourishedPatientRows}
                </tbody>
            </table>
        </div>
        
        <div class="report-section">
            <h4><i class="fas fa-clipboard-list" style="color: #2e7d32; margin-right: 0.5rem;"></i>WHO BMI Classification Standards</h4>
            <ul style="line-height: 1.8;">
                <li><strong style="color: #991b1b;">Severe Malnourishment:</strong> BMI < 16 - Requires immediate medical attention and hospitalization</li>
                <li><strong style="color: #ef4444;">Malnourished:</strong> BMI 16-18.5 - Needs urgent nutritional intervention and monitoring</li>
                <li><strong style="color: #f59e0b;">Underweight:</strong> BMI < 17 - Monitor closely and provide nutritional support</li>
                <li><strong style="color: #10b981;">Normal Weight:</strong> BMI ≥ 18.5 - Maintain current nutritional status</li>
            </ul>
        </div>
        
        <div class="report-section">
            <h4><i class="fas fa-tasks" style="color: #2e7d32; margin-right: 0.5rem;"></i>Recommended Actions</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <div style="background: #fee2e2; padding: 1rem; border-radius: 8px; border-left: 4px solid #dc2626;">
                    <strong style="color: #991b1b;">Priority 1 - Severe Cases</strong>
                    <ul style="margin-top: 0.5rem; font-size: 0.875rem;">
                        <li>Immediate medical referral</li>
                        <li>Daily monitoring required</li>
                        <li>Emergency food assistance</li>
                    </ul>
                </div>
                <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <strong style="color: #92400e;">Priority 2 - High-Risk Barangays</strong>
                    <ul style="margin-top: 0.5rem; font-size: 0.875rem;">
                        <li>Conduct household visits</li>
                        <li>Community feeding programs</li>
                        <li>Nutrition education sessions</li>
                    </ul>
                </div>
                <div style="background: #dbeafe; padding: 1rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <strong style="color: #1e40af;">General Recommendations</strong>
                    <ul style="margin-top: 0.5rem; font-size: 0.875rem;">
                        <li>Weekly weight monitoring</li>
                        <li>Monthly progress assessments</li>
                        <li>Family counseling programs</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
}

function generatePatientProgressContent() {
    const monthlyData = window.monthlyProgressData || {
        months: [],
        assessments: [],
        recovered: [],
        total_assessments: 0,
        total_recovered: 0,
        patient_progress: [],
        barangay_progress: {},
        barangays: []
    };
    
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

function generateLowStockAlertContent() {
    const stats = window.reportsStatsData || { low_stock_items: 0, low_stock_items_data: [] };
    const alertItems = stats.low_stock_items_data || [];
    
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
                    <strong>Action Required:</strong> ${stats.low_stock_items} item${stats.low_stock_items !== 1 ? 's' : ''} require immediate attention.
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

function generateMonthlyTrendsContent() {
    const monthlyData = window.monthlyProgressData || {
        months: [],
        assessments: [],
        recovered: []
    };
    
    return `
        <div class="report-section">
            <h4>6-Month Trend Analysis</h4>
            <p>Comprehensive trends showing program effectiveness over time.</p>
            <canvas id="trendChart" style="max-height: 300px;"></canvas>
            <script>
                setTimeout(() => {
                    const ctx = document.getElementById('trendChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ${JSON.stringify(monthlyData.months)},
                            datasets: [{
                                label: 'Assessments',
                                data: ${JSON.stringify(monthlyData.assessments)},
                                borderColor: '#3b82f6',
                                tension: 0.4
                            }, {
                                label: 'Recovered',
                                data: ${JSON.stringify(monthlyData.recovered)},
                                borderColor: '#10b981',
                                tension: 0.4
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false }
                    });
                }, 100);
            </script>
        </div>
    `;
}

/**
 * Generate individual patient selector interface
 */
function generateIndividualPatientSelector() {
    const monthlyData = window.monthlyProgressData || { patient_progress: [] };
    const patients = monthlyData.patient_progress || [];
    
    if (patients.length === 0) {
        return `
            <div style="text-align: center; padding: 3rem; color: #9ca3af;">
                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.125rem; font-weight: 500;">No patient data available</p>
                <p style="font-size: 0.875rem;">Patient progress data will appear here once assessments are recorded.</p>
            </div>
        `;
    }
    
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
                onclick="showIndividualPatientReport(${index})"
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
    const monthlyData = window.monthlyProgressData || { patient_progress: [] };
    const patients = monthlyData.patient_progress || [];
    
    const listContainer = document.getElementById('patientSelectionList');
    if (listContainer) {
        listContainer.innerHTML = generatePatientSelectionList(patients, searchTerm);
    }
}

/**
 * Show detailed report for individual patient
 */
function showIndividualPatientReport(patientIndex) {
    const monthlyData = window.monthlyProgressData || { patient_progress: [] };
    const patients = monthlyData.patient_progress || [];
    const patient = patients[patientIndex];
    
    if (!patient) {
        showAlert('Patient data not found', 'error');
        return;
    }
    
    const content = `
        <div class="report-section">
            <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #bae6fd;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0; color: #1e3a8a; font-size: 1.5rem;">
                            <i class="fas fa-user-circle"></i> ${patient.name}
                        </h3>
                        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                            <span style="color: #1e40af; font-size: 0.875rem;">
                                <i class="fas fa-map-marker-alt" style="color: #f59e0b;"></i> ${patient.barangay}
                            </span>
                            <span style="color: #1e40af; font-size: 0.875rem;">
                                <i class="fas fa-birthday-cake"></i> Age: ${patient.age || 'N/A'}
                            </span>
                        </div>
                    </div>
                    <button onclick="generateIndividualPatientPDF(${patientIndex}, '${patient.name.replace(/'/g, "\\'")}')" class="btn btn-primary" 
                        style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
            
            <div class="stat-grid" style="margin-bottom: 1.5rem;">
                <div class="stat-item">
                    <div class="stat-label">Total Assessments</div>
                    <div class="stat-value">${patient.total_assessments}</div>
                    <div class="stat-meta">Recorded visits</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Weight Change</div>
                    <div class="stat-value" style="color: ${patient.weight_change > 0 ? '#10b981' : patient.weight_change < 0 ? '#ef4444' : '#6b7280'}">
                        ${patient.weight_change ? (patient.weight_change > 0 ? '+' : '') + patient.weight_change + ' kg' : 'N/A'}
                    </div>
                    <div class="stat-meta">Overall trend</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">BMI Change</div>
                    <div class="stat-value" style="color: ${patient.bmi_change > 0 ? '#10b981' : patient.bmi_change < 0 ? '#ef4444' : '#6b7280'}">
                        ${patient.bmi_change ? (patient.bmi_change > 0 ? '+' : '') + patient.bmi_change : 'N/A'}
                    </div>
                    <div class="stat-meta">Progress indicator</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Progress Status</div>
                    <div class="stat-value" style="color: ${patient.progress_trend === 'improving' ? '#10b981' : patient.progress_trend === 'declining' ? '#ef4444' : '#3b82f6'}; font-size: 1.25rem;">
                        ${patient.progress_trend === 'improving' ? '↗ Improving' : patient.progress_trend === 'declining' ? '↘ Declining' : '→ Stable'}
                    </div>
                    <div class="stat-meta">Current trend</div>
                </div>
            </div>
            
            <div class="report-section">
                <h4><i class="fas fa-calendar-alt" style="color: #3b82f6;"></i> Assessment Period</h4>
                <p style="color: #6b7280; font-size: 0.875rem;">
                    <strong>First Assessment:</strong> ${patient.first_assessment_date}<br>
                    <strong>Latest Assessment:</strong> ${patient.last_assessment_date}
                </p>
            </div>
            
            <div class="alert alert-info" style="margin-top: 1rem;">
                <i class="fas fa-lightbulb"></i>
                <strong>Progress Summary:</strong> 
                ${patient.progress_trend === 'improving' 
                    ? `This patient shows positive progress with ${patient.bmi_change > 0 ? 'improving' : 'stable'} BMI trends. Continue current intervention plan.`
                    : patient.progress_trend === 'declining'
                    ? `This patient's progress shows decline. Review and adjust intervention plan as needed. Consider additional nutritional support.`
                    : `This patient maintains stable condition. Continue monitoring and maintain current care plan.`
                }
            </div>
        </div>
    `;
    
    showReportModalWithContent(`Individual Patient Report: ${patient.name}`, content, 'individual-patient-detail');
}

/**
 * Generate PDF for individual patient
 */
function generateIndividualPatientPDF(patientIndex, patientName) {
    // Validation check
    if (patientIndex === undefined || patientIndex === null || patientIndex === '') {
        showAlert('Please select a patient first before generating PDF', 'warning');
        return;
    }
    
    const monthlyData = window.monthlyProgressData || { patient_progress: [] };
    const patients = monthlyData.patient_progress || [];
    const patient = patients[patientIndex];
    
    if (!patient) {
        showAlert('Patient data not found. Please select a patient from the list.', 'error');
        return;
    }
    
    // Check if jsPDF is loaded
    if (!window.jspdf || !window.jspdf.jsPDF) {
        showAlert('PDF library not loaded. Please refresh the page and try again.', 'error');
        return;
    }
    
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Header
    doc.setFontSize(18);
    doc.setTextColor(46, 125, 50);
    doc.text('Individual Patient Report', 20, 20);
    
    doc.setFontSize(10);
    doc.setTextColor(100, 100, 100);
    doc.text(`Generated: ${new Date().toLocaleDateString()}`, 20, 27);
    
    // Patient Info
    let yPos = 40;
    doc.setFontSize(14);
    doc.setTextColor(0, 0, 0);
    doc.text(`Patient: ${patient.name}`, 20, yPos);
    yPos += 8;
    
    doc.setFontSize(10);
    doc.text(`Barangay: ${patient.barangay}`, 20, yPos);
    doc.text(`Age: ${patient.age || 'N/A'}`, 80, yPos);
    yPos += 15;
    
    // Summary Stats
    doc.setFontSize(12);
    doc.text('Progress Summary:', 20, yPos);
    yPos += 8;
    
    doc.setFontSize(10);
    doc.text(`• Total Assessments: ${patient.total_assessments}`, 25, yPos);
    yPos += 7;
    doc.text(`• Weight Change: ${patient.weight_change ? (patient.weight_change > 0 ? '+' : '') + patient.weight_change + ' kg' : 'N/A'}`, 25, yPos);
    yPos += 7;
    doc.text(`• BMI Change: ${patient.bmi_change ? (patient.bmi_change > 0 ? '+' : '') + patient.bmi_change : 'N/A'}`, 25, yPos);
    yPos += 7;
    doc.text(`• Progress Status: ${patient.progress_trend === 'improving' ? 'Improving' : patient.progress_trend === 'declining' ? 'Declining' : 'Stable'}`, 25, yPos);
    yPos += 10;
    
    // Assessment Period
    doc.setFontSize(12);
    doc.text('Assessment Period:', 20, yPos);
    yPos += 8;
    
    doc.setFontSize(10);
    doc.text(`First Assessment: ${patient.first_assessment_date}`, 25, yPos);
    yPos += 7;
    doc.text(`Latest Assessment: ${patient.last_assessment_date}`, 25, yPos);
    yPos += 15;
    
    // Recommendation
    doc.setFontSize(12);
    doc.text('Recommendation:', 20, yPos);
    yPos += 8;
    
    doc.setFontSize(9);
    doc.setTextColor(60, 60, 60);
    const recommendation = patient.progress_trend === 'improving' 
        ? 'Patient shows positive progress. Continue current intervention plan and monitor regularly.'
        : patient.progress_trend === 'declining'
        ? 'Patient progress shows decline. Review and adjust intervention plan. Consider additional support.'
        : 'Patient maintains stable condition. Continue monitoring and maintain current care plan.';
    
    const lines = doc.splitTextToSize(recommendation, 170);
    doc.text(lines, 25, yPos);
    
    // Save PDF
    doc.save(`Patient_Report_${patient.name.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`);
    
    showAlert(`PDF report generated for ${patient.name}`, 'success');
}


