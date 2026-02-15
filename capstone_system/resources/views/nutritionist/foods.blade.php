@extends('layouts.dashboard')

@section('title', 'Food Database')

@section('page-title', 'Food Database')
@section('page-subtitle', 'Browse available food items (Read-Only)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/foods.css') }}?v={{ filemtime(public_path('css/nutritionist/foods.css')) }}">
@endpush

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success fade-in" id="successAlert">
            <i class="fas fa-check-circle"></i> 
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger fade-in" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i> 
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger fade-in" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div class="error-item">{{ $error }}</div>
                @endforeach
            </div>
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
            <input type="text" id="searchInput" placeholder="Search by food name, alternate names, or tags..." value="{{ $search ?? '' }}">
        </div>
        
        <div class="filter-group">
            <select id="tagFilter" class="modern-select">
                <option value="">All Tags</option>
                @foreach($allTags as $tagOption)
                    <option value="{{ $tagOption }}" {{ $tag == $tagOption ? 'selected' : '' }}>{{ $tagOption }}</option>
                @endforeach
            </select>

            <button onclick="openRequestFoodModal()" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Request New Food
            </button>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <span>You have read-only access. To add new foods, submit a request for admin approval.</span>
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
            <div class="card-header">
                <h3><i class="fas fa-database"></i> Food Database</h3>
                <span class="item-count">{{ $foods->total() }} items</span>
            </div>
            
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th>Food Name & Description</th>
                            <th width="180">Alternate Names</th>
                            <th width="180">Tags</th>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($foods as $food)
                            <tr class="table-row-hover">
                                <td><span class="id-badge">{{ $food->food_id }}</span></td>
                                <td class="food-name">{{ Str::limit($food->food_name_and_description, 80) }}</td>
                                <td><span class="alternate-name">{{ Str::limit($food->alternate_common_names, 40) ?? '-' }}</span></td>
                                <td><span class="tags-cell">{{ Str::limit($food->nutrition_tags, 40) }}</span></td>
                                <td class="action-cell">
                                    <button onclick="viewFoodDetails({{ $food->food_id }})" class="btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-search"></i>
                                    <p>No food items found</p>
                                    <small>Try adjusting your search or filters</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $foods->links() }}
            </div>
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
            <div class="card-header">
                <h3><i class="fas fa-clipboard-list"></i> My Food Requests</h3>
            </div>
            
            @forelse($requests as $request)
                <div class="request-card status-{{ $request->status }}">
                    <div class="request-header">
                        <span class="request-id">#{{ $request->id }}</span>
                        <span class="badge badge-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                    </div>
                    
                    <h4>{{ $request->food_name_and_description }}</h4>
                    
                    <div class="request-details">
                        @if($request->alternate_common_names)
                            <div class="detail-item">
                                <i class="fas fa-tag"></i>
                                <strong>Alternate Names:</strong> 
                                <span>{{ $request->alternate_common_names }}</span>
                            </div>
                        @endif
                        
                        @if($request->nutrition_tags)
                            <div class="detail-item">
                                <i class="fas fa-tags"></i>
                                <strong>Tags:</strong> 
                                <span>{{ $request->nutrition_tags }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <p class="request-date">
                        <i class="fas fa-calendar-alt"></i> 
                        Requested on {{ $request->created_at->format('M d, Y h:i A') }}
                    </p>
                    
                    @if($request->admin_notes && $request->status != 'pending')
                        <div class="admin-response">
                            <div class="response-header">
                                <i class="fas fa-user-shield"></i> 
                                <strong>Admin Response</strong>
                            </div>
                            <p>{{ $request->admin_notes }}</p>
                            @if($request->reviewer)
                                <small class="reviewer-info">
                                    <i class="fas fa-user-check"></i>
                                    Reviewed by {{ $request->reviewer->first_name }} {{ $request->reviewer->last_name }} 
                                    on {{ $request->reviewed_at->format('M d, Y h:i A') }}
                                </small>
                            @endif
                        </div>
                    @endif
                    
                    <div class="request-actions">
                        <button onclick="viewRequestDetails({{ $request->id }})" class="btn-sm btn-info" title="View Details">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        @if($request->status == 'pending')
                            <button onclick="cancelRequest({{ $request->id }})" class="btn-sm btn-danger" title="Cancel Request">
                                <i class="fas fa-times-circle"></i> Cancel
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state-large">
                    <i class="fas fa-clipboard-list"></i>
                    <h4>No requests yet</h4>
                    <p>Click "Request New Food" to submit your first request</p>
                </div>
            @endforelse

            @if(isset($requests) && $requests->count() > 0)
                <div class="pagination-wrapper">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- View Food Details Modal -->
    <div id="viewFoodModal" class="modal" style="display:none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeViewFoodModal()">&times;</span>
            <h2><i class="fas fa-utensils"></i> Food Details</h2>
            <div id="foodDetailsContent">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- View Request Details Modal -->
    <div id="viewRequestModal" class="modal" style="display:none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeViewRequestModal()">&times;</span>
            <h2><i class="fas fa-file-alt"></i> Request Details</h2>
            <div id="requestDetailsContent">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/nutritionist/foods.js') }}?v={{ filemtime(public_path('js/nutritionist/foods.js')) }}"></script>
@endpush
