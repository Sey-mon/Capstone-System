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
            return '<span class="status-badge status-suspended" style="background-color: #f59e0b; color: white;"><i class="fas fa-ban"></i> Inactivated</span>';
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
                        <input type="text" id="add_first_name" name="first_name" class="swal2-input" required pattern="[a-zA-ZàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÄËÏÖÜāēīōūĀĒĪŌŪčďěňřšťžČĎĚŇŘŠŤŽćłńśźżĆŁŃŚŹŻ\s\-\.]+" title="Only letters (including ñ, á, é, í, ó, ú), spaces, hyphens, and periods allowed">
                        <small class="field-hint">Letters, spaces, hyphens, and periods allowed</small>
                    </div>
                    <div class="form-group">
                        <label for="add_middle_name">Middle Name</label>
                        <input type="text" id="add_middle_name" name="middle_name" class="swal2-input" pattern="[a-zA-ZàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÄËÏÖÜāēīōūĀĒĪŌŪčďěňřšťžČĎĚŇŘŠŤŽćłńśźżĆŁŃŚŹŻ\s\-\.]*" title="Only letters (including ñ, á, é, í, ó, ú), spaces, hyphens, and periods allowed">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_last_name">Last Name *</label>
                        <input type="text" id="add_last_name" name="last_name" class="swal2-input" required pattern="[a-zA-ZàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÄËÏÖÜāēīōūĀĒĪŌŪčďěňřšťžČĎĚŇŘŠŤŽćłńśźżĆŁŃŚŹŻ\s\-\.]+" title="Only letters (including ñ, á, é, í, ó, ú), spaces, hyphens, and periods allowed">
                        <small class="field-hint">Letters, spaces, hyphens, and periods allowed</small>
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
            
            // Name validation (letters, spaces, hyphens, periods, and accents)
            const namePattern = /^[a-zA-ZàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÄËÏÖÜāēīōūĀĒĪŌŪčďěňřšťžČĎĚŇŘŠŤŽćłńśźżĆŁŃŚŹŻ\s\-\.]+$/;
            if (!namePattern.test(firstName)) {
                Swal.showValidationMessage('First name contains invalid characters');
                return false;
            }
            if (!namePattern.test(lastName)) {
                Swal.showValidationMessage('Last name contains invalid characters');
                return false;
            }
            if (middleName && !namePattern.test(middleName)) {
                Swal.showValidationMessage('Middle name contains invalid characters');
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
                                <input type="text" id="edit_first_name" name="first_name" class="swal2-input" value="${user.first_name}" required pattern="[a-zA-ZàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÄËÏÖÜāēīōūĀĒĪŌŪčďěňřšťžČĎĚŇŘŠŤŽćłńśźżĆŁŃŚŹŻ\\s\\-\\.]+" title="Only letters (including ñ, á, é, í, ó, ú), spaces, hyphens, and periods allowed">
                                <small class="field-hint">Letters, spaces, hyphens, and periods allowed</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_middle_name">Middle Name</label>
                                <input type="text" id="edit_middle_name" name="middle_name" class="swal2-input" value="${user.middle_name || ''}" pattern="[a-zA-ZàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÄËÏÖÜāēīōūĀĒĪŌŪčďěňřšťžČĎĚŇŘŠŤŽćłńśźżĆŁŃŚŹŻ\\s\\-\\.]*" title="Only letters (including ñ, á, é, í, ó, ú), spaces, hyphens, and periods allowed">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_last_name">Last Name *</label>
                                <input type="text" id="edit_last_name" name="last_name" class="swal2-input" value="${user.last_name}" required pattern="[a-zA-ZàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÄËÏÖÜāēīōūĀĒĪŌŪčďěňřšťžČĎĚŇŘŠŤŽćłńśźżĆŁŃŚŹŻ\\s\\-\\.]+" title="Only letters (including ñ, á, é, í, ó, ú), spaces, hyphens, and periods allowed">
                                <small class="field-hint">Letters, spaces, hyphens, and periods allowed</small>
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
                    
                    // Name validation (letters, spaces, hyphens, periods, and accents)
                    const namePattern = /^[a-zA-ZàèìòùÀÈÌÒÙáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÄËÏÖÜāēīōūĀĒĪŌŪčďěňřšťžČĎĚŇŘŠŤŽćłńśźżĆŁŃŚŹŻ\s\-\.]+$/;
                    if (!namePattern.test(firstName)) {
                        Swal.showValidationMessage('First name contains invalid characters');
                        return false;
                    }
                    if (!namePattern.test(lastName)) {
                        Swal.showValidationMessage('Last name contains invalid characters');
                        return false;
                    }
                    if (middleName && !namePattern.test(middleName)) {
                        Swal.showValidationMessage('Middle name contains invalid characters');
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
                    Swal.fire('Deleted!', 'User deleted successfully!', 'success').then(() => {
                        location.reload();
                    });
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
    const displayAction = activate ? 'Active' : 'Inactive';
    Swal.fire({
        title: `Mark as ${displayAction}`,
        text: `Are you sure you want to mark ${userName} as ${displayAction.toLowerCase()}?`,
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
                    Swal.fire('Success!', data.message, 'success').then(() => {
                        // Update the user row with new data
                        if (data.user) {
                            updateUserRow(userId, data.user);
                        } else {
                            location.reload();
                        }
                    });
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

// Reactivate deactivated user
function reactivateDeactivatedUser(userId, userName) {
    Swal.fire({
        title: 'Reactivate Deactivated Account',
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
                    Swal.fire('Success!', data.message || 'User reactivated successfully', 'success').then(() => {
                        // Update the user row with new data
                        if (data.user) {
                            updateUserRow(userId, data.user);
                        } else {
                            location.reload();
                        }
                    });
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
                    Swal.fire('Success!', data.message || 'User restored successfully', 'success').then(() => {
                        // Update the user row with new data
                        if (data.user) {
                            updateUserRow(userId, data.user);
                        } else {
                            location.reload();
                        }
                    });
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

    // Prevent filter form from submitting traditionally
    const filterForm = document.getElementById('userFilterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Form changes are handled by individual field listeners
        });
    }

    // Setup "Clear All" button to use AJAX
    const clearAllBtn = document.querySelector('.btn-clear-all');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Clear all filter inputs
            const searchInput = document.getElementById('searchInput');
            const roleFilter = document.getElementById('roleFilter');
            const accountStatusFilter = document.getElementById('accountStatusFilter');
            const sortByFilter = document.getElementById('sortByFilter');
            
            if (searchInput) searchInput.value = '';
            if (roleFilter) roleFilter.value = '';
            if (accountStatusFilter) accountStatusFilter.value = '';
            if (sortByFilter) sortByFilter.value = 'newest';
            
            // Clear selections
            clearAllSelections();
            
            // Reload users with no filters
            loadUsers(1);
        });
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
    
    // Show loading indicator
    const tableContainer = document.querySelector('.users-table-container');
    const tbody = document.querySelector('.users-table tbody');
    
    if (tableContainer) {
        tableContainer.style.opacity = '0.6';
        tableContainer.style.pointerEvents = 'none';
    }
    
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading users...</td></tr>';
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
            updateUserCountDisplay(data.total);
            
            // Update URL without reload
            const newUrl = window.location.pathname + '?' + params.toString().replace('&ajax=1', '').replace('ajax=1&', '').replace('ajax=1', '');
            if (newUrl !== window.location.pathname + window.location.search) {
                window.history.pushState({}, '', newUrl || window.location.pathname);
            }
        }
    })
    .catch(error => {
        console.error('Error loading users:', error);
        Swal.fire('Error', 'Failed to load users', 'error');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Failed to load users</td></tr>';
        }
    })
    .finally(() => {
        if (tableContainer) {
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
        }
    });
}

// Render users table
function renderUsersTable(users) {
    const tbody = document.querySelector('.users-table tbody');
    if (!tbody) return;
    
    const currentUserId = parseInt(window.currentUserId);

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem;">No users found</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => {
        const roleName = user.role?.role_name || 'Unknown';
        const roleClass = getRoleClass(roleName);
        const isAdmin = roleName === 'Admin';
        const isDeleted = user.deleted_at !== null;
        const isSuspended = user.account_status === 'suspended';
        const isCurrent = user.user_id === currentUserId;
        const userName = `${user.first_name} ${user.last_name}`;
        
        return `
            <tr data-user-id="${user.user_id}" 
                data-user-name="${userName}"
                data-is-admin="${isAdmin ? '1' : '0'}"
                data-is-deleted="${isDeleted ? '1' : '0'}"
                data-is-suspended="${isSuspended ? '1' : '0'}"
                data-is-current="${isCurrent ? '1' : '0'}">
                <td class="checkbox-column">
                    ${!isCurrent && !isAdmin && !isDeleted && !isSuspended ? `
                        <input type="checkbox" class="user-checkbox" value="${user.user_id}" onchange="updateBulkActions()">
                    ` : ''}
                </td>
                <td>
                    <div class="user-info">
                        <div class="user-avatar">
                            ${getUserInitials(user.first_name, user.last_name)}
                        </div>
                        <div>
                            <div class="user-name">${userName}</div>
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
                    ${generateActionButtons(user)}
                </td>
            </tr>
        `;
    }).join('');
    
    // Clear any existing selections and reset select all checkbox
    clearAllSelections();
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

    // Calculate pagination info
    const currentPage = pagination.current_page;
    const perPage = pagination.per_page;
    const total = pagination.total;
    const firstItem = total === 0 ? 0 : ((currentPage - 1) * perPage + 1);
    const lastItem = Math.min(currentPage * perPage, total);
    
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
// ==================== BULK SELECTION & ACTIONS ====================

/**
 * Update user row in the table with new data
 */
function updateUserRow(userId, userData) {
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (!row) return;

    // Update data attributes
    row.setAttribute('data-is-deleted', userData.deleted_at ? '1' : '0');
    row.setAttribute('data-is-suspended', userData.account_status === 'suspended' ? '1' : '0');
    
    // Update status badge
    const statusCell = row.querySelector('td:nth-child(5)');
    if (statusCell) {
        statusCell.innerHTML = getStatusBadge(userData);
    }
    
    // Update action buttons
    const actionsCell = row.querySelector('td:nth-child(7)');
    if (actionsCell) {
        actionsCell.innerHTML = generateActionButtons(userData);
    }
    
    // Update checkbox visibility - remove checkbox if suspended or deleted
    const checkboxCell = row.querySelector('td.checkbox-column');
    if (checkboxCell) {
        const isAdmin = userData.role && userData.role.role_name === 'Admin';
        const isCurrent = userData.user_id === parseInt(window.currentUserId);
        const isDeleted = userData.deleted_at !== null;
        const isSuspended = userData.account_status === 'suspended';
        
        if (!isCurrent && !isAdmin && !isDeleted && !isSuspended) {
            checkboxCell.innerHTML = `<input type="checkbox" class="user-checkbox" value="${userData.user_id}" onchange="updateBulkActions()">`;
        } else {
            checkboxCell.innerHTML = '';
        }
    }
    
    // Add visual update animation
    row.classList.add('row-updated');
    setTimeout(() => {
        row.classList.remove('row-updated');
    }, 1500);
    
    // Update bulk actions bar if needed
    updateBulkActions();
}

/**
 * Generate action buttons HTML based on user data
 */
function generateActionButtons(user) {
    const currentUserId = parseInt(window.currentUserId);
    const isAdmin = user.role && user.role.role_name === 'Admin';
    const isDeleted = user.deleted_at !== null;
    const isCurrent = user.user_id === currentUserId;
    const userName = `${user.first_name} ${user.last_name}`;
    
    let buttonsHtml = '<div class="action-buttons">';
    
    // Edit button (not for deleted users)
    if (!isDeleted) {
        buttonsHtml += `
            <button class="action-btn edit" onclick="editUser(${user.user_id})">
                <i class="fas fa-edit"></i>
            </button>`;
    }
    
    // Status buttons (not for current user and admin users)
    if (!isCurrent && !isAdmin) {
        if (isDeleted) {
            // Restore button for deleted users
            buttonsHtml += `
                <button class="action-btn activate" onclick="restoreUser(${user.user_id}, '${userName}')" title="Restore User">
                    <i class="fas fa-undo"></i>
                </button>`;
        } else if (user.account_status === 'suspended') {
            // Reactivate button for suspended users
            buttonsHtml += `
                <button class="action-btn activate" onclick="reactivateDeactivatedUser(${user.user_id}, '${userName}')" title="Reactivate User">
                    <i class="fas fa-user-check"></i>
                </button>`;
        } else if (user.is_active) {
            // Inactive button for active users
            buttonsHtml += `
                <button class="action-btn deactivate" onclick="toggleUserStatus(${user.user_id}, false, '${userName}')">
                    <i class="fas fa-user-slash"></i>
                </button>`;
        } else {
            // Active button for inactive users
            buttonsHtml += `
                <button class="action-btn activate" onclick="toggleUserStatus(${user.user_id}, true, '${userName}')">
                    <i class="fas fa-user-check"></i>
                </button>`;
        }
    }
    
    // Delete button (not for admin users and not for deleted users)
    if (!isAdmin && !isDeleted) {
        buttonsHtml += `
            <button class="action-btn delete" onclick="deleteUser(${user.user_id}, '${userName}')">
                <i class="fas fa-trash"></i>
            </button>`;
    }
    
    buttonsHtml += '</div>';
    return buttonsHtml;
}

/**
 * Remove user rows from table (for deleted users)
 */
function removeUserRows(userIds) {
    userIds.forEach(userId => {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (row) {
            row.style.transition = 'opacity 0.3s ease';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
        }
    });
}

/**
 * Update user count display after filters
 */
function updateUserCountDisplay(totalCount) {
    const totalCountElement = document.getElementById('totalUserCount');
    if (totalCountElement) {
        totalCountElement.textContent = totalCount;
    }
}

/**
 * Update user count display after deletions
 */
function updateUserCount() {
    const visibleRows = document.querySelectorAll('.users-table tbody tr');
    const totalCountElement = document.getElementById('totalUserCount');
    
    if (totalCountElement) {
        const currentTotal = parseInt(totalCountElement.textContent);
        const visibleCount = visibleRows.length;
        
        // Calculate the difference (deleted rows)
        const totalRows = document.querySelectorAll('.users-table tbody tr').length;
        
        // Update the display
        totalCountElement.textContent = visibleCount;
    }
}

/**
 * Refresh user data from server
 */
function refreshUserData(userIds, callback) {
    if (!userIds || userIds.length === 0) {
        if (callback) callback([]);
        return;
    }
    
    // Fetch updated user data
    const promises = userIds.map(userId => 
        fetch(`${window.userUrlBase}/${userId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .catch(error => {
            console.error(`Error fetching user ${userId}:`, error);
            return null;
        })
    );
    
    Promise.all(promises).then(results => {
        const validUsers = results.filter(r => r && r.success).map(r => r.user);
        if (callback) callback(validUsers);
    });
}

/**
 * Toggle select all checkboxes
 */
function toggleSelectAll(checkbox) {
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    userCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

/**
 * Update bulk actions bar visibility and count
 */
function updateBulkActions() {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    const count = selectedCheckboxes.length;
    
    if (count > 0) {
        bulkActionsBar.style.display = 'flex';
        selectedCount.textContent = count;
    } else {
        bulkActionsBar.style.display = 'none';
        selectAllCheckbox.checked = false;
    }
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.user-checkbox');
    if (allCheckboxes.length > 0) {
        selectAllCheckbox.checked = count === allCheckboxes.length;
        selectAllCheckbox.indeterminate = count > 0 && count < allCheckboxes.length;
    }
}

/**
 * Clear all selections
 */
function clearAllSelections() {
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    userCheckboxes.forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

/**
 * Get selected user IDs and names
 */
function getSelectedUsers() {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const users = [];
    
    selectedCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        users.push({
            id: parseInt(checkbox.value),
            name: row.getAttribute('data-user-name'),
            isAdmin: row.getAttribute('data-is-admin') === '1',
            isDeleted: row.getAttribute('data-is-deleted') === '1',
            isSuspended: row.getAttribute('data-is-suspended') === '1',
            isCurrent: row.getAttribute('data-is-current') === '1'
        });
    });
    
    return users;
}

/**
 * Bulk activate users
 */
function bulkActivate() {
    const selectedUsers = getSelectedUsers();
    
    if (selectedUsers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Users Selected',
            text: 'Please select at least one user to activate.'
        });
        return;
    }
    
    // Filter out users that cannot be activated
    const validUsers = selectedUsers.filter(user => !user.isAdmin && !user.isCurrent && !user.isDeleted && !user.isSuspended);
    
    if (validUsers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Valid Users',
            text: 'None of the selected users can be activated (admin users, current user, deleted users, or suspended users are excluded).'
        });
        return;
    }
    
    const userNames = validUsers.map(u => u.name).join(', ');
    const count = validUsers.length;
    
    Swal.fire({
        title: 'Activate Users',
        html: `Are you sure you want to activate <strong>${count}</strong> user(s)?<br><br><small>${userNames}</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5cb85c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Activate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkAction('activate', validUsers.map(u => u.id));
        }
    });
}

/**
 * Bulk inactivate users
 */
function bulkInactivate() {
    const selectedUsers = getSelectedUsers();
    
    if (selectedUsers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Users Selected',
            text: 'Please select at least one user to inactivate.'
        });
        return;
    }
    
    // Filter out users that cannot be inactivated
    const validUsers = selectedUsers.filter(user => !user.isAdmin && !user.isCurrent && !user.isDeleted && !user.isSuspended);
    
    if (validUsers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Valid Users',
            text: 'None of the selected users can be inactivated (admin users, current user, deleted users, or already suspended users are excluded).'
        });
        return;
    }
    
    const userNames = validUsers.map(u => u.name).join(', ');
    const count = validUsers.length;
    
    Swal.fire({
        title: 'Inactivate Users',
        html: `Are you sure you want to inactivate <strong>${count}</strong> user(s)?<br><br><small>${userNames}</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Inactivate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkAction('deactivate', validUsers.map(u => u.id));
        }
    });
}

/**
 * Bulk delete users
 */
function bulkDelete() {
    const selectedUsers = getSelectedUsers();
    
    if (selectedUsers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Users Selected',
            text: 'Please select at least one user to delete.'
        });
        return;
    }
    
    // Filter out users that cannot be deleted
    const validUsers = selectedUsers.filter(user => !user.isAdmin && !user.isCurrent && !user.isDeleted && !user.isSuspended);
    
    if (validUsers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Valid Users',
            text: 'None of the selected users can be deleted (admin users, current user, already deleted users, or already suspended users are excluded).'
        });
        return;
    }
    
    const userNames = validUsers.map(u => u.name).join(', ');
    const count = validUsers.length;
    
    Swal.fire({
        title: 'Delete Users',
        html: `Are you sure you want to delete <strong>${count}</strong> user(s)?<br><br><small>${userNames}</small><br><br><em>Note: Nutritionist accounts will be suspended instead of deleted.</em>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkAction('delete', validUsers.map(u => u.id));
        }
    });
}

/**
 * Perform bulk action via AJAX
 */
function performBulkAction(action, userIds) {
    const actionUrls = {
        'activate': '/admin/users/bulk/activate',
        'deactivate': '/admin/users/bulk/deactivate',
        'delete': '/admin/users/bulk/delete',
        'restore': '/admin/users/bulk/restore'
    };
    
    const actionMessages = {
        'activate': 'Activating users...',
        'deactivate': 'Inactivating users...',
        'delete': 'Deleting users...',
        'restore': 'Restoring users...'
    };
    
    Swal.fire({
        title: actionMessages[action],
        html: 'Please wait...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(actionUrls[action], {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            user_ids: userIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const results = data.results;
            const successCount = results.success.length;
            const failedCount = results.failed.length;
            const skippedCount = results.skipped.length;
            
            let resultHtml = '';
            
            if (successCount > 0) {
                resultHtml += `<div class="bulk-result-section success-section">
                    <strong>✓ Success (${successCount}):</strong><br>
                    <small>${results.success.map(u => u.name).join(', ')}</small>
                </div>`;
            }
            
            if (skippedCount > 0) {
                resultHtml += `<div class="bulk-result-section warning-section">
                    <strong>⚠ Skipped (${skippedCount}):</strong><br>
                    <small>${results.skipped.map(u => `${u.name}: ${u.reason}`).join('<br>')}</small>
                </div>`;
            }
            
            if (failedCount > 0) {
                resultHtml += `<div class="bulk-result-section error-section">
                    <strong>✗ Failed (${failedCount}):</strong><br>
                    <small>${results.failed.map(u => u.reason).join('<br>')}</small>
                </div>`;
            }
            
            Swal.fire({
                icon: successCount > 0 ? 'success' : 'warning',
                title: 'Bulk Action Completed',
                html: resultHtml,
                confirmButtonColor: '#5cb85c'
            }).then(() => {
                if (action === 'delete') {
                    // Reload page for delete actions
                    location.reload();
                } else {
                    // For activate/deactivate, update UI dynamically
                    clearAllSelections();
                    
                    if (successCount > 0) {
                        const successUserIds = results.success.map(u => u.user_id);
                        refreshUserData(successUserIds, (users) => {
                            users.forEach(user => {
                                updateUserRow(user.user_id, user);
                            });
                        });
                    }
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Bulk action failed'
            });
        }
    })
    .catch(error => {
        console.error('Bulk action error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while performing bulk action'
        });
    });
}