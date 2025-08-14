@extends('layouts.dashboard')

@section('title', 'Inventory Management')

@section('page-title', 'Inventory Management')
@section('page-subtitle', 'Manage your inventory items and track stock levels.')

@section('navigation')
    @include('partials.navigation')
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
                <button class="btn btn-secondary">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i>
                    Add Item
                </button>
            </div>
        </div>
        <div class="card-content">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-light);">
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">ID</th>
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
                            <td style="padding: 1rem; font-weight: 500;">#{{ $item->item_id }}</td>
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
                                    $stockStatus = '';
                                    if ($item->quantity <= 5) {
                                        $stockStatus = 'color: var(--danger-color); font-weight: 600;';
                                    } elseif ($item->quantity <= 10) {
                                        $stockStatus = 'color: var(--warning-color); font-weight: 600;';
                                    } else {
                                        $stockStatus = 'color: var(--success-color); font-weight: 500;';
                                    }
                                @endphp
                                <span style="{{ $stockStatus }}">{{ $item->quantity }}</span>
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
                                    $statusStyle = 'background: linear-gradient(135deg, var(--success-color), #16a34a); color: white;';
                                    
                                    if ($item->quantity <= 0) {
                                        $status = 'Out of Stock';
                                        $statusStyle = 'background: linear-gradient(135deg, var(--danger-color), #dc2626); color: white;';
                                    } elseif ($item->quantity <= 5) {
                                        $status = 'Critical';
                                        $statusStyle = 'background: linear-gradient(135deg, var(--danger-color), #dc2626); color: white;';
                                    } elseif ($item->quantity <= 10) {
                                        $status = 'Low Stock';
                                        $statusStyle = 'background: linear-gradient(135deg, var(--warning-color), #d97706); color: white;';
                                    }
                                    
                                    if ($item->expiry_date && \Carbon\Carbon::now()->gt($item->expiry_date)) {
                                        $status = 'Expired';
                                        $statusStyle = 'background: linear-gradient(135deg, #6b7280, #4b5563); color: white;';
                                    }
                                @endphp
                                <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; {{ $statusStyle }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <button style="padding: 0.5rem; background: var(--primary-color); color: white; border: none; border-radius: 0.375rem; cursor: pointer; transition: all var(--transition-fast);" 
                                            onmouseover="this.style.backgroundColor='var(--primary-dark)'" 
                                            onmouseout="this.style.backgroundColor='var(--primary-color)'"
                                            onclick="openEditModal({{ $item->item_id }})"
                                            title="Edit Item">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button style="padding: 0.5rem; background: var(--success-color); color: white; border: none; border-radius: 0.375rem; cursor: pointer; transition: all var(--transition-fast);" 
                                            onmouseover="this.style.backgroundColor='#16a34a'" 
                                            onmouseout="this.style.backgroundColor='var(--success-color)'"
                                            title="Add Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button style="padding: 0.5rem; background: var(--warning-color); color: white; border: none; border-radius: 0.375rem; cursor: pointer; transition: all var(--transition-fast);" 
                                            onmouseover="this.style.backgroundColor='#d97706'" 
                                            onmouseout="this.style.backgroundColor='var(--warning-color)'"
                                            title="View Transactions">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button style="padding: 0.5rem; background: var(--danger-color); color: white; border: none; border-radius: 0.375rem; cursor: pointer; transition: all var(--transition-fast);" 
                                            onmouseover="this.style.backgroundColor='#dc2626'" 
                                            onmouseout="this.style.backgroundColor='var(--danger-color)'"
                                            onclick="confirmDelete({{ $item->item_id }}, '{{ addslashes($item->item_name) }}')"
                                            title="Delete Item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="padding: 3rem; text-align: center;">
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
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-inventory.js') }}"></script>
@endpush
