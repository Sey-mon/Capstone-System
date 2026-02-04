// Admin Users Management JavaScript

// Helper function to get status badge HTML
function getStatusBadge(user) {
    // Determine account status - prioritize deleted_at, then account_status field
    let status;
    if (user.deleted_at) {
        status = 'deleted';
    } else if (user.account_status === 'pending') {
        status = 'pending';
    } else if (user.account_status === 'suspended') {
        status = 'suspended';
    } else if (user.account_status === 'rejected') {
        status = 'rejected';
    } else if (user.account_status === 'active') {
        status = 'active';
    } else if (user.is_active) {
        status = 'active';
    } else {
        status = 'inactive';
    }

    // Return appropriate badge HTML
    switch(status) {
        case 'deleted':
            return '<span class="status-badge status-deleted" style="background-color: #6b7280; color: white;"><i class="fas fa-trash"></i> Deleted</span>';
        case 'pending':
            return '<span class="status-badge status-pending" style="background-color: #3b82f6; color: white;"><i class="fas fa-clock"></i> Pending</span>';
        case 'suspended':
            return '<span class="status-badge status-suspended" style="background-color: #f59e0b; color: white;"><i class="fas fa-ban"></i> Suspended</span>';
        case 'rejected':
            return '<span class="status-badge status-rejected" style="background-color: #ef4444; color: white;"><i class="fas fa-times-circle"></i> Rejected</span>';
        case 'active':
            return '<span class="status-badge status-active"><i class="fas fa-check-circle"></i> Active</span>';
        default:
            return '<span class="status-badge status-inactive"><i class="fas fa-times-circle"></i> Inactive</span>';
    }
}

function openAddUserModal() {
    Swal.fire({
        title: '<div class="modal-header-title"><i class="fas fa-user-plus"></i> Add New User</div>',
        html: `
            <form id="addUserForm" class="swal-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_first_name">First Name *</label>
                        <input type="text" id="add_first_name" name="first_name" class="swal2-input" required pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
                        <small class="field-hint">Letters only</small>
                    </div>
                    <div class="form-group">
                        <label for="add_middle_name">Middle Name</label>
                        <input type="text" id="add_middle_name" name="middle_name" class="swal2-input" pattern="[A-Za-z\s]*" title="Only letters and spaces allowed">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_last_name">Last Name *</label>
                        <input type="text" id="add_last_name" name="last_name" class="swal2-input" required pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
                        <small class="field-hint">Letters only</small>
                    </div>
                    <div class="form-group">
                        <label for="add_role_id">Role *</label>
                        <select id="add_role_id" name="role_id" class="swal2-select" required>
                            <option value="">Select Role</option>
                            ${window.rolesData.map(role => `<option value="${role.role_id}">${role.role_name}</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_email">Email *</label>
                        <input type="email" id="add_email" name="email" class="swal2-input" required autocomplete="email">
                        <small class="field-hint">Valid email format required</small>
                    </div>
                    <div class="form-group">
                        <label for="add_contact_number">Contact Number</label>
                        <input type="text" id="add_contact_number" name="contact_number" class="swal2-input" pattern="09[0-9]{9}" maxlength="11" title="Format: 09XXXXXXXXX">
                        <small class="field-hint">Format: 09XXXXXXXXX</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_password">Password *</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="add_password" name="password" class="swal2-input password-input" required minlength="8" autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('add_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="add_password_strength"></div>
                        <small class="field-hint">Min 8 characters, include uppercase, lowercase & number</small>
                    </div>
                    <div class="form-group">
                        <label for="add_password_confirmation">Confirm Password *</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="add_password_confirmation" name="password_confirmation" class="swal2-input password-input" required autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('add_password_confirmation', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="field-hint">Re-enter your password</small>
                    </div>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Add User',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        width: '650px',
        customClass: {
            popup: 'user-modal-popup modern-modal compact-modal',
            confirmButton: 'btn btn-primary modal-btn',
            cancelButton: 'btn btn-secondary modal-btn'
        },
        didOpen: () => {
            // Real-time password strength indicator
            const passwordInput = document.getElementById('add_password');
            const strengthDiv = document.getElementById('add_password_strength');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                strengthDiv.className = 'password-strength ' + strength.class;
                strengthDiv.textContent = strength.text;
            });
            
            // Real-time contact number formatting
            const contactInput = document.getElementById('add_contact_number');
            contactInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        },
        preConfirm: () => {
            const firstName = document.getElementById('add_first_name').value.trim();
            const middleName = document.getElementById('add_middle_name').value.trim();
            const lastName = document.getElementById('add_last_name').value.trim();
            const roleId = document.getElementById('add_role_id').value;
            const email = document.getElementById('add_email').value.trim();
            const contactNumber = document.getElementById('add_contact_number').value.trim();
            const password = document.getElementById('add_password').value;
            const passwordConfirmation = document.getElementById('add_password_confirmation').value;

            // Required fields validation
            if (!firstName || !lastName || !roleId || !email || !password || !passwordConfirmation) {
                Swal.showValidationMessage('Please fill in all required fields');
                return false;
            }
            
            // Name validation (letters and spaces only)
            const namePattern = /^[A-Za-z\s]+$/;
            if (!namePattern.test(firstName)) {
                Swal.showValidationMessage('First name should contain only letters');
                return false;
            }
            if (!namePattern.test(lastName)) {
                Swal.showValidationMessage('Last name should contain only letters');
                return false;
            }
            if (middleName && !namePattern.test(middleName)) {
                Swal.showValidationMessage('Middle name should contain only letters');
                return false;
            }
            
            // Email validation
            const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
            if (!emailPattern.test(email)) {
                Swal.showValidationMessage('Please enter a valid email address');
                return false;
            }
            
            // Contact number validation (if provided)
            if (contactNumber) {
                const phonePattern = /^09[0-9]{9}$/;
                if (!phonePattern.test(contactNumber)) {
                    Swal.showValidationMessage('Contact number must be in format: 09XXXXXXXXX');
                    return false;
                }
            }
            
            // Password strength validation
            if (password.length < 8) {
                Swal.showValidationMessage('Password must be at least 8 characters long');
                return false;
            }
            
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            
            if (!hasUpperCase || !hasLowerCase || !hasNumber) {
                Swal.showValidationMessage('Password must contain uppercase, lowercase, and numbers');
                return false;
            }
            
            // Password match validation
            if (password !== passwordConfirmation) {
                Swal.showValidationMessage('Passwords do not match');
                return false;
            }

            return { firstName, middleName, lastName, roleId, email, contactNumber, password, passwordConfirmation };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('first_name', result.value.firstName);
            formData.append('middle_name', result.value.middleName);
            formData.append('last_name', result.value.lastName);
            formData.append('role_id', result.value.roleId);
            formData.append('email', result.value.email);
            formData.append('contact_number', result.value.contactNumber);
            formData.append('password', result.value.password);
            formData.append('password_confirmation', result.value.passwordConfirmation);
            formData.append('is_active', '1');

            fetch(window.storeUserRoute, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'User created successfully!', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Failed to create user', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'An error occurred while creating the user', 'error');
            });
        }
    });
}

function closeModal(modalId) {
    // Legacy function for compatibility
    Swal.close();
}

function editUser(userId) {
    fetch(`${window.userUrlBase}/${userId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            Swal.fire({
                title: '<div class="modal-header-title"><i class="fas fa-user-edit"></i> Edit User</div>',
                html: `
                    <form id="editUserForm" class="swal-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_first_name">First Name *</label>
                                <input type="text" id="edit_first_name" name="first_name" class="swal2-input" value="${user.first_name}" required pattern="[A-Za-z\\s]+" title="Only letters and spaces allowed">
                                <small class="field-hint">Letters only</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_middle_name">Middle Name</label>
                                <input type="text" id="edit_middle_name" name="middle_name" class="swal2-input" value="${user.middle_name || ''}" pattern="[A-Za-z\\s]*" title="Only letters and spaces allowed">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_last_name">Last Name *</label>
                                <input type="text" id="edit_last_name" name="last_name" class="swal2-input" value="${user.last_name}" required pattern="[A-Za-z\\s]+" title="Only letters and spaces allowed">
                                <small class="field-hint">Letters only</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_role_id">Role *</label>
                                <select id="edit_role_id" name="role_id" class="swal2-select" required>
                                    <option value="">Select Role</option>
                                    ${window.rolesData.map(role => `<option value="${role.role_id}" ${role.role_id == user.role_id ? 'selected' : ''}>${role.role_name}</option>`).join('')}
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_email">Email *</label>
                                <input type="email" id="edit_email" name="email" class="swal2-input" value="${user.email}" required autocomplete="email">
                                <small class="field-hint">Valid email format required</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_contact_number">Contact Number</label>
                                <input type="text" id="edit_contact_number" name="contact_number" class="swal2-input" value="${user.contact_number || ''}" pattern="09[0-9]{9}" maxlength="11" title="Format: 09XXXXXXXXX">
                                <small class="field-hint">Format: 09XXXXXXXXX</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_password">New Password (optional)</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="edit_password" name="password" class="swal2-input password-input" minlength="8" autocomplete="new-password">
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('edit_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="edit_password_strength"></div>
                                <small class="field-hint">Leave blank to keep current password</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_password_confirmation">Confirm New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="edit_password_confirmation" name="password_confirmation" class="swal2-input password-input" autocomplete="new-password">
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('edit_password_confirmation', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="field-hint">Re-enter your new password</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="checkbox-label-flex">
                                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" ${user.is_active ? 'checked' : ''}>
                                    <span>Active Account</span>
                                </label>
                                <small class="help-text-small">
                                    For staff members: Activating will also verify their email automatically.
                                </small>
                            </div>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Update User',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                width: '650px',
                customClass: {
                    popup: 'user-modal-popup modern-modal compact-modal',
                    confirmButton: 'btn btn-primary modal-btn',
                    cancelButton: 'btn btn-secondary modal-btn'
                },
                didOpen: () => {
                    // Real-time password strength indicator
                    const passwordInput = document.getElementById('edit_password');
                    const strengthDiv = document.getElementById('edit_password_strength');
                    
                    passwordInput.addEventListener('input', function() {
                        const password = this.value;
                        if (password.length > 0) {
                            const strength = checkPasswordStrength(password);
                            strengthDiv.className = 'password-strength ' + strength.class;
                            strengthDiv.textContent = strength.text;
                        } else {
                            strengthDiv.className = 'password-strength';
                            strengthDiv.textContent = '';
                        }
                    });
                    
                    // Real-time contact number formatting
                    const contactInput = document.getElementById('edit_contact_number');
                    contactInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                },
                preConfirm: () => {
                    const firstName = document.getElementById('edit_first_name').value.trim();
                    const middleName = document.getElementById('edit_middle_name').value.trim();
                    const lastName = document.getElementById('edit_last_name').value.trim();
                    const roleId = document.getElementById('edit_role_id').value;
                    const email = document.getElementById('edit_email').value.trim();
                    const contactNumber = document.getElementById('edit_contact_number').value.trim();
                    const password = document.getElementById('edit_password').value;
                    const passwordConfirmation = document.getElementById('edit_password_confirmation').value;
                    const isActive = document.getElementById('edit_is_active').checked;

                    // Required fields validation
                    if (!firstName || !lastName || !roleId || !email) {
                        Swal.showValidationMessage('Please fill in all required fields');
                        return false;
                    }
                    
                    // Name validation (letters and spaces only)
                    const namePattern = /^[A-Za-z\s]+$/;
                    if (!namePattern.test(firstName)) {
                        Swal.showValidationMessage('First name should contain only letters and spaces');
                        return false;
                    }
                    if (!namePattern.test(lastName)) {
                        Swal.showValidationMessage('Last name should contain only letters and spaces');
                        return false;
                    }
                    if (middleName && !namePattern.test(middleName)) {
                        Swal.showValidationMessage('Middle name should contain only letters and spaces');
                        return false;
                    }
                    
                    // Email validation
                    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
                    if (!emailPattern.test(email)) {
                        Swal.showValidationMessage('Please enter a valid email address');
                        return false;
                    }
                    
                    // Contact number validation (if provided)
                    if (contactNumber) {
                        const phonePattern = /^09[0-9]{9}$/;
                        if (!phonePattern.test(contactNumber)) {
                            Swal.showValidationMessage('Contact number must be in format: 09XXXXXXXXX');
                            return false;
                        }
                    }
                    
                    // Password validation (only if provided)
                    if (password) {
                        if (password.length < 8) {
                            Swal.showValidationMessage('Password must be at least 8 characters long');
                            return false;
                        }
                        
                        const hasUpperCase = /[A-Z]/.test(password);
                        const hasLowerCase = /[a-z]/.test(password);
                        const hasNumber = /[0-9]/.test(password);
                        
                        if (!hasUpperCase || !hasLowerCase || !hasNumber) {
                            Swal.showValidationMessage('Password must contain uppercase, lowercase, and numbers');
                            return false;
                        }
                        
                        // Password match validation
                        if (password !== passwordConfirmation) {
                            Swal.showValidationMessage('Passwords do not match');
                            return false;
                        }
                    }

                    return { firstName, middleName, lastName, roleId, email, contactNumber, password, passwordConfirmation, isActive };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('first_name', result.value.firstName);
                    formData.append('middle_name', result.value.middleName);
                    formData.append('last_name', result.value.lastName);
                    formData.append('role_id', result.value.roleId);
                    formData.append('email', result.value.email);
                    formData.append('contact_number', result.value.contactNumber);
                    if (result.value.password) {
                        formData.append('password', result.value.password);
                        formData.append('password_confirmation', result.value.passwordConfirmation);
                    }
                    formData.append('is_active', result.value.isActive ? '1' : '0');
                    formData.append('_method', 'PUT');

                    fetch(`${window.userUrlBase}/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok && response.status === 422) {
                            return response.json().then(err => Promise.reject(err));
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success!', 'User updated successfully!', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Failed to update user', 'error');
                        }
                    })
                    .catch(error => {
                        if (error.message) {
                            Swal.fire('Validation Error', error.message, 'error');
                        } else if (error.errors) {
                            const errorMessages = Object.values(error.errors).flat().join('<br>');
                            Swal.fire('Validation Error', errorMessages, 'error');
                        } else {
                            Swal.fire('Error', 'An error occurred while updating the user', 'error');
                        }
                    });
                }
            });
        } else {
            Swal.fire('Error', 'Failed to load user data', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'An error occurred while loading user data', 'error');
    });
}

function deleteUser(userId, userName) {
    Swal.fire({
        title: 'Confirm Delete',
        html: `<p>Are you sure you want to delete the user <strong>${userName}</strong>?</p><p class="warning-text">This action cannot be undone.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${window.userUrlBase}/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'User deleted successfully!', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Failed to delete user', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'An error occurred while deleting the user', 'error');
            });
        }
    });
}

function toggleUserStatus(userId, activate, userName) {
    const action = activate ? 'activate' : 'deactivate';
    Swal.fire({
        title: `Confirm ${action.charAt(0).toUpperCase() + action.slice(1)}`,
        text: `Are you sure you want to ${action} ${userName}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${window.userUrlBase}/${userId}/${action}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || `Failed to ${action} user`, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'An error occurred while updating user status', 'error');
            });
        }
    });
}

// Reactivate suspended user
function reactivateSuspendedUser(userId, userName) {
    Swal.fire({
        title: 'Reactivate Suspended Account',
        html: `
            <p>Are you sure you want to reactivate <strong>${userName}</strong>?</p>
            <p class="text-muted">This will restore full account access.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-user-check"></i> Reactivate',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${window.userUrlBase}/${userId}/reactivate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message || 'User reactivated successfully', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Failed to reactivate user', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred while reactivating user', 'error');
            });
        }
    });
}

// Restore deleted user
function restoreUser(userId, userName) {
    Swal.fire({
        title: 'Restore Deleted Account',
        html: `
            <p>Are you sure you want to restore <strong>${userName}</strong>?</p>
            <p class="text-muted">This will undelete the account and restore access.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-undo"></i> Restore',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#17a2b8',
        customClass: {
            confirmButton: 'btn btn-info',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${window.userUrlBase}/${userId}/restore`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message || 'User restored successfully', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Failed to restore user', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred while restoring user', 'error');
            });
        }
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Store routes and data globally from hidden data div
    const userDataDiv = document.getElementById('userData');
    if (userDataDiv) {
        window.storeUserRoute = userDataDiv.getAttribute('data-route');
        window.userUrlBase = userDataDiv.getAttribute('data-user-url-base');
    }

    // Setup AJAX filters
    setupFilters();
    setupPaginationLinks();
    setupPaginationControls();
});

// Setup pagination controls
function setupPaginationControls() {
    const jumpToPageBtn = document.getElementById('jumpToPage');
    const pageJumpInput = document.getElementById('pageJump');
    
    if (jumpToPageBtn && pageJumpInput) {
        jumpToPageBtn.addEventListener('click', function() {
            const page = parseInt(pageJumpInput.value);
            const maxPage = parseInt(pageJumpInput.getAttribute('max'));
            
            if (page >= 1 && page <= maxPage) {
                loadUsers(page);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Page',
                    text: `Please enter a page number between 1 and ${maxPage}`,
                    confirmButtonColor: '#28a745'
                });
            }
        });
        
        pageJumpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                jumpToPageBtn.click();
            }
        });
    }
}

// Setup filter event listeners for AJAX
function setupFilters() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const accountStatusFilter = document.getElementById('accountStatusFilter');
    const sortByFilter = document.getElementById('sortByFilter');

    let searchTimeout;

    // Search input with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadUsers();
            }, 500); // Wait 500ms after user stops typing
        });
    }

    // Dropdowns - immediate filter
    if (roleFilter) {
        roleFilter.addEventListener('change', loadUsers);
    }
    if (accountStatusFilter) {
        accountStatusFilter.addEventListener('change', loadUsers);
    }
    if (sortByFilter) {
        sortByFilter.addEventListener('change', loadUsers);
    }
}

// Load users via AJAX
function loadUsers(page = 1) {
    const form = document.getElementById('userFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('page', page);
    params.append('ajax', '1');
    
    // Prevent interaction during loading
    const tableContainer = document.querySelector('.users-table-container');
    if (tableContainer) {
        tableContainer.style.pointerEvents = 'none';
    }

    fetch(`${window.location.pathname}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderUsersTable(data.users);
            renderPagination(data.pagination);
            updateUserCount(data.total);
            
            // Update URL without reload
            const newUrl = window.location.pathname + '?' + params.toString().replace('&ajax=1', '');
            window.history.pushState({}, '', newUrl);
        }
    })
    .catch(error => {
        console.error('Error loading users:', error);
        Swal.fire('Error', 'Failed to load users', 'error');
    })
    .finally(() => {
        if (tableContainer) {
            tableContainer.style.pointerEvents = 'auto';
        }
    });
}

// Render users table
function renderUsersTable(users) {
    const tbody = document.querySelector('.users-table tbody');
    if (!tbody) return;

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No users found</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => {
        const roleName = user.role?.role_name || 'Unknown';
        const roleClass = getRoleClass(roleName);
        
        return `
            <tr>
                <td>
                    <div class="user-info">
                        <div class="user-avatar">
                            ${getUserInitials(user.first_name, user.last_name)}
                        </div>
                        <div>
                            <div class="user-name">${user.first_name} ${user.last_name}</div>
                        </div>
                    </div>
                </td>
                <td class="user-email">${user.email}</td>
                <td>
                    <span class="role-badge ${roleClass}">
                        ${roleName}
                    </span>
                </td>
                <td>
                    ${getStatusBadge(user)}
                </td>
                <td class="user-created">${formatDate(user.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        ${!user.deleted_at ? `
                            <button class="action-btn edit" onclick="editUser(${user.user_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                        ` : ''}
                        ${user.user_id !== window.currentUserId && roleName !== 'Admin' ? `
                            ${user.deleted_at ? `
                                <button class="action-btn activate" onclick="restoreUser(${user.user_id}, '${user.first_name} ${user.last_name}')" title="Restore User">
                                    <i class="fas fa-undo"></i>
                                </button>
                            ` : user.account_status === 'suspended' ? `
                                <button class="action-btn activate" onclick="reactivateSuspendedUser(${user.user_id}, '${user.first_name} ${user.last_name}')" title="Reactivate User">
                                    <i class="fas fa-user-check"></i>
                                </button>
                            ` : user.is_active ? `
                                <button class="action-btn deactivate" onclick="toggleUserStatus(${user.user_id}, false, '${user.first_name} ${user.last_name}')">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            ` : `
                                <button class="action-btn activate" onclick="toggleUserStatus(${user.user_id}, true, '${user.first_name} ${user.last_name}')">
                                    <i class="fas fa-user-check"></i>
                                </button>
                            `}
                        ` : ''}
                        ${roleName !== 'Admin' && !user.deleted_at ? `
                            <button class="action-btn delete" onclick="deleteUser(${user.user_id}, '${user.first_name} ${user.last_name}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Render pagination
function renderPagination(pagination) {
    const paginationContainer = document.querySelector('.pagination-container');
    if (!paginationContainer || !pagination) return;

    if (pagination.last_page <= 1) {
        paginationContainer.style.display = 'none';
        return;
    }
    
    paginationContainer.style.display = 'flex';

    // Update pagination info
    const firstItem = pagination.from || 0;
    const lastItem = pagination.to || 0;
    const total = pagination.total || 0;
    
    const paginationInfo = paginationContainer.querySelector('.pagination-info-text');
    if (paginationInfo) {
        paginationInfo.innerHTML = `Showing <strong>${firstItem}</strong> to <strong>${lastItem}</strong> of <strong>${total}</strong> users`;
    }

    // Update pagination links
    const paginationLinks = paginationContainer.querySelector('.pagination-links');
    if (paginationLinks) {
        let html = '<nav><ul class="pagination">';
        
        // Previous button
        if (pagination.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadUsers(${pagination.current_page - 1}); return false;">‹</a></li>`;
        } else {
            html += `<li class="page-item disabled"><span class="page-link">‹</span></li>`;
        }
        
        // Page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === pagination.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadUsers(${i}); return false;">${i}</a></li>`;
            } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        // Next button
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadUsers(${pagination.current_page + 1}); return false;">›</a></li>`;
        } else {
            html += `<li class="page-item disabled"><span class="page-link">›</span></li>`;
        }
        
        html += '</ul></nav>';
        paginationLinks.innerHTML = html;
    }
    
    // Update page jump input
    const pageJumpInput = paginationContainer.querySelector('#pageJump');
    if (pageJumpInput) {
        pageJumpInput.setAttribute('max', pagination.last_page);
        pageJumpInput.value = pagination.current_page;
    }
}

// Update user count in header
function updateUserCount(total) {
    const countBtn = document.querySelector('.btn-count');
    if (countBtn) {
        countBtn.innerHTML = `<i class="fas fa-user"></i> ${total} users`;
    }
}

// Helper functions
function getUserInitials(firstName, lastName) {
    return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
}

function getRoleClass(roleName) {
    const roleMap = {
        'Admin': 'role-admin',
        'Nutritionist': 'role-nutritionist',
        'Parent': 'role-parent'
    };
    return roleMap[roleName] || 'role-unknown';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Setup pagination links (for initial page load)
function setupPaginationLinks() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const link = e.target.closest('.pagination a');
            const url = new URL(link.href);
            const page = url.searchParams.get('page') || 1;
            loadUsers(page);
        }
    });
}

// Store current user ID for comparison
window.currentUserId = document.querySelector('meta[name="user-id"]')?.content;

// Password strength checker helper function
function checkPasswordStrength(password) {
    if (password.length === 0) {
        return { class: '', text: '' };
    }
    
    let strength = 0;
    const hasLower = /[a-z]/.test(password);
    const hasUpper = /[A-Z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    if (password.length >= 8) strength++;
    if (hasLower) strength++;
    if (hasUpper) strength++;
    if (hasNumber) strength++;
    if (hasSpecial) strength++;
    
    if (strength <= 2) {
        return { class: 'weak', text: 'Weak Password' };
    } else if (strength <= 3) {
        return { class: 'medium', text: 'Medium Password' };
    } else {
        return { class: 'strong', text: 'Strong Password' };
    }
}

// Toggle password visibility
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
