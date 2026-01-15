@extends('layouts.dashboard')

@section('title', 'Users Management')

@section('page-title', 'Users Management')
@section('page-subtitle', 'Manage all system users and their roles.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-users.css') }}">
    <meta name="user-id" content="{{ Auth::id() }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Filter Section -->
    <div class="filter-container">
        <div class="filter-header-bar">
            <h3><i class="fas fa-filter"></i> Filters & Search</h3>
            <a href="{{ route('admin.users') }}" class="btn-clear-all">
                <i class="fas fa-times"></i> Clear All
            </a>
        </div>
        <div class="filter-content">
            <form method="GET" action="" id="userFilterForm">
                <div class="filter-grid">
                    <div class="filter-field">
                        <label>Search Patient</label>
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control search-input" placeholder="Search by name, contact..." id="searchInput">
                        </div>
                    </div>
                    <div class="filter-field">
                        <label>Role</label>
                        <select name="role" class="form-control" id="roleFilter">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}" @if(request('role') == $role->role_id) selected @endif>{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Status</label>
                        <select name="status" class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1" @if(request('status')==='1') selected @endif>Active</option>
                            <option value="0" @if(request('status')==='0') selected @endif>Inactive</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Per Page</label>
                        <select name="per_page" class="form-control" id="perPageFilter">
                            <option value="10" @if(request('per_page')=='10') selected @endif>10</option>
                            <option value="25" @if(request('per_page')=='25') selected @endif>25</option>
                            <option value="50" @if(request('per_page')=='50') selected @endif>50</option>
                            <option value="100" @if(request('per_page')=='100') selected @endif>100</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Content Card -->
    <div class="content-card">
        <div class="card-header-modern">
            <div class="header-title-section">
                <div class="title-with-icon">
                    <i class="fas fa-users"></i>
                    <h3 class="card-title-modern">Users Management</h3>
                </div>
                <p class="card-subtitle">Manage and organize all system users and their roles</p>
            </div>
            <div class="header-actions">
                <button class="btn-count">
                    <i class="fas fa-user"></i> {{ $users->total() }} users
                </button>
                <button class="btn btn-primary" onclick="openAddUserModal()">
                    <i class="fas fa-plus"></i>
                    Add New User
                </button>
            </div>
        </div>
        <div class="card-content">
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

    <!-- Hidden data for JavaScript -->
    <div id="userData" 
         data-route="{{ route('admin.users.store') }}" 
         data-user-url-base="{{ url('admin/users') }}"
         style="display:none;">
    </div>

@endsection

@push('scripts')
    <script>
        // Pass roles data to JavaScript
        window.rolesData = @json($roles);
    </script>
    <script src="{{ asset('js/admin/admin-users.js') }}"></script>
@endpush
