/**
 * Enhanced Admin Food Requests JavaScript
 * 
 * Features:
 * - Batch approve/reject
 * - Better modals
 * - Real-time updates
 */

// ========== BULK SELECTION ==========
let selectedRequests = new Set();

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedRequests.add(parseInt(cb.value));
        } else {
            selectedRequests.delete(parseInt(cb.value));
        }
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.request-checkbox:checked');
    selectedRequests.clear();
    checkboxes.forEach(cb => selectedRequests.add(parseInt(cb.value)));
    
    const count = selectedRequests.size;
    const bulkBar = document.getElementById('bulkActionsBar');
    const bulkCount = document.getElementById('bulkCount');
    
    if (bulkCount) bulkCount.textContent = count;
    
    if (bulkBar) {
        bulkBar.style.display = count > 0 ? 'flex' : 'none';
    }
    
    // Update select all checkbox
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.request-checkbox');
    if (selectAll && allCheckboxes.length > 0) {
        selectAll.checked = count === allCheckboxes.length && count > 0;
        selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
    }
}

function clearSelection() {
    selectedRequests.clear();
    document.querySelectorAll('.request-checkbox').forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('selectAll');
    if (selectAll) selectAll.checked = false;
    updateBulkActions();
}

// ========== BULK APPROVE ==========
function bulkApprove() {
    if (selectedRequests.size === 0) {
        Swal.fire('No Selection', 'Please select requests to approve', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Approve Selected Requests?',
        html: `
            <p>You are about to approve <strong>${selectedRequests.size}</strong> request(s).</p>
            <p>These will be added to the food database.</p>
            <div class="modern-form-group" style="margin-top: 20px;">
                <label class="modern-label">Admin Notes (Optional)</label>
                <textarea id="bulk-approve-notes" class="modern-input modern-textarea" rows="3" placeholder="Add notes visible to nutritionists..."></textarea>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Approve All',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            return {
                notes: document.getElementById('bulk-approve-notes').value.trim()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkApprove(result.value.notes);
        }
    });
}

function performBulkApprove(notes) {
    const ids = Array.from(selectedRequests);
    
    Swal.fire({
        title: 'Processing...',
        html: 'Approving requests and adding to database...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('/admin/food-requests/batch-approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids, admin_notes: notes })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to approve requests');
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

// ========== BULK REJECT ==========
function bulkReject() {
    if (selectedRequests.size === 0) {
        Swal.fire('No Selection', 'Please select requests to reject', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Reject Selected Requests?',
        html: `
            <p>You are about to reject <strong>${selectedRequests.size}</strong> request(s).</p>
            <div class="modern-form-group" style="margin-top: 20px;">
                <label class="modern-label">Reason for Rejection <span class="required-badge">*</span></label>
                <textarea id="bulk-reject-notes" class="modern-input modern-textarea" rows="4" placeholder="Explain why these requests are being rejected..." required></textarea>
                <small class="input-hint">This will be visible to the nutritionists</small>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Reject All',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const notes = document.getElementById('bulk-reject-notes').value.trim();
            if (!notes) {
                Swal.showValidationMessage('Please provide a reason for rejection');
                return false;
            }
            return { notes };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkReject(result.value.notes);
        }
    });
}

function performBulkReject(notes) {
    const ids = Array.from(selectedRequests);
    
    Swal.fire({
        title: 'Processing...',
        html: 'Rejecting requests...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('/admin/food-requests/batch-reject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids, admin_notes: notes })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to reject requests');
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

// ========== SINGLE APPROVE ==========
function approveRequest(id) {
    Swal.fire({
        title: 'Approve Request?',
        html: `
            <p>This will add the food to the database.</p>
            <div class="modern-form-group" style="margin-top: 20px;">
                <label class="modern-label">Admin Notes (Optional)</label>
                <textarea id="approve-notes" class="modern-input modern-textarea" rows="3" placeholder="Add notes..."></textarea>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, Approve',
        preConfirm: () => {
            return {
                notes: document.getElementById('approve-notes').value.trim()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performApprove(id, result.value.notes);
        }
    });
}

function performApprove(id, notes) {
    Swal.fire({
        title: 'Processing...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch(`/admin/food-requests/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ admin_notes: notes })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Approved!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to approve');
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

// ========== SINGLE REJECT ==========
function rejectRequest(id) {
    Swal.fire({
        title: 'Reject Request?',
        html: `
            <div class="modern-form-group">
                <label class="modern-label">Reason for Rejection <span class="required-badge">*</span></label>
                <textarea id="reject-notes" class="modern-input modern-textarea" rows="4" placeholder="Explain why this request is being rejected..." required></textarea>
                <small class="input-hint">This will be visible to the nutritionist</small>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, Reject',
        preConfirm: () => {
            const notes = document.getElementById('reject-notes').value.trim();
            if (!notes) {
                Swal.showValidationMessage('Please provide a reason');
                return false;
            }
            return { notes };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performReject(id, result.value.notes);
        }
    });
}

function performReject(id, notes) {
    Swal.fire({
        title: 'Processing...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch(`/admin/food-requests/${id}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ admin_notes: notes })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Rejected',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to reject');
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

// ========== DELETE REQUEST ==========
function deleteRequest(id) {
    Swal.fire({
        title: 'Delete Request?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            performDeleteRequest(id);
        }
    });
}

function performDeleteRequest(id) {
    Swal.fire({
        title: 'Deleting...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch(`/admin/food-requests/${id}`, {
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

// ========== VIEW REQUEST DETAILS ==========
function viewRequestDetails(id) {
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch(`/admin/food-requests/${id}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(request => {
        const statusColors = {
            'pending': '#f59e0b',
            'approved': '#10b981',
            'rejected': '#dc2626'
        };
        
        let html = `
            <div style="text-align: left; padding: 20px;">
                <div style="margin-bottom: 20px;">
                    <span class="badge" style="background: ${statusColors[request.status]}; color: white; padding: 6px 12px; border-radius: 6px; font-weight: bold;">
                        ${request.status.toUpperCase()}
                    </span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #059669; margin-bottom: 8px;"><i class="fas fa-user"></i> Requested By</h4>
                    <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                        ${request.requester.first_name} ${request.requester.last_name}
                    </p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #059669; margin-bottom: 8px;"><i class="fas fa-apple-alt"></i> Food Name & Description</h4>
                    <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                        ${request.food_name_and_description}
                    </p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #059669; margin-bottom: 8px;"><i class="fas fa-list-alt"></i> Alternate Names</h4>
                    <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                        ${request.alternate_common_names || 'None'}
                    </p>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <h4 style="color: #059669; margin-bottom: 8px;"><i class="fas fa-fire"></i> Energy</h4>
                        <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                            ${request.energy_kcal ? request.energy_kcal + ' kcal' : 'N/A'}
                        </p>
                    </div>
                    <div>
                        <h4 style="color: #059669; margin-bottom: 8px;"><i class="fas fa-calendar"></i> Date</h4>
                        <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                            ${new Date(request.created_at).toLocaleDateString()}
                        </p>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #059669; margin-bottom: 8px;"><i class="fas fa-tags"></i> Tags</h4>
                    <p style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin: 0;">
                        ${request.nutrition_tags || 'No tags'}
                    </p>
                </div>
        `;
        
        if (request.admin_notes) {
            html += `
                <div style="margin-bottom: 20px; border-left: 4px solid #059669; padding-left: 16px;">
                    <h4 style="color: #059669; margin-bottom: 8px;"><i class="fas fa-comment"></i> Admin Notes</h4>
                    <p style="background: #ecfdf5; padding: 12px; border-radius: 6px; margin: 0;">
                        ${request.admin_notes}
                    </p>
                </div>
            `;
        }
        
        if (request.reviewer) {
            html += `
                <div style="margin-top: 16px; padding: 12px; background: #f9fafb; border-radius: 6px;">
                    <small style="color: #6b7280;">
                        Reviewed by ${request.reviewer.first_name} ${request.reviewer.last_name} 
                        on ${new Date(request.reviewed_at).toLocaleDateString()}
                    </small>
                </div>
            `;
        }
        
        html += '</div>';
        
        Swal.fire({
            title: '<i class="fas fa-file-alt"></i> Request Details',
            html: html,
            width: '600px',
            confirmButtonText: 'Close',
            confirmButtonColor: '#059669'
        });
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load request details'
        });
    });
}

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
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
