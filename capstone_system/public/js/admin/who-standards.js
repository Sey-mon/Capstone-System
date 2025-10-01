// Modern WHO Standards JavaScript

let currentData = null;
let currentView = 'table';

// Enhanced modal functionality
function showStandardDataModern(type, title) {
    const button = event.target.closest('button');
    const dataString = button.getAttribute('data-standard');
    
    try {
        const data = JSON.parse(dataString);
        currentData = data;
        
        const modal = new bootstrap.Modal(document.getElementById('standardModal'));
        document.getElementById('standardModalLabel').textContent = title;
        
        // Initialize view
        renderDataView('table');
        updateRecordCount();
        
        modal.show();
    } catch (error) {
        console.error('Error parsing standard data:', error);
        showNotification('Error loading standard data', 'error');
    }
}

// Render data based on current view
function renderDataView(viewType) {
    currentView = viewType;
    const content = document.getElementById('standardDataContent');
    
    // Update toggle buttons
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-view') === viewType) {
            btn.classList.add('active');
        }
    });
    
    if (viewType === 'table') {
        renderTableView(content);
    } else if (viewType === 'chart') {
        renderChartView(content);
    }
}

// Render table view
function renderTableView(container) {
    if (!currentData || !currentData.data || currentData.data.length === 0) {
        container.innerHTML = `
            <div class="no-data-message">
                <i class="fas fa-database"></i>
                <h4>No Data Available</h4>
                <p>No standard data found for this category.</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-modern">';
    html += '<thead><tr>';
    
    // Table headers
    Object.keys(currentData.data[0]).forEach(key => {
        html += `<th>${formatHeader(key)}</th>`;
    });
    html += '</tr></thead><tbody>';
    
    // Table rows (limit to 100 for performance)
    const displayData = currentData.data.slice(0, 100);
    displayData.forEach((row, index) => {
        html += `<tr class="data-row" data-index="${index}">`;
        Object.values(row).forEach(value => {
            html += `<td>${formatCellValue(value)}</td>`;
        });
        html += '</tr>';
    });
    
    if (currentData.data.length > 100) {
        html += `<tr class="summary-row">
            <td colspan="${Object.keys(currentData.data[0]).length}" class="text-center">
                <div class="data-limit-notice">
                    <i class="fas fa-info-circle"></i>
                    <strong>Showing first 100 of ${currentData.data.length} records</strong>
                    <small>Use search to filter specific data</small>
                </div>
            </td>
        </tr>`;
    }
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

// Render chart view (placeholder for future implementation)
function renderChartView(container) {
    container.innerHTML = `
        <div class="chart-placeholder">
            <div class="chart-icon">
                <i class="fas fa-chart-area"></i>
            </div>
            <h4>Chart View</h4>
            <p>Visual representation of WHO standard data</p>
            <div class="chart-coming-soon">
                <span class="badge">Coming Soon</span>
                <small>Chart visualization will be available in future updates</small>
            </div>
        </div>
    `;
}

// Format header text
function formatHeader(key) {
    return key.replace(/_/g, ' ')
              .replace(/\b\w/g, l => l.toUpperCase())
              .replace(/Id/g, 'ID');
}

// Format cell values
function formatCellValue(value) {
    if (typeof value === 'number') {
        return Number(value).toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        });
    }
    return value;
}

// Update record count
function updateRecordCount() {
    const countElement = document.getElementById('recordCount');
    if (currentData && currentData.data) {
        countElement.textContent = `${currentData.data.length} records`;
    } else {
        countElement.textContent = '0 records';
    }
}

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById('dataSearch');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterData(this.value);
        }, 300);
    });
}

// Filter data based on search term
function filterData(searchTerm) {
    if (!currentData || !currentData.data) return;
    
    const rows = document.querySelectorAll('.data-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = searchTerm === '' || text.includes(searchTerm.toLowerCase());
        
        if (matches) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update count
    const countElement = document.getElementById('recordCount');
    if (searchTerm === '') {
        countElement.textContent = `${currentData.data.length} records`;
    } else {
        countElement.textContent = `${visibleCount} of ${currentData.data.length} records`;
    }
}

// Export functionality
function exportData(format) {
    if (!currentData || !currentData.data) {
        showNotification('No data available to export', 'warning');
        return;
    }
    
    if (format === 'csv') {
        exportToCSV();
    } else if (format === 'json') {
        exportToJSON();
    }
}

// Export to CSV
function exportToCSV() {
    const data = currentData.data;
    if (data.length === 0) return;
    
    // Create CSV content
    const headers = Object.keys(data[0]);
    let csvContent = headers.join(',') + '\n';
    
    data.forEach(row => {
        const values = headers.map(header => {
            const value = row[header];
            return typeof value === 'string' ? `"${value}"` : value;
        });
        csvContent += values.join(',') + '\n';
    });
    
    // Download file
    downloadFile(csvContent, 'who-standards-data.csv', 'text/csv');
    showNotification('CSV file downloaded successfully', 'success');
}

// Export to JSON
function exportToJSON() {
    const jsonContent = JSON.stringify(currentData, null, 2);
    downloadFile(jsonContent, 'who-standards-data.json', 'application/json');
    showNotification('JSON file downloaded successfully', 'success');
}

// Helper function to download file
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

// Notification system (reuse from api-management.js)
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

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search when modal is shown
    document.getElementById('standardModal').addEventListener('shown.bs.modal', function() {
        initializeSearch();
    });
    
    // View toggle handlers
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            renderDataView(viewType);
        });
    });
    
    // Add hover effects to standard cards
    document.querySelectorAll('.standard-card-modern').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Add hover effects to knowledge cards
    document.querySelectorAll('.knowledge-card-modern').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.01)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    console.log('🧬 WHO Standards page initialized with modern functionality');
});
