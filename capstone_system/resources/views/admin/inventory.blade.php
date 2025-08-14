@extends('layouts.dashboard')

@section('title', 'Inventory Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-inventory.css') }}">
@endpush

@section('page-title', 'Inventory Management')
@section('page-subtitle', 'Manage your inventory items and track stock levels.')

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Quick Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Items</div>
                <div class="stat-icon primary">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
            <div class="stat-value">{{ $items->total() }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Low Stock</div>
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $items->where('quantity', '<', 10)->count() }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Categories</div>
                <div class="stat-icon success">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
            <div class="stat-value">{{ $items->pluck('category')->unique()->count() }}</div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Inventory Items</h3>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i>
                    Add Item
                </button>
            </div>
        </div>
        
        <!-- Real-time Filters -->
        <div class="filter-section" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-light); background: var(--bg-tertiary);">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: center;">
                <div class="form-group" style="margin: 0;">
                    <input type="text" 
                           id="searchFilter" 
                           placeholder="Search items by name or category..." 
                           class="form-input"
                           style="margin: 0;">
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <select id="categoryFilter" class="form-input" style="margin: 0;">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <select id="statusFilter" class="form-input" style="margin: 0;">
                        <option value="">All Status</option>
                        <option value="in-stock">In Stock</option>
                        <option value="low-stock">Low Stock</option>
                        <option value="critical">Critical</option>
                        <option value="out-of-stock">Out of Stock</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                
                <button onclick="clearFilters()" class="btn btn-secondary" style="height: fit-content;">
                    <i class="fas fa-times"></i>
                    Clear
                </button>
            </div>
        </div>
        
        <div class="card-content">
            <div style="overflow-x: auto;">
                <table class="inventory-table" style="border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-light);">
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">Item Name</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">Category</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">Quantity</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">Unit</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">Expiry Date</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">Status</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr style="border-bottom: 1px solid var(--border-light); transition: background-color var(--transition-fast);" 
                            onmouseover="this.style.backgroundColor='var(--bg-tertiary)'" 
                            onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 1rem;">
                                <div style="font-weight: 500;">{{ $item->item_name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                    {{ $item->inventoryTransactions->count() }} transactions
                                </div>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="padding: 0.25rem 0.75rem; background: var(--bg-tertiary); border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">
                                    {{ $item->category->category_name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                @php
                                    $quantityClass = 'high';
                                    if ($item->quantity <= 5) {
                                        $quantityClass = 'low';
                                    } elseif ($item->quantity <= 10) {
                                        $quantityClass = 'medium';
                                    }
                                @endphp
                                <span class="quantity-display {{ $quantityClass }}">{{ $item->quantity }}</span>
                            </td>
                            <td style="padding: 1rem; color: var(--text-secondary);">{{ $item->unit }}</td>
                            <td style="padding: 1rem; color: var(--text-secondary);">
                                @if($item->expiry_date)
                                    @php
                                        $daysToExpiry = \Carbon\Carbon::now()->diffInDays($item->expiry_date, false);
                                        $expiryStyle = '';
                                        if ($daysToExpiry < 0) {
                                            $expiryStyle = 'color: var(--danger-color); font-weight: 600;';
                                        } elseif ($daysToExpiry <= 30) {
                                            $expiryStyle = 'color: var(--warning-color); font-weight: 600;';
                                        }
                                    @endphp
                                    <span style="{{ $expiryStyle }}">{{ $item->expiry_date ? $item->expiry_date->format('M d, Y') : 'N/A' }}</span>
                                @else
                                    <span style="color: var(--text-muted);">No expiry</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @php
                                    $status = 'In Stock';
                                    $statusClass = 'in-stock';
                                    
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
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="action-btn stock-in" 
                                            onclick="openStockInModal({{ $item->item_id }}, '{{ addslashes($item->item_name) }}')"
                                            title="Stock In">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="action-btn stock-out" 
                                            onclick="openStockOutModal({{ $item->item_id }}, '{{ addslashes($item->item_name) }}', {{ $item->quantity }})"
                                            title="Stock Out">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button class="action-btn edit" 
                                            onclick="openEditModal({{ $item->item_id }})"
                                            title="Edit Item">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn view" 
                                            onclick="window.location.href='{{ route('admin.audit.logs') }}'"
                                            title="View Activity Logs">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="action-btn delete" 
                                            onclick="confirmDelete({{ $item->item_id }}, '{{ addslashes($item->item_name) }}')"
                                            title="Delete Item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding: 3rem; text-align: center;">
                                <div style="color: var(--text-secondary);">
                                    <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                    <p>No inventory items found.</p>
                                    <button class="btn btn-primary" style="margin-top: 1rem;" onclick="openAddModal()">
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

            @if(method_exists($items, 'links') && $items->hasPages())
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light);">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div id="itemModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle" class="modal-title">Add New Item</h3>
                <button onclick="closeModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="itemForm">
                <input type="hidden" id="itemId" name="itemId">
                
                <div class="form-group">
                    <label for="itemName" class="form-label">Item Name *</label>
                    <input type="text" id="itemName" name="item_name" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="categoryId" class="form-label">Category *</label>
                    <select id="categoryId" name="category_id" required class="form-input">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="unit" class="form-label">Unit *</label>
                        <input type="text" id="unit" name="unit" required placeholder="e.g., kg, pcs, bottles" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="form-label">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" required min="0" class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="expiryDate" class="form-label">Expiry Date</label>
                    <input type="date" id="expiryDate" name="expiry_date" class="form-input">
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content delete-modal-content">
            <div class="delete-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="delete-title">Confirm Deletion</h3>
            <p class="delete-message">Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
            <p class="delete-warning">This action cannot be undone.</p>
            
            <div class="modal-actions" style="justify-content: center;">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="button" onclick="deleteItem()" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Stock In Modal -->
    <div id="stockInModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Stock In</h3>
                <button type="button" onclick="closeStockInModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="stockInForm" onsubmit="processStockIn(event)">
                <div class="form-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" id="stockInItemName" class="form-input" readonly style="background-color: var(--bg-tertiary);">
                </div>
                
                <div class="form-group">
                    <label for="stockInQuantity" class="form-label">Quantity to Add <span class="required">*</span></label>
                    <input type="number" id="stockInQuantity" name="quantity" class="form-input" min="1" required>
                    <small class="form-help">Enter the number of units to add to stock</small>
                </div>
                
                <div class="form-group">
                    <label for="stockInRemarks" class="form-label">Remarks</label>
                    <textarea id="stockInRemarks" name="remarks" class="form-input" rows="3" placeholder="Optional notes about this stock in..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeStockInModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stock Out Modal -->
    <div id="stockOutModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Stock Out</h3>
                <button type="button" onclick="closeStockOutModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="stockOutForm" onsubmit="processStockOut(event)">
                <div class="form-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" id="stockOutItemName" class="form-input" readonly style="background-color: var(--bg-tertiary);">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Available Stock</label>
                    <input type="text" id="stockOutAvailable" class="form-input" readonly style="background-color: var(--bg-tertiary);">
                </div>
                
                <div class="form-group">
                    <label for="stockOutQuantity" class="form-label">Quantity to Remove <span class="required">*</span></label>
                    <input type="number" id="stockOutQuantity" name="quantity" class="form-input" min="1" required>
                    <small class="form-help">Enter the number of units to remove from stock</small>
                </div>
                
                <div class="form-group">
                    <label for="stockOutPatient" class="form-label">Patient (Optional)</label>
                    <select id="stockOutPatient" name="patient_id" class="form-input">
                        <option value="">Select patient (if applicable)</option>
                        @if(isset($patients))
                            @foreach($patients as $patient)
                                <option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <small class="form-help">Select a patient if this stock out is for a specific patient</small>
                </div>
                
                <div class="form-group">
                    <label for="stockOutRemarks" class="form-label">Remarks</label>
                    <textarea id="stockOutRemarks" name="remarks" class="form-input" rows="3" placeholder="Optional notes about this stock out..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeStockOutModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-minus"></i>
                        Remove Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-inventory.js') }}"></script>
@endpush
