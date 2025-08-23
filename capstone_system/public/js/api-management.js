async function checkApiStatus() {
    try {
        const response = await fetch(apiManagementStatusRoute);
        const data = await response.json();
        if (data.success) {
            alert('API Status: ' + data.data.status + '\nMessage: ' + (data.data.message || 'API is running normally'));
        } else {
            alert('API Check Failed: ' + data.error);
        }
    } catch (error) {
        alert('Error checking API status: ' + error.message);
    }
}

// Get route from a global variable or set it here
const apiManagementStatusRoute = document.getElementById('apiManagementStatusRoute')?.value || '';
