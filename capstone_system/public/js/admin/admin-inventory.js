/**
 * Admin Inventory JavaScript
 * Handles inventory management functionality
 */

let currentItemId = null;
let isEditMode = false;

// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Modal functions
function openAddModal() {
    isEditMode = false;
    currentItemId = null;
    document.getElementById('modalTitle').textContent = 'Add New Item';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus"></i> Add Item';
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
    showModal();
}

function openEditModal(itemId) {
    isEditMode = true;
    currentItemId = itemId;
    document.getElementById('modalTitle').textContent = 'Edit Item';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Item';
    
    // Fetch item data
    fetch(`/admin/inventory/${itemId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
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
    document.getElementById('itemModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('itemModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('itemForm').reset();
}

function confirmDelete(itemId, itemName) {
    currentItemId = itemId;
    document.getElementById('deleteItemName').textContent = itemName;
    document.getElementById('deleteModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentItemId = null;
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
            'X-CSRF-TOKEN': csrfToken,
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
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
                    'X-CSRF-TOKEN': csrfToken,
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
