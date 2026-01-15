@extends('layouts.dashboard')

@section('title', 'Inventory Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-inventory.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-inventory-modern.css') }}">
@endpush

@section('page-title', 'Inventory Management')
@section('page-subtitle', 'Manage your inventory items and track stock levels.')

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Modern Filter Section -->
    <div class="filter-card">
        <div class="filter-header">
            <div class="filter-title-section">
                <i class="fas fa-filter"></i>
                <h3>Filters & Search</h3>
            </div>
            <button class="btn-clear-all" id="clearAllBtn">
                <i class="fas fa-times"></i>
                <span>Clear All</span>
            </button>
        </div>
        
        <div class="filter-grid-modern">
            <div class="filter-input-wrapper">
                <label for="searchFilter" class="filter-label">Search Patient</label>
                <div class="input-with-icon">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           id="searchFilter" 
                           placeholder="Search by name, contact..." 
                           class="filter-input">
                </div>
            </div>
            
            <div class="filter-input-wrapper">
                <label for="categoryFilter" class="filter-label">Role</label>
                <select id="categoryFilter" class="filter-input">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-input-wrapper">
                <label for="statusFilter" class="filter-label">Status</label>
                <select id="statusFilter" class="filter-input">
                    <option value="">All Status</option>
                    <option value="in-stock">In Stock</option>
                    <option value="low-stock">Low Stock</option>
                    <option value="critical">Critical</option>
                    <option value="out-of-stock">Out of Stock</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Modern Management Header -->
    <div class="management-header">
        <div class="management-title-section">
            <div class="icon-wrapper">
                <i class="fas fa-boxes"></i>
            </div>
            <div>
                <h2 class="management-title">Inventory Management</h2>
                <p class="management-subtitle">Manage and organize all inventory items and track stock levels</p>
            </div>
        </div>
        <div class="management-actions">
            <div class="count-badge">
                <i class="fas fa-boxes"></i>
                <span>{{ $items->total() }} items</span>
            </div>
            <button class="btn-add-new btn-add-item">
                <i class="fas fa-plus"></i>
                <span>Add New User</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid-modern">
        <div class="stat-card-modern stat-primary">
            <div class="stat-icon-wrapper">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $items->total() }}</div>
                <div class="stat-label">Total Items</div>
            </div>
        </div>
        
        <div class="stat-card-modern stat-warning">
            <div class="stat-icon-wrapper">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $items->where('quantity', '<', 10)->count() }}</div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>
        
        <div class="stat-card-modern stat-success">
            <div class="stat-icon-wrapper">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $items->pluck('category')->unique()->count() }}</div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="content-card-modern">
            <div class="table-container-modern">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="table-row-modern">
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($item->item_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $item->item_name }}</div>
                                        <div class="user-email">{{ $item->inventoryTransactions->count() }} transactions</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $categoryClass = 'badge-role';
                                    if (strtolower($item->category->category_name ?? '') == 'admin') {
                                        $categoryClass = 'badge-admin';
                                    }
                                @endphp
                                <span class="badge {{ $categoryClass }}">
                                    {{ $item->category->category_name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $quantityClass = 'high';
                                    if ($item->quantity <= 5) {
                                        $quantityClass = 'low';
                                    } elseif ($item->quantity <= 10) {
                                        $quantityClass = 'medium';
                                    }
                                @endphp
                                <span class="quantity-badge quantity-{{ $quantityClass }}">{{ $item->quantity }}</span>
                            </td>
                            <td><span class="text-secondary">{{ $item->unit }}</span></td>
                            <td>
                                <span class="text-secondary">{{ $item->expiry_date ? $item->expiry_date->format('M d, Y') : 'No expiry' }}</span>
                            </td>
                            <td>
                                @php
                                    $status = 'Active';
                                    $statusClass = '';
                                    $statusIcon = 'check-circle';
                                    
                                    if ($item->quantity <= 0) {
                                        $status = 'Out of Stock';
                                        $statusClass = 'out-of-stock';
                                    } elseif ($item->quantity <= 5) {
                                        $status = 'Critical';
                                        $statusClass = 'critical';
                                    } elseif ($item->quantity <= 10) {
                                        $status = 'Low Stock';
                                        $statusClass = 'low-stock';
                                    }
                                    
                                    if ($item->expiry_date && \Carbon\Carbon::now()->gt($item->expiry_date)) {
                                        $status = 'Expired';
                                        $statusClass = 'expired';
                                    }
                                @endphp
                                <span class="stock-status {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons-modern">
                                    <button class="action-btn-modern action-btn-edit action-btn edit" 
                                            data-item-id="{{ $item->item_id }}"
                                            title="Edit Item">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn-modern action-btn-stock-in action-btn stock-in" 
                                            data-item-id="{{ $item->item_id }}"
                                            data-item-name="{{ $item->item_name }}"
                                            title="Stock In">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button class="action-btn-modern action-btn-stock-out action-btn stock-out" 
                                            data-item-id="{{ $item->item_id }}"
                                            data-item-name="{{ $item->item_name }}"
                                            data-quantity="{{ $item->quantity }}"
                                            title="Stock Out">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                    <button class="action-btn-modern action-btn-view action-btn view" 
                                            data-audit-url="{{ route('admin.audit.logs') }}"
                                            title="View Activity Logs">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="action-btn-modern action-btn-delete action-btn delete" 
                                            data-item-id="{{ $item->item_id }}"
                                            data-item-name="{{ $item->item_name }}"
                                            title="Delete Item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <div>
                                    <i class="fas fa-box-open empty-state-icon"></i>
                                    <p>No inventory items found.</p>
                                    <button class="btn btn-primary btn-add-item">
                                        <i class="fas fa-plus"></i>
                                        Add Your First Item
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Modern Pagination Footer -->
            <div class="pagination-footer-modern">
                <div class="pagination-info">
                    Showing <strong>{{ $items->firstItem() ?? 0 }}</strong> to <strong>{{ $items->lastItem() ?? 0 }}</strong> of <strong>{{ $items->total() }}</strong> items
                </div>
                
                @if($items->hasPages())
                <div class="pagination-controls">
                    <nav class="pagination-nav">
                        {{-- Previous Button --}}
                        @if ($items->onFirstPage())
                            <button class="pagination-btn pagination-btn-disabled" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                        @else
                            <a href="{{ $items->previousPageUrl() }}" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                            @if ($page == $items->currentPage())
                                <button class="pagination-btn pagination-btn-active">{{ $page }}</button>
                            @else
                                <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if ($items->hasMorePages())
                            <a href="{{ $items->nextPageUrl() }}" class="pagination-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="pagination-btn pagination-btn-disabled" disabled>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </nav>
                </div>
                
                <div class="pagination-goto">
                    <label for="gotoPage">Go to page:</label>
                    <input type="number" 
                           id="gotoPage" 
                           class="goto-page-input" 
                           min="1" 
                           max="{{ $items->lastPage() }}"
                           value="{{ $items->currentPage() }}"
                           placeholder="1">
                    <button class="goto-page-btn" onclick="goToPage()">Go</button>
                </div>
                @endif
            </div>
    </div>


@endsection

@push('scripts')
    <!-- Hidden data for SweetAlert2 modals -->
    <div id="categoriesData" style="display: none;" data-categories='@json($categories)'></div>
    <div id="patientsData" style="display: none;" data-patients='@json($patients ?? [])'></div>
    
    <script>
        function goToPage() {
            const page = document.getElementById('gotoPage').value;
            const maxPage = {{ $items->lastPage() }};
            if (page >= 1 && page <= maxPage) {
                window.location.href = '{{ $items->url(1) }}'.replace('page=1', 'page=' + page);
            }
        }
    </script>
    
    <script src="{{ asset('js/admin/admin-inventory.js') }}"></script>
@endpush

