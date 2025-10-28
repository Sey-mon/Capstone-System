@extends('layouts.dashboard')

@section('title', 'Food Database Management')

@section('page-title', 'Food Database')
@section('page-subtitle', 'Manage food items and nutritional information')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/foods.css') }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search foods..." value="{{ $search ?? '' }}">
        </div>
        
        <select id="tagFilter">
            <option value="">All Tags</option>
            @if(isset($allTags) && count($allTags) > 0)
                @foreach($allTags as $tagOption)
                    <option value="{{ $tagOption }}" {{ ($tag ?? '') == $tagOption ? 'selected' : '' }}>{{ $tagOption }}</option>
                @endforeach
            @endif
        </select>

        <button class="btn btn-secondary" onclick="document.getElementById('importForm').style.display='block'">
            <i class="fas fa-file-import"></i> Import CSV
        </button>
        <a href="{{ route('admin.foods.export') }}" class="btn btn-secondary">
            <i class="fas fa-file-export"></i> Export
        </a>
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i class="fas fa-plus"></i> Add Food
        </button>
    </div>

    <!-- Import Form (Hidden) -->
    <div id="importForm" style="display:none; margin: 20px 0; padding: 20px; background: white; border-radius: 8px;">
        <h3>Import CSV</h3>
        <form method="POST" action="{{ route('admin.foods.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit" class="btn btn-primary">Upload</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('importForm').style.display='none'">Cancel</button>
        </form>
    </div>

    <!-- Foods Table -->
    <div class="content-card">
        <h3>Food Items ({{ $foods->total() }} total)</h3>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Food Name & Description</th>
                    <th>Alternate Names</th>
                    <th>Energy (kcal)</th>
                    <th>Tags</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($foods as $food)
                    <tr>
                        <td>{{ $food->food_id }}</td>
                        <td>{{ Str::limit($food->food_name_and_description, 60) }}</td>
                        <td>{{ Str::limit($food->alternate_common_names, 40) ?? '-' }}</td>
                        <td>{{ number_format($food->energy_kcal, 1) }}</td>
                        <td>{{ Str::limit($food->nutrition_tags, 30) }}</td>
                        <td>
                            <button class="btn-sm btn-edit" onclick="editFood({{ $food->food_id }})">Edit</button>
                            <form method="POST" action="{{ route('admin.foods.destroy', $food->food_id) }}" style="display:inline;" onsubmit="return confirm('Delete this food item?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px;">
                            No food items found. <button onclick="openCreateModal()">Add First Item</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $foods->links() }}
    </div>

    <!-- Create/Edit Modal -->
    <div id="foodModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeFoodModal()">&times;</span>
            <h2 id="modalTitle">Add Food Item</h2>
            
            <form id="foodForm" method="POST" action="{{ route('admin.foods.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="food_id" id="foodId">
                
                <div class="form-group">
                    <label>Food Name & Description *</label>
                    <textarea name="food_name_and_description" id="foodName" required rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Alternate Names</label>
                    <input type="text" name="alternate_common_names" id="alternateNames">
                </div>

                <div class="form-group">
                    <label>Energy (kcal) *</label>
                    <input type="number" name="energy_kcal" id="energyKcal" step="0.1" required>
                </div>

                <div class="form-group">
                    <label>Nutrition Tags</label>
                    <input type="text" name="nutrition_tags" id="nutritionTags" placeholder="comma-separated">
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeFoodModal()">Cancel</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/foods.js') }}"></script>
@endpush
