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

    // Filter children with at least one assessment
    const childrenWithAssessments = childrenData.filter(item => 
        item.assessment_history && item.assessment_history.length > 0
    );

    if (childrenWithAssessments.length === 0) return;

    let currentChart = null;
    
    // Generate distinct colors for each child
    const colors = [
        { bg: 'rgba(59, 130, 246, 0.2)', border: 'rgba(59, 130, 246, 1)' },      // Blue
        { bg: 'rgba(139, 92, 246, 0.2)', border: 'rgba(139, 92, 246, 1)' },      // Purple
        { bg: 'rgba(236, 72, 153, 0.2)', border: 'rgba(236, 72, 153, 1)' },      // Pink
        { bg: 'rgba(249, 115, 22, 0.2)', border: 'rgba(249, 115, 22, 1)' },      // Orange
        { bg: 'rgba(34, 197, 94, 0.2)', border: 'rgba(34, 197, 94, 1)' },        // Green
        { bg: 'rgba(14, 165, 233, 0.2)', border: 'rgba(14, 165, 233, 1)' },      // Sky
        { bg: 'rgba(168, 85, 247, 0.2)', border: 'rgba(168, 85, 247, 1)' },      // Violet
        { bg: 'rgba(251, 146, 60, 0.2)', border: 'rgba(251, 146, 60, 1)' },      // Amber
    ];
    
    function createChart(type) {
        if (currentChart) {
            currentChart.destroy();
        }
        
        const isWeight = type === 'weight';
        
        // Create datasets for each child
        const datasets = childrenWithAssessments.map((item, index) => {
            const color = colors[index % colors.length];
            const childName = item.child.first_name + ' ' + item.child.last_name;
            
            return {
                label: childName,
                data: item.assessment_history.map(assessment => ({
                    x: assessment.date,
                    y: isWeight ? assessment.weight : assessment.height
                })),
                borderColor: color.border,
                backgroundColor: color.bg,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: color.border,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: color.border,
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3,
            };
        });
        
        // Get all unique dates across all children
        const allDates = [...new Set(
            childrenWithAssessments.flatMap(item => 
                item.assessment_history.map(a => a.date)
            )
        )].sort((a, b) => new Date(a) - new Date(b));
        
        currentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: allDates,
                datasets: datasets
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
                        display: true,
                        position: 'top',
                        align: 'start',
                        labels: {
                            font: {
                                size: 13,
                                weight: '600',
                                family: "'Inter', sans-serif"
                            },
                            color: '#475569',
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            boxHeight: 8
                        }
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
                        displayColors: true,
                        boxWidth: 10,
                        boxHeight: 10,
                        boxPadding: 6,
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                const unit = isWeight ? 'kg' : 'cm';
                                const childName = context.dataset.label;
                                return `${childName}: ${value.toFixed(1)} ${unit}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.06)',
                            drawBorder: false,
                            lineWidth: 1
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif"
                            },
                            color: '#64748b',
                            padding: 10,
                            callback: function(value) {
                                return value.toFixed(1) + (isWeight ? ' kg' : ' cm');
                            }
                        },
                        title: {
                            display: true,
                            text: isWeight ? 'Weight (kg)' : 'Height (cm)',
                            font: {
                                size: 13,
                                weight: '600',
                                family: "'Inter', sans-serif"
                            },
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
                            font: {
                                size: 11,
                                family: "'Inter', sans-serif",
                                weight: '500'
                            },
                            color: '#64748b',
                            padding: 10,
                            maxRotation: 45,
                            minRotation: 45
                        },
                        title: {
                            display: true,
                            text: 'Assessment Date',
                            font: {
                                size: 13,
                                weight: '600',
                                family: "'Inter', sans-serif"
                            },
                            color: '#475569',
                            padding: { top: 10, bottom: 0 }
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
