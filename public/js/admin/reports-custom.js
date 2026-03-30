// Custom JS for admin reports modal and actions
function closeReportModalOld() {
    // This function is deprecated - use modal.js closeReportModal instead
    document.getElementById('reportModal').style.display = 'none';
}

// Close modal when clicking outside  
window.onclick = function(event) {
    const modal = document.getElementById('reportModal');
    if (event.target === modal) {
        // Use the enhanced close function from modal.js
        if (typeof closeReportModal === 'function') {
            closeReportModal();
        } else {
            closeReportModalOld();
        }
    }
}

function updateChartPeriod(period) {
    // Implement chart update logic here
    console.log('Chart period changed to: ' + period);
    // Add your chart update logic here
}
