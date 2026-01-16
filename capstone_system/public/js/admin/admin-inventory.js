/**
 * Admin Inventory JavaScript with SweetAlert2
 * Handles inventory management functionality using AJAX
 */

let currentItemId = null;
let isEditMode = false;
let currentStockItemId = null;
let currentStockItemName = '';
let currentAvailableStock = 0;
let categoriesData = [];
let patientsData = [];

// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (!csrfToken) {
    console.error('CSRF token meta tag not found!');
}
const csrfTokenValue = csrfToken ? csrfToken.getAttribute('content') : null;

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

// Load categories and patients data
function loadData() {
    const categoriesEl = document.getElementById('categoriesData');
    const patientsEl = document.getElementById('patientsData');
    
    if (categoriesEl) {
        categoriesData = JSON.parse(categoriesEl.dataset.categories || '[]');
    }
    if (patientsEl) {
        patientsData = JSON.parse(patientsEl.dataset.patients || '[]');
    }
}

// Generate category options HTML
function getCategoryOptions(selectedId = '') {
    let options = '<option value="">Select Category</option>';
    categoriesData.forEach(category => {
        const selected = category.category_id == selectedId ? 'selected' : '';
        options += `<option value="${category.category_id}" ${selected}>${category.category_name}</option>`;
    });
    return options;
}

// Generate patient options HTML
function getPatientOptions(selectedId = '') {
    let options = '<option value="">Select patient (if applicable)</option>';
    patientsData.forEach(patient => {
        const selected = patient.patient_id == selectedId ? 'selected' : '';
        options += `<option value="${patient.patient_id}" ${selected}>${patient.first_name} ${patient.last_name}</option>`;
    });
    return options;
}

// Add/Edit Item Modal using SweetAlert2
function openAddModal() {
    isEditMode = false;
    currentItemId = null;
    
    // Get tomorrow's date for min date
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    
    Swal.fire({
        title: 'Add New Item',
        html: `
            <form id="itemForm" style="text-align: left;">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-itemName" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Item Name *</label>
                    <input type="text" id="swal-itemName" name="item_name" required class="swal2-input" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-categoryId" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Category *</label>
                    <select id="swal-categoryId" name="category_id" required class="swal2-input" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                        ${getCategoryOptions()}
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="swal-unit" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Unit *</label>
                        <input type="text" id="swal-unit" name="unit" required placeholder="e.g., kg, pcs, bottles" class="swal2-input" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                    </div>
                    <div class="form-group">
                        <label for="swal-quantity" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Quantity *</label>
                        <input type="number" id="swal-quantity" name="quantity" required min="0" class="swal2-input" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-expiryDate" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Expiry Date</label>
                    <input type="date" id="swal-expiryDate" name="expiry_date" min="${minDate}" class="swal2-input" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                </div>
            </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-plus"></i> Add Item',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#43a047',
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'inventory-modal',
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            const itemName = document.getElementById('swal-itemName').value;
            const categoryId = document.getElementById('swal-categoryId').value;
            const unit = document.getElementById('swal-unit').value;
            const quantity = document.getElementById('swal-quantity').value;
            const expiryDate = document.getElementById('swal-expiryDate').value;
            
            if (!itemName || !categoryId || !unit || !quantity) {
                Swal.showValidationMessage('Please fill in all required fields');
                return false;
            }
            
            return {
                item_name: itemName,
                category_id: categoryId,
                unit: unit,
                quantity: quantity,
                expiry_date: expiryDate || null
            };
        },
        didOpen: () => {
            // Focus on first input
            document.getElementById('swal-itemName').focus();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            saveItem(result.value, false);
        }
    });
}

function openEditModal(itemId) {
    isEditMode = true;
    currentItemId = itemId;
    
    // Show loading
    Swal.fire({
        title: 'Loading...',
        html: 'Please wait while we fetch the item details',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Fetch item data
    fetch(`/admin/inventory/${itemId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfTokenValue,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        // Handle different response structures
        let item = null;
        if (data.success && data.inventoryitem) {
            // Laravel response with inventoryitem key
            item = data.inventoryitem;
        } else if (data.success && data.item) {
            // Alternative response with item key
            item = data.item;
        } else if (data.item_id) {
            // Data is the item itself
            item = data;
        } else if (data.data) {
            // Data is wrapped in a data property
            item = data.data;
        } else if (data.inventoryitem) {
            // Direct inventoryitem property
            item = data.inventoryitem;
        }
        
        if (item && item.item_name) {
            showEditModal(item);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load item data - invalid response format',
                confirmButtonColor: '#43a047'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while loading item data',
            confirmButtonColor: '#43a047'
        });
    });
}

function showEditModal(item) {
    // Get tomorrow's date for min date
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    
    const itemName = escapeHtml(item.item_name || '');
    const unit = escapeHtml(item.unit || '');
    const quantity = escapeHtml(item.quantity || 0);
    const expiryDate = item.expiry_date || '';
    
    Swal.fire({
        title: 'Edit Item',
        html: `
            <form id="itemForm" style="text-align: left;">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-itemName" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Item Name *</label>
                    <input type="text" id="swal-itemName" name="item_name" required class="swal2-input" value="${itemName}" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-categoryId" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Category *</label>
                    <select id="swal-categoryId" name="category_id" required class="swal2-input" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                        ${getCategoryOptions(item.category_id)}
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="swal-unit" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Unit *</label>
                        <input type="text" id="swal-unit" name="unit" required placeholder="e.g., kg, pcs, bottles" class="swal2-input" value="${unit}" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                    </div>
                    <div class="form-group">
                        <label for="swal-quantity" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Quantity *</label>
                        <input type="number" id="swal-quantity" name="quantity" required min="0" class="swal2-input" value="${quantity}" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-expiryDate" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Expiry Date</label>
                    <input type="date" id="swal-expiryDate" name="expiry_date" min="${minDate}" class="swal2-input" value="${expiryDate}" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                </div>
            </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Update Item',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#43a047',
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'inventory-modal',
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            const itemName = document.getElementById('swal-itemName').value;
            const categoryId = document.getElementById('swal-categoryId').value;
            const unit = document.getElementById('swal-unit').value;
            const quantity = document.getElementById('swal-quantity').value;
            const expiryDate = document.getElementById('swal-expiryDate').value;
            
            if (!itemName || !categoryId || !unit || !quantity) {
                Swal.showValidationMessage('Please fill in all required fields');
                return false;
            }
            
            return {
                item_name: itemName,
                category_id: categoryId,
                unit: unit,
                quantity: quantity,
                expiry_date: expiryDate || null
            };
        },
        didOpen: () => {
            // Focus on first input
            document.getElementById('swal-itemName').focus();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            saveItem(result.value, true);
        }
    });
}

// Save Item using AJAX
function saveItem(data, isEdit) {
    const method = isEdit ? 'PUT' : 'POST';
    const url = isEdit ? `/admin/inventory/${currentItemId}` : '/admin/inventory';
    
    // Show loading
    Swal.fire({
        title: isEdit ? 'Updating Item...' : 'Adding Item...',
        html: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': csrfTokenValue,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#43a047',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#43a047'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while saving the item',
            confirmButtonColor: '#43a047'
        });
    });
}

// Delete Confirmation using SweetAlert2
function confirmDelete(itemId, itemName) {
    currentItemId = itemId;
    const safeName = escapeHtml(itemName);
    
    Swal.fire({
        title: 'Confirm Deletion',
        html: `
            <div style="text-align: center; padding: 1rem;">
                <div style="font-size: 4rem; color: #f44336; margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p style="font-size: 1rem; margin-bottom: 0.5rem;">
                    Are you sure you want to delete <strong>${safeName}</strong>?
                </p>
                <p style="color: #f44336; font-size: 0.875rem;">
                    This action cannot be undone.
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash"></i> Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#f44336',
        cancelButtonColor: '#6c757d',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            deleteItem();
        }
    });
}

function deleteItem() {
    if (!currentItemId) return;
    
    // Show loading
    Swal.fire({
        title: 'Deleting Item...',
        html: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/admin/inventory/${currentItemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfTokenValue,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message,
                confirmButtonColor: '#43a047',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#43a047'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while deleting the item',
            confirmButtonColor: '#43a047'
        });
    });
}

// Stock In Modal using SweetAlert2
function openStockInModal(itemId, itemName) {
    currentStockItemId = itemId;
    currentStockItemName = itemName;
    const safeName = escapeHtml(itemName);
    
    Swal.fire({
        title: 'Stock In',
        html: `
            <form id="stockInForm" style="text-align: left;">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Item Name</label>
                    <input type="text" value="${safeName}" class="swal2-input" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0; background-color: #f5f5f5;">
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-stockInQuantity" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Quantity to Add <span style="color: #f44336;">*</span></label>
                    <input type="number" id="swal-stockInQuantity" name="quantity" class="swal2-input" min="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                    <small style="display: block; margin-top: 0.25rem; color: #666; font-size: 0.875rem;">Enter the number of units to add to stock</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-stockInRemarks" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Remarks</label>
                    <textarea id="swal-stockInRemarks" name="remarks" class="swal2-textarea" rows="3" placeholder="Optional notes about this stock in..." style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0; resize: vertical;"></textarea>
                </div>
            </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-plus"></i> Add Stock',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#43a047',
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'inventory-modal',
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            const quantity = document.getElementById('swal-stockInQuantity').value;
            const remarks = document.getElementById('swal-stockInRemarks').value;
            
            if (!quantity || quantity < 1) {
                Swal.showValidationMessage('Please enter a valid quantity');
                return false;
            }
            
            return {
                quantity: quantity,
                remarks: remarks || ''
            };
        },
        didOpen: () => {
            document.getElementById('swal-stockInQuantity').focus();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processStockIn(result.value);
        }
    });
}

function processStockIn(data) {
    // Show loading
    Swal.fire({
        title: 'Processing Stock In...',
        html: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = new FormData();
    formData.append('quantity', data.quantity);
    formData.append('remarks', data.remarks);
    
    fetch(`/admin/inventory/${currentStockItemId}/stock-in`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfTokenValue,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#43a047',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#43a047'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while processing stock in',
            confirmButtonColor: '#43a047'
        });
    });
}

// Stock Out Modal using SweetAlert2
function openStockOutModal(itemId, itemName, availableStock) {
    currentStockItemId = itemId;
    currentStockItemName = itemName;
    currentAvailableStock = parseInt(availableStock);
    const safeName = escapeHtml(itemName);
    const safeStock = escapeHtml(availableStock);
    
    Swal.fire({
        title: 'Stock Out',
        html: `
            <form id="stockOutForm" style="text-align: left;">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Item Name</label>
                    <input type="text" value="${safeName}" class="swal2-input" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0; background-color: #f5f5f5;">
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Available Stock</label>
                    <input type="text" value="${safeStock} units" class="swal2-input" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0; background-color: #f5f5f5;">
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-stockOutQuantity" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Quantity to Remove <span style="color: #f44336;">*</span></label>
                    <input type="number" id="swal-stockOutQuantity" name="quantity" class="swal2-input" min="1" max="${availableStock}" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                    <small style="display: block; margin-top: 0.25rem; color: #666; font-size: 0.875rem;">Enter the number of units to remove from stock</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-stockOutPatient" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Patient (Optional)</label>
                    <select id="swal-stockOutPatient" name="patient_id" class="swal2-input" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                        ${getPatientOptions()}
                    </select>
                    <small style="display: block; margin-top: 0.25rem; color: #666; font-size: 0.875rem;">Select a patient if this stock out is for a specific patient</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="swal-stockOutRemarks" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Remarks</label>
                    <textarea id="swal-stockOutRemarks" name="remarks" class="swal2-textarea" rows="3" placeholder="Optional notes about this stock out..." style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0; resize: vertical;"></textarea>
                </div>
            </form>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-minus"></i> Remove Stock',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ff9800',
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'inventory-modal',
            confirmButton: 'btn btn-warning',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            const quantity = parseInt(document.getElementById('swal-stockOutQuantity').value);
            const patientId = document.getElementById('swal-stockOutPatient').value;
            const remarks = document.getElementById('swal-stockOutRemarks').value;
            
            if (!quantity || quantity < 1) {
                Swal.showValidationMessage('Please enter a valid quantity');
                return false;
            }
            
            if (quantity > currentAvailableStock) {
                Swal.showValidationMessage(`Cannot remove ${quantity} units. Only ${currentAvailableStock} units available.`);
                return false;
            }
            
            return {
                quantity: quantity,
                patient_id: patientId || '',
                remarks: remarks || ''
            };
        },
        didOpen: () => {
            document.getElementById('swal-stockOutQuantity').focus();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processStockOut(result.value);
        }
    });
}

function processStockOut(data) {
    // Show loading
    Swal.fire({
        title: 'Processing Stock Out...',
        html: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = new FormData();
    formData.append('quantity', data.quantity);
    if (data.patient_id) {
        formData.append('patient_id', data.patient_id);
    }
    formData.append('remarks', data.remarks);
    
    fetch(`/admin/inventory/${currentStockItemId}/stock-out`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfTokenValue,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#43a047',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#43a047'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while processing stock out',
            confirmButtonColor: '#43a047'
        });
    });
}

// Notification function (kept for backwards compatibility)
function showNotification(message, type = 'info') {
    const icons = {
        'success': 'success',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };
    
    Swal.fire({
        icon: icons[type] || 'info',
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Real-time filtering functionality
function setupRealTimeFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchFilter) {
        searchFilter.addEventListener('input', filterTable);
        
        searchFilter.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary-color)';
            this.style.boxShadow = '0 0 0 3px rgba(67, 160, 71, 0.1)';
        });
        
        searchFilter.addEventListener('blur', function() {
            if (!this.value) {
                this.style.borderColor = 'var(--border-light)';
                this.style.boxShadow = 'none';
            }
        });
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterTable);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f' && searchFilter) {
            e.preventDefault();
            searchFilter.focus();
            searchFilter.select();
        }
        
        if (e.key === 'Escape') {
            clearFilters();
            if (searchFilter) {
                searchFilter.blur();
            }
        }
    });
}

function filterTable() {
    const searchValue = document.getElementById('searchFilter')?.value.toLowerCase() || '';
    const categoryValue = document.getElementById('categoryFilter')?.value.toLowerCase() || '';
    const statusValue = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    
    const tableRows = document.querySelectorAll('.inventory-table tbody tr, .table-modern tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        if (row.querySelector('td[colspan]')) {
            return;
        }
        
        // Get item name from user-info structure or item-name-main
        const userInfo = row.querySelector('.user-info .user-name');
        const itemNameMain = row.querySelector('.item-name-main');
        const itemName = (userInfo?.textContent || itemNameMain?.textContent || '').toLowerCase();
        
        // Get category from badge
        const categoryBadge = row.querySelector('.badge-role, .badge-admin, .category-badge');
        const category = categoryBadge?.textContent.toLowerCase().trim() || '';
        
        // Get status from status-badge or stock-status
        const statusElement = row.querySelector('.status-badge, .stock-status');
        const status = statusElement?.textContent.toLowerCase().trim() || '';
        
        const matchesSearch = searchValue === '' || 
            itemName.includes(searchValue) || 
            category.includes(searchValue);
        
        const matchesCategory = categoryValue === '' || category.includes(categoryValue);
        
        // Handle status filtering
        let matchesStatus = true;
        if (statusValue !== '') {
            if (statusValue === 'in-stock') {
                matchesStatus = status.includes('active') || status.includes('in stock');
            } else if (statusValue === 'low-stock') {
                matchesStatus = status.includes('low stock');
            } else if (statusValue === 'critical') {
                matchesStatus = status.includes('critical');
            } else if (statusValue === 'out-of-stock') {
                matchesStatus = status.includes('out of stock');
            } else if (statusValue === 'expired') {
                matchesStatus = status.includes('expired');
            }
        }
        
        const shouldShow = matchesSearch && matchesCategory && matchesStatus;
        
        if (shouldShow) {
            row.style.display = '';
            visibleCount++;
            
            if (searchValue && matchesSearch) {
                if (userInfo) highlightSearchTerms(userInfo, searchValue);
                if (itemNameMain) highlightSearchTerms(itemNameMain, searchValue);
                if (categoryBadge) highlightSearchTerms(categoryBadge, searchValue);
            } else {
                if (userInfo) removeHighlights(userInfo);
                if (itemNameMain) removeHighlights(itemNameMain);
                if (categoryBadge) removeHighlights(categoryBadge);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    updateFilterResults(visibleCount);
}

function highlightSearchTerms(element, searchTerm) {
    if (!element || !searchTerm) return;
    
    const text = element.textContent;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    const highlightedText = text.replace(regex, '<mark style="background: var(--warning-color); color: white; padding: 0.1rem 0.2rem; border-radius: 0.2rem;">$1</mark>');
    
    if (highlightedText !== text) {
        element.innerHTML = highlightedText;
    }
}

function removeHighlights(element) {
    if (!element) return;
    
    const marks = element.querySelectorAll('mark');
    marks.forEach(mark => {
        mark.replaceWith(mark.textContent);
    });
}

function updateFilterResults(visibleCount) {
    const existingInfo = document.querySelector('.filter-results-info');
    if (existingInfo) {
        existingInfo.remove();
    }
    
    const existingNoResults = document.querySelector('.no-filter-results');
    if (existingNoResults) {
        existingNoResults.remove();
    }
    
    const filterSection = document.querySelector('.filter-section');
    const tableBody = document.querySelector('.inventory-table tbody');
    
    if (visibleCount === 0) {
        if (tableBody) {
            const noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-filter-results';
            noResultsRow.innerHTML = `
                <td colspan="7" style="padding: 3rem; text-align: center;">
                    <div style="color: var(--text-secondary);">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>No items match your current filters.</p>
                        <button class="btn btn-secondary" style="margin-top: 1rem;" onclick="clearFilters()">
                            <i class="fas fa-times"></i>
                            Clear Filters
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(noResultsRow);
        }
    }
    
    if (filterSection && visibleCount !== undefined) {
        const filterInfo = document.createElement('div');
        filterInfo.className = 'filter-results-info';
        filterInfo.style.cssText = `
            padding: 0.5rem 0;
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-align: center;
            border-top: 1px solid var(--border-light);
            margin-top: 1rem;
        `;
        
        if (visibleCount === 0) {
            filterInfo.textContent = 'No items found matching filters';
            filterInfo.style.color = 'var(--warning-color)';
        } else {
            filterInfo.innerHTML = `
                <i class="fas fa-filter" style="margin-right: 0.5rem;"></i>
                Showing ${visibleCount} item${visibleCount !== 1 ? 's' : ''}
            `;
        }
        
        filterSection.appendChild(filterInfo);
    }
}

function clearFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchFilter) searchFilter.value = '';
    if (categoryFilter) categoryFilter.value = '';
    if (statusFilter) statusFilter.value = '';
    
    const tableRows = document.querySelectorAll('.inventory-table tbody tr, .table-modern tbody tr');
    tableRows.forEach(row => {
        if (!row.querySelector('td[colspan]')) {
            row.style.display = '';
            
            // Remove highlights from all potential cells
            const userInfo = row.querySelector('.user-info .user-name');
            const itemNameMain = row.querySelector('.item-name-main');
            const categoryBadge = row.querySelector('.badge-role, .badge-admin, .category-badge');
            
            if (userInfo) removeHighlights(userInfo);
            if (itemNameMain) removeHighlights(itemNameMain);
            if (categoryBadge) removeHighlights(categoryBadge);
        }
    });
    
    const existingInfo = document.querySelector('.filter-results-info');
    if (existingInfo) {
        existingInfo.remove();
    }
    
    const existingNoResults = document.querySelector('.no-filter-results');
    if (existingNoResults) {
        existingNoResults.remove();
    }
    
    const searchFilter2 = document.getElementById('searchFilter');
    if (searchFilter2) {
        searchFilter2.style.borderColor = 'var(--border-light)';
        searchFilter2.style.boxShadow = 'none';
    }
}

// Setup event listeners for buttons
function setupEventListeners() {
    document.querySelectorAll('.btn-add-item').forEach(btn => {
        btn.addEventListener('click', openAddModal);
    });
    
    document.querySelectorAll('.btn-clear-filters, .btn-clear-all, #clearAllBtn').forEach(btn => {
        btn.addEventListener('click', clearFilters);
    });
    
    document.querySelectorAll('.action-btn.stock-in, .action-btn-stock-in').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const itemName = this.dataset.itemName;
            openStockInModal(itemId, itemName);
        });
    });
    
    document.querySelectorAll('.action-btn.stock-out, .action-btn-stock-out').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const itemName = this.dataset.itemName;
            const quantity = this.dataset.quantity;
            openStockOutModal(itemId, itemName, quantity);
        });
    });
    
    document.querySelectorAll('.action-btn.edit, .action-btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            openEditModal(itemId);
        });
    });
    
    document.querySelectorAll('.action-btn.view, .action-btn-view').forEach(btn => {
        btn.addEventListener('click', function() {
            const auditUrl = this.dataset.auditUrl;
            window.location.href = auditUrl;
        });
    });
    
    document.querySelectorAll('.action-btn.delete, .action-btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const itemName = this.dataset.itemName;
            confirmDelete(itemId, itemName);
        });
    });
    
    document.querySelectorAll('.inventory-table tbody tr, .table-modern tbody tr').forEach(row => {
        if (!row.querySelector('td[colspan]')) {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'var(--bg-tertiary)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
            });
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Load categories and patients data
    loadData();
    
    // Set up real-time filtering
    setupRealTimeFilters();
    
    // Setup event listeners for buttons
    setupEventListeners();
});

// Make functions globally available
window.openAddModal = openAddModal;
window.openEditModal = openEditModal;
window.confirmDelete = confirmDelete;
window.deleteItem = deleteItem;
window.openStockInModal = openStockInModal;
window.processStockIn = processStockIn;
window.openStockOutModal = openStockOutModal;
window.processStockOut = processStockOut;
window.clearFilters = clearFilters;
