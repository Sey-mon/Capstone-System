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
 * Initialize Growth Chart with Chart.js
 */
function initializeGrowthChart() {
    const ctx = document.getElementById('growthChart');
    if (!ctx) return;

    const childrenData = window.childrenGrowthData || [];
    
    if (childrenData.length === 0) return;

    // Prepare chart data
    const chartData = childrenData.map(item => ({
        name: item.child.first_name + ' ' + item.child.last_name,
        weight: parseFloat(item.child.weight_kg) || 0,
        height: parseFloat(item.child.height_cm) || 0,
        assessments: item.assessments_count || 0
    }));

    let currentChart = null;
    
    function createChart(type) {
        const labels = chartData.map(child => child.name);
        const data = chartData.map(child => type === 'weight' ? child.weight : child.height);
        
        if (currentChart) {
            currentChart.destroy();
        }
        
        const isWeight = type === 'weight';
        
        // Calculate dynamic max value for Y-axis
        const maxValue = Math.max(...data);
        const minValue = Math.min(...data);
        
        // Add 20% padding to the max value for better visualization
        const yAxisMax = Math.ceil(maxValue * 1.2);
        
        // Calculate nice step size for better readability
        const range = yAxisMax;
        let stepSize;
        if (isWeight) {
            stepSize = range <= 20 ? 2 : range <= 50 ? 5 : 10;
        } else {
            stepSize = range <= 100 ? 10 : range <= 200 ? 20 : 25;
        }
        
        currentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: isWeight ? 'Weight (kg)' : 'Height (cm)',
                    data: data,
                    backgroundColor: isWeight 
                        ? 'rgba(59, 130, 246, 0.85)'
                        : 'rgba(139, 92, 246, 0.85)',
                    borderColor: isWeight
                        ? 'rgba(59, 130, 246, 1)'
                        : 'rgba(139, 92, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 10,
                    borderSkipped: false,
                    hoverBackgroundColor: isWeight
                        ? 'rgba(59, 130, 246, 1)'
                        : 'rgba(139, 92, 246, 1)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 800,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.95)',
                        padding: 14,
                        borderRadius: 10,
                        titleFont: {
                            size: 15,
                            weight: '700',
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            family: "'Inter', sans-serif"
                        },
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                const unit = isWeight ? 'kg' : 'cm';
                                return `${value.toFixed(1)} ${unit}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: yAxisMax,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.06)',
                            drawBorder: false,
                            lineWidth: 1
                        },
                        ticks: {
                            stepSize: stepSize,
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif"
                            },
                            color: '#64748b',
                            padding: 10,
                            callback: function(value) {
                                return value + (isWeight ? ' kg' : ' cm');
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif",
                                weight: '600'
                            },
                            color: '#475569',
                            padding: 10
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
    // Initialize with weight chart
    createChart('weight');
    
    // Chart toggle buttons with smooth transitions
    document.querySelectorAll('.chart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.classList.contains('active')) return;
            
            document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            createChart(this.dataset.chart);
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
