@extends('layouts.dashboard')

@section('title', 'Users Management')

@section('page-title', 'Users Management')
@section('page-subtitle', 'Manage all system users and their roles.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-users.css') }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
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
            <div class="filter-bar">
                <form method="GET" action="" id="userFilterForm">
                    <div class="filter-input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, email, contact..." oninput="document.getElementById('userFilterForm').submit();">
                    </div>
                    <div class="filter-select-group">
                        <select name="role" class="form-control" onchange="document.getElementById('userFilterForm').submit();">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}" @if(request('role') == $role->role_id) selected @endif>{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-select-group">
                        <select name="status" class="form-control" onchange="document.getElementById('userFilterForm').submit();">
                            <option value="">All Status</option>
                            <option value="1" @if(request('status')==='1') selected @endif>Active</option>
                            <option value="0" @if(request('status')==='0') selected @endif>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-light">&#10006; Clear Filter</a>
                    </div>
                </form>
            </div>
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
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
                            <td>
                                @if($user->is_active)
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check-circle"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-times-circle"></i>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="user-created">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit" onclick="editUser({{ $user->user_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($user->user_id !== Auth::id())
                                        @if($user->is_active)
                                            <button class="action-btn deactivate" onclick="toggleUserStatus({{ $user->user_id }}, false, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        @else
                                            <button class="action-btn activate" onclick="toggleUserStatus({{ $user->user_id }}, true, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        @endif
                                    @endif
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
            <form id="addUserForm" data-route="{{ route('admin.users.store') }}" data-user-url-base="{{ url('admin/users') }}">
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
                <input type="hidden" name="is_active" value="1">
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
                <div class="form-row">
                    <div class="form-group">
                        <label class="checkbox-label-flex">
                            <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <span>Active Account</span>
                        </label>
                        <small class="help-text-small">
                            For staff members (Nutritionist, Health Worker, BHW): Activating will also verify their email automatically.
                        </small>
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
    <script src="{{ asset('js/admin/admin-users.js') }}"></script>
@endpush
