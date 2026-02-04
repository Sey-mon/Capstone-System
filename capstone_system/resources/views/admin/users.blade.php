@extends('layouts.dashboard')

@section('title', 'Users Management')

@section('page-title', 'Users Management')
@section('page-subtitle', 'Manage all system users and their roles.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-users.css?v=' . filemtime(public_path('css/admin/admin-users.css'))) }}">
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
                        <label>Search User</label>
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control search-input" placeholder="Search by name" id="searchInput">
                        </div>
                    </div>
                    <div class="filter-field">
                        <label>Role</label>
                        <select name="role" class="form-control" id="roleFilter">
                            <option value="" disabled selected hidden>All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}" @if(request('role') == $role->role_id) selected @endif>{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Status</label>
                        <select name="account_status" class="form-control" id="accountStatusFilter">
                            <option value="" disabled selected hidden>All Status</option>
                            <option value="pending" @if(request('account_status')==='pending') selected @endif>Pending</option>
                            <option value="active" @if(request('account_status')==='active') selected @endif>Active</option>
                            <option value="suspended" @if(request('account_status')==='suspended') selected @endif>Suspended</option>
                            <option value="rejected" @if(request('account_status')==='rejected') selected @endif>Rejected</option>
                            <option value="deleted" @if(request('account_status')==='deleted') selected @endif>Deleted</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Sort By</label>
                        <select name="sort_by" class="form-control" id="sortByFilter">
                            <option value="" disabled selected hidden>Sort By</option>
                            <option value="newest" @if(request('sort_by')=='newest' || !request('sort_by')) selected @endif>Newest First</option>
                            <option value="oldest" @if(request('sort_by')=='oldest') selected @endif>Oldest First</option>
                            <option value="name_asc" @if(request('sort_by')=='name_asc') selected @endif>Name (A-Z)</option>
                            <option value="name_desc" @if(request('sort_by')=='name_desc') selected @endif>Name (Z-A)</option>
                            <option value="email_asc" @if(request('sort_by')=='email_asc') selected @endif>Email (A-Z)</option>
                            <option value="email_desc" @if(request('sort_by')=='email_desc') selected @endif>Email (Z-A)</option>
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
                                @php
                                    // Determine account status - prioritize deleted_at, then account_status field
                                    if ($user->deleted_at) {
                                        $status = 'deleted';
                                    } elseif ($user->account_status === 'pending') {
                                        $status = 'pending';
                                    } elseif ($user->account_status === 'suspended') {
                                        $status = 'suspended';
                                    } elseif ($user->account_status === 'rejected') {
                                        $status = 'rejected';
                                    } elseif ($user->account_status === 'active') {
                                        $status = 'active';
                                    } elseif ($user->is_active) {
                                        // Legacy: no account_status set, use is_active flag
                                        $status = 'active';
                                    } else {
                                        // Legacy: is_active is false
                                        $status = 'inactive';
                                    }
                                @endphp

                                @if($status === 'deleted')
                                    <span class="status-badge status-deleted" style="background-color: #6b7280; color: white;">
                                        <i class="fas fa-trash"></i>
                                        Deleted
                                    </span>
                                @elseif($status === 'pending')
                                    <span class="status-badge status-pending" style="background-color: #3b82f6; color: white;">
                                        <i class="fas fa-clock"></i>
                                        Pending
                                    </span>
                                @elseif($status === 'suspended')
                                    <span class="status-badge status-suspended" style="background-color: #f59e0b; color: white;">
                                        <i class="fas fa-ban"></i>
                                        Suspended
                                    </span>
                                @elseif($status === 'rejected')
                                    <span class="status-badge status-rejected" style="background-color: #ef4444; color: white;">
                                        <i class="fas fa-times-circle"></i>
                                        Rejected
                                    </span>
                                @elseif($status === 'active')
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
                                    @if(!$user->deleted_at)
                                        <button class="action-btn edit" onclick="editUser({{ $user->user_id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif
                                    @if($user->user_id !== Auth::id() && ($user->role->role_name ?? '') !== 'Admin')
                                        @if($user->deleted_at)
                                            <button class="action-btn activate" onclick="restoreUser({{ $user->user_id }}, '{{ $user->first_name }} {{ $user->last_name }}')" title="Restore User">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @elseif($user->account_status === 'suspended')
                                            <button class="action-btn activate" onclick="reactivateSuspendedUser({{ $user->user_id }}, '{{ $user->first_name }} {{ $user->last_name }}')" title="Reactivate User">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        @elseif($user->is_active)
                                            <button class="action-btn deactivate" onclick="toggleUserStatus({{ $user->user_id }}, false, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        @else
                                            <button class="action-btn activate" onclick="toggleUserStatus({{ $user->user_id }}, true, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        @endif
                                    @endif
                                    @if(($user->role->role_name ?? '') !== 'Admin' && !$user->deleted_at)
                                        <button class="action-btn delete" onclick="deleteUser({{ $user->user_id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($users, 'links'))
            <div class="pagination-container">
                <div class="pagination-info-text">
                    Showing <strong>{{ $users->firstItem() ?? 0 }}</strong> to <strong>{{ $users->lastItem() ?? 0 }}</strong> of <strong>{{ $users->total() }}</strong> users
                </div>
                <div class="pagination-links">
                    {{ $users->appends(request()->query())->links() }}
                </div>
                <div class="pagination-jump-section">
                    <label>Go to page:</label>
                    <input type="number" id="pageJump" min="1" max="{{ $users->lastPage() }}" value="{{ $users->currentPage() }}" class="page-jump-input">
                    <button class="btn-page-jump" id="jumpToPage">Go</button>
                </div>
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
    <script src="{{ asset('js/admin/admin-users.js?v=' . filemtime(public_path('js/admin/admin-users.js'))) }}"></script>
@endpush
