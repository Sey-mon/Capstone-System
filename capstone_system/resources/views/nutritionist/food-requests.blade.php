@extends('layouts.dashboard')

@section('title', 'My Food Requests')

@section('page-title', 'Food Requests')
@section('page-subtitle', 'Submit and track your food addition requests')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/food-requests.css') }}">
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
            <h4>Pending</h4>
            <p>{{ $stats['pending'] }}</p>
        </div>
        <div class="stat-card">
            <h4>Approved</h4>
            <p>{{ $stats['approved'] }}</p>
        </div>
        <div class="stat-card">
            <h4>Rejected</h4>
            <p>{{ $stats['rejected'] }}</p>
        </div>
    </div>

    <!-- Create Request Form -->
    <div class="content-card" id="createForm" style="display: {{ Route::currentRouteName() == 'nutritionist.food-requests.create' ? 'block' : 'none' }};">
        <h3>Submit New Food Request</h3>
        
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
            <a href="{{ route('nutritionist.food-requests.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    @if(Route::currentRouteName() != 'nutritionist.food-requests.create')
        <div class="action-bar">
            <a href="{{ route('nutritionist.food-requests.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Request
            </a>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="{{ route('nutritionist.food-requests.index') }}" class="{{ !$status ? 'active' : '' }}">All</a>
            <a href="{{ route('nutritionist.food-requests.index', ['status' => 'pending']) }}" class="{{ $status == 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('nutritionist.food-requests.index', ['status' => 'approved']) }}" class="{{ $status == 'approved' ? 'active' : '' }}">Approved</a>
            <a href="{{ route('nutritionist.food-requests.index', ['status' => 'rejected']) }}" class="{{ $status == 'rejected' ? 'active' : '' }}">Rejected</a>
        </div>

        <!-- Requests List -->
        <div class="content-card">
            <h3>My Requests</h3>
            
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
                    
                    <p><small>Requested on {{ $request->created_at->format('M d, Y') }}</small></p>
                    
                    @if($request->admin_notes && $request->status != 'pending')
                        <div class="admin-response">
                            <strong>Admin Response:</strong>
                            <p>{{ $request->admin_notes }}</p>
                            @if($request->reviewer)
                                <small>Reviewed by {{ $request->reviewer->first_name }} {{ $request->reviewer->last_name }} on {{ $request->reviewed_at->format('M d, Y') }}</small>
                            @endif
                        </div>
                    @endif
                    
                    @if($request->status == 'pending')
                        <div class="request-actions">
                            <button onclick="viewRequestDetails({{ $request->id }})" class="btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <form method="POST" action="{{ route('nutritionist.food-requests.destroy', $request->id) }}" style="display:inline;" onsubmit="return confirmDelete()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-danger" title="Cancel Request">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </form>
                        </div>
                    @else
                        <button onclick="viewRequestDetails({{ $request->id }})" class="btn-sm btn-info" title="View Details">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    @endif
                </div>
            @empty
                <p style="text-align:center; padding: 40px;">
                    You haven't submitted any requests yet. 
                    <a href="{{ route('nutritionist.food-requests.create') }}">Submit your first request</a>
                </p>
            @endforelse

            @if(isset($requests))
                {{ $requests->links() }}
            @endif
        </div>
    @endif

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
    <script src="{{ asset('js/nutritionist/food-requests.js') }}"></script>
@endpush
