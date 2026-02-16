/**
 * Report Content Generation JavaScript
 */

/**
 * Generate report content HTML
 */
function generateReportContent(reportType, data) {
    switch (reportType) {
        case 'user-activity':
            return generateUserActivityContent(data);
        case 'inventory':
            return generateInventoryContent(data);
        case 'assessment-trends':
            return generateAssessmentTrendsContent(data);
        case 'low-stock':
            return generateLowStockContent(data);
        default:
            return '<div class="empty-state">Report content not available for this type.</div>';
    }
}

/**
 * Generate user activity report content
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
                    <div class="stat-value">${data.active_users_30_days || 0}</div>
                </div>
            </div>
        </div>
        
        <div class="report-section">
            <h4>Users by Role</h4>
            <div class="data-grid">
                ${Object.entries(data.users_by_role || {}).map(([role, count]) => `
                    <div class="data-item">
                        <span>${role}</span>
                        <span class="data-value">${count}</span>
                    </div>
                `).join('')}
            </div>
        </div>
        
        <div class="report-section">
            <h4>Recent Assessments</h4>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <!-- Assessment ID column removed -->
                            <th>Patient</th>
                            <th>BNS</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(data.recent_assessments || []).length > 0 ? 
                            (data.recent_assessments || []).map(assessment => `
                                <tr>
                                    <td>${assessment.patient?.first_name || 'N/A'} ${assessment.patient?.last_name || ''}</td>
                                    <td>${assessment.user?.name || 'N/A'}</td>
                                    <td>${formatDate(assessment.created_at)}</td>
                                </tr>
                            `).join('') :
                            '<tr><td colspan="3" class="empty-state">No recent assessments found</td></tr>'
                        }
                    </tbody>
                </table>
            </div>
        </div>
    `;
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
                    <div class="stat-value">${data.total_items || 0}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Low Stock Items</div>
                    <div class="stat-value">${Array.isArray(data.low_stock_items) ? data.low_stock_items.length : 0}</div>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h4>Low Stock Items</h4>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Minimum Stock</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.isArray(data.low_stock_items) ? data.low_stock_items.map(item => `
                            <tr>
                                <td>${item.item_name || 'N/A'}</td>
                                <td>${item.quantity || 0}</td>
                                <td>${item.minimum_stock || 0}</td>
                                <td>${item.unit || 'N/A'}</td>
                            </tr>
                        `).join('') : ''}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section">
            <h4>Stock Levels</h4>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.isArray(data.stock_levels) ? data.stock_levels.map(item => `
                            <tr>
                                <td>${item.item_name || 'N/A'}</td>
                                <td>${item.quantity || 0}</td>
                                <td>${item.unit || 'N/A'}</td>
                                <td>${item.status || 'N/A'}</td>
                            </tr>
                        `).join('') : ''}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section">
            <h4>Recent Transactions</h4>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item Name</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>User</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.isArray(data.recent_transactions) ? data.recent_transactions.map(tx => `
                            <tr>
                                <td>${tx.transaction_date ? formatDate(tx.transaction_date) : 'N/A'}</td>
                                <td>${tx.item?.item_name || 'N/A'}</td>
                                <td>${tx.transaction_type || 'N/A'}</td>
                                <td>${tx.quantity || 0}</td>
                                <td>${tx.user ? `${tx.user.first_name} ${tx.user.last_name}` : 'N/A'}</td>
                                <td>${tx.remarks || ''}</td>
                            </tr>
                        `).join('') : ''}
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
                    <div class="stat-value">${data.total_assessments || 0}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">${data.assessments_this_month || 0}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Average Per Day</div>
                    <div class="stat-value">${data.avg_assessments_per_day || 0}</div>
                </div>
            </div>
        </div>
        
        ${(data.monthly_trends && data.monthly_trends.length > 0) ? `
        <div class="report-section">
            <h4>Monthly Trends</h4>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Assessments</th>
                            <th>Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.monthly_trends.map(trend => `
                            <tr>
                                <td>${trend.month || 'N/A'}</td>
                                <td>${trend.count || 0}</td>
                                <td>${trend.growth !== undefined ? (trend.growth >= 0 ? '+' : '') + trend.growth + '%' : 'N/A'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        ` : ''}
        
        ${(data.assessments_by_nutritionist && Object.keys(data.assessments_by_nutritionist).length > 0) ? `
        <div class="report-section">
            <h4>Assessments by BNS</h4>
            <div class="data-grid">
                ${Object.entries(data.assessments_by_nutritionist).map(([nutritionist, count]) => `
                    <div class="data-item">
                        <span>${nutritionist}</span>
                        <span class="data-value">${count}</span>
                    </div>
                `).join('')}
            </div>
        </div>
        ` : ''}
    `;
}

/**
 * Generate low stock alert report content
 */
function generateLowStockContent(data) {
    const criticalItems = data.critical_stock_items || [];
    const warningItems = data.warning_stock_items || [];
    
    return `
        <div class="report-summary">
            ${(criticalItems.length > 0 || warningItems.length > 0) ? `
            <div class="report-alert danger">
                <strong>Urgent Attention Required!</strong> ${criticalItems.length} critical and ${warningItems.length} warning items need immediate action.
            </div>
            ` : `
            <div class="report-alert info">
                <strong>Good News!</strong> All items are adequately stocked.
            </div>
            `}
            
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-label">Critical Items</div>
                    <div class="stat-value">${data.critical_items || 0}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Warning Items</div>
                    <div class="stat-value">${data.warning_items || 0}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Affected Value</div>
                    <div class="stat-value">₱${formatCurrency(data.total_affected_value || 0)}</div>
                </div>
            </div>
        </div>
        
        ${criticalItems.length > 0 ? `
        <div class="report-section">
            <h4>Critical Stock Items (Immediate Action Required)</h4>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Current Stock</th>
                            <th>Minimum Stock</th>
                            <th>Category</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${criticalItems.map(item => `
                            <tr style="background-color: #f8d7da;">
                                <td><strong>${item.item_name || 'N/A'}</strong></td>
                                <td><strong>${item.current_stock || 0}</strong></td>
                                <td>${item.minimum_stock || 0}</td>
                                <td>${item.category || 'N/A'}</td>
                                <td>${formatDate(item.updated_at)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        ` : ''}
        
        ${warningItems.length > 0 ? `
        <div class="report-section">
            <h4>Warning Stock Items (Reorder Soon)</h4>
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Current Stock</th>
                            <th>Minimum Stock</th>
                            <th>Category</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${warningItems.map(item => `
                            <tr style="background-color: #fff3cd;">
                                <td>${item.item_name || 'N/A'}</td>
                                <td>${item.current_stock || 0}</td>
                                <td>${item.minimum_stock || 0}</td>
                                <td>${item.category || 'N/A'}</td>
                                <td>${formatDate(item.updated_at)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        ` : ''}
    `;
}

/**
 * Helper function to format dates
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    } catch (error) {
        return 'Invalid Date';
    }
}

/**
 * Helper function to format currency
 */
