/**
 * Nutritionist Dashboard JavaScript
 * Handles dashboard animations, interactions, and chart rendering
 */

document.addEventListener('DOMContentLoaded', function() {
    // Animate stat numbers when page loads
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(element => {
        const target = parseInt(element.textContent);
        if (window.DashboardUtils && window.DashboardUtils.animateCounter) {
            window.DashboardUtils.animateCounter(element, target, 1500);
        }
    });
});

// Chart.js Global Configuration
Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.color = '#4a5568';

// Register datalabels plugin but disable by default
Chart.register(ChartDataLabels);
Chart.defaults.set('plugins.datalabels', {
    display: false
});

// Assessment Trends Chart (Line Chart)
const trendsCtx = document.getElementById('assessmentTrendsChart').getContext('2d');
new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: window.assessmentTrendsData.map(d => {
            const date = new Date(d.month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        }),
        datasets: [{
            label: 'Total Screenings',
            data: window.assessmentTrendsData.map(d => d.count),
            borderColor: '#4299e1',
            backgroundColor: 'rgba(66, 153, 225, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Completed',
            data: window.assessmentTrendsData.map(d => d.completed),
            borderColor: '#48bb78',
            backgroundColor: 'rgba(72, 187, 120, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Pending',
            data: window.assessmentTrendsData.map(d => d.pending),
            borderColor: '#ed8936',
            backgroundColor: 'rgba(237, 137, 54, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
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

// Gender Distribution Chart (Doughnut Chart)
const genderCtx = document.getElementById('genderChart').getContext('2d');
new Chart(genderCtx, {
    type: 'doughnut',
    data: {
        labels: window.genderData.map(d => d.sex === 'Male' || d.sex === 'M' ? 'Male' : 'Female'),
        datasets: [{
            data: window.genderData.map(d => d.count),
            backgroundColor: [
                'rgba(66, 153, 225, 0.8)',
                'rgba(237, 100, 166, 0.8)'
            ],
            borderColor: [
                'rgba(66, 153, 225, 1)',
                'rgba(237, 100, 166, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            },
            datalabels: {
                display: true,
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 14
                },
                formatter: (value, ctx) => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return percentage + '%';
                }
            }
        }
    }
});

// Age Distribution Chart (Bar Chart)
const ageCtx = document.getElementById('ageChart').getContext('2d');
new Chart(ageCtx, {
    type: 'bar',
    data: {
        labels: window.ageData.map(d => d.age_years + (d.age_years === 1 ? ' year' : ' years')),
        datasets: [{
            label: 'Number of Patients',
            data: window.ageData.map(d => d.count),
            backgroundColor: 'rgba(72, 187, 120, 0.7)',
            borderColor: 'rgba(72, 187, 120, 1)',
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
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

// Nutritional Status Chart (Doughnut Chart)
const statusColors = {
    'Severe Wasting': '#dc2626',
    'Wasting': '#ef4444',
    'Underweight': '#3b82f6',
    'Normal': '#48bb78',
    'Possible Risk of Overweight': '#ecc94b',
    'Overweight': '#ed8936',
    'Obese': '#dc2626'
};

const nutritionalCtx = document.getElementById('nutritionalStatusChart').getContext('2d');
new Chart(nutritionalCtx, {
    type: 'doughnut',
    data: {
        labels: window.nutritionalData.map(d => d.bmi_for_age || 'Not Assessed'),
        datasets: [{
            data: window.nutritionalData.map(d => d.count),
            backgroundColor: window.nutritionalData.map(d => statusColors[d.bmi_for_age] || '#cbd5e0'),
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 10,
                    font: {
                        size: 11
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            },
            datalabels: {
                display: true,
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 12
                },
                formatter: (value, ctx) => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return percentage + '%';
                }
            }
        }
    }
});
