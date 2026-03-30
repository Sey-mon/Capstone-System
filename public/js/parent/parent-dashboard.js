/**
 * Parent Dashboard JavaScript
 * Modern UI/UX interactions and animations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dashboard features
    initCounterAnimations();
    initializeGrowthChart();
    initScrollAnimations();
    initTooltips();
});

/**
 * Animate counter numbers with smooth counting effect
 */
function initCounterAnimations() {
    const statValues = document.querySelectorAll('.stat-value');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                animateCounter(entry.target);
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);
    
    statValues.forEach(element => observer.observe(element));
}

/**
 * Counter animation helper
 */
function animateCounter(element) {
    const target = parseInt(element.textContent) || 0;
    const duration = 1500;
    const increment = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

/**
 * Initialize Growth Chart with Chart.js - Individual Child Focus
 */
function initializeGrowthChart() {
    const ctx = document.getElementById('growthChart');
    if (!ctx) return;

    const childrenData = window.childrenGrowthData || [];
    
    if (childrenData.length === 0) return;

    // Filter children with at least one assessment
    const childrenWithAssessments = childrenData.filter(item => 
        item.assessment_history && item.assessment_history.length > 0
    );

    if (childrenWithAssessments.length === 0) return;

    let currentChart = null;
    let currentChildIndex = 0;
    let currentView = 'combined'; // 'combined', 'weight', or 'height'
    
    // Color scheme for weight and height
    const weightColor = { 
        bg: 'rgba(59, 130, 246, 0.15)', 
        border: 'rgba(59, 130, 246, 1)',
        point: '#3b82f6'
    };
    const heightColor = { 
        bg: 'rgba(34, 197, 94, 0.15)', 
        border: 'rgba(34, 197, 94, 1)',
        point: '#22c55e'
    };
    
    function updateChartInsights(childData) {
        const insightsEl = document.getElementById('chartInsights');
        if (!insightsEl || !childData.assessment_history || childData.assessment_history.length === 0) return;
        
        const history = childData.assessment_history;
        const latest = history[history.length - 1];
        const previous = history.length > 1 ? history[history.length - 2] : null;
        
        let insights = [];
        
        // Total assessments
        insights.push(`
            <div class=\"insight-item\">
                <i class=\"fas fa-clipboard-check\"></i>
                <span><strong>${history.length}</strong> screening${history.length > 1 ? 's' : ''} recorded</span>
            </div>
        `);
        
        // Latest values
        insights.push(`
            <div class=\"insight-item\">
                <i class=\"fas fa-weight\"></i>
                <span>Current weight: <strong>${latest.weight} kg</strong></span>
            </div>
            <div class=\"insight-item\">
                <i class=\"fas fa-ruler-vertical\"></i>
                <span>Current height: <strong>${latest.height} cm</strong></span>
            </div>
        `);
        
        // Changes if there's previous data
        if (previous) {
            const weightChange = (latest.weight - previous.weight).toFixed(1);
            const heightChange = (latest.height - previous.height).toFixed(1);
            
            if (weightChange != 0) {
                const weightIcon = weightChange > 0 ? 'arrow-up' : 'arrow-down';
                const weightClass = weightChange > 0 ? 'positive' : 'negative';
                insights.push(`
                    <div class=\"insight-item ${weightClass}\">
                        <i class=\"fas fa-${weightIcon}\"></i>
                        <span>${weightChange > 0 ? '+' : ''}${weightChange} kg weight change</span>
                    </div>
                `);
            }
            
            if (heightChange != 0 && heightChange > 0) {
                insights.push(`
                    <div class=\"insight-item positive\">
                        <i class=\"fas fa-arrow-up\"></i>
                        <span>+${heightChange} cm growth</span>
                    </div>
                `);
            }
        }
        
        insightsEl.innerHTML = insights.join('');
    }
    
    function createChart(childIndex, viewType) {
        if (currentChart) {
            currentChart.destroy();
        }
        
        const childData = childrenWithAssessments[childIndex];
        if (!childData) return;
        
        const assessmentHistory = childData.assessment_history;
        if (!assessmentHistory || assessmentHistory.length === 0) return;
        
        // Update insights
        updateChartInsights(childData);
        
        // Prepare data
        const labels = assessmentHistory.map(a => a.date);
        const weightData = assessmentHistory.map(a => a.weight);
        const heightData = assessmentHistory.map(a => a.height);
        
        // Determine optimal point size and tick settings based on data volume
        const dataPointCount = assessmentHistory.length;
        const pointRadius = dataPointCount > 20 ? 4 : dataPointCount > 10 ? 5 : 6;
        const pointHoverRadius = pointRadius + 2;
        
        // Smart tick configuration for readability
        const maxTicksLimit = dataPointCount > 30 ? 10 : dataPointCount > 20 ? 12 : dataPointCount > 10 ? 15 : undefined;
        const labelRotation = dataPointCount > 15 ? 45 : dataPointCount > 8 ? 30 : 0;
        
        let datasets = [];
        let scales = {};
        
        if (viewType === 'combined') {
            // Dual-axis chart: weight and height together
            datasets = [
                {
                    label: 'Weight (kg)',
                    data: weightData,
                    borderColor: weightColor.border,
                    backgroundColor: weightColor.bg,
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointRadius: pointRadius,
                    pointHoverRadius: pointHoverRadius,
                    pointBackgroundColor: weightColor.point,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y-weight',
                },
                {
                    label: 'Height (cm)',
                    data: heightData,
                    borderColor: heightColor.border,
                    backgroundColor: heightColor.bg,
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointRadius: pointRadius,
                    pointHoverRadius: pointHoverRadius,
                    pointBackgroundColor: heightColor.point,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y-height',
                }
            ];
            
            scales = {
                'y-weight': {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: false,
                    grid: {
                        color: 'rgba(59, 130, 246, 0.1)',
                        drawBorder: false,
                    },
                    ticks: {
                        font: { size: 12, family: "'Inter', sans-serif" },
                        color: '#3b82f6',
                        padding: 10,
                        callback: function(value) {
                            return value.toFixed(1) + ' kg';
                        }
                    },
                    title: {
                        display: true,
                        text: 'Weight (kg)',
                        font: { size: 13, weight: '600', family: "'Inter', sans-serif" },
                        color: '#3b82f6',
                        padding: { top: 10, bottom: 10 }
                    }
                },
                'y-height': {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: false,
                    grid: {
                        display: false,
                    },
                    ticks: {
                        font: { size: 12, family: "'Inter', sans-serif" },
                        color: '#22c55e',
                        padding: 10,
                        callback: function(value) {
                            return value.toFixed(1) + ' cm';
                        }
                    },
                    title: {
                        display: true,
                        text: 'Height (cm)',
                        font: { size: 13, weight: '600', family: "'Inter', sans-serif" },
                        color: '#22c55e',
                        padding: { top: 10, bottom: 10 }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: { size: 11, family: "'Inter', sans-serif", weight: '500' },
                        color: '#64748b',
                        padding: 10,
                        maxRotation: labelRotation,
                        minRotation: labelRotation,
                        maxTicksLimit: maxTicksLimit,
                        autoSkip: true,
                        autoSkipPadding: 15
                    },
                    title: {
                        display: true,
                        text: 'Screening Date',
                        font: { size: 13, weight: '600', family: "'Inter', sans-serif" },
                        color: '#475569',
                        padding: { top: 10, bottom: 0 }
                    }
                }
            };
        } else {
            // Single metric view
            const isWeight = viewType === 'weight';
            const color = isWeight ? weightColor : heightColor;
            const data = isWeight ? weightData : heightData;
            
            datasets = [{
                label: isWeight ? 'Weight (kg)' : 'Height (cm)',
                data: data,
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointRadius: pointRadius,
                pointHoverRadius: pointHoverRadius,
                pointBackgroundColor: color.point,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }];
            
            scales = {
                y: {
                    beginAtZero: false,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.06)',
                        drawBorder: false,
                    },
                    ticks: {
                        font: { size: 12, family: "'Inter', sans-serif" },
                        color: '#64748b',
                        padding: 10,
                        callback: function(value) {
                            return value.toFixed(1) + (isWeight ? ' kg' : ' cm');
                        }
                    },
                    title: {
                        display: true,
                        text: isWeight ? 'Weight (kg)' : 'Height (cm)',
                        font: { size: 13, weight: '600', family: "'Inter', sans-serif" },
                        color: '#475569',
                        padding: { top: 10, bottom: 10 }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: { size: 11, family: "'Inter', sans-serif", weight: '500' },
                        color: '#64748b',
                        padding: 10,
                        maxRotation: labelRotation,
                        minRotation: labelRotation,
                        maxTicksLimit: maxTicksLimit,
                        autoSkip: true,
                        autoSkipPadding: 15
                    },
                    title: {
                        display: true,
                        text: 'Screening Date',
                        font: { size: 13, weight: '600', family: "'Inter', sans-serif" },
                        color: '#475569',
                        padding: { top: 10, bottom: 0 }
                    }
                }
            };
        }
        
        currentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 600,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            font: { size: 13, weight: '600', family: "'Inter', sans-serif" },
                            color: '#475569',
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 10,
                            boxHeight: 10
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.96)',
                        padding: 16,
                        borderRadius: 12,
                        titleFont: { size: 14, weight: '700', family: "'Inter', sans-serif" },
                        bodyFont: { size: 13, family: "'Inter', sans-serif" },
                        displayColors: true,
                        boxWidth: 12,
                        boxHeight: 12,
                        boxPadding: 8,
                        callbacks: {
                            title: function(context) {
                                return 'Screening: ' + context[0].label;
                            },
                            label: function(context) {
                                const value = context.parsed.y;
                                const label = context.dataset.label;
                                return label + ': ' + value.toFixed(1);
                            }
                        }
                    }
                },
                scales: scales,
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
    // Initialize with first child and combined view
    createChart(currentChildIndex, currentView);
    
    // Child selector dropdown
    const childSelector = document.getElementById('childSelector');
    if (childSelector) {
        childSelector.addEventListener('change', function() {
            currentChildIndex = parseInt(this.value);
            createChart(currentChildIndex, currentView);
        });
    }
    
    // View toggle buttons
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.classList.contains('active')) return;
            
            document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentView = this.dataset.view;
            createChart(currentChildIndex, currentView);
        });
    });
}

/**
 * Initialize scroll animations for cards
 */
function initScrollAnimations() {
    const cards = document.querySelectorAll('.growth-item, .activity-item-modern');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
}

/**
 * Initialize tooltips for better UX
 */
function initTooltips() {
    // Add subtle hover effects
    const hoverElements = document.querySelectorAll('.metric-card-small, .nutrition-status, .growth-trend');
    
    hoverElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}
