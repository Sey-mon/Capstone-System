// Admin Food Requests JavaScript

// View request details
function viewRequestDetails(id) {
    fetch(`/admin/food-requests/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        const request = data.request;
        let html = `
            <div class="detail-row">
                <strong>Request ID:</strong> #${request.id}
            </div>
            <div class="detail-row">
                <strong>Requested By:</strong> ${request.requester.first_name} ${request.requester.last_name}
            </div>
            <div class="detail-row">
                <strong>Status:</strong> <span class="badge badge-${request.status}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span>
            </div>
            <div class="detail-row">
                <strong>Food Name:</strong> ${request.food_name_and_description}
            </div>
            <div class="detail-row">
                <strong>Alternate Names:</strong> ${request.alternate_common_names || 'N/A'}
            </div>
            <div class="detail-row">
                <strong>Energy (kcal):</strong> ${request.energy_kcal ? parseFloat(request.energy_kcal).toFixed(1) : 'N/A'}
            </div>
            <div class="detail-row">
                <strong>Nutrition Tags:</strong> ${request.nutrition_tags || 'N/A'}
            </div>
            <div class="detail-row">
                <strong>Requested Date:</strong> ${new Date(request.created_at).toLocaleDateString()}
            </div>
        `;
        
        if (request.admin_notes) {
            html += `
                <div class="detail-row">
                    <strong>Admin Notes:</strong> ${request.admin_notes}
                </div>
            `;
        }
        
        if (request.reviewer) {
            html += `
                <div class="detail-row">
                    <strong>Reviewed By:</strong> ${request.reviewer.first_name} ${request.reviewer.last_name}
                </div>
                <div class="detail-row">
                    <strong>Reviewed Date:</strong> ${new Date(request.reviewed_at).toLocaleDateString()}
                </div>
            `;
        }
        
        document.getElementById('viewContent').innerHTML = html;
        document.getElementById('viewModal').style.display = 'block';
    })
    .catch(error => {
        alert('Error loading request details: ' + error.message);
        console.error('Error:', error);
    });
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Reject request
function rejectRequest(id) {
    document.getElementById('rejectForm').action = `/admin/food-requests/${id}/reject`;
    document.getElementById('rejectModal').style.display = 'block';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const viewModal = document.getElementById('viewModal');
    const rejectModal = document.getElementById('rejectModal');
    
    if (event.target == viewModal) {
        closeViewModal();
    }
    if (event.target == rejectModal) {
        closeRejectModal();
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

