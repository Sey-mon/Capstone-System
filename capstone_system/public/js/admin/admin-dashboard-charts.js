/**
 * Admin Dashboard Charts JavaScript
 * Handles chart initialization, AJAX updates, and collapsible sections
 */

// Chart instances
let screeningTrendsChart;
let nutritionalStatusChart;
let inventoryCategoryChart;
let lowStockChart;

// Flatpickr instance
let dateRangePicker;

// Color scheme from config
const colors = window.dashboardData?.colors || {
    sam: '#ef4444',
    mam: '#f59e0b',
    normal: '#3b82f6',
    critical: '#ef4444',
    warning: '#f59e0b',
    low: '#eab308',
    success: '#10b981'
};

/**
 * Initialize all charts on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeCollapsibleSections();
    initializeCharts();
    initializeDateRangePicker();
    initializeMapFilters();
});

/**
 * Initialize collapsible sections with localStorage persistence
 */
function initializeCollapsibleSections() {
    const headers = document.querySelectorAll('.collapsible-header');
    
    headers.forEach(header => {
        const sectionName = header.dataset.section;
        const sectionContent = header.nextElementSibling;
        
        // Restore state from localStorage
        const isCollapsed = localStorage.getItem(`dashboard-${sectionName}-collapsed`) === 'true';
        
        // On mobile, default to collapsed if no saved state
        const isMobile = window.innerWidth <= 768;
        if (isMobile && localStorage.getItem(`dashboard-${sectionName}-collapsed`) === null) {
            header.parentElement.classList.add('section-collapsed');
        } else if (isCollapsed) {
            header.parentElement.classList.add('section-collapsed');
        }
        
        // Click handler to toggle
        header.addEventListener('click', function() {
            const section = this.parentElement;
            const isNowCollapsed = section.classList.toggle('section-collapsed');
            
            // Save state to localStorage
            localStorage.setItem(`dashboard-${sectionName}-collapsed`, isNowCollapsed);
        });
    });
}

/**
 * Initialize all charts
 */
function initializeCharts() {
    initScreeningTrendsChart();
    initNutritionalStatusChart();
    initInventoryCategoryChart();
    initLowStockChart();
}

/**
 * Initialize Screening Trends Line Chart
 */
function initScreeningTrendsChart() {
    const ctx = document.getElementById('screeningTrendsChart');
    if (!ctx) return;
    
    const trends = window.dashboardData?.screening_trends || [];
    const labels = trends.map(t => t.month);
    const data = trends.map(t => t.count);
    
    screeningTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Screenings',
                data: data,
                borderColor: colors.normal,
                backgroundColor: colors.normal + '20',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

/**
 * Initialize Nutritional Status Doughnut Chart
 */
function initNutritionalStatusChart() {
    const ctx = document.getElementById('nutritionalStatusChart');
    if (!ctx) return;
    
    const status = window.dashboardData?.nutritional_status || {};
    
    nutritionalStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['SAM', 'MAM', 'Normal'],
            datasets: [{
                data: [status.sam || 0, status.mam || 0, status.normal || 0],
                backgroundColor: [colors.sam, colors.mam, colors.normal],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 14 },
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return percentage + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

/**
 * Initialize Inventory by Category Bar Chart
 */
function initInventoryCategoryChart() {
    const ctx = document.getElementById('inventoryCategoryChart');
    if (!ctx) return;
    
    const inventory = window.dashboardData?.inventory_by_category || [];
    const labels = inventory.map(i => i.category);
    const data = inventory.map(i => i.count);
    
    inventoryCategoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Items',
                data: data,
                backgroundColor: colors.normal,
                borderColor: colors.normal,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

/**
 * Initialize Low Stock Horizontal Bar Chart
 */
function initLowStockChart() {
    const ctx = document.getElementById('lowStockChart');
    if (!ctx) return;
    
    // Fetch low stock data via AJAX
    fetchChartData('low-stock-alerts').then(items => {
        if (!items || items.length === 0) {
            ctx.parentElement.innerHTML = '<div class="success-state"><i class="fas fa-check-circle"></i><span>All items well stocked!</span></div>';
            return;
        }
        
        const topItems = items.slice(0, 10);
        const labels = topItems.map(i => i.name);
        const data = topItems.map(i => i.quantity);
        const backgroundColors = topItems.map(i => {
            if (i.severity === 'critical') return colors.critical;
            if (i.severity === 'warning') return colors.warning;
            return colors.low;
        });
        
        lowStockChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Quantity',
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
}

/**
 * Initialize date range picker
 */
function initializeDateRangePicker() {
    const input = document.getElementById('dateRangePicker');
    if (!input) return;
    
    dateRangePicker = flatpickr(input, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        maxDate: 'today',
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                updateScreeningTrendsChart(selectedDates[0], selectedDates[1]);
            }
        }
    });
    
    // Preset button handlers
    const presetButtons = document.querySelectorAll('.preset-btn');
    presetButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active from all
            presetButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const period = this.dataset.period;
            
            if (period === 'custom') {
                input.style.display = 'block';
                dateRangePicker.open();
            } else {
                input.style.display = 'none';
                const days = parseInt(period);
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(startDate.getDate() - days);
                
                updateScreeningTrendsChart(startDate, endDate);
            }
        });
    });
}

/**
 * Update screening trends chart with new date range
 */
function updateScreeningTrendsChart(startDate, endDate) {
    const chartCard = document.querySelector('#screeningTrendsChart').closest('.chart-card');
    const loading = chartCard.querySelector('.chart-loading');
    
    // Show loading
    if (loading) loading.style.display = 'flex';
    
    const start = formatDate(startDate);
    const end = formatDate(endDate);
    
    fetchChartData('screening-trends', { start_date: start, end_date: end })
        .then(trends => {
            if (trends && screeningTrendsChart) {
                const labels = trends.map(t => t.month);
                const data = trends.map(t => t.count);
                
                screeningTrendsChart.data.labels = labels;
                screeningTrendsChart.data.datasets[0].data = data;
                screeningTrendsChart.update('none');
            }
        })
        .catch(error => {
            showToast('Failed to load chart data', 'error');
            console.error('Chart update error:', error);
        })
        .finally(() => {
            if (loading) loading.style.display = 'none';
        });
}

/**
 * Fetch chart data via AJAX
 */
function fetchChartData(type, params = {}) {
    const url = window.dashboardData?.chart_data_url?.replace('__TYPE__', type) || '';
    if (!url) return Promise.reject('Chart data URL not configured');
    
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;
    
    return fetch(fullUrl)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(result => {
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to load data');
            }
        });
}

/**
 * Initialize map filter buttons
 */
function initializeMapFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const searchInput = document.getElementById('barangaySearch');
    
    // Filter buttons
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            filterMapMarkers(filter);
        });
    });
    
    // Search input
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchBarangays(this.value);
        });
    }
}

/**
 * Filter map markers by severity
 */
function filterMapMarkers(filter) {
    // This will be handled by the existing admin-dashboard.js
    // Dispatch custom event
    window.dispatchEvent(new CustomEvent('filterMapMarkers', { detail: filter }));
}

/**
 * Search barangays on map
 */
function searchBarangays(query) {
    // Dispatch custom event for map search
    window.dispatchEvent(new CustomEvent('searchBarangays', { detail: query }));
}

/**
 * Format date to Y-m-d
 */
function formatDate(date) {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        font-size: 14px;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
