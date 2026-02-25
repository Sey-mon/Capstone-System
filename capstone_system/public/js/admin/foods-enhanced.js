/**
 * Enhanced Admin Foods JavaScript
 * 
 * NEW Features:
 * - Bulk selection and batch operations
 * - Quick add modal
 * - Real-time duplicate detection
 * - Inline view/edit
 * - Better AJAX error handling
 * - Delete confirmation
 */

// ========== BULK SELECTION ==========
let selectedFoods = new Set();

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.food-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedFoods.add(parseInt(cb.value));
        } else {
            selectedFoods.delete(parseInt(cb.value));
        }
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.food-checkbox:checked');
    selectedFoods.clear();
    checkboxes.forEach(cb => selectedFoods.add(parseInt(cb.value)));
    
    const count = selectedFoods.size;
    const bulkBar = document.getElementById('bulkActionsBar');
    const bulkCount = document.getElementById('bulkCount');
    
    if (bulkCount) bulkCount.textContent = count;
    
    if (bulkBar) {
        bulkBar.style.display = count > 0 ? 'flex' : 'none';
    }
    
    // Update select all checkbox
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.food-checkbox');
    if (selectAll && allCheckboxes.length > 0) {
        selectAll.checked = count === allCheckboxes.length && count > 0;
        selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
    }
}

function clearSelection() {
    selectedFoods.clear();
    document.querySelectorAll('.food-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function bulkDelete() {
    if (selectedFoods.size === 0) {
        Swal.fire('No Selection', 'Please select items to delete', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Delete Selected Items?',
        html: `You are about to delete <strong>${selectedFoods.size}</strong> food item(s).<br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkDelete();
        }
    });
}

function performBulkDelete() {
    const ids = Array.from(selectedFoods);
    
    Swal.fire({
        title: 'Deleting...',
        html: 'Please wait while we delete the selected items.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('/admin/foods/batch-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to delete items');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'An error occurred while deleting items'
        });
    });
}

// ========== QUICK ADD MODAL ==========
function openQuickAddModal() {
    Swal.fire({
        title: '<i class="fas fa-bolt"></i> Quick Add Food',
        html: `
            <div class="modern-form-container">
                <div class="alert alert-info" style="margin-bottom: 20px; text-align: left;">
                    <i class="fas fa-info-circle"></i> Quick add requires only essential fields. You can edit later for complete details.
                </div>
                
                <div class="modern-form-group">
                    <label class="modern-label">Food Name & Description <span class="required-badge">*</span></label>
                    <textarea id="quick-foodName" class="modern-input modern-textarea" rows="3" placeholder="Enter food name and brief description..."></textarea>
                    <div id="quick-duplicate-check" style="margin-top: 8px; font-size: 13px;"></div>
                </div>

                <div class="modern-form-group">
                    <label class="modern-label">Energy (kcal) <span class="required-badge">*</span></label>
                    <input id="quick-energyKcal" type="number" step="0.1" class="modern-input" placeholder="0.0">
                </div>

                <div class="modern-form-group">
                    <label class="modern-label">Alternate Names <span style="color: #9ca3af;">(Optional)</span></label>
                    <input id="quick-alternateNames" class="modern-input" placeholder="Separate with commas">
                </div>

                <div class="modern-form-group">
                    <label class="modern-label">Tags <span style="color: #9ca3af;">(Optional)</span></label>
                    <input id="quick-nutritionTags" class="modern-input" placeholder="protein, vitamins, etc.">
                </div>
            </div>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-bolt"></i> Quick Add',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#10b981',
        didOpen: () => {
            // Add real-time duplicate checking
            const nameInput = document.getElementById('quick-foodName');
            let checkTimeout;
            
            nameInput.addEventListener('input', function() {
                clearTimeout(checkTimeout);
                const value = this.value.trim();
                const checkDiv = document.getElementById('quick-duplicate-check');
                
                if (value.length < 3) {
                    checkDiv.innerHTML = '';
                    return;
                }
                
                checkDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking for duplicates...';
                
                checkTimeout = setTimeout(() => {
                    fetch('/admin/foods/check-duplicate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ name: value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            checkDiv.innerHTML = `<span style="color: #f59e0b;"><i class="fas fa-exclamation-triangle"></i> ${data.message}</span>`;
                        } else {
                            checkDiv.innerHTML = `<span style="color: #10b981;"><i class="fas fa-check-circle"></i> ${data.message}</span>`;
                        }
                    })
                    .catch(() => {
                        checkDiv.innerHTML = '';
                    });
                }, 800);
            });
        },
        preConfirm: () => {
            const foodName = document.getElementById('quick-foodName').value.trim();
            const energyKcal = document.getElementById('quick-energyKcal').value;
            const alternateNames = document.getElementById('quick-alternateNames').value.trim();
            const nutritionTags = document.getElementById('quick-nutritionTags').value.trim();
            
            if (!foodName) {
                Swal.showValidationMessage('Please enter food name');
                return false;
            }
            if (!energyKcal || energyKcal < 0) {
                Swal.showValidationMessage('Please enter valid energy (kcal)');
                return false;
            }
            
            return { foodName, energyKcal, alternateNames, nutritionTags };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            submitQuickAdd(data);
        }
    });
}

function submitQuickAdd(data) {
    Swal.fire({
        title: 'Adding...',
        html: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('/admin/foods/quick-add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            food_name_and_description: data.foodName,
            energy_kcal: data.energyKcal,
            alternate_common_names: data.alternateNames,
            nutrition_tags: data.nutritionTags
        })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(result => {
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Added!',
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(result.message || 'Failed to add food');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',client:733 [vite] connecting...
modal-cleanup.js:25 ✅ Modal backdrops cleaned up
modal-cleanup.js:123 ✅ Modal cleanup utility initialized
dashboard-modal.js:73 ✅ Bootstrap loaded successfully
sharebx.js:8 2
sharebx.js:20 2
client:827 [vite] connected.
css.js:38 cssjs
css.js:51 enabled.
css.js:89 go
css.js:336 cssjs
sharebx.js:39 2864710
Grammarly.js:2 grm ERROR [iterable] ░░ Not supported: in app messages from Iterable
write @ Grammarly.js:2
handleEvent @ Grammarly.js:2
_logMessage @ Grammarly.js:2
error @ Grammarly.js:2
error @ Grammarly.js:2
onTrigger @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
e.next @ Grammarly.js:2
t._next @ Grammarly.js:2
t.next @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
t._execute @ Grammarly.js:2
t.execute @ Grammarly.js:2
t.flush @ Grammarly.js:2
setInterval
setInterval @ Grammarly.js:2
t.requestAsyncId @ Grammarly.js:2
t.schedule @ Grammarly.js:2
e.schedule @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
e._trySubscribe @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
o @ Grammarly.js:2
e.subscribe @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
e.next @ Grammarly.js:2
t._next @ Grammarly.js:2
t.next @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
o @ Grammarly.js:2
t.next @ Grammarly.js:2
t.next @ Grammarly.js:2
set @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
e.next @ Grammarly.js:2
t._next @ Grammarly.js:2
t.next @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
a._next @ Grammarly.js:2
t.next @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
o @ Grammarly.js:2
t.next @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
e.next @ Grammarly.js:2
t._next @ Grammarly.js:2
t.next @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
a._next @ Grammarly.js:2
t.next @ Grammarly.js:2
_processObservableMessage @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
e.next @ Grammarly.js:2
t._next @ Grammarly.js:2
t.next @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
a._next @ Grammarly.js:2
t.next @ Grammarly.js:2
t @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
(anonymous) @ Grammarly.js:2
fire @ Grammarly.js:2
_onBgPortMessage @ Grammarly.js:2
foods.js:669 Error: SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
(anonymous) @ foods.js:669
Promise.catch
submitFoodRequest @ foods.js:668
(anonymous) @ foods.js:570

            text: error.message
        });
    });
}

// ========== VIEW FOOD DETAILS ==========
function viewFood(id) {
    Swal.fire({
        title: 'Loading...',
        html: 'Fetching food details',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch(`/admin/foods/${id}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(food => {
        Swal.fire({
            title: '<i class="fas fa-utensils"></i> Food Details',
            html: `
                <div style="text-align: left; padding: 20px;">
                    <div style="margin-bottom: 20px;">
                        <h4 style="color: #059669; margin-bottom: 8px;">
                            <i class="fas fa-apple-alt"></i> Food Name & Description
                        </h4>
                        <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                            ${food.food_name_and_description || 'N/A'}
                        </p>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <h4 style="color: #059669; margin-bottom: 8px;">
                            <i class="fas fa-list-alt"></i> Alternate Names
                        </h4>
                        <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                            ${food.alternate_common_names || 'None'}
                        </p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div>
                            <h4 style="color: #059669; margin-bottom: 8px;">
                                <i class="fas fa-fire"></i> Energy
                            </h4>
                            <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0; font-weight: bold;">
                                ${food.energy_kcal ? food.energy_kcal + ' kcal' : 'N/A'}
                            </p>
                        </div>
                        <div>
                            <h4 style="color: #059669; margin-bottom: 8px;">
                                <i class="fas fa-hashtag"></i> ID
                            </h4>
                            <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0; font-weight: bold;">
                                #${food.food_id}
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 style="color: #059669; margin-bottom: 8px;">
                            <i class="fas fa-tags"></i> Nutrition Tags
                        </h4>
                        <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                            ${food.nutrition_tags || 'No tags'}
                        </p>
                    </div>
                </div>
            `,
            width: '600px',
            confirmButtonText: '<i class="fas fa-edit"></i> Edit',
            showCancelButton: true,
            cancelButtonText: 'Close',
            confirmButtonColor: '#059669'
        }).then((result) => {
            if (result.isConfirmed) {
                editFood(id);
            }
        });
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load food details'
        });
    });
}

// ========== DELETE FOOD ==========
function deleteFood(id) {
    Swal.fire({
        title: 'Delete Food Item?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(id);
        }
    });
}

function performDelete(id) {
    Swal.fire({
        title: 'Deleting...',
        html: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch(`/admin/foods/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to delete');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// ========== AUTO-DISMISS ALERTS ==========
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Initialize bulk actions state
    updateBulkActions();
});

// ========== SEARCH & FILTER ==========
document.getElementById('searchInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const search = e.target.value;
        const tag = document.getElementById('tagFilter')?.value || '';
        updateUrl(search, tag);
    }
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

// Note: Keep the original openCreateModal() and editFood() functions from the original file
// They should be preserved and work alongside these new functions
