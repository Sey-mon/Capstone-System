@extends('layouts.dashboard')

@section('title', 'System Management')

@section('page-title', 'System Management')
@section('page-subtitle', 'Manage system categories and barangays for your application.')

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/system-management-modals.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/system-management.js') }}"></script>
@endpush

@section('content')
    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Categories</div>
                <div class="stat-icon primary">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
            <div class="stat-value">{{ $categories->total() }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Barangays</div>
                <div class="stat-icon success">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>
            <div class="stat-value">{{ $barangays->total() }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Categories with Items</div>
                <div class="stat-icon warning">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
            <div class="stat-value">{{ $categories->where('inventory_items_count', '>', 0)->count() }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Barangays with Patients</div>
                <div class="stat-icon info">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>
            <div class="stat-value">{{ $barangays->where('patients_count', '>', 0)->count() }}</div>
        </div>
    </div>

    <!-- Management Tabs -->
    <div class="content-card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="tab-button active" data-tab="categories" id="categories-tab">
                    <i class="fas fa-tags"></i>
                    Item Categories
                </button>
                <button class="tab-button" data-tab="barangays" id="barangays-tab">
                    <i class="fas fa-map-marker-alt"></i>
                    Barangays
                </button>
            </div>
        </div>

        <!-- Categories Tab -->
        <div id="categories-content" class="tab-content active">
            <div class="card-header" style="border-top: 1px solid var(--border-light); margin-top: 1rem; padding-top: 1rem;">
                <h3 class="card-title">Item Categories</h3>
                <button class="btn btn-primary" onclick="openAddCategoryModal()">
                    <i class="fas fa-plus"></i>
                    Add Category
                </button>
            </div>

            <div style="margin-top: 1rem;">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Items Count</th>
                            <th>Created Date</th>
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
                            <td style="color: #6b7280;">{{ $category->created_at ? $category->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit" 
                                            onclick="openEditCategoryModal({{ $category->category_id }})"
                                            title="Edit Category">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" 
                                            onclick="confirmDeleteCategory({{ $category->category_id }}, '{{ addslashes($category->category_name) }}')"
                                            title="Delete Category"
                                            @if($category->inventory_items_count > 0) disabled @endif>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <h3>No categories found</h3>
                                    <p>Get started by creating your first category</p>
                                    <button class="btn btn-primary" onclick="openAddCategoryModal()">
                                        <i class="fas fa-plus"></i>
                                        Add Your First Category
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($categories, 'links') && $categories->hasPages())
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light);">
                {{ $categories->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>

        <!-- Barangays Tab -->
        <div id="barangays-content" class="tab-content" style="display: none;">
            <div class="card-header" style="border-top: 1px solid var(--border-light); margin-top: 1rem; padding-top: 1rem;">
                <h3 class="card-title">Barangays</h3>
                <button class="btn btn-primary" onclick="openAddBarangayModal()">
                    <i class="fas fa-plus"></i>
                    Add Barangay
                </button>
            </div>

            <div style="margin-top: 1rem;">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Barangay Name</th>
                            <th>Patients Count</th>
                            <th>Created Date</th>
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
                            <td style="color: #6b7280;">{{ $barangay->created_at ? $barangay->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit" 
                                            onclick="openEditBarangayModal({{ $barangay->barangay_id }})"
                                            title="Edit Barangay">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" 
                                            onclick="confirmDeleteBarangay({{ $barangay->barangay_id }}, '{{ addslashes($barangay->barangay_name) }}')"
                                            title="Delete Barangay"
                                            @if($barangay->patients_count > 0) disabled @endif>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <h3>No barangays found</h3>
                                    <p>Get started by adding your first barangay</p>
                                    <button class="btn btn-primary" onclick="openAddBarangayModal()">
                                        <i class="fas fa-plus"></i>
                                        Add Your First Barangay
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($barangays, 'links') && $barangays->hasPages())
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light);">
                {{ $barangays->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-plus-circle"></i>
                    Add New Category
                </h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <form id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="category_name">Category Name</label>
                        <input type="text" id="category_name" name="category_name" class="form-control" required placeholder="Enter category name">
                        <div class="invalid-feedback" id="category_name_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addCategoryModal')">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-edit"></i>
                    Edit Category
                </h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_category_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_category_name">Category Name</label>
                        <input type="text" id="edit_category_name" name="category_name" class="form-control" required placeholder="Enter category name">
                        <div class="invalid-feedback" id="edit_category_name_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editCategoryModal')">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Barangay Modal -->
    <div id="addBarangayModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-plus-circle"></i>
                    Add New Barangay
                </h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <form id="addBarangayForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="barangay_name">Barangay Name</label>
                        <input type="text" id="barangay_name" name="barangay_name" class="form-control" required placeholder="Enter barangay name">
                        <div class="invalid-feedback" id="barangay_name_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addBarangayModal')">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Barangay
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Barangay Modal -->
    <div id="editBarangayModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-edit"></i>
                    Edit Barangay
                </h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <form id="editBarangayForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_barangay_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_barangay_name">Barangay Name</label>
                        <input type="text" id="edit_barangay_name" name="barangay_name" class="form-control" required placeholder="Enter barangay name">
                        <div class="invalid-feedback" id="edit_barangay_name_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editBarangayModal')">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Barangay
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
