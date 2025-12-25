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
    
    modal.style.display = 'flex';
    content.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading food details...</p>
        </div>
    `;
    
    fetch(`/admin/foods/${foodId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch food details');
            }
            return response.json();
        })
        .then(data => {
            content.innerHTML = `
                <div class="modal-details">
                    <div class="detail-section">
                        <div class="detail-header">
                            <i class="fas fa-info-circle"></i>
                            <h3>Basic Information</h3>
                        </div>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label><i class="fas fa-hashtag"></i> Food ID</label>
                                <span class="value">${data.food_id}</span>
                            </div>
                            <div class="detail-item full-width">
                                <label><i class="fas fa-utensils"></i> Food Name & Description</label>
                                <span class="value">${data.food_name_and_description || '-'}</span>
                            </div>
                            <div class="detail-item full-width">
                                <label><i class="fas fa-tag"></i> Alternate Names</label>
                                <span class="value">${data.alternate_common_names || 'None'}</span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-header">
                            <i class="fas fa-fire"></i>
                            <h3>Nutritional Information</h3>
                        </div>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label><i class="fas fa-bolt"></i> Energy</label>
                                <span class="value highlight">${data.energy_kcal ? parseFloat(data.energy_kcal).toFixed(1) + ' kcal' : '-'}</span>
                            </div>
                            <div class="detail-item full-width">
                                <label><i class="fas fa-tags"></i> Nutrition Tags</label>
                                <span class="value tags">${formatTags(data.nutrition_tags)}</span>
                            </div>
                        </div>
                    </div>

                    ${data.created_at || data.updated_at ? `
                    <div class="detail-section">
                        <div class="detail-header">
                            <i class="fas fa-clock"></i>
                            <h3>Record Information</h3>
                        </div>
                        <div class="detail-grid">
                            ${data.created_at ? `
                            <div class="detail-item">
                                <label><i class="fas fa-calendar-plus"></i> Created</label>
                                <span class="value">${new Date(data.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                            </div>
                            ` : ''}
                            ${data.updated_at ? `
                            <div class="detail-item">
                                <label><i class="fas fa-calendar-check"></i> Last Updated</label>
                                <span class="value">${new Date(data.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Unable to Load Food Details</h3>
                    <p>An error occurred while fetching the food information. Please try again.</p>
                    <button onclick="viewFoodDetails(${foodId})" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                </div>
            `;
            console.error('Error:', error);
        });
}

function formatTags(tags) {
    if (!tags) return '<span class="no-tags">No tags</span>';
    const tagArray = tags.split(',').map(tag => tag.trim()).filter(tag => tag);
    if (tagArray.length === 0) return '<span class="no-tags">No tags</span>';
    return tagArray.map(tag => `<span class="tag-pill">${tag}</span>`).join('');
}

function closeViewFoodModal() {
    document.getElementById('viewFoodModal').style.display = 'none';
}

// Request Food using SweetAlert2 with duplicate validation
function openRequestFoodModal() {
    Swal.fire({
        title: '<span style="color: #2e7d32;">Request New Food</span>',
        html: `
            <form id="requestFoodForm" style="text-align: left;">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2e7d32;">
                        Food Name & Description <span style="color: #dc3545;">*</span>
                    </label>
                    <textarea 
                        id="food_name_and_description" 
                        rows="3" 
                        placeholder="Enter detailed food name and description"
                        style="width: 100%; padding: 10px; border: 2px solid #e8f5e9; border-radius: 10px; font-size: 14px; font-family: inherit; transition: all 0.3s;"
                        required
                    ></textarea>
                    <small id="food_name_validation" style="color: #dc3545; font-size: 12px; display: none; margin-top: 4px;">
                        <i class="fas fa-exclamation-triangle"></i> This food may already exist in the database
                    </small>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2e7d32;">
                        Alternate Names
                    </label>
                    <input 
                        type="text" 
                        id="alternate_common_names" 
                        placeholder="Other common names (comma-separated)"
                        style="width: 100%; padding: 10px; border: 2px solid #e8f5e9; border-radius: 10px; font-size: 14px; transition: all 0.3s;"
                    />
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2e7d32;">
                        Energy (kcal) <span style="color: #dc3545;">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="energy_kcal" 
                        step="0.1" 
                        min="0"
                        placeholder="Caloric content per serving"
                        style="width: 100%; padding: 10px; border: 2px solid #e8f5e9; border-radius: 10px; font-size: 14px; transition: all 0.3s;"
                        required
                    />
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2e7d32;">
                        Nutrition Tags
                    </label>
                    <input 
                        type="text" 
                        id="nutrition_tags" 
                        placeholder="e.g., high-protein, low-fat (comma-separated)"
                        style="width: 100%; padding: 10px; border: 2px solid #e8f5e9; border-radius: 10px; font-size: 14px; transition: all 0.3s;"
                    />
                </div>
                
                <div style="background: #e8f5e9; padding: 12px; border-radius: 8px; margin-top: 15px;">
                    <small style="color: #2e7d32; display: flex; align-items: flex-start; gap: 8px; line-height: 1.5;">
                        <i class="fas fa-info-circle" style="margin-top: 2px;"></i>
                        <span>All fields marked with <strong>*</strong> are required. Your request will be reviewed by an admin before being added to the database.</span>
                    </small>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane"></i> Submit Request',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        confirmButtonColor: '#4caf50',
        cancelButtonColor: '#6c757d',
        width: '650px',
        focusConfirm: false,
        didOpen: () => {
            // Add real-time duplicate checking
            const foodNameInput = document.getElementById('food_name_and_description');
            const validationMsg = document.getElementById('food_name_validation');
            let checkTimeout;

            foodNameInput.addEventListener('input', function() {
                clearTimeout(checkTimeout);
                const value = this.value.trim();
                
                if (value.length < 3) {
                    validationMsg.style.display = 'none';
                    this.style.borderColor = '#e8f5e9';
                    return;
                }

                checkTimeout = setTimeout(() => {
                    checkDuplicateFood(value, validationMsg, this);
                }, 500);
            });

            // Add focus effects
            const inputs = document.querySelectorAll('#requestFoodForm input, #requestFoodForm textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#4caf50';
                    this.style.boxShadow = '0 0 0 3px rgba(76, 175, 80, 0.1)';
                });
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.style.borderColor = '#e8f5e9';
                        this.style.boxShadow = 'none';
                    }
                });
            });
        },
        preConfirm: () => {
            const foodName = document.getElementById('food_name_and_description').value.trim();
            const alternateName = document.getElementById('alternate_common_names').value.trim();
            const energy = document.getElementById('energy_kcal').value;
            const tags = document.getElementById('nutrition_tags').value.trim();

            // Enhanced validation
            if (!foodName) {
                Swal.showValidationMessage('<i class=\"fas fa-exclamation-circle\"></i> Please enter food name and description');
                return false;
            }

            if (foodName.length < 3) {
                Swal.showValidationMessage('<i class=\"fas fa-exclamation-circle\"></i> Food name must be at least 3 characters');
                return false;
            }

            if (!energy || energy <= 0) {
                Swal.showValidationMessage('<i class=\"fas fa-exclamation-circle\"></i> Please enter a valid energy value (kcal)');
                return false;
            }

            if (energy > 10000) {
                Swal.showValidationMessage('<i class=\"fas fa-exclamation-circle\"></i> Energy value seems too high. Please verify.');
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

// Check for duplicate food in database
function checkDuplicateFood(foodName, validationMsg, inputElement) {
    fetch(`/api/foods/check-duplicate?name=${encodeURIComponent(foodName)}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                validationMsg.style.display = 'block';
                inputElement.style.borderColor = '#ff9800';
            } else {
                validationMsg.style.display = 'none';
                inputElement.style.borderColor = '#4caf50';
            }
        })
        .catch(error => {
            console.error('Error checking duplicate:', error);
            validationMsg.style.display = 'none';
            inputElement.style.borderColor = '#e8f5e9';
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
    
    modal.style.display = 'flex';
    content.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading request details...</p>
        </div>
    `;
    
    fetch(`/nutritionist/food-requests/${requestId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch request details');
            }
            return response.json();
        })
        .then(data => {
            const statusConfig = {
                pending: { color: '#ffc107', icon: 'fa-clock', label: 'Pending Review' },
                approved: { color: '#4caf50', icon: 'fa-check-circle', label: 'Approved' },
                rejected: { color: '#dc3545', icon: 'fa-times-circle', label: 'Rejected' }
            };
            
            const status = statusConfig[data.status] || statusConfig.pending;
            
            content.innerHTML = `
                <div class="modal-details">
                    <div class="status-banner" style="background: linear-gradient(135deg, ${status.color}15 0%, ${status.color}25 100%); border-left: 4px solid ${status.color};">
                        <i class="fas ${status.icon}" style="color: ${status.color};"></i>
                        <div>
                            <h3 style="margin: 0; color: ${status.color};">${status.label}</h3>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">Request #${data.id}</p>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-header">
                            <i class="fas fa-info-circle"></i>
                            <h3>Request Information</h3>
                        </div>
                        <div class="detail-grid">
                            <div class="detail-item full-width">
                                <label><i class="fas fa-utensils"></i> Food Name & Description</label>
                                <span class="value">${data.food_name_and_description}</span>
                            </div>
                            <div class="detail-item full-width">
                                <label><i class="fas fa-tag"></i> Alternate Names</label>
                                <span class="value">${data.alternate_common_names || 'None'}</span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-fire"></i> Energy</label>
                                <span class="value highlight">${data.energy_kcal ? parseFloat(data.energy_kcal).toFixed(1) + ' kcal' : '-'}</span>
                            </div>
                            <div class="detail-item full-width">
                                <label><i class="fas fa-tags"></i> Nutrition Tags</label>
                                <span class="value tags">${formatTags(data.nutrition_tags)}</span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-calendar-plus"></i> Requested On</label>
                                <span class="value">${new Date(data.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                            </div>
                        </div>
                    </div>

                    ${data.admin_notes || data.reviewed_at ? `
                    <div class="detail-section">
                        <div class="detail-header">
                            <i class="fas fa-user-shield"></i>
                            <h3>Admin Review</h3>
                        </div>
                        <div class="detail-grid">
                            ${data.admin_notes ? `
                            <div class="detail-item full-width">
                                <label><i class="fas fa-comment-alt"></i> Admin Notes</label>
                                <div class="admin-notes">${data.admin_notes}</div>
                            </div>
                            ` : ''}
                            ${data.reviewed_at ? `
                            <div class="detail-item">
                                <label><i class="fas fa-calendar-check"></i> Reviewed On</label>
                                <span class="value">${new Date(data.reviewed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                            </div>
                            ` : ''}
                            ${data.reviewer ? `
                            <div class="detail-item">
                                <label><i class="fas fa-user-check"></i> Reviewed By</label>
                                <span class="value">${data.reviewer.first_name} ${data.reviewer.last_name}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Unable to Load Request Details</h3>
                    <p>An error occurred while fetching the request information. Please try again.</p>
                    <button onclick="viewRequestDetails(${requestId})" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                </div>
            `;
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
