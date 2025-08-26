let currentUserId = null;

// Modal functions
function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'block';
    document.getElementById('addUserForm').reset();
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = ['addUserModal', 'editUserModal', 'deleteUserModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
}

// Add User Form Submission
if (document.getElementById('addUserForm')) {
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const addUserRoute = this.getAttribute('data-route');
        fetch(addUserRoute, {
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
                showAlert('User created successfully!', 'success');
                closeModal('addUserModal');
                location.reload();
            } else {
                showAlert(data.message || 'Failed to create user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while creating the user', 'error');
        });
    });
}

// Edit User Function
function editUser(userId) {
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
            document.getElementById('edit_user_id').value = user.user_id;
            document.getElementById('edit_first_name').value = user.first_name;
            document.getElementById('edit_middle_name').value = user.middle_name || '';
            document.getElementById('edit_last_name').value = user.last_name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_contact_number').value = user.contact_number || '';
            document.getElementById('edit_role_id').value = user.role_id;
            document.getElementById('edit_is_active').checked = user.is_active;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';
            document.getElementById('editUserModal').style.display = 'block';
        } else {
            showAlert('Failed to load user data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while loading user data', 'error');
    });
}

// Edit User Form Submission
if (document.getElementById('editUserForm')) {
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
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
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('User updated successfully!', 'success');
                closeModal('editUserModal');
                location.reload();
            } else {
                showAlert(data.message || 'Failed to update user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while updating the user', 'error');
        });
    });
}

// Delete User Function
function deleteUser(userId, userName) {
    currentUserId = userId;
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteUserModal').style.display = 'block';
}

// Confirm Delete User
if (document.getElementById('confirmDeleteUser')) {
    document.getElementById('confirmDeleteUser').addEventListener('click', function() {
        if (!currentUserId) return;
        const userUrlBase = document.getElementById('addUserForm')?.getAttribute('data-user-url-base');
        fetch(`${userUrlBase}/${currentUserId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('User deleted successfully!', 'success');
                closeModal('deleteUserModal');
                location.reload();
            } else {
                showAlert(data.message || 'Failed to delete user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while deleting the user', 'error');
        });
    });
}

// Toggle User Status Function
function toggleUserStatus(userId, activate, userName) {
    const action = activate ? 'activate' : 'deactivate';
    const actionText = activate ? 'activate' : 'deactivate';
    const userUrlBase = document.getElementById('addUserForm')?.getAttribute('data-user-url-base');
    if (confirm(`Are you sure you want to ${actionText} ${userName}?`)) {
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
                showAlert(data.message, 'success');
                location.reload();
            } else {
                showAlert(data.message || `Failed to ${actionText} user`, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert(`An error occurred while trying to ${actionText} the user`, 'error');
        });
    }
}

// Alert function
function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.style.position = 'fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.padding = '15px 20px';
    alert.style.borderRadius = '5px';
    alert.style.zIndex = '10000';
    alert.style.color = 'white';
    alert.style.fontWeight = 'bold';
    if (type === 'success') {
        alert.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        alert.style.backgroundColor = '#dc3545';
    }
    document.body.appendChild(alert);
    setTimeout(() => {
        document.body.removeChild(alert);
    }, 3000);
}
