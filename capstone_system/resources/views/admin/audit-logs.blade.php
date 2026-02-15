@extends('layouts.dashboard')

@section('title', 'Audit Logs')

@section('page-title', 'Audit Logs')
@section('page-subtitle', 'Comprehensive system activity tracking including inventory management, user actions, and data changes.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-audit-logs.css') }}?v={{ filemtime(public_path('css/admin/admin-audit-logs.css')) }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')

    <!-- Filter Section -->
    <div class="filter-container">
        <div class="filter-header-bar">
            <h3><i class="fas fa-filter"></i> Filters & Search</h3>
            <div class="filter-header-actions">
                <button type="button" class="btn-clear-all" id="clearAllBtn" onclick="clearAllFilters()">
                    <i class="fas fa-times"></i> Clear All
                </button>
            </div>
        </div>
        
        <div class="filter-content">
            <form method="GET" action="{{ route('admin.audit.logs') }}" id="filtersForm">
                <div class="filter-grid" style="grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr;">
                    <!-- Search Field -->
                    <div class="filter-field">
                        <label for="search_filter">Search Description</label>
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   name="search" 
                                   id="search_filter" 
                                   class="form-control search-input auto-filter" 
                                   placeholder="Search by description..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    <!-- Action Filter -->
                    <div class="filter-field">
                        <label for="action_filter">Action</label>
                        <select name="action" id="action_filter" class="form-control auto-filter">
                            <option value="" disabled selected hidden>All Actions</option>
                            <optgroup label="User Actions">
                                <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                                <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                            </optgroup>
                            <optgroup label="Data Management">
                                <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                                <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                                <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                            </optgroup>
                            <optgroup label="Inventory Management">
                                <option value="stock_in" {{ request('action') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
                                <option value="stock_out" {{ request('action') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                                <option value="inventory_create" {{ request('action') == 'inventory_create' ? 'selected' : '' }}>Inventory Item Created</option>
                                <option value="inventory_update" {{ request('action') == 'inventory_update' ? 'selected' : '' }}>Inventory Item Updated</option>
                                <option value="inventory_delete" {{ request('action') == 'inventory_delete' ? 'selected' : '' }}>Inventory Item Deleted</option>
                            </optgroup>
                            <optgroup label="Patient Management">
                                <option value="patient_create" {{ request('action') == 'patient_create' ? 'selected' : '' }}>Patient Created</option>
                                <option value="patient_update" {{ request('action') == 'patient_update' ? 'selected' : '' }}>Patient Updated</option>
                                <option value="patient_delete" {{ request('action') == 'patient_delete' ? 'selected' : '' }}>Patient Deleted</option>
                                <option value="assessment_create" {{ request('action') == 'assessment_create' ? 'selected' : '' }}>Assessment Created</option>
                                <option value="assessment_update" {{ request('action') == 'assessment_update' ? 'selected' : '' }}>Assessment Updated</option>
                                <option value="assessment_complete" {{ request('action') == 'assessment_complete' ? 'selected' : '' }}>Assessment Completed</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <!-- User Filter -->
                    <div class="filter-field">
                        <label for="user_filter">User</label>
                        <select name="user" id="user_filter" class="form-control auto-filter">
                            <option value="" disabled selected hidden>All Users</option>
                            @if(isset($users))
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" {{ request('user') == $user->user_id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <!-- Date From -->
                    <div class="filter-field">
                        <label for="date_from">Date From</label>
                        <input type="date" 
                               name="date_from" 
                               id="date_from" 
                               class="form-control auto-filter" 
                               value="{{ request('date_from') }}">
                    </div>
                    
                    <!-- Date To -->
                    <div class="filter-field">
                        <label for="date_to">Date To</label>
                        <input type="date" 
                               name="date_to" 
                               id="date_to" 
                               class="form-control auto-filter" 
                               value="{{ request('date_to') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="content-card">
        <div class="card-content">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <div class="user-info-cell">
                                            @if($log->user)
                                                <div class="user-avatar-small">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="user-details-small">
                                                    <div class="user-name">{{ $log->user->first_name }} {{ $log->user->last_name }}</div>
                                                    <div class="user-role">{{ $log->user->role->role_name ?? 'Unknown' }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $actionClass = match(strtolower($log->action)) {
                                                'create', 'insert', 'inventory_create', 'patient_create', 'assessment_create' => 'success',
                                                'update', 'edit', 'inventory_update', 'patient_update', 'assessment_update', 'assessment_complete' => 'warning',
                                                'delete', 'remove', 'inventory_delete', 'patient_delete' => 'danger',
                                                'stock_in' => 'success',
                                                'stock_out' => 'info',
                                                'login' => 'info',
                                                'logout' => 'secondary',
                                                default => 'primary'
                                            };
                                            
                                            $actionDisplay = match(strtolower($log->action)) {
                                                'stock_in' => 'Stock In',
                                                'stock_out' => 'Stock Out',
                                                'inventory_create' => 'Inventory Created',
                                                'inventory_update' => 'Inventory Updated', 
                                                'inventory_delete' => 'Inventory Deleted',
                                                'patient_create' => 'Patient Created',
                                                'patient_update' => 'Patient Updated',
                                                'patient_delete' => 'Patient Deleted',
                                                'assessment_create' => 'Assessment Created',
                                                'assessment_update' => 'Assessment Updated',
                                                'assessment_complete' => 'Assessment Completed',
                                                default => ucfirst($log->action)
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $actionClass }}">{{ $actionDisplay }}</span>
                                    </td>
                                    <td>
                                        <div class="description-cell">
                                            {{ $log->description ?? $log->action }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="timestamp-cell">
                                            <div class="timestamp">{{ $log->log_timestamp ? $log->log_timestamp->format('M d, Y') : 'N/A' }}</div>
                                            <div class="time">{{ $log->log_timestamp ? $log->log_timestamp->format('h:i A') : 'N/A' }}</div>
                                            <small class="text-muted">{{ $log->log_timestamp ? $log->log_timestamp->diffForHumans() : 'N/A' }}</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h3 class="empty-state-title">No Audit Logs Found</h3>
                    <p class="empty-state-description">
                        No audit logs have been recorded yet. System activities will appear here once they start being logged.
                    </p>
                </div>
            @endif
        </div>
    </div>

@push('scripts')
<script src="{{ asset('js/admin/admin-audit-logs.js') }}?v={{ filemtime(public_path('js/admin/admin-audit-logs.js')) }}"></script>
@endpush
@endsection


