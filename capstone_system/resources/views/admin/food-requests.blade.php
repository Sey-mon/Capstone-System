@extends('layouts.dashboard')

@section('title', 'Food Requests')

@section('page-title', 'Food Requests')
@section('page-subtitle', 'Review and manage food addition requests from nutritionists')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/food-requests.css') }}?v={{ time() }}">
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
        <a href="{{ route('admin.food-requests.index', ['status' => 'pending']) }}" class="{{ $status == 'pending' ? 'active' : '' }}">Pending ({{ $stats['pending'] }})</a>
        <a href="{{ route('admin.food-requests.index', ['status' => 'approved']) }}" class="{{ $status == 'approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('admin.food-requests.index', ['status' => 'rejected']) }}" class="{{ $status == 'rejected' ? 'active' : '' }}">Rejected</a>
    </div>

    <!-- Bulk Actions Bar (Hidden by default) -->
    @if($status == 'pending' || !$status)
    <div id="bulkActionsBar" style="display:none; background: #fff; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; box-shadow: 0 1px 2px 0 rgba(0,0,0,.05); gap: 12px; align-items: center;">
        <span style="font-weight: 600; color: #374151;"><span id="bulkCount">0</span> request(s) selected</span>
        <button class="btn btn-success btn-sm" onclick="bulkApprove()">
            <i class="fas fa-check"></i> Approve Selected
        </button>
        <button class="btn btn-danger btn-sm" onclick="bulkReject()">
            <i class="fas fa-times"></i> Reject Selected
        </button>
        <button class="btn btn-secondary btn-sm" onclick="clearSelection()">
            <i class="fas fa-times-circle"></i> Clear Selection
        </button>
    </div>
    @endif

    <!-- Requests Table -->
    <div class="content-card">
        <h3>Food Requests</h3>
        
        <table class="data-table">
            <thead>
                <tr>
                    @if($status == 'pending' || !$status)
                    <th style="width: 50px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" title="Select all on this page">
                    </th>
                    @endif
                    <th>ID</th>
                    <th>Requested By</th>
                    <th>Food Name</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr data-request-id="{{ $request->id }}">
                        @if($status == 'pending' || !$status)
                        <td>
                            @if($request->status == 'pending')
                            <input type="checkbox" class="request-checkbox" value="{{ $request->id }}" onchange="updateBulkActions()">
                            @endif
                        </td>
                        @endif
                        <td>{{ $request->id }}</td>
                        <td>{{ $request->requester->first_name }} {{ $request->requester->last_name }}</td>
                        <td>{{ Str::limit($request->food_name_and_description, 50) }}</td>
                        <td>
                            <span class="badge badge-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <button class="btn-sm btn-info" onclick="viewRequestDetails({{ $request->id }})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($request->status == 'pending')
                                    <button class="btn-sm btn-success" onclick="approveRequest({{ $request->id }})" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn-sm btn-danger" onclick="rejectRequest({{ $request->id }})" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                <button class="btn-sm btn-delete" onclick="deleteRequest({{ $request->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($status == 'pending' || !$status) ? '7' : '6' }}" style="text-align:center; padding: 40px;">
                            <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                            <p style="color: #6b7280;">No food requests found</p>
                        </td>
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
    <script src="{{ asset('js/admin/food-requests.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/admin/food-requests-enhanced.js') }}?v={{ time() }}"></script>
@endpush
