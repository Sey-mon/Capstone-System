/**
 * Admin Foods JavaScript - Modern Green & White Theme
 * 
 * Features:
 * - Real-time search with debounce
 * - Tag filtering
 * - Modal for create/edit operations
 * - AJAX data loading
 * - Keyboard shortcuts (Esc to close, Ctrl+K to search)
 * - Auto-dismissible alerts
 * - Loading states and animations
 */

// Search functionality with debounce
let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const search = e.target.value;
    const tag = document.getElementById('tagFilter')?.value || '';
    
    searchTimeout = setTimeout(() => {
        updateUrl(search, tag);
    }, 500);
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

// SweetAlert2 Modal functions
function openCreateModal() {
    Swal.fire({
        title: '<div class="modal-header-icon"><i class="fas fa-plus-circle"></i></div><div class="modal-title-text">Add New Food Item</div>',
        html: `
            <div class="modern-form-container">
                <div class="form-row">
                    <div class="modern-form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-apple-alt input-icon"></i>
                            <div class="label-wrapper">
                                <label class="modern-label">
                                    Food Name & Description
                                    <span class="required-badge">Required</span>
                                </label>
                            </div>
                        </div>
                        <textarea id="swal-foodName" class="modern-input modern-textarea" rows="4" placeholder="E.g., Fresh Atlantic Salmon - Rich in omega-3 fatty acids..."></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="modern-form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-list-ul input-icon"></i>
                            <label class="modern-label">Alternate Names</label>
                        </div>
                        <input id="swal-alternateNames" class="modern-input" placeholder="E.g., Fish, Salmon fillet, Atlantic salmon">
                        <small class="input-hint">Separate multiple names with commas</small>
                    </div>
                </div>

                <div class="form-row-split">
                    <div class="modern-form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-fire input-icon"></i>
                            <div class="label-wrapper">
                                <label class="modern-label">
                                    Energy (kcal)
                                    <span class="required-badge">Required</span>
                                </label>
                            </div>
                        </div>
                        <input id="swal-energyKcal" type="number" step="0.1" class="modern-input" placeholder="0.0">
                        <small class="input-hint">Per 100g serving</small>
                    </div>

                    <div class="modern-form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-tags input-icon"></i>
                            <label class="modern-label">Nutrition Tags</label>
                        </div>
                        <input id="swal-nutritionTags" class="modern-input" placeholder="protein, omega-3, low-carb">
                        <small class="input-hint">Comma-separated</small>
                    </div>
                </div>
            </div>
        `,
        width: '700px',
        padding: '0',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check-circle"></i> Save Food Item',
        cancelButtonText: '<i class="fas fa-times-circle"></i> Cancel',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'modern-modal-popup',
            header: 'modern-modal-header',
            title: 'modern-modal-title',
            htmlContainer: 'modern-modal-body',
            actions: 'modern-modal-actions',
            confirmButton: 'modern-btn-confirm',
            cancelButton: 'modern-btn-cancel'
        },
        showClass: {
            popup: 'animate-modal-in'
        },
        hideClass: {
            popup: 'animate-modal-out'
        },
        preConfirm: () => {
            const foodName = document.getElementById('swal-foodName').value.trim();
            const energyKcal = document.getElementById('swal-energyKcal').value;

            if (!foodName) {
                Swal.showValidationMessage('<i class="fas fa-exclamation-circle"></i> Please enter food name and description');
                return false;
            }
            if (!energyKcal || energyKcal <= 0) {
                Swal.showValidationMessage('<i class="fas fa-exclamation-circle"></i> Please enter a valid energy value');
                return false;
            }

            return {
                food_name_and_description: foodName,
                alternate_common_names: document.getElementById('swal-alternateNames').value.trim(),
                energy_kcal: energyKcal,
                nutrition_tags: document.getElementById('swal-nutritionTags').value.trim()
            };
        },
        didOpen: () => {
            // Add focus effect to first input
            document.getElementById('swal-foodName')?.focus();
            
            // Add input animations
            const inputs = document.querySelectorAll('.modern-input, .modern-textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('input-focused');
                });
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('input-focused');
                });
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitFoodForm('POST', window.location.origin + '/admin/foods', result.value);
        }
    });
}

function editFood(id) {
    // Show modern loading
    Swal.fire({
        title: '<div class="loading-spinner-wrapper"><i class="fas fa-spinner fa-spin"></i></div>',
        html: '<p class="loading-text">Loading food data...</p>',
        showConfirmButton: false,
        allowOutsideClick: false,
        customClass: {
            popup: 'modern-loading-popup'
        },
        didOpen: () => {
            Swal.getPopup().style.background = 'rgba(255, 255, 255, 0.95)';
            Swal.getPopup().style.backdropFilter = 'blur(10px)';
        }
    });

    // Fetch food data
    fetch(window.location.origin + `/admin/foods/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load food data');
        }
        return response.json();
    })
    .then(food => {
        Swal.fire({
            title: '<div class="modal-header-icon edit-icon"><i class="fas fa-edit"></i></div><div class="modal-title-text">Edit Food Item</div>',
            html: `
                <div class="modern-form-container">
                    <div class="info-badge">
                        <i class="fas fa-info-circle"></i> Editing Food ID: <strong>#${food.food_id}</strong>
                    </div>
                    
                    <div class="form-row">
                        <div class="modern-form-group">
                            <div class="input-icon-wrapper">
                                <i class="fas fa-apple-alt input-icon"></i>
                                <div class="label-wrapper">
                                    <label class="modern-label">
                                        Food Name & Description
                                        <span class="required-badge">Required</span>
                                    </label>
                                </div>
                            </div>
                            <textarea id="swal-foodName" class="modern-input modern-textarea" rows="4" placeholder="E.g., Fresh Atlantic Salmon - Rich in omega-3 fatty acids...">${food.food_name_and_description || ''}</textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="modern-form-group">
                            <div class="input-icon-wrapper">
                                <i class="fas fa-list-ul input-icon"></i>
                                <label class="modern-label">Alternate Names</label>
                            </div>
                            <input id="swal-alternateNames" class="modern-input" placeholder="E.g., Fish, Salmon fillet, Atlantic salmon" value="${food.alternate_common_names || ''}">
                            <small class="input-hint">Separate multiple names with commas</small>
                        </div>
                    </div>

                    <div class="form-row-split">
                        <div class="modern-form-group">
                            <div class="input-icon-wrapper">
                                <i class="fas fa-fire input-icon"></i>
                                <div class="label-wrapper">
                                    <label class="modern-label">
                                        Energy (kcal)
                                        <span class="required-badge">Required</span>
                                    </label>
                                </div>
                            </div>
                            <input id="swal-energyKcal" type="number" step="0.1" class="modern-input" placeholder="0.0" value="${food.energy_kcal || ''}">
                            <small class="input-hint">Per 100g serving</small>
                        </div>

                        <div class="modern-form-group">
                            <div class="input-icon-wrapper">
                                <i class="fas fa-tags input-icon"></i>
                                <label class="modern-label">Nutrition Tags</label>
                            </div>
                            <input id="swal-nutritionTags" class="modern-input" placeholder="protein, omega-3, low-carb" value="${food.nutrition_tags || ''}">
                            <small class="input-hint">Comma-separated</small>
                        </div>
                    </div>
                </div>
            `,
            width: '700px',
            padding: '0',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle"></i> Update Food Item',
            cancelButtonText: '<i class="fas fa-times-circle"></i> Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'modern-modal-popup',
                header: 'modern-modal-header',
                title: 'modern-modal-title',
                htmlContainer: 'modern-modal-body',
                actions: 'modern-modal-actions',
                confirmButton: 'modern-btn-confirm',
                cancelButton: 'modern-btn-cancel'
            },
            showClass: {
                popup: 'animate-modal-in'
            },
            hideClass: {
                popup: 'animate-modal-out'
            },
            preConfirm: () => {
                const foodName = document.getElementById('swal-foodName').value.trim();
                const energyKcal = document.getElementById('swal-energyKcal').value;

                if (!foodName) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-circle"></i> Please enter food name and description');
                    return false;
                }
                if (!energyKcal || energyKcal <= 0) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-circle"></i> Please enter a valid energy value');
                    return false;
                }

                return {
                    food_name_and_description: foodName,
                    alternate_common_names: document.getElementById('swal-alternateNames').value.trim(),
                    energy_kcal: energyKcal,
                    nutrition_tags: document.getElementById('swal-nutritionTags').value.trim()
                };
            },
            didOpen: () => {
                // Add focus effect to first input
                document.getElementById('swal-foodName')?.focus();
                
                // Add input animations
                const inputs = document.querySelectorAll('.modern-input, .modern-textarea');
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.parentElement.classList.add('input-focused');
                    });
                    input.addEventListener('blur', function() {
                        this.parentElement.classList.remove('input-focused');
                    });
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitFoodForm('PUT', window.location.origin + `/admin/foods/${id}`, result.value);
            }
        });
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load food data: ' + error.message,
            confirmButtonColor: '#10b981'
        });
        console.error('Error:', error);
    });
}

// Submit food form via AJAX
function submitFoodForm(method, url, data) {
    Swal.fire({
        title: '<div class="loading-spinner-wrapper"><i class="fas fa-spinner fa-spin"></i></div>',
        html: '<p class="loading-text">Saving changes...</p>',
        showConfirmButton: false,
        allowOutsideClick: false,
        customClass: {
            popup: 'modern-loading-popup'
        },
        didOpen: () => {
            Swal.getPopup().style.background = 'rgba(255, 255, 255, 0.95)';
            Swal.getPopup().style.backdropFilter = 'blur(10px)';
        }
    });

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }
    formData.append('food_name_and_description', data.food_name_and_description);
    formData.append('alternate_common_names', data.alternate_common_names);
    formData.append('energy_kcal', data.energy_kcal);
    formData.append('nutrition_tags', data.nutrition_tags);

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to save food item');
        }
        return response.text();
    })
    .then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: method === 'PUT' ? 'Food item updated successfully!' : 'Food item created successfully!',
            confirmButtonColor: '#10b981'
        }).then(() => {
            window.location.reload();
        });
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to save food item: ' + error.message,
            confirmButtonColor: '#10b981'
        });
        console.error('Error:', error);
    });
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Make alerts dismissible
        alert.style.cursor = 'pointer';
        alert.addEventListener('click', function() {
            this.style.opacity = '0';
            setTimeout(() => this.remove(), 300);
        });
        
        // Auto-hide
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Add smooth scrolling to pagination links
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Let the default behavior happen but scroll to top after page change
            setTimeout(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);
        });
    });

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape key to close import form
        if (e.key === 'Escape') {
            const importForm = document.getElementById('importForm');
            if (importForm && importForm.style.display === 'block') {
                importForm.style.display = 'none';
            }
        }
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('searchInput')?.focus();
        }
    });

    // Replace default confirm delete with SweetAlert2
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Delete Food Item?',
                html: '<p style="color: #6b7280; margin-top: 8px;">This action cannot be undone!</p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#10b981',
                confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                reverseButtons: true,
                customClass: {
                    popup: 'animated-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show deleting message
                    Swal.fire({
                        title: 'Deleting...',
                        html: '<i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #ef4444;"></i>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                    // Submit the form
                    form.submit();
                }
            });
        });
    });
});

