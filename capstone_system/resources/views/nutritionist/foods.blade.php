@extends('layouts.dashboard')

@section('title', 'Food Database')

@section('page-title', 'Food Database')
@section('page-subtitle', 'Browse available food items (Read-Only)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/foods.css') }}">
@endpush

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success" id="successAlert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin: 5px 0 0 20px;">
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
            @foreach($allTags as $tagOption)
                <option value="{{ $tagOption }}" {{ $tag == $tagOption ? 'selected' : '' }}>{{ $tagOption }}</option>
            @endforeach
        </select>

        <a href="{{ route('nutritionist.food-requests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Request New Food
        </a>
        
        <a href="{{ route('nutritionist.food-requests.index') }}" class="btn btn-secondary">
            <i class="fas fa-clipboard-list"></i> My Requests
        </a>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> You have read-only access. To add new foods, submit a request for admin approval.
    </div>

    <!-- Foods Table -->
    <div class="content-card">
        <h3>Food Database ({{ $foods->total() }} items)</h3>
        
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
                        <td>{{ Str::limit($food->food_name_and_description, 80) }}</td>
                        <td>{{ Str::limit($food->alternate_common_names, 40) ?? '-' }}</td>
                        <td><strong>{{ number_format($food->energy_kcal, 1) }}</strong></td>
                        <td>{{ Str::limit($food->nutrition_tags, 40) }}</td>
                        <td>
                            <button onclick="viewFoodDetails({{ $food->food_id }})" class="btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px;">
                            No food items found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $foods->links() }}
    </div>

    <!-- View Food Details Modal -->
    <div id="viewFoodModal" class="modal" style="display:none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeViewFoodModal()">&times;</span>
            <h2>Food Details</h2>
            <div id="foodDetailsContent">
                <p style="text-align:center; padding: 20px;">Loading...</p>
            </div>
        </div>
    </div>

    <!-- Quick Request Modal -->
    <div id="quickRequestModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeQuickRequestModal()">&times;</span>
            <h2>Quick Food Request</h2>
            
            <form method="POST" action="{{ route('nutritionist.food-requests.store') }}">
                @csrf
                
                <div class="form-group">
                    <label>Food Name & Description *</label>
                    <textarea name="food_name_and_description" required rows="3" placeholder="Enter detailed food name and description"></textarea>
                </div>

                <div class="form-group">
                    <label>Alternate Names</label>
                    <input type="text" name="alternate_common_names" placeholder="Other common names (comma-separated)">
                </div>

                <div class="form-group">
                    <label>Energy (kcal)</label>
                    <input type="number" name="energy_kcal" step="0.1" placeholder="Caloric content per serving">
                </div>

                <div class="form-group">
                    <label>Nutrition Tags</label>
                    <input type="text" name="nutrition_tags" placeholder="e.g., high-protein, low-fat (comma-separated)">
                </div>

                <button type="submit" class="btn btn-primary">Submit Request</button>
                <button type="button" class="btn btn-secondary" onclick="closeQuickRequestModal()">Cancel</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/nutritionist/foods.js') }}"></script>
@endpush
