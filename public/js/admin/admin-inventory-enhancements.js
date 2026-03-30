/**
 * Advanced Inventory Enhancements
 * Features: Sorting, Bulk Actions, Export, Column Toggle
 */

// State management
let sortColumn = null;
let sortDirection = 'asc';
let selectedItems = new Set();
let hiddenColumns = new Set();

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    loadSelectionsFromStorage();
    initializeSorting();
    initializeBulkActions();
    initializeExportButtons();
    initializeColumnToggle();
    initializeAdvancedFilters();
    initializeKeyboardShortcuts();
});

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
    // Load selections from sessionStorage
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
    // Save selections to sessionStorage
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
    // Show loading with progress
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
    
    // Delete items one by one
    let deletedCount = 0;
    let failedCount = 0;
    const totalItems = itemIds.length;
    
    // Function to delete a single item
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
            if (data.success) {
                deletedCount++;
            } else {
                failedCount++;
            }
            
            // Update progress
            const progress = ((deletedCount + failedCount) / totalItems) * 100;
            const progressBar = document.getElementById('progressBar');
            const currentItemEl = document.getElementById('currentItem');
            
            if (progressBar) progressBar.style.width = progress + '%';
            if (currentItemEl) currentItemEl.textContent = deletedCount + failedCount;
            
            return data;
        })
        .catch(error => {
            failedCount++;
            const progress = ((deletedCount + failedCount) / totalItems) * 100;
            const progressBar = document.getElementById('progressBar');
            const currentItemEl = document.getElementById('currentItem');
            
            if (progressBar) progressBar.style.width = progress + '%';
            if (currentItemEl) currentItemEl.textContent = deletedCount + failedCount;
            
            return { success: false, error: error.message };
        });
    };
    
    // Delete all items sequentially
    const deletePromises = itemIds.reduce((promise, itemId) => {
        return promise.then(() => deleteOneItem(itemId));
    }, Promise.resolve());
    
    deletePromises.then(() => {
        // Clear selections from storage after deletion
        sessionStorage.removeItem('inventory_selections');
        
        // Show result
        if (failedCount === 0) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: `Successfully deleted ${deletedCount} item(s).`,
                confirmButtonColor: '#43a047'
            }).then(() => {
                window.location.reload();
            });
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
                html: `
                    <p>Successfully deleted: <strong>${deletedCount}</strong> item(s)</p>
                    <p>Failed to delete: <strong>${failedCount}</strong> item(s)</p>
                `,
                confirmButtonColor: '#43a047'
            }).then(() => {
                window.location.reload();
            });
        }
    });
}

// ==================== EXPORT FUNCTIONALITY ====================
function initializeExportButtons() {
    // Export functionality removed
}

// ==================== COLUMN TOGGLE ====================
function initializeColumnToggle() {
    const toggleBtn = document.getElementById('columnToggleBtn');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', showColumnToggleModal);
    }
}

function showColumnToggleModal() {
    const columns = [
        { name: 'category', label: 'Category', class: 'column-category' },
        { name: 'quantity', label: 'Quantity', class: 'column-quantity' },
        { name: 'unit', label: 'Unit', class: 'column-unit' },
        { name: 'expiry', label: 'Expiry Date', class: 'column-expiry' },
        { name: 'status', label: 'Status', class: 'column-status' }
    ];
    
    const checkboxes = columns.map(col => {
        const isHidden = hiddenColumns.has(col.class);
        return `
            <div style="margin: 0.5rem 0;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" ${!isHidden ? 'checked' : ''} 
                           value="${col.class}" 
                           style="margin-right: 0.5rem;">
                    ${col.label}
                </label>
            </div>
        `;
    }).join('');
    
    Swal.fire({
        title: 'Toggle Columns',
        html: `
            <div style="text-align: left;">
                ${checkboxes}
            </div>
        `,
        confirmButtonText: 'Apply',
        confirmButtonColor: '#43a047',
        preConfirm: () => {
            const checkboxElements = Swal.getPopup().querySelectorAll('input[type="checkbox"]');
            hiddenColumns.clear();
            
            checkboxElements.forEach(checkbox => {
                if (!checkbox.checked) {
                    hiddenColumns.add(checkbox.value);
                }
            });
            
            applyColumnVisibility();
        }
    });
}

function applyColumnVisibility() {
    const allColumns = document.querySelectorAll('[class*="column-"]');
    
    allColumns.forEach(col => {
        const classList = Array.from(col.classList);
        const columnClass = classList.find(c => c.startsWith('column-'));
        
        if (columnClass && hiddenColumns.has(columnClass)) {
            col.style.display = 'none';
        } else if (columnClass) {
            col.style.display = '';
        }
    });
}

// ==================== ADVANCED FILTERS ====================
function initializeAdvancedFilters() {
    const expiryFromFilter = document.getElementById('expiryFromFilter');
    const expiryToFilter = document.getElementById('expiryToFilter');
    
    if (expiryFromFilter) {
        expiryFromFilter.addEventListener('change', applyFilters);
    }
    
    if (expiryToFilter) {
        expiryToFilter.addEventListener('change', applyFilters);
    }
}

function applyFilters() {
    const expiryFrom = document.getElementById('expiryFromFilter')?.value;
    const expiryTo = document.getElementById('expiryToFilter')?.value;
    
    const rows = document.querySelectorAll('#inventoryTable tbody tr:not(.empty-state)');
    
    rows.forEach(row => {
        const expiryCell = row.querySelectorAll('td')[5];
        if (!expiryCell) return;
        
        const expiryText = expiryCell.textContent.trim();
        
        // Skip rows with "No expiry"
        if (expiryText === 'No expiry') {
            if (expiryFrom || expiryTo) {
                row.style.display = 'none';
            }
            return;
        }
        
        const expiryDate = new Date(expiryText);
        let showRow = true;
        
        if (expiryFrom) {
            const fromDate = new Date(expiryFrom);
            if (expiryDate < fromDate) showRow = false;
        }
        
        if (expiryTo) {
            const toDate = new Date(expiryTo);
            if (expiryDate > toDate) showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

// ==================== KEYBOARD SHORTCUTS ====================
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N: Add new item
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const addBtn = document.querySelector('.btn-add-item');
            if (addBtn) addBtn.click();
        }
        
        // Escape: Clear selections
        if (e.key === 'Escape') {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            checkboxes.forEach(cb => cb.checked = false);
            selectedItems.clear();
            sessionStorage.removeItem('inventory_selections');
            document.getElementById('selectAllCheckbox').checked = false;
            document.getElementById('bulkActionsContainer').style.display = 'none';
        }
    });
}

// ==================== HELPER FUNCTIONS ====================
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

// Show keyboard shortcuts help
function showKeyboardShortcuts() {
    Swal.fire({
        title: 'Keyboard Shortcuts',
        html: `
            <div style="text-align: left;">
                <p><strong>Ctrl/Cmd + N:</strong> Add new item</p>
                <p><strong>Escape:</strong> Clear selections</p>
            </div>
        `,
        confirmButtonColor: '#43a047'
    });
}

// Add help button for shortcuts (optional)
setTimeout(() => {
    const helpBtn = document.createElement('button');
    helpBtn.innerHTML = '<i class="fas fa-keyboard"></i>';
    helpBtn.className = 'btn btn-outline-info';
    helpBtn.title = 'Keyboard Shortcuts';
    helpBtn.style.position = 'fixed';
    helpBtn.style.bottom = '20px';
    helpBtn.style.right = '20px';
    helpBtn.style.borderRadius = '50%';
    helpBtn.style.width = '50px';
    helpBtn.style.height = '50px';
    helpBtn.style.zIndex = '1000';
    helpBtn.onclick = showKeyboardShortcuts;
    document.body.appendChild(helpBtn);
}, 1000);
