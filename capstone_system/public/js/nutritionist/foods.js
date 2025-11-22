// Nutritionist Foods JavaScript

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        if (successAlert) successAlert.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';
    }, 5000);
});

// Debounced search functionality
let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const search = e.target.value;
    const tag = document.getElementById('tagFilter')?.value || '';
    
    searchTimeout = setTimeout(() => {
        updateUrl(search, tag);
    }, 500); // Wait 500ms after user stops typing
});

document.getElementById('tagFilter')?.addEventListener('change', function(e) {
    const tag = e.target.value;
    const search = document.getElementById('searchInput')?.value || '';
    updateUrl(search, tag);
});

function updateUrl(search, tag) {
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (tag) params.set('tag', tag);
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

// View food details function
function viewFoodDetails(foodId) {
    const modal = document.getElementById('viewFoodModal');
    const content = document.getElementById('foodDetailsContent');
    
    modal.style.display = 'block';
    content.innerHTML = '<p style="text-align:center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
    
    fetch(`/admin/foods/${foodId}`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = `
                <div class="detail-row">
                    <strong>Food ID:</strong>
                    <span>${data.food_id}</span>
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
                    <strong>Created:</strong>
                    <span>${data.created_at ? new Date(data.created_at).toLocaleDateString() : '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Last Updated:</strong>
                    <span>${data.updated_at ? new Date(data.updated_at).toLocaleDateString() : '-'}</span>
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = '<p style="text-align:center; padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Error loading food details</p>';
            console.error('Error:', error);
        });
}

function closeViewFoodModal() {
    document.getElementById('viewFoodModal').style.display = 'none';
}

// Request Food using SweetAlert2
function openRequestFoodModal() {
    Swal.fire({
        title: '<span style="color: #2e7d32;">Request New Food</span>',
        html: `
            <form id="requestFoodForm" style="text-align: left;">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2e7d32;">
                        Food Name & Description <span style="color: #dc3545;">*</span>
                    </label>
                    <textarea 
                        id="food_name_and_description" 
                        rows="3" 
                        placeholder="Enter detailed food name and description"
                        style="width: 100%; padding: 8px; border: 2px solid #e8f5e9; border-radius: 8px; font-size: 14px; font-family: inherit;"
                        required
                    ></textarea>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2e7d32;">
                        Alternate Names
                    </label>
                    <input 
                        type="text" 
                        id="alternate_common_names" 
                        placeholder="Other common names (comma-separated)"
                        style="width: 100%; padding: 8px; border: 2px solid #e8f5e9; border-radius: 8px; font-size: 14px;"
                    />
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2e7d32;">
                        Energy (kcal)
                    </label>
                    <input 
                        type="number" 
                        id="energy_kcal" 
                        step="0.1" 
                        placeholder="Caloric content per serving"
                        style="width: 100%; padding: 8px; border: 2px solid #e8f5e9; border-radius: 8px; font-size: 14px;"
                    />
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2e7d32;">
                        Nutrition Tags
                    </label>
                    <input 
                        type="text" 
                        id="nutrition_tags" 
                        placeholder="e.g., high-protein, low-fat (comma-separated)"
                        style="width: 100%; padding: 8px; border: 2px solid #e8f5e9; border-radius: 8px; font-size: 14px;"
                    />
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane"></i> Submit Request',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#4caf50',
        cancelButtonColor: '#6c757d',
        width: '600px',
        focusConfirm: false,
        preConfirm: () => {
            const foodName = document.getElementById('food_name_and_description').value;
            const alternateName = document.getElementById('alternate_common_names').value;
            const energy = document.getElementById('energy_kcal').value;
            const tags = document.getElementById('nutrition_tags').value;

            if (!foodName.trim()) {
                Swal.showValidationMessage('Please enter food name and description');
                return false;
            }

            return {
                food_name_and_description: foodName,
                alternate_common_names: alternateName,
                energy_kcal: energy,
                nutrition_tags: tags
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitFoodRequest(result.value);
        }
    });
}

// Submit food request
function submitFoodRequest(data) {
    Swal.fire({
        title: 'Submitting...',
        html: 'Please wait while we submit your request',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData();
    formData.append('food_name_and_description', data.food_name_and_description);
    formData.append('alternate_common_names', data.alternate_common_names);
    formData.append('energy_kcal', data.energy_kcal);
    formData.append('nutrition_tags', data.nutrition_tags);

    fetch('/nutritionist/food-requests', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '<span style="color: #2e7d32;">Request Submitted!</span>',
                html: data.message,
                confirmButtonColor: '#4caf50',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: data.message || 'Failed to submit request',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            html: 'An error occurred while submitting your request',
            confirmButtonColor: '#dc3545'
        });
    });
}

// View request details
function viewRequestDetails(requestId) {
    const modal = document.getElementById('viewRequestModal');
    const content = document.getElementById('requestDetailsContent');
    
    modal.style.display = 'block';
    content.innerHTML = '<p style="text-align:center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
    
    fetch(`/nutritionist/food-requests/${requestId}`)
        .then(response => response.json())
        .then(data => {
            const statusColors = {
                pending: '#ffc107',
                approved: '#4caf50',
                rejected: '#dc3545'
            };
            
            content.innerHTML = `
                <div class="detail-row">
                    <strong>Request ID:</strong>
                    <span>#${data.id}</span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong>
                    <span style="color: ${statusColors[data.status]}; font-weight: bold;">${data.status.toUpperCase()}</span>
                </div>
                <div class="detail-row">
                    <strong>Food Name & Description:</strong>
                    <span>${data.food_name_and_description}</span>
                </div>
                <div class="detail-row">
                    <strong>Alternate Names:</strong>
                    <span>${data.alternate_common_names || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Energy (kcal):</strong>
                    <span>${data.energy_kcal || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Nutrition Tags:</strong>
                    <span>${data.nutrition_tags || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Requested On:</strong>
                    <span>${new Date(data.created_at).toLocaleDateString()}</span>
                </div>
                ${data.admin_notes ? `
                    <div class="detail-row">
                        <strong>Admin Notes:</strong>
                        <span>${data.admin_notes}</span>
                    </div>
                ` : ''}
                ${data.reviewed_at ? `
                    <div class="detail-row">
                        <strong>Reviewed On:</strong>
                        <span>${new Date(data.reviewed_at).toLocaleDateString()}</span>
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

// Cancel request
function cancelRequest(requestId) {
    Swal.fire({
        title: 'Cancel Request?',
        text: "Are you sure you want to cancel this food request?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/nutritionist/food-requests/${requestId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelled!',
                        text: 'Your request has been cancelled.',
                        confirmButtonColor: '#4caf50'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to cancel request',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const viewFoodModal = document.getElementById('viewFoodModal');
    const viewRequestModal = document.getElementById('viewRequestModal');
    
    if (event.target == viewFoodModal) {
        closeViewFoodModal();
    }
    if (event.target == viewRequestModal) {
        closeViewRequestModal();
    }
}
