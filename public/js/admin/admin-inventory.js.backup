/**
 * Admin Inventory JavaScript
 * Handles inventory management functionality
 */

let currentItemId = null;
let isEditMode = false;
let currentStockItemId = null;
let currentStockItemName = '';
let currentAvailableStock = 0;

// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (!csrfToken) {
    console.error('CSRF token meta tag not found!');
}
const csrfTokenValue = csrfToken ? csrfToken.getAttribute('content') : null;

// Modal functions
function openAddModal() {
    console.log('Opening Add Modal...');
    isEditMode = false;
    currentItemId = null;
    document.getElementById('modalTitle').textContent = 'Add New Item';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus"></i> Add Item';
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
    showModal();
}

function openEditModal(itemId) {
    console.log('Opening Edit Modal for item:', itemId);
    isEditMode = true;
    currentItemId = itemId;
    document.getElementById('modalTitle').textContent = 'Edit Item';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Item';
    
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
        if (data.success) {
            const item = data.item;
            document.getElementById('itemId').value = item.item_id;
            document.getElementById('itemName').value = item.item_name;
            document.getElementById('categoryId').value = item.category_id;
            document.getElementById('unit').value = item.unit;
            document.getElementById('quantity').value = item.quantity;
            document.getElementById('expiryDate').value = item.expiry_date || '';
            showModal();
        } else {
            showNotification('Error loading item data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading item data', 'error');
    });
}

function showModal() {
    console.log('Showing modal...');
    const modal = document.getElementById('itemModal');
    if (!modal) {
        console.error('Modal element not found!');
        return;
    }
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Force a reflow then add the show class for proper animation
    modal.offsetHeight;
    modal.classList.add('show');
    console.log('Modal should now be visible');
}

function closeModal() {
    const modal = document.getElementById('itemModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('itemForm').reset();
    }, 300);
}

function confirmDelete(itemId, itemName) {
    console.log('Opening Delete Modal for item:', itemId, itemName);
    currentItemId = itemId;
    document.getElementById('deleteItemName').textContent = itemName;
    const modal = document.getElementById('deleteModal');
    if (!modal) {
        console.error('Delete modal element not found!');
        return;
    }
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Force a reflow then add the show class for proper animation
    modal.offsetHeight;
    modal.classList.add('show');
    console.log('Delete modal should now be visible');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        currentItemId = null;
    }, 300);
}

function deleteItem() {
    if (!currentItemId) return;

    const submitButton = event.target;
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    submitButton.disabled = true;

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
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting the item', 'error');
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        closeDeleteModal();
    });
}

// Stock In Modal Functions
function openStockInModal(itemId, itemName) {
    currentStockItemId = itemId;
    currentStockItemName = itemName;
    document.getElementById('stockInItemName').value = itemName;
    document.getElementById('stockInForm').reset();
    document.getElementById('stockInItemName').value = itemName; // Reset clears this, so set it again
    const modal = document.getElementById('stockInModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Force a reflow then add the show class for proper animation
    modal.offsetHeight;
    modal.classList.add('show');
}

function closeStockInModal() {
    const modal = document.getElementById('stockInModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        currentStockItemId = null;
        currentStockItemName = '';
    }, 300);
}

function processStockIn(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitButton.disabled = true;
    
    const formData = new FormData(form);
    
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
            showNotification(data.message, 'success');
            closeStockInModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while processing stock in', 'error');
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Stock Out Modal Functions
function openStockOutModal(itemId, itemName, availableStock) {
    currentStockItemId = itemId;
    currentStockItemName = itemName;
    currentAvailableStock = availableStock;
    
    document.getElementById('stockOutItemName').value = itemName;
    document.getElementById('stockOutAvailable').value = `${availableStock} units`;
    document.getElementById('stockOutForm').reset();
    document.getElementById('stockOutItemName').value = itemName; // Reset clears this, so set it again
    document.getElementById('stockOutAvailable').value = `${availableStock} units`; // Reset clears this, so set it again
    document.getElementById('stockOutQuantity').setAttribute('max', availableStock);
    const modal = document.getElementById('stockOutModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Force a reflow then add the show class for proper animation
    modal.offsetHeight;
    modal.classList.add('show');
}

function closeStockOutModal() {
    const modal = document.getElementById('stockOutModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        currentStockItemId = null;
        currentStockItemName = '';
        currentAvailableStock = 0;
    }, 300);
}

function processStockOut(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    const quantity = parseInt(document.getElementById('stockOutQuantity').value);
    
    // Validate quantity against available stock
    if (quantity > currentAvailableStock) {
        showNotification(`Cannot remove ${quantity} units. Only ${currentAvailableStock} units available.`, 'error');
        return;
    }
    
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitButton.disabled = true;
    
    const formData = new FormData(form);
    
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
            showNotification(data.message, 'success');
            closeStockOutModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while processing stock out', 'error');
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Notification function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        color: white;
        font-weight: 500;
        z-index: 10000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;

    // Set background color based on type
    const colors = {
        'success': 'var(--success-color)',
        'error': 'var(--danger-color)',
        'warning': 'var(--warning-color)',
        'info': 'var(--primary-color)'
    };
    notification.style.backgroundColor = colors[type] || colors['info'];

    // Add icon
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    const icon = icons[type] || icons['info'];
    notification.innerHTML = `<i class="fas ${icon}" style="margin-right: 0.5rem;"></i>${message}`;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Real-time filtering functionality
function setupRealTimeFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchFilter) {
        searchFilter.addEventListener('input', filterTable);
        
        // Add placeholder animation
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
    
    // Add keyboard shortcut for search (Ctrl+F or Cmd+F)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f' && searchFilter) {
            e.preventDefault();
            searchFilter.focus();
            searchFilter.select();
        }
        
        // ESC to clear filters
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
    
    const tableRows = document.querySelectorAll('.inventory-table tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        // Skip the "no items found" row
        if (row.querySelector('td[colspan]')) {
            return;
        }
        
        const itemNameCell = row.querySelector('td:nth-child(1) div:first-child');
        const categoryCell = row.querySelector('td:nth-child(2) span');
        const statusElement = row.querySelector('.stock-status');
        
        const itemName = itemNameCell?.textContent.toLowerCase() || '';
        const category = categoryCell?.textContent.toLowerCase() || '';
        const status = statusElement?.className.toLowerCase() || '';
        
        // Check search filter (search in name and category)
        const matchesSearch = searchValue === '' || 
            itemName.includes(searchValue) || 
            category.includes(searchValue);
        
        // Check category filter
        const matchesCategory = categoryValue === '' || category.includes(categoryValue);
        
        // Check status filter
        const matchesStatus = statusValue === '' || status.includes(statusValue);
        
        const shouldShow = matchesSearch && matchesCategory && matchesStatus;
        
        if (shouldShow) {
            row.style.display = '';
            visibleCount++;
            
            // Highlight matching search terms
            if (searchValue && matchesSearch) {
                highlightSearchTerms(itemNameCell, searchValue);
                highlightSearchTerms(categoryCell, searchValue);
            } else {
                // Remove existing highlights
                removeHighlights(itemNameCell);
                removeHighlights(categoryCell);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update filter results info
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
    // Remove existing filter info
    const existingInfo = document.querySelector('.filter-results-info');
    if (existingInfo) {
        existingInfo.remove();
    }
    
    // Remove existing "no results" row
    const existingNoResults = document.querySelector('.no-filter-results');
    if (existingNoResults) {
        existingNoResults.remove();
    }
    
    const filterSection = document.querySelector('.filter-section');
    const tableBody = document.querySelector('.inventory-table tbody');
    
    if (visibleCount === 0) {
        // Add "no results found" message
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
    
    // Add filter info
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
    
    // Show all rows and remove highlights
    const tableRows = document.querySelectorAll('.inventory-table tbody tr');
    tableRows.forEach(row => {
        if (!row.querySelector('td[colspan]')) {
            row.style.display = '';
            
            // Remove highlights from all cells
            const itemNameCell = row.querySelector('td:nth-child(1) div:first-child');
            const categoryCell = row.querySelector('td:nth-child(2) span');
            
            removeHighlights(itemNameCell);
            removeHighlights(categoryCell);
        }
    });
    
    // Remove filter results info
    const existingInfo = document.querySelector('.filter-results-info');
    if (existingInfo) {
        existingInfo.remove();
    }
    
    // Remove "no results" row
    const existingNoResults = document.querySelector('.no-filter-results');
    if (existingNoResults) {
        existingNoResults.remove();
    }
    
    // Reset filter section appearance
    const searchFilter2 = document.getElementById('searchFilter');
    if (searchFilter2) {
        searchFilter2.style.borderColor = 'var(--border-light)';
        searchFilter2.style.boxShadow = 'none';
    }
}

// Setup event listeners for buttons (replacing onclick handlers)
function setupEventListeners() {
    // Add Item buttons
    document.querySelectorAll('.btn-add-item').forEach(btn => {
        btn.addEventListener('click', openAddModal);
    });
    
    // Clear Filters button
    document.querySelectorAll('.btn-clear-filters').forEach(btn => {
        btn.addEventListener('click', clearFilters);
    });
    
    // Stock In buttons
    document.querySelectorAll('.action-btn.stock-in').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const itemName = this.dataset.itemName;
            openStockInModal(itemId, itemName);
        });
    });
    
    // Stock Out buttons
    document.querySelectorAll('.action-btn.stock-out').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const itemName = this.dataset.itemName;
            const quantity = this.dataset.quantity;
            openStockOutModal(itemId, itemName, quantity);
        });
    });
    
    // Edit buttons
    document.querySelectorAll('.action-btn.edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            openEditModal(itemId);
        });
    });
    
    // View buttons (redirect to audit logs)
    document.querySelectorAll('.action-btn.view').forEach(btn => {
        btn.addEventListener('click', function() {
            const auditUrl = this.dataset.auditUrl;
            window.location.href = auditUrl;
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.action-btn.delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const itemName = this.dataset.itemName;
            confirmDelete(itemId, itemName);
        });
    });
    
    // Modal close buttons
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal.id === 'itemModal') {
                closeModal();
            } else if (modal.id === 'deleteModal') {
                closeDeleteModal();
            } else if (modal.id === 'stockInModal') {
                closeStockInModal();
            } else if (modal.id === 'stockOutModal') {
                closeStockOutModal();
            }
        });
    });
    
    // Modal cancel buttons
    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal.id === 'itemModal') {
                closeModal();
            } else if (modal.id === 'deleteModal') {
                closeDeleteModal();
            } else if (modal.id === 'stockInModal') {
                closeStockInModal();
            } else if (modal.id === 'stockOutModal') {
                closeStockOutModal();
            }
        });
    });
    
    // Delete confirmation button
    document.querySelectorAll('.btn-delete-confirm').forEach(btn => {
        btn.addEventListener('click', deleteItem);
    });
    
    // Row hover events (replacing onmouseover/onmouseout)
    document.querySelectorAll('.inventory-table tbody tr').forEach(row => {
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
    // Set up real-time filtering
    setupRealTimeFilters();
    
    // Setup event listeners for buttons
    setupEventListeners();
    
    // Form submission
    const itemForm = document.getElementById('itemForm');
    if (itemForm) {
        itemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = document.getElementById('submitBtn');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitButton.disabled = true;

            const formData = new FormData(this);
            const method = isEditMode ? 'PUT' : 'POST';
            const url = isEditMode ? `/admin/inventory/${currentItemId}` : '/admin/inventory';

            // Convert FormData to JSON for PUT request
            const data = {};
            formData.forEach((value, key) => {
                if (key !== 'itemId') {
                    data[key] = value;
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
                    showNotification(data.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while saving the item', 'error');
            })
            .finally(() => {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            });
        });
    }

    // Close modals when clicking outside
    const itemModal = document.getElementById('itemModal');
    if (itemModal) {
        itemModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    }

    const stockInModal = document.getElementById('stockInModal');
    if (stockInModal) {
        stockInModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStockInModal();
            }
        });
    }

    const stockOutModal = document.getElementById('stockOutModal');
    if (stockOutModal) {
        stockOutModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStockOutModal();
            }
        });
    }

    // Setup form event listeners for stock in/out forms
    const stockInForm = document.getElementById('stockInForm');
    if (stockInForm) {
        stockInForm.addEventListener('submit', function(e) {
            processStockIn(e);
        });
    }

    const stockOutForm = document.getElementById('stockOutForm');
    if (stockOutForm) {
        stockOutForm.addEventListener('submit', function(e) {
            processStockOut(e);
        });
    }

    // Set minimum date for expiry date to tomorrow
    const expiryDateInput = document.getElementById('expiryDate');
    if (expiryDateInput) {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDate = tomorrow.toISOString().split('T')[0];
        expiryDateInput.setAttribute('min', minDate);
    }
});

// Make functions globally available
window.openAddModal = openAddModal;
window.openEditModal = openEditModal;
window.closeModal = closeModal;
window.confirmDelete = confirmDelete;
window.closeDeleteModal = closeDeleteModal;
window.deleteItem = deleteItem;
window.openStockInModal = openStockInModal;
window.closeStockInModal = closeStockInModal;
window.processStockIn = processStockIn;
window.openStockOutModal = openStockOutModal;
window.closeStockOutModal = closeStockOutModal;
window.processStockOut = processStockOut;
window.clearFilters = clearFilters;