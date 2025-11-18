@extends('layouts.dashboard')

@section('title', 'Food Database Management')

@section('page-title', 'Food Database')
@section('page-subtitle', 'Manage food items and nutritional information')

@push('preload-styles')
    <link rel="preload" href="{{ asset('css/admin/foods.css') }}" as="style" fetchpriority="high">
@endpush

@push('styles')
    <style>
        /* Critical CSS for above-the-fold content */
        .action-bar{display:flex;gap:8px;align-items:center;margin-bottom:16px;flex-wrap:wrap;padding:12px 16px;background:#fff;border-radius:8px;box-shadow:0 1px 2px 0 rgba(0,0,0,.05)}
        .search-box{flex:1;min-width:220px;position:relative}
        .content-card{background:#fff;border-radius:8px;padding:16px;box-shadow:0 1px 2px 0 rgba(0,0,0,.05);margin-bottom:16px;border:1px solid #e5e7eb}
        .alert{padding:10px 14px;border-radius:6px;margin-bottom:16px;display:flex;align-items:center;gap:8px;font-weight:500;font-size:13px}
        .alert-success{background:#ecfdf5;color:#047857;border-left:3px solid #10b981}
        .alert-danger{background:#fee2e2;color:#991b1b;border-left:3px solid #ef4444}
    </style>
    <link rel="stylesheet" href="{{ asset('css/admin/foods.css') }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Action Bar -->
    <div class="action-bar" style="contain: layout;">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search foods by name, description..." value="{{ $search ?? '' }}" autocomplete="off">
        </div>
        
        <select id="tagFilter">
            <option value="">🏷️ All Tags</option>
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
            <i class="fas fa-plus"></i> Add New Food
        </button>
    </div>

    <!-- Import Form (Hidden) -->
    <div id="importForm" style="display:none;">
        <h3><i class="fas fa-file-import"></i> Import CSV File</h3>
        <form method="POST" action="{{ route('admin.foods.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="csv_file" accept=".csv" required>
            <div style="display: flex; gap: 12px; margin-top: 16px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload
                </button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('importForm').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Foods Table -->
    <div class="content-card">
        <h3><i class="fas fa-utensils"></i> Food Items ({{ $foods->total() }} total)</h3>
        
        <div style="overflow-x: auto; min-height: 400px;">
            <table class="data-table" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-apple-alt"></i> Food Name & Description</th>
                        <th><i class="fas fa-list-alt"></i> Alternate Names</th>
                        <th><i class="fas fa-fire"></i> Energy (kcal)</th>
                        <th><i class="fas fa-tags"></i> Tags</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($foods as $food)
                        <tr>
                            <td><strong>#{{ $food->food_id }}</strong></td>
                            <td>{{ Str::limit($food->food_name_and_description, 60) }}</td>
                            <td>{{ Str::limit($food->alternate_common_names, 40) ?? '-' }}</td>
                            <td><strong>{{ number_format($food->energy_kcal, 1) }}</strong></td>
                            <td>
                                @if($food->nutrition_tags)
                                    <span style="display: inline-block; padding: 4px 12px; background: var(--light-green); color: var(--dark-green); border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        {{ Str::limit($food->nutrition_tags, 30) }}
                                    </span>
                                @else
                                    <span style="color: var(--gray-400);">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <button class="btn-sm btn-edit" onclick="editFood({{ $food->food_id }})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.foods.destroy', $food->food_id) }}" style="display:inline;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div style="text-align: center; padding: 60px 20px;">
                                    <i class="fas fa-inbox" style="font-size: 48px; color: var(--gray-300); margin-bottom: 16px;"></i>
                                    <p style="color: var(--gray-500); margin-bottom: 16px;">No food items found.</p>
                                    <button onclick="openCreateModal()">
                                        <i class="fas fa-plus"></i> Add First Item
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $foods->links() }}
    </div>

@endsection

@push('scripts')
    <script defer src="{{ asset('js/admin/foods.js') }}"></script>
@endpush
