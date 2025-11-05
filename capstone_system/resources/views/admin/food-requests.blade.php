@extends('layouts.dashboard')

@section('title', 'Food Requests')

@section('page-title', 'Food Requests')
@section('page-subtitle', 'Review and manage food addition requests from nutritionists')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/food-requests.css') }}">
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

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="{{ route('admin.food-requests.index') }}" class="{{ !$status ? 'active' : '' }}">All</a>
        <a href="{{ route('admin.food-requests.index', ['status' => 'pending']) }}" class="{{ $status == 'pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('admin.food-requests.index', ['status' => 'approved']) }}" class="{{ $status == 'approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('admin.food-requests.index', ['status' => 'rejected']) }}" class="{{ $status == 'rejected' ? 'active' : '' }}">Rejected</a>
    </div>

    <!-- Requests Table -->
    <div class="content-card">
        <h3>Food Requests</h3>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Requested By</th>
                    <th>Food Name</th>
                    <th>Energy (kcal)</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ $request->id }}</td>
                        <td>{{ $request->requester->first_name }} {{ $request->requester->last_name }}</td>
                        <td>{{ Str::limit($request->food_name_and_description, 50) }}</td>
                        <td>{{ $request->energy_kcal ? number_format($request->energy_kcal, 1) : 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($request->status == 'pending')
                                <button class="btn-sm btn-info" onclick="viewRequestDetails({{ $request->id }})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.food-requests.approve', $request->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-sm btn-success" onclick="return confirm('Approve this request and add to database?')" title="Approve">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <button class="btn-sm btn-danger" onclick="rejectRequest({{ $request->id }})" title="Reject">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            @else
                                <button class="btn-sm btn-info" onclick="viewRequestDetails({{ $request->id }})" title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <span class="badge badge-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                            @endif
                            
                            <form method="POST" action="{{ route('admin.food-requests.destroy', $request->id) }}" style="display:inline;" onsubmit="return confirm('Delete this request?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 40px;">No food requests found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $requests->links() }}
    </div>

    <!-- View Details Modal -->
    <div id="viewModal" class="modal" style="display:none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeViewModal()">&times;</span>
            <h2>Request Details</h2>
            <div id="viewContent">
                <!-- Content loaded via JavaScript -->
            </div>
            <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeRejectModal()">&times;</span>
            <h2>Reject Food Request</h2>
            
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Reason for Rejection *</label>
                    <textarea name="admin_notes" required rows="4" placeholder="Explain why this request is being rejected..."></textarea>
                    <small>This will be visible to the nutritionist.</small>
                </div>

                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Reject Request
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/food-requests.js') }}"></script>
@endpush
