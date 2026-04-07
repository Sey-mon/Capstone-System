@extends('layouts.dashboard')

@section('title', 'System Management')

@section('page-title', 'System Management')
@section('page-subtitle', 'Manage system categories and barangays for your application.')

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="@assetv('css/admin/system-management-modals.css')">
    <link rel="stylesheet" href="@assetv('css/admin/system-management-modern.css')">
@endpush

@push('scripts')
    <script src="@assetv('js/admin/system-management.js')"></script>
@endpush

@section('content')
    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card-modern">
            <div class="stat-header-modern">
                <div>
                    <div class="stat-title-modern">Total Categories</div>
                    <div class="stat-value-modern">{{ $categories->total() }}</div>
                </div>
                <div class="stat-icon-modern success">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card-modern warning">
            <div class="stat-header-modern">
                <div>
                    <div class="stat-title-modern">Total Barangays</div>
                    <div class="stat-value-modern">{{ $barangays->total() }}</div>
                </div>
                <div class="stat-icon-modern warning">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card-modern danger">
            <div class="stat-header-modern">
                <div>
                    <div class="stat-title-modern">Categories with Items</div>
                    <div class="stat-value-modern">{{ $categories->where('inventory_items_count', '>', 0)->count() }}</div>
                </div>
                <div class="stat-icon-modern danger">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="stat-card-modern info">
            <div class="stat-header-modern">
                <div>
                    <div class="stat-title-modern">Barangays with Patients</div>
                    <div class="stat-value-modern">{{ $barangays->where('patients_count', '>', 0)->count() }}</div>
                </div>
                <div class="stat-icon-modern info">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Tabs -->
    <div class="content-card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="tab-button active" data-tab="system-health" id="system-health-tab">
                    <i class="fas fa-heartbeat"></i>
                    System Health
                </button>
                <button class="tab-button" data-tab="categories" id="categories-tab">
                    <i class="fas fa-tags"></i>
                    Item Categories
                </button>
                <button class="tab-button" data-tab="barangays" id="barangays-tab">
                    <i class="fas fa-map-marker-alt"></i>
                    Barangays
                </button>
            </div>
        </div>

        <!-- System Health Tab -->
        <div id="system-health-content" class="tab-content active">
            <div class="card-header" style="border-top: 1px solid var(--border-light); margin-top: 1rem; padding-top: 1rem;">
                <div>
                    <h3 class="card-title">System Health & Statistics</h3>
                    <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;" id="lastUpdated">
                        Last updated: <span id="lastUpdatedTime">{{ now()->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="refreshSystemHealth()" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>

            <div style="margin-top: 1.5rem; padding: 0 1.5rem 1.5rem;">
                <!-- System Overview -->
                <div style="margin-bottom: 2rem;">
                    <h4 style="color: #374151; margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">
                        <i class="fas fa-chart-line" style="color: #5cb85c;"></i> System Overview
                    </h4>
                    <div class="stats-grid" style="margin-top: 1rem;">
                        <div class="stat-card-modern">
                            <div class="stat-header-modern">
                                <div>
                                    <div class="stat-title-modern">Total Users</div>
                                    <div class="stat-value-modern">{{ $systemHealth['total_users'] }}</div>
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                                        {{ $systemHealth['active_users'] }} active
                                    </div>
                                </div>
                                <div class="stat-icon-modern success">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card-modern warning">
                            <div class="stat-header-modern">
                                <div>
                                    <div class="stat-title-modern">Total Patients</div>
                                    <div class="stat-value-modern">{{ $systemHealth['total_patients'] }}</div>
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                                        Registered patients
                                    </div>
                                </div>
                                <div class="stat-icon-modern warning">
                                    <i class="fas fa-user-injured"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card-modern danger">
                            <div class="stat-header-modern">
                                <div>
                                    <div class="stat-title-modern">Inventory Items</div>
                                    <div class="stat-value-modern">{{ $systemHealth['total_inventory_items'] }}</div>
                                    <div style="font-size: 0.875rem; color: #ff9800; margin-top: 0.25rem;">
                                        <i class="fas fa-exclamation-triangle"></i> {{ $systemHealth['low_stock_items'] }} low stock
                                    </div>
                                </div>
                                <div class="stat-icon-modern danger">
                                    <i class="fas fa-boxes"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card-modern info">
                            <div class="stat-header-modern">
                                <div>
                                    <div class="stat-title-modern">Total Assessments</div>
                                    <div class="stat-value-modern">{{ $systemHealth['total_assessments'] }}</div>
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                                        Patient assessments
                                    </div>
                                </div>
                                <div class="stat-icon-modern info">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div style="margin-bottom: 2rem;">
                    <h4 style="color: #374151; margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">
                        <i class="fas fa-server" style="color: #17a2b8;"></i> System Information
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                        <div style="background: #f9fafb; padding: 1.25rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Database Size</div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">{{ $systemHealth['database_size'] }} MB</div>
                                </div>
                                <i class="fas fa-database" style="font-size: 2rem; color: #5cb85c; opacity: 0.3;"></i>
                            </div>
                        </div>
                        
                        <div style="background: #f9fafb; padding: 1.25rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Laravel Version</div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">{{ $systemHealth['laravel_version'] }}</div>
                                </div>
                                <i class="fab fa-laravel" style="font-size: 2rem; color: #ff2d20; opacity: 0.3;"></i>
                            </div>
                        </div>
                        
                        <div style="background: #f9fafb; padding: 1.25rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">PHP Version</div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">{{ $systemHealth['php_version'] }}</div>
                                </div>
                                <i class="fab fa-php" style="font-size: 2rem; color: #777bb4; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div>
                    <h4 style="color: #374151; margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">
                        <i class="fas fa-history" style="color: #ff9800;"></i> Recent Activity
                    </h4>
                    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($systemHealth['recent_activity'] as $log)
                                <tr>
                                    <td style="font-weight: 500;">
                                        @if($log->user)
                                            {{ $log->user->first_name }} {{ $log->user->last_name }}
                                        @else
                                            <span style="color: #6b7280;">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge 
                                            @if(str_contains(strtolower($log->action), 'create') || str_contains(strtolower($log->action), 'add')) success
                                            @elseif(str_contains(strtolower($log->action), 'delete')) danger
                                            @elseif(str_contains(strtolower($log->action), 'update') || str_contains(strtolower($log->action), 'edit')) warning
                                            @else info
                                            @endif">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td style="color: #6b7280; font-size: 0.875rem;">{{ Str::limit($log->description, 50) }}</td>
                                    <td style="color: #6b7280; font-size: 0.875rem;">{{ $log->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem; color: #6b7280;">
                                        <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                        <div>No recent activity</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Tab -->
        <div id="categories-content" class="tab-content" style="display: none;">
            <div class="card-header" style="border-top: 1px solid var(--border-light); margin-top: 1rem; padding-top: 1rem;">
                <h3 class="card-title">Item Categories</h3>
                <div style="display: flex; gap: 0.75rem;">
                    <div style="position: relative; width: 250px;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; z-index: 2;"></i>
                        <input type="text" 
                               id="categorySearch" 
                               placeholder="Search categories..." 
                               class="search-input-field"
                               style="width: 100%; padding: 10px 40px 10px 40px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; color: #374151; background: white;"
                               oninput="toggleCategoryClear()">
                        <button type="button" class="search-clear-btn-field" 
                                id="clearCategoryBtn"
                                onclick="clearCategorySearch()"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.125rem; padding: 0; margin: 0; z-index: 10;" 
                                title="Clear search">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary" onclick="openAddCategoryModal()">
                        <i class="fas fa-plus"></i>
                        Add Category
                    </button>
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Items Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td style="font-weight: 500;">{{ $category->category_name }}</td>
                            <td>
                                <span class="status-badge primary">
                                    <i class="fas fa-box"></i>
                                    {{ $category->inventory_items_count }} items
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons-modern">
                                    <button class="action-btn edit" 
                                            onclick="openEditCategoryModal({{ $category->category_id }})"
                                            title="Edit Category">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" 
                                            onclick="confirmDeleteCategory({{ $category->category_id }}, '{{ addslashes($category->category_name) }}')"
                                            title="@if($category->inventory_items_count > 0)Cannot delete - {{ $category->inventory_items_count }} items associated@else Delete Category @endif"
                                            @if($category->inventory_items_count > 0) disabled @endif>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <h3>No categories found</h3>
                                    <p>Get started by creating your first category</p>
                                    <button class="btn btn-primary" onclick="openAddCategoryModal()">
                                        <i class="fas fa-plus"></i>
                                        Add Category
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Barangays Tab -->
        <div id="barangays-content" class="tab-content" style="display: none;">
            <div class="card-header" style="border-top: 1px solid var(--border-light); margin-top: 1rem; padding-top: 1rem;">
                <h3 class="card-title">Barangays</h3>
                <div style="display: flex; gap: 0.75rem;">
                    <div style="position: relative; width: 250px;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; z-index: 2;"></i>
                        <input type="text" 
                               id="barangaySearch" 
                               placeholder="Search barangays..." 
                               class="search-input-field"
                               style="width: 100%; padding: 10px 40px 10px 40px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; color: #374151; background: white;"
                               oninput="toggleBarangayClear()">
                        <button type="button" class="search-clear-btn-field" 
                                id="clearBarangayBtn"
                                onclick="clearBarangaySearch()"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.125rem; padding: 0; margin: 0; z-index: 10;" 
                                title="Clear search">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary" onclick="openAddBarangayModal()">
                        <i class="fas fa-plus"></i>
                        Add Barangay
                    </button>
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Barangay Name</th>
                            <th>Patients Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangays as $barangay)
                        <tr>
                            <td style="font-weight: 500;">{{ $barangay->barangay_name }}</td>
                            <td>
                                <span class="status-badge success">
                                    <i class="fas fa-user-injured"></i>
                                    {{ $barangay->patients_count }} patients
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons-modern">
                                    <button class="action-btn edit" 
                                            onclick="openEditBarangayModal({{ $barangay->barangay_id }})"
                                            title="Edit Barangay">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" 
                                            onclick="confirmDeleteBarangay({{ $barangay->barangay_id }}, '{{ addslashes($barangay->barangay_name) }}')"
                                            title="@if($barangay->patients_count > 0)Cannot delete - {{ $barangay->patients_count }} patients associated@else Delete Barangay @endif"
                                            @if($barangay->patients_count > 0) disabled @endif>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <h3>No barangays found</h3>
                                    <p>Get started by adding your first barangay</p>
                                    <button class="btn btn-primary" onclick="openAddBarangayModal()">
                                        <i class="fas fa-plus"></i>
                                        Add Barangay
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
