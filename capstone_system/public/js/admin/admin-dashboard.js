/**
 * Admin Dashboard JavaScript
 * Handles dashboard animations and interactions
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
