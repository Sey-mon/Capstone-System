@extends('layouts.dashboard')

@section('title', 'Users Management')

@section('page-title', 'Users Management')
@section('page-subtitle', 'Manage all system users and their roles.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-users.css') }}">
@endpush

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">All Users</h3>
            <button class="btn btn-primary" onclick="openAddUserModal()">
                <i class="fas fa-user-plus"></i>
                Add New User
            </button>
        </div>
        <div class="card-content">
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="user-id">#{{ $user->user_id }}</td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $user->first_name }} {{ $user->last_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="user-email">{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleName = $user->role->role_name ?? 'Unknown';
                                    $roleClass = match($roleName) {
                                        'Admin' => 'role-admin',
                                        'Nutritionist' => 'role-nutritionist',
                                        'Parent' => 'role-parent',
                                        default => 'role-unknown'
                                    };
                                @endphp
                                <span class="role-badge {{ $roleClass }}">
                                    {{ $roleName }}
                                </span>
                            </td>
                            <td class="user-contact">{{ $user->contact_number ?? 'N/A' }}</td>
                            <td class="user-created">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit" onclick="editUser({{ $user->user_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="deleteUser({{ $user->user_id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($users, 'links'))
            <div class="pagination-container">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <span class="close" onclick="closeModal('addUserModal')">&times;</span>
            </div>
            <form id="addUserForm">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_first_name">First Name *</label>
                        <input type="text" id="add_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_middle_name">Middle Name</label>
                        <input type="text" id="add_middle_name" name="middle_name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_last_name">Last Name *</label>
                        <input type="text" id="add_last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_role_id">Role *</label>
                        <select id="add_role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_email">Email *</label>
                        <input type="email" id="add_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="add_contact_number">Contact Number</label>
                        <input type="text" id="add_contact_number" name="contact_number">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_password">Password *</label>
                        <input type="password" id="add_password" name="password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="add_password_confirmation">Confirm Password *</label>
                        <input type="password" id="add_password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            </div>
            <form id="editUserForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_first_name">First Name *</label>
                        <input type="text" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_middle_name">Middle Name</label>
                        <input type="text" id="edit_middle_name" name="middle_name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_last_name">Last Name *</label>
                        <input type="text" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_role_id">Role *</label>
                        <select id="edit_role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email *</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_contact_number">Contact Number</label>
                        <input type="text" id="edit_contact_number" name="contact_number">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_password">New Password (leave blank to keep current)</label>
                        <input type="password" id="edit_password" name="password" minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="edit_password_confirmation">Confirm New Password</label>
                        <input type="password" id="edit_password_confirmation" name="password_confirmation">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close" onclick="closeModal('deleteUserModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user <strong id="deleteUserName"></strong>?</p>
                <p class="warning-text">This action cannot be undone.</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteUserModal')">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUser">Delete</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
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
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.users.store") }}', {
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
            location.reload(); // Refresh the page to show new user
        } else {
            showAlert(data.message || 'Failed to create user', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while creating the user', 'error');
    });
});

// Edit User Function
function editUser(userId) {
    fetch(`{{ url('admin/users') }}/${userId}`, {
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
            
            // Clear password fields
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
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const userId = document.getElementById('edit_user_id').value;
    
    // Add the method override for PUT request
    formData.append('_method', 'PUT');
    
    fetch(`{{ url('admin/users') }}/${userId}`, {
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
            location.reload(); // Refresh the page to show updated data
        } else {
            showAlert(data.message || 'Failed to update user', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating the user', 'error');
    });
});

// Delete User Function
function deleteUser(userId, userName) {
    currentUserId = userId;
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteUserModal').style.display = 'block';
}

// Confirm Delete User
document.getElementById('confirmDeleteUser').addEventListener('click', function() {
    if (!currentUserId) return;
    
    fetch(`{{ url('admin/users') }}/${currentUserId}`, {
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
            location.reload(); // Refresh the page to remove deleted user
        } else {
            showAlert(data.message || 'Failed to delete user', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while deleting the user', 'error');
    });
});

// Alert function
function showAlert(message, type) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    // Style the alert
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
    
    // Remove alert after 3 seconds
    setTimeout(() => {
        document.body.removeChild(alert);
    }, 3000);
}
</script>
@endpush
