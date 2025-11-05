// Nutritionist Food Requests JavaScript

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        if (successAlert) successAlert.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';
    }, 5000);
});

// View request details function
function viewRequestDetails(requestId) {
    const modal = document.getElementById('viewRequestModal');
    const content = document.getElementById('requestDetailsContent');
    
    modal.style.display = 'block';
    content.innerHTML = '<p style="text-align:center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
    
    fetch(`/nutritionist/food-requests/${requestId}`)
        .then(response => response.json())
        .then(data => {
            let statusBadge = '';
            if (data.status === 'pending') {
                statusBadge = '<span class="badge badge-pending">Pending</span>';
            } else if (data.status === 'approved') {
                statusBadge = '<span class="badge badge-approved">Approved</span>';
            } else {
                statusBadge = '<span class="badge badge-rejected">Rejected</span>';
            }
            
            content.innerHTML = `
                <div class="detail-row">
                    <strong>Request ID:</strong>
                    <span>#${data.id}</span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong>
                    <span>${statusBadge}</span>
                </div>
                <div class="detail-row">
                    <strong>Food Name & Description:</strong>
                    <span>${data.food_name_and_description || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Alternate Names:</strong>
                    <span>${data.alternate_common_names || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Energy (kcal):</strong>
                    <span>${data.energy_kcal ? parseFloat(data.energy_kcal).toFixed(1) : '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Nutrition Tags:</strong>
                    <span>${data.nutrition_tags || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Requested On:</strong>
                    <span>${data.created_at ? new Date(data.created_at).toLocaleDateString() : '-'}</span>
                </div>
                ${data.reviewed_at ? `
                <div class="detail-row">
                    <strong>Reviewed On:</strong>
                    <span>${new Date(data.reviewed_at).toLocaleDateString()}</span>
                </div>
                ` : ''}
                ${data.reviewer ? `
                <div class="detail-row">
                    <strong>Reviewed By:</strong>
                    <span>${data.reviewer.first_name} ${data.reviewer.last_name}</span>
                </div>
                ` : ''}
                ${data.admin_notes ? `
                <div class="detail-row">
                    <strong>Admin Notes:</strong>
                    <span style="white-space: pre-wrap;">${data.admin_notes}</span>
                </div>
                ` : ''}
            `;
        })
        .catch(error => {
            content.innerHTML = '<p style="text-align:center; padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Error loading request details</p>';
            console.error('Error:', error);
        });
}

function closeViewRequestModal() {
    document.getElementById('viewRequestModal').style.display = 'none';
}

// Confirm delete function
function confirmDelete() {
    return confirm('Are you sure you want to cancel this request? This action cannot be undone.');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const viewModal = document.getElementById('viewRequestModal');
    
    if (event.target == viewModal) {
        closeViewRequestModal();
    }
}
