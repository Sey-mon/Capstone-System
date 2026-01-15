// Admin Users Management JavaScript

function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
}

function editUser(userId) {
    // First fetch the user data
    const userUrlBase = document.getElementById('addUserForm')?.getAttribute('data-user-url-base');
    fetch(`${userUrlBase}/${userId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            // Fill the form with user data
            document.getElementById('edit_user_id').value = user.user_id;
            document.getElementById('edit_first_name').value = user.first_name;
            document.getElementById('edit_middle_name').value = user.middle_name || '';
            document.getElementById('edit_last_name').value = user.last_name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_contact_number').value = user.contact_number || '';
            document.getElementById('edit_role_id').value = user.role_id;
            document.getElementById('edit_is_active').checked = user.is_active ? true : false;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';
            
            // Show the modal
            const modal = document.getElementById('editUserModal');
            modal.classList.add('show');
        } else {
            alert('Failed to load user data');
        }
    })
    .catch(error => {
        alert('An error occurred while loading user data');
    });
}

function deleteUser(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    // Store the user ID in the confirm button for later use
    document.getElementById('confirmDeleteUser').dataset.userId = userId;
    const modal = document.getElementById('deleteUserModal');
    modal.classList.add('show');
}

function toggleUserStatus(userId, activate, userName) {
    const action = activate ? 'activate' : 'deactivate';
    const userUrlBase = document.getElementById('addUserForm')?.getAttribute('data-user-url-base');
    if (confirm(`Are you sure you want to ${action} ${userName}?`)) {
        fetch(`${userUrlBase}/${userId}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || `Failed to ${action} user`);
            }
        })
        .catch(error => {
            alert('An error occurred while updating user status');
        });
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Handle add user form submission
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const route = this.getAttribute('data-route');
            
            fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('User created successfully!');
                    closeModal('addUserModal');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to create user');
                }
            })
            .catch(error => {
                if (error.errors) {
                    let errorMsg = 'Validation errors:\n';
                    Object.keys(error.errors).forEach(key => {
                        errorMsg += `${key}: ${error.errors[key].join(', ')}\n`;
                    });
                    alert(errorMsg);
                } else {
                    alert(error.message || 'An error occurred while creating the user');
                }
            });
        });
    }

    // Handle edit user form submission
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const userId = document.getElementById('edit_user_id').value;
            const userUrlBase = document.getElementById('addUserForm')?.getAttribute('data-user-url-base');
            formData.append('_method', 'PUT');
            
            fetch(`${userUrlBase}/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('User updated successfully!');
                    closeModal('editUserModal');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update user');
                }
            })
            .catch(error => {
                if (error.errors) {
                    let errorMsg = 'Validation errors:\n';
                    Object.keys(error.errors).forEach(key => {
                        errorMsg += `${key}: ${error.errors[key].join(', ')}\n`;
                    });
                    alert(errorMsg);
                } else {
                    alert(error.message || 'An error occurred while updating the user');
                }
            });
        });
    }
    
    // Handle delete user confirmation
    const confirmDeleteBtn = document.getElementById('confirmDeleteUser');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userUrlBase = document.getElementById('addUserForm')?.getAttribute('data-user-url-base');
            
            if (!userId) {
                alert('Error: User ID not found');
                return;
            }
            
            fetch(`${userUrlBase}/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully!');
                    closeModal('deleteUserModal');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete user');
                }
            })
            .catch(error => {
                if (error.errors) {
                    let errorMsg = 'Validation errors:\n';
                    Object.keys(error.errors).forEach(key => {
                        errorMsg += `${key}: ${error.errors[key].join(', ')}\n`;
                    });
                    alert(errorMsg);
                } else {
                    alert(error.message || 'An error occurred while deleting the user');
                }
            });
        });
    }
});
