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
                                        <th>By User</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.recent_assessments.map(assessment => `
                                        <tr>
                                            <td>${assessment.id}</td>
                                            <td>${assessment.patient_name}</td>
                                            <td>${assessment.user_name}</td>
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
