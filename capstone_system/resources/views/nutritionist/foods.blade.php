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

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
            <h4>Pending Requests</h4>
            <p>{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon approved"><i class="fas fa-check-circle"></i></div>
            <h4>Approved</h4>
            <p>{{ $stats['approved'] ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon rejected"><i class="fas fa-times-circle"></i></div>
            <h4>Rejected</h4>
            <p>{{ $stats['rejected'] ?? 0 }}</p>
        </div>
    </div>

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

        <button onclick="openRequestFoodModal()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Request New Food
        </button>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> You have read-only access. To add new foods, submit a request for admin approval.
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="?view=foods" class="{{ (!request('view') || request('view') == 'foods') ? 'active' : '' }}">
            <i class="fas fa-utensils"></i> Food Database
        </a>
        <a href="?view=requests{{ request('status') ? '&status=' . request('status') : '' }}" class="{{ request('view') == 'requests' ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> My Requests
        </a>
    </div>

    @if(!request('view') || request('view') == 'foods')
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
    @else
        <!-- My Requests Section -->
        <div class="filter-tabs-secondary">
            <a href="?view=requests" class="{{ !request('status') ? 'active' : '' }}">All</a>
            <a href="?view=requests&status=pending" class="{{ request('status') == 'pending' ? 'active' : '' }}">Pending</a>
            <a href="?view=requests&status=approved" class="{{ request('status') == 'approved' ? 'active' : '' }}">Approved</a>
            <a href="?view=requests&status=rejected" class="{{ request('status') == 'rejected' ? 'active' : '' }}">Rejected</a>
        </div>

        <div class="content-card">
            <h3>My Food Requests</h3>
            
            @forelse($requests as $request)
                <div class="request-card status-{{ $request->status }}">
                    <div class="request-header">
                        <span class="request-id">#{{ $request->id }}</span>
                        <span class="badge badge-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                    </div>
                    
                    <h4>{{ $request->food_name_and_description }}</h4>
                    
                    @if($request->alternate_common_names)
                        <p><strong>Alternate Names:</strong> {{ $request->alternate_common_names }}</p>
                    @endif
                    
                    @if($request->energy_kcal)
                        <p><strong>Energy:</strong> {{ number_format($request->energy_kcal, 1) }} kcal</p>
                    @endif
                    
                    @if($request->nutrition_tags)
                        <p><strong>Tags:</strong> {{ $request->nutrition_tags }}</p>
                    @endif
                    
                    <p class="request-date"><i class="fas fa-calendar"></i> Requested on {{ $request->created_at->format('M d, Y') }}</p>
                    
                    @if($request->admin_notes && $request->status != 'pending')
                        <div class="admin-response">
                            <strong><i class="fas fa-user-shield"></i> Admin Response:</strong>
                            <p>{{ $request->admin_notes }}</p>
                            @if($request->reviewer)
                                <small>Reviewed by {{ $request->reviewer->first_name }} {{ $request->reviewer->last_name }} on {{ $request->reviewed_at->format('M d, Y') }}</small>
                            @endif
                        </div>
                    @endif
                    
                    <div class="request-actions">
                        <button onclick="viewRequestDetails({{ $request->id }})" class="btn-sm btn-info" title="View Details">
                            <i class="fas fa-eye"></i> View
                        </button>
                        @if($request->status == 'pending')
                            <button onclick="cancelRequest({{ $request->id }})" class="btn-sm btn-danger" title="Cancel Request">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding: 60px 20px;">
                    <i class="fas fa-clipboard-list" style="font-size: 64px; color: #ddd; margin-bottom: 20px;"></i>
                    <p style="font-size: 18px; color: #666; margin-bottom: 10px;">No requests yet</p>
                    <p style="color: #999;">Click "Request New Food" to submit your first request</p>
                </div>
            @endforelse

            @if(isset($requests) && $requests->count() > 0)
                {{ $requests->links() }}
            @endif
        </div>
    @endif

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

    <!-- View Request Details Modal -->
    <div id="viewRequestModal" class="modal" style="display:none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeViewRequestModal()">&times;</span>
            <h2>Request Details</h2>
            <div id="requestDetailsContent">
                <p style="text-align:center; padding: 20px;">Loading...</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/nutritionist/foods.js') }}"></script>
@endpush
