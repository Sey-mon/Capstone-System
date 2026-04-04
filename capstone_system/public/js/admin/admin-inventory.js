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
let bnsData = [];

// Full-page search variables
let allInventoryItems = []; // Store all items from database
let filteredItems = []; // Store filtered results
let currentFilteredPage = 1;
let itemsPerPage = 10;

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

// Helper function to format date for HTML5 date input (YYYY-MM-DD)
function formatDateForInput(dateString) {
    if (!dateString) return '';
    
    // Handle different date formats
    let date;
    
    // If it's already in YYYY-MM-DD format, return it
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
        return dateString;
    }
    
    // Try to parse the date string
    try {
        if (dateString instanceof Date) {
            date = dateString;
        } else {
            date = new Date(dateString);
        }
        
        // Check if date is valid
        if (isNaN(date.getTime())) {
            return '';
        }
        
        // Format as YYYY-MM-DD
        const year = date.getUTCFullYear();
        const month = String(date.getUTCMonth() + 1).padStart(2, '0');
        const day = String(date.getUTCDate()).padStart(2, '0');
        
        return `${year}-${month}-${day}`;
    } catch (e) {
        console.warn('Invalid date format:', dateString);
        return '';
    }
}

// Load categories, patients, and BNS data
function loadData() {
    const categoriesEl = document.getElementById('categoriesData');
    const patientsEl = document.getElementById('patientsData');
    const bnsEl = document.getElementById('bnsData');
    
    if (categoriesEl) {
        categoriesData = JSON.parse(categoriesEl.dataset.categories || '[]');
    }
    if (patientsEl) {
        patientsData = JSON.parse(patientsEl.dataset.patients || '[]');
    }
    if (bnsEl) {
        bnsData = JSON.parse(bnsEl.dataset.bns || '[]');
    }
}

// Load all inventory items for full-page searching
function loadAllInventoryItems() {
    return fetch('/admin/inventory/data/all', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfTokenValue,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            allInventoryItems = data.data || [];
            filteredItems = [...allInventoryItems]; // Initialize filtered items
            currentFilteredPage = 1;
            console.log(`Loaded ${allInventoryItems.length} inventory items for search`);
            return true;
        } else {
            console.error('Failed to load inventory items:', data.message);
            return false;
        }
    })
    .catch(error => {
        console.error('Error loading inventory items:', error);
        return false;
    });
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

// Generate BNS (Barangay Nutrition Specialist) options HTML
function getBNSOptions(selectedId = '') {
    let options = '<option value="">Select BNS</option>';
    bnsData.forEach(bns => {
        const selected = bns.user_id == selectedId ? 'selected' : '';
        options += `<option value="${bns.user_id}" ${selected}>${bns.first_name} ${bns.last_name}</option>`;
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
    const expiryDate = formatDateForInput(item.expiry_date);
    
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
    
    const itemName = document.querySelector(`button[data-item-id="${currentItemId}"]`)?.dataset.itemName || 'item';
    
    // Show confirmation with text input
    Swal.fire({
        title: 'Delete Item?',
        html: `
            <p style="margin-bottom: 1rem; color: #666;">You are about to delete <strong>${escapeHtml(itemName)}</strong></p>
            <p style="margin-bottom: 1.5rem; font-size: 0.9rem; color: #f44336;">This action will remove the item from your inventory.</p>
            <p style="margin-bottom: 0.5rem; font-size: 0.9rem; color: #666;">Type <strong>"delete"</strong> to confirm:</p>
            <input type="text" id="confirmDeleteInput" class="swal2-input" placeholder="Type 'delete' to confirm" style="margin-top: 0.5rem;">
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#f44336',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const input = Swal.getPopup().querySelector('#confirmDeleteInput');
            if (input.value.toLowerCase() !== 'delete') {
                Swal.showValidationMessage('Please type "delete" to confirm');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
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
                    <label for="swal-stockOutBNS" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">BNS (Barangay Nutrition Specialist) <span style="color: #f44336;">*</span></label>
                    <select id="swal-stockOutBNS" name="bns_id" class="swal2-input" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.5rem; margin: 0;">
                        ${getBNSOptions()}
                    </select>
                    <small style="display: block; margin-top: 0.25rem; color: #666; font-size: 0.875rem;">Select the BNS who is receiving/distributing this stock</small>
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
            const bnsId = document.getElementById('swal-stockOutBNS').value;
            const remarks = document.getElementById('swal-stockOutRemarks').value;
            
            if (!quantity || quantity < 1) {
                Swal.showValidationMessage('Please enter a valid quantity');
                return false;
            }
            
            if (quantity > currentAvailableStock) {
                Swal.showValidationMessage(`Cannot remove ${quantity} units. Only ${currentAvailableStock} units available.`);
                return false;
            }
            
            if (!bnsId) {
                Swal.showValidationMessage('Please select a BNS');
                return false;
            }
            
            return {
                quantity: quantity,
                bns_id: bnsId,
                remarks: remarks || ''
            };
        },
        didOpen: () => {
            document.getElementById('swal-stockOutQuantity').focus();
            
            // Initialize Select2 on BNS dropdown with inline search
            $('#swal-stockOutBNS').select2({
                dropdownParent: $('.swal2-container'),
                width: '100%',
                allowClear: true,
                placeholder: 'Search or select BNS...',
                minimumResultsForSearch: 0
            });
            
            console.log('Select2 initialized for stock out BNS dropdown');
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
    formData.append('bns_id', data.bns_id);
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
    const expiryFromFilter = document.getElementById('expiryFromFilter');
    const expiryToFilter = document.getElementById('expiryToFilter');
    
    if (searchFilter) {
        searchFilter.addEventListener('keydown', function(e) { if (e.key === 'Enter') filterTable(); });
        
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
    if (expiryFromFilter) {
        expiryFromFilter.addEventListener('change', filterTable);
    }
    if (expiryToFilter) {
        expiryToFilter.addEventListener('change', filterTable);
    }
}

function filterTable() {
    const searchValue = document.getElementById('searchFilter')?.value.toLowerCase() || '';
    const categoryValue = document.getElementById('categoryFilter')?.value.toLowerCase() || '';
    const statusValue = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const expiryFromValue = document.getElementById('expiryFromFilter')?.value || '';
    const expiryToValue = document.getElementById('expiryToFilter')?.value || '';
    
    // Filter all items in memory (not just DOM rows)
    filteredItems = allInventoryItems.filter(item => {
        // Search filter
        const matchesSearch = searchValue === '' || 
            item.item_name.toLowerCase().includes(searchValue) || 
            item.category_name.toLowerCase().includes(searchValue);
        
        // Category filter
        const matchesCategory = categoryValue === '' || 
            item.category_name.toLowerCase().includes(categoryValue);
        
        // Status filter
        let matchesStatus = true;
        if (statusValue !== '') {
            if (statusValue === 'in-stock') {
                matchesStatus = item.status_class === 'in-stock';
            } else if (statusValue === 'low-stock') {
                matchesStatus = item.status_class === 'low-stock';
            } else if (statusValue === 'critical') {
                matchesStatus = item.status_class === 'critical';
            } else if (statusValue === 'out-of-stock') {
                matchesStatus = item.status_class === 'out-of-stock';
            } else if (statusValue === 'expired') {
                matchesStatus = item.status_class === 'expired';
            }
        }
        
        // Expiry date range filter
        let matchesExpiryRange = true;
        if (expiryFromValue || expiryToValue) {
            if (!item.expiry_date_raw) {
                matchesExpiryRange = false;
            } else {
                const itemDate = new Date(item.expiry_date_raw);
                const fromDate = expiryFromValue ? new Date(expiryFromValue) : null;
                const toDate = expiryToValue ? new Date(expiryToValue) : null;
                
                if (fromDate && itemDate < fromDate) {
                    matchesExpiryRange = false;
                }
                if (toDate && itemDate > toDate) {
                    matchesExpiryRange = false;
                }
            }
        }
        
        return matchesSearch && matchesCategory && matchesStatus && matchesExpiryRange;
    });
    
    // Reset to first page when filtering
    currentFilteredPage = 1;
    
    // Display the first page of filtered results
    displayFilteredPage();
    
    // Update filter results info
    updateFilterResults(filteredItems.length);
}

// Display current page of filtered results in the table
function displayFilteredPage() {
    const table = document.getElementById('inventoryTable');
    const tableContainer = document.querySelector('.table-container-modern');
    let noResultsMessage = document.getElementById('noResultsMessage');
    
    const startIndex = (currentFilteredPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageItems = filteredItems.slice(startIndex, endIndex);
    
    // If no results, show message and hide table
    if (pageItems.length === 0 && filteredItems.length === 0) {
        // Hide the table
        if (tableContainer) tableContainer.style.display = 'none';
        
        // Create or show the no-results message
        if (!noResultsMessage) {
            noResultsMessage = document.createElement('div');
            noResultsMessage.id = 'noResultsMessage';
            noResultsMessage.style.cssText = `
                padding: 3rem 2rem;
                text-align: center;
                background: #f8f9fa;
                border-radius: 8px;
                border: 2px dashed #ddd;
                margin: 2rem 0;
            `;
            tableContainer.parentElement.insertBefore(noResultsMessage, tableContainer.nextSibling);
        }
        
        noResultsMessage.innerHTML = `
            <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5; color: #666; display: block;"></i>
            <h3 style="font-size: 1.8rem; font-weight: 700; margin: 1rem 0 0.5rem 0; color: #333;">Nothing Found</h3>
            <p style="margin: 0.75rem 0 1rem 0; font-size: 1rem; color: #666;">No items match your current filters.</p>
            <p style="margin: 0 0 1.5rem 0; font-size: 0.95rem; color: #888;">Try adjusting your search criteria or clearing the filters.</p>
            <button class="btn btn-secondary" style="margin-top: 0.5rem;" onclick="clearFilters()">
                <i class="fas fa-times"></i>
                Clear All Filters
            </button>
        `;
        noResultsMessage.style.display = 'block';
        updatePagination(0, 1);
        return;
    }
    
    // Show table and hide message
    if (tableContainer) tableContainer.style.display = 'block';
    if (noResultsMessage) noResultsMessage.style.display = 'none';
    
    // Find table body
    let tableBody = document.querySelector('table.table-modern tbody');
    if (!tableBody) {
        tableBody = document.querySelector('#inventoryTable tbody');
    }
    if (!tableBody) {
        tableBody = document.querySelector('table tbody');
    }
    if (!tableBody) {
        return;
    }
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    // Add rows for items on current page
    pageItems.forEach(item => {
        const row = document.createElement('tr');
        row.className = 'table-row-modern';
        row.dataset.itemId = item.item_id;
        row.innerHTML = `
            <td>
                <input type="checkbox" class="row-checkbox" value="${item.item_id}">
            </td>
            <td>
                <div class="user-info">
                    <div class="user-avatar">
                        ${item.item_name.substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                        <div class="user-name">${escapeHtml(item.item_name)}</div>
                        <div class="user-email">${item.transaction_count} transactions</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge badge-role">
                    ${escapeHtml(item.category_name)}
                </span>
            </td>
            <td>
                <div class="quantity-container">
                    <span class="quantity-badge quantity-${item.quantity < 6 ? 'low' : (item.quantity <= 10 ? 'medium' : 'high')}">
                        ${item.quantity}
                    </span>
                </div>
            </td>
            <td><span class="text-secondary">${escapeHtml(item.unit)}</span></td>
            <td>
                <span class="text-secondary">${escapeHtml(item.expiry_date)}</span>
            </td>
            <td>
                <span class="stock-status ${item.status_class}">
                    ${item.status}
                </span>
            </td>
            <td>
                <div class="action-buttons-modern">
                    <button class="action-btn-modern action-btn-edit action-btn edit" 
                            data-item-id="${item.item_id}"
                            title="Edit Item">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn-modern action-btn-stock-in action-btn stock-in" 
                            data-item-id="${item.item_id}"
                            data-item-name="${item.item_name}"
                            title="Stock In">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="action-btn-modern action-btn-stock-out action-btn stock-out" 
                            data-item-id="${item.item_id}"
                            data-item-name="${item.item_name}"
                            data-quantity="${item.quantity}"
                            title="Stock Out">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button class="action-btn-modern action-btn-view action-btn view" 
                            data-audit-url="/admin/audit/logs"
                            title="View Activity Logs">
                        <i class="fas fa-history"></i>
                    </button>
                    <button class="action-btn-modern action-btn-delete action-btn delete" 
                            data-item-id="${item.item_id}"
                            data-item-name="${item.item_name}"
                            title="Delete Item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
    
    // Re-attach event listeners to new rows
    attachRowEventListeners();
    
    // Update pagination controls
    updatePagination(filteredItems.length, Math.ceil(filteredItems.length / itemsPerPage));
}

// Update pagination display
function updatePagination(totalItems, totalPages) {
    const paginationControls = document.querySelector('.pagination-controls');
    const paginationInfo = document.querySelector('.pagination-info');
    const gotoPage = document.querySelector('.pagination-goto');
    const paginationFooter = document.querySelector('.pagination-footer-modern');
    
    if (!paginationInfo) return;
    
    // Update info text
    const startItem = totalItems === 0 ? 0 : (currentFilteredPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentFilteredPage * itemsPerPage, totalItems);
    paginationInfo.textContent = `Showing ${startItem} to ${endItem} of ${totalItems} items`;
    
    // Hide pagination footer if no results or only 1 page
    if (paginationFooter) {
        if (totalItems === 0 || totalPages <= 1) {
            paginationFooter.style.display = 'none';
            return;
        } else {
            paginationFooter.style.display = 'block';
        }
    }
    
    if (!paginationControls) return;
    
    // Clear existing pagination buttons
    paginationControls.innerHTML = '';
    
    const nav = document.createElement('nav');
    nav.className = 'pagination-nav';
    
    // Previous button
    const prevBtn = document.createElement('button' );
    prevBtn.className = `pagination-btn ${currentFilteredPage === 1 ? 'pagination-btn-disabled' : ''}`;
    prevBtn.disabled = currentFilteredPage === 1;
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prevBtn.addEventListener('click', () => {
        if (currentFilteredPage > 1) {
            currentFilteredPage--;
            displayFilteredPage();
        }
    });
    nav.appendChild(prevBtn);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentFilteredPage - 1 && i <= currentFilteredPage + 1)) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `pagination-btn ${i === currentFilteredPage ? 'pagination-btn-active' : ''}`;
            pageBtn.textContent = i;
            pageBtn.addEventListener('click', () => {
                currentFilteredPage = i;
                displayFilteredPage();
            });
            nav.appendChild(pageBtn);
        } else if (i === 2 || i === totalPages - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.style.padding = '0.5rem';
            ellipsis.textContent = '...';
            nav.appendChild(ellipsis);
        }
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = `pagination-btn ${currentFilteredPage === totalPages ? 'pagination-btn-disabled' : ''}`;
    nextBtn.disabled = currentFilteredPage === totalPages;
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    nextBtn.addEventListener('click', () => {
        if (currentFilteredPage < totalPages) {
            currentFilteredPage++;
            displayFilteredPage();
        }
    });
    nav.appendChild(nextBtn);
    
    paginationControls.appendChild(nav);
    
    // Update goto page
    if (gotoPage) {
        const gotoInput = gotoPage.querySelector('#gotoPage');
        if (gotoInput) {
            gotoInput.max = totalPages;
            gotoInput.value = currentFilteredPage;
        }
    }
}

// Attach event listeners to dynamically created rows
function attachRowEventListeners() {
    // Re-attach edit listeners
    document.querySelectorAll('.action-btn.edit, .action-btn-edit').forEach(btn => {
        btn.removeEventListener('click', handleEditClick);
        btn.addEventListener('click', handleEditClick);
    });
    
    // Re-attach stock-in listeners
    document.querySelectorAll('.action-btn.stock-in, .action-btn-stock-in').forEach(btn => {
        btn.removeEventListener('click', handleStockInClick);
        btn.addEventListener('click', handleStockInClick);
    });
    
    // Re-attach stock-out listeners
    document.querySelectorAll('.action-btn.stock-out, .action-btn-stock-out').forEach(btn => {
        btn.removeEventListener('click', handleStockOutClick);
        btn.addEventListener('click', handleStockOutClick);
    });
    
    // Re-attach view listeners
    document.querySelectorAll('.action-btn.view, .action-btn-view').forEach(btn => {
        btn.removeEventListener('click', handleViewClick);
        btn.addEventListener('click', handleViewClick);
    });
    
    // Re-attach delete listeners
    document.querySelectorAll('.action-btn.delete, .action-btn-delete').forEach(btn => {
        btn.removeEventListener('click', handleDeleteClick);
        btn.addEventListener('click', handleDeleteClick);
    });
}

// Event handlers
function handleEditClick() {
    const itemId = this.dataset.itemId;
    openEditModal(itemId);
}

function handleStockInClick() {
    const itemId = this.dataset.itemId;
    const itemName = this.dataset.itemName;
    openStockInModal(itemId, itemName);
}

function handleStockOutClick() {
    const itemId = this.dataset.itemId;
    const itemName = this.dataset.itemName;
    const quantity = this.dataset.quantity;
    openStockOutModal(itemId, itemName, quantity);
}

function handleViewClick() {
    const auditUrl = this.dataset.auditUrl;
    window.location.href = auditUrl;
}

function handleDeleteClick() {
    const itemId = this.dataset.itemId;
    const itemName = this.dataset.itemName;
    confirmDelete(itemId, itemName);
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
                        <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3; color: #999;"></i>
                        <h3 style="font-size: 1.5rem; font-weight: 600; margin: 0 0 0.5rem 0; color: var(--text-primary);">Nothing Found</h3>
                        <p style="margin: 0.5rem 0 1rem 0; font-size: 0.95rem;">No items match your current filters.</p>
                        <p style="margin: 0 0 1.5rem 0; font-size: 0.85rem; color: #999;">Try adjusting your search criteria or clearing the filters.</p>
                        <button class="btn btn-secondary" style="margin-top: 0.5rem;" onclick="clearFilters()">
                            <i class="fas fa-times"></i>
                            Clear All Filters
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
    const expiryFromFilter = document.getElementById('expiryFromFilter');
    const expiryToFilter = document.getElementById('expiryToFilter');
    
    if (searchFilter) {
        searchFilter.value = '';
        searchFilter.style.borderColor = 'var(--border-light)';
        searchFilter.style.boxShadow = 'none';
    }
    if (categoryFilter) categoryFilter.value = '';
    if (statusFilter) statusFilter.value = '';
    if (expiryFromFilter) expiryFromFilter.value = '';
    if (expiryToFilter) expiryToFilter.value = '';
    
    // Reset filtered items to all items
    filteredItems = [...allInventoryItems];
    currentFilteredPage = 1;
    
    // Redisplay table with all items
    displayFilteredPage();
    
    // Update filter info
    updateFilterResults(filteredItems.length);
}

// Setup event listeners for static buttons
function setupEventListeners() {
    // Add item button
    document.querySelectorAll('.btn-add-item').forEach(btn => {
        btn.addEventListener('click', openAddModal);
    });
    
    // Clear filters button
    document.querySelectorAll('.btn-clear-filters, .btn-clear-all, #clearAllBtn').forEach(btn => {
        btn.addEventListener('click', clearFilters);
    });
    
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Bulk delete button
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', bulkDeleteItems);
    }
    
    // Clear selections button
    const clearSelectionsBtn = document.getElementById('clearSelectionsBtn');
    if (clearSelectionsBtn) {
        clearSelectionsBtn.addEventListener('click', () => {
            document.querySelectorAll('.row-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            updateSelectedCount();
        });
    }
}

// Update selected items count display
function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    const selectedCount = document.getElementById('selectedCount');
    if (selectedCount) {
        selectedCount.textContent = `${selectedCheckboxes.length} selected`;
    }
}

// Bulk delete items
function bulkDeleteItems(itemIds = null) {
    // Get selected item IDs if not provided
    if (!itemIds) {
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        itemIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    }
    
    if (itemIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Items Selected',
            text: 'Please select at least one item to delete',
            confirmButtonColor: '#43a047'
        });
        return;
    }
    
    Swal.fire({
        title: 'Confirm Bulk Delete',
        html: `
            <p style="margin-bottom: 1rem; color: #666;">You are about to delete <strong>${itemIds.length} item${itemIds.length > 1 ? 's' : ''}</strong></p>
            <p style="margin-bottom: 1rem; font-size: 0.9rem; color: #f44336;">This will remove these items from your inventory.</p>
            <p style="margin-bottom: 0.5rem; font-size: 0.9rem; color: #666;">Type <strong>"delete"</strong> to confirm:</p>
            <input type="text" id="confirmBulkDeleteInput" class="swal2-input" placeholder="Type 'delete' to confirm" style="margin-top: 0.5rem;">
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash"></i> Delete All',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#f44336',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const input = Swal.getPopup().querySelector('#confirmBulkDeleteInput');
            if (input.value.toLowerCase() !== 'delete') {
                Swal.showValidationMessage('Please type "delete" to confirm');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete each item
            let deleted = 0;
            const total = itemIds.length;
            
            Swal.fire({
                title: 'Deleting items...',
                html: `<p>Deleting item 1 of ${total}</p>`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Delete items sequentially
            const deleteNextItem = (index) => {
                if (index >= itemIds.length) {
                    // Reload page after all deletions
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: `Successfully deleted ${deleted} item${deleted > 1 ? 's' : ''}!`,
                        confirmButtonColor: '#43a047',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                    return;
                }
                
                fetch(`/admin/inventory/${itemIds[index]}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfTokenValue,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        deleted++;
                    }
                    // Update progress
                    Swal.update({
                        html: `<p>Deleting item ${index + 2} of ${total}</p>`
                    });
                    deleteNextItem(index + 1);
                })
                .catch(error => {
                    console.error('Error:', error);
                    deleteNextItem(index + 1);
                });
            };
            
            archiveNextItem(0);
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', async function() {
    // Load categories and patients data
    loadData();
    
    // Load all inventory items for full-page search
    const itemsLoaded = await loadAllInventoryItems();
    
    if (itemsLoaded) {
        // Display first page
        displayFilteredPage();
    }
    
    // Set up real-time filtering
    setupRealTimeFilters();
    
    // Setup event listeners for buttons
    setupEventListeners();
    
    // Initialize enhancements
    loadSelectionsFromStorage();
    initializeSorting();
    initializeBulkActions();
    initializeExportButtons();
    initializeAdvancedFilters();
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

// ==================== ADVANCED FEATURES ====================
// State management for advanced features
let sortColumn = null;
let sortDirection = 'asc';
let selectedItems = new Set();

// ==================== TABLE SORTING ====================
function initializeSorting() {
    const sortableHeaders = document.querySelectorAll('.sortable');
    
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            
            // Update sort direction
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }
            
            // Update UI
            sortableHeaders.forEach(h => {
                h.classList.remove('sorted-asc', 'sorted-desc');
            });
            this.classList.add(`sorted-${sortDirection}`);
            
            // Sort table
            sortTable(column, sortDirection);
        });
    });
}

function sortTable(column, direction) {
    const tbody = document.querySelector('#inventoryTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr:not(.empty-state)'));
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        switch(column) {
            case 'item_name':
                aVal = a.querySelector('.user-name')?.textContent.trim().toLowerCase() || '';
                bVal = b.querySelector('.user-name')?.textContent.trim().toLowerCase() || '';
                break;
            case 'category':
                aVal = a.querySelectorAll('td')[2]?.textContent.trim().toLowerCase() || '';
                bVal = b.querySelectorAll('td')[2]?.textContent.trim().toLowerCase() || '';
                break;
            case 'quantity':
                aVal = parseInt(a.querySelector('.quantity-badge')?.textContent.trim()) || 0;
                bVal = parseInt(b.querySelector('.quantity-badge')?.textContent.trim()) || 0;
                break;
            case 'expiry_date':
                aVal = new Date(a.querySelectorAll('td')[5]?.textContent.trim() || '9999-12-31');
                bVal = new Date(b.querySelectorAll('td')[5]?.textContent.trim() || '9999-12-31');
                break;
            default:
                return 0;
        }
        
        if (direction === 'asc') {
            return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
        } else {
            return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
        }
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

// ==================== BULK ACTIONS ====================
function loadSelectionsFromStorage() {
    const saved = sessionStorage.getItem('inventory_selections');
    if (saved) {
        try {
            const savedArray = JSON.parse(saved);
            selectedItems = new Set(savedArray);
        } catch (e) {
            selectedItems = new Set();
        }
    }
}

function saveSelectionsToStorage() {
    sessionStorage.setItem('inventory_selections', JSON.stringify(Array.from(selectedItems)));
}

function initializeBulkActions() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActionsContainer = document.getElementById('bulkActionsContainer');
    const selectedCountEl = document.getElementById('selectedCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const clearSelectionsBtn = document.getElementById('clearSelectionsBtn');
    
    if (!selectAllCheckbox) return;
    
    // Restore checked state from storage
    rowCheckboxes.forEach(checkbox => {
        if (selectedItems.has(checkbox.value)) {
            checkbox.checked = true;
        }
    });
    
    // Update UI after restoring selections
    updateBulkActionsUI();
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            if (isChecked) {
                selectedItems.add(checkbox.value);
            } else {
                selectedItems.delete(checkbox.value);
            }
        });
        saveSelectionsToStorage();
        updateBulkActionsUI();
    });
    
    // Individual checkbox selection
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedItems.add(this.value);
            } else {
                selectedItems.delete(this.value);
                selectAllCheckbox.checked = false;
            }
            saveSelectionsToStorage();
            updateBulkActionsUI();
        });
    });
    
    // Bulk delete
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            if (selectedItems.size === 0) return;
            
            Swal.fire({
                title: 'Delete Selected Items?',
                html: `You are about to delete <strong>${selectedItems.size}</strong> item(s). This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkDeleteItems(Array.from(selectedItems));
                }
            });
        });
    }
    
    // Clear all selections
    if (clearSelectionsBtn) {
        clearSelectionsBtn.addEventListener('click', function() {
            rowCheckboxes.forEach(cb => cb.checked = false);
            selectAllCheckbox.checked = false;
            selectedItems.clear();
            sessionStorage.removeItem('inventory_selections');
            updateBulkActionsUI();
            
            Swal.fire({
                icon: 'success',
                title: 'Selections Cleared',
                text: 'All selections have been cleared.',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }
    
    function updateBulkActionsUI() {
        if (selectedItems.size > 0) {
            bulkActionsContainer.style.display = 'flex';
            selectedCountEl.textContent = `${selectedItems.size} selected`;
        } else {
            bulkActionsContainer.style.display = 'none';
        }
    }
}

function bulkDeleteItems(itemIds) {
    Swal.fire({
        title: 'Deleting Items...',
        html: `
            <div style="margin: 1rem 0;">
                <p>Deleting <strong id="currentItem">0</strong> of <strong>${itemIds.length}</strong> items...</p>
                <div style="width: 100%; background: #e0e0e0; border-radius: 10px; height: 20px; margin-top: 1rem; overflow: hidden;">
                    <div id="progressBar" style="width: 0%; background: linear-gradient(135deg, #5cb85c 0%, #449d44 100%); height: 100%; transition: width 0.3s;"></div>
                </div>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });
    
    let deletedCount = 0;
    let failedCount = 0;
    const totalItems = itemIds.length;
    
    const deleteOneItem = (itemId) => {
        return fetch(`/admin/inventory/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfTokenValue,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) deletedCount++;
            else failedCount++;
            
            const progress = ((deletedCount + failedCount) / totalItems) * 100;
            const progressBar = document.getElementById('progressBar');
            const currentItemEl = document.getElementById('currentItem');
            
            if (progressBar) progressBar.style.width = progress + '%';
            if (currentItemEl) currentItemEl.textContent = deletedCount + failedCount;
            
            return data;
        })
        .catch(error => {
            failedCount++;
            return { success: false, error: error.message };
        });
    };
    
    const deletePromises = itemIds.reduce((promise, itemId) => {
        return promise.then(() => deleteOneItem(itemId));
    }, Promise.resolve());
    
    deletePromises.then(() => {
        sessionStorage.removeItem('inventory_selections');
        
        if (failedCount === 0) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: `Successfully deleted ${deletedCount} item(s).`,
                confirmButtonColor: '#43a047'
            }).then(() => window.location.reload());
        } else if (deletedCount === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Deletion Failed',
                text: `Failed to delete ${failedCount} item(s). Please try again.`,
                confirmButtonColor: '#43a047'
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Partial Success',
                html: `<p>Successfully deleted: <strong>${deletedCount}</strong> item(s)</p>
                       <p>Failed to delete: <strong>${failedCount}</strong> item(s)</p>`,
                confirmButtonColor: '#43a047'
            }).then(() => window.location.reload());
        }
    });
}

// ==================== COLUMN TOGGLE ====================
function initializeExportButtons() {
    // Export functionality removed
}

// ==================== ADVANCED FILTERS ====================
function initializeAdvancedFilters() {
    const expiryFromFilter = document.getElementById('expiryFromFilter');
    const expiryToFilter = document.getElementById('expiryToFilter');
    
    if (expiryFromFilter) expiryFromFilter.addEventListener('change', applyFilters);
    if (expiryToFilter) expiryToFilter.addEventListener('change', applyFilters);
}

function applyFilters() {
    const expiryFrom = document.getElementById('expiryFromFilter')?.value;
    const expiryTo = document.getElementById('expiryToFilter')?.value;
    const rows = document.querySelectorAll('#inventoryTable tbody tr:not(.empty-state)');
    
    rows.forEach(row => {
        const expiryCell = row.querySelectorAll('td')[5];
        if (!expiryCell) return;
        
        const expiryText = expiryCell.textContent.trim();
        
        if (expiryText === 'No expiry') {
            if (expiryFrom || expiryTo) row.style.display = 'none';
            return;
        }
        
        const expiryDate = new Date(expiryText);
        let showRow = true;
        
        if (expiryFrom && expiryDate < new Date(expiryFrom)) showRow = false;
        if (expiryTo && expiryDate > new Date(expiryTo)) showRow = false;
        
        row.style.display = showRow ? '' : 'none';
    });
}



// Pagination Go To Page Function
function goToPage() {
    const gotoInput = document.getElementById('gotoPage');
    if (!gotoInput) return;
    
    const page = parseInt(gotoInput.value);
    const maxPage = parseInt(gotoInput.max) || 1;
    
    if (page >= 1 && page <= maxPage) {
        currentFilteredPage = page;
        displayFilteredPage();
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        gotoInput.value = currentFilteredPage;
    }
}

// Make goToPage available globally
window.goToPage = goToPage;
