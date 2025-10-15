@extends('layouts.dashboard')

@section('title', 'Code Requests')

@section('page-title', 'Registration Code Requests')
@section('page-subtitle', 'Review and approve parent requests for registration codes')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/code-requests.css') }}">
    <style>
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-completed { background: #dbeafe; color: #1e40af; }  
        .status-rejected { background: #fee2e2; color: #991b1b; }
        
        .request-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .request-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .request-info {
            flex: 1;
        }
        
        .request-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .request-details {
            padding: 1.5rem;
            background: #f9fafb;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 500;
            color: #111827;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .filter-bar {
            background: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-item {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        
        .filter-item label {
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        
        .filter-item select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
    </style>
@endpush

@section('content')
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-item">
            <label for="status-filter">Status</label>
            <select id="status-filter" name="status">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        
        <div class="filter-item">
            <label for="date-filter">Date Range</label>
            <select id="date-filter" name="date_range">
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
        
        <div class="filter-item">
            <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                <i class="fas fa-times"></i> Clear Filters
            </button>
        </div>
        
        <div class="filter-item" style="margin-left: auto;">
            <a href="{{ route('code-request.form') }}" target="_blank" class="btn btn-secondary">
                <i class="fas fa-external-link-alt"></i> Share Request Form
            </a>
        </div>
    </div>

    <!-- Requests List -->
    <div class="requests-container">
        @forelse($requests as $request)
            <div class="request-card" data-status="{{ $request->status }}">
                <div class="request-header">
                    <div class="request-info">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <h3 style="margin: 0; color: #111827;">{{ $request->parent_name }}</h3>
                            <span class="status-badge status-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                        </div>
                        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
                            <i class="fas fa-child"></i> {{ $request->child_full_name }} 
                            ({{ $request->child_age_in_months }} months old)
                        </p>
                        <p style="margin: 0.25rem 0 0 0; color: #6b7280; font-size: 0.875rem;">
                            <i class="fas fa-clock"></i> {{ $request->created_at->diffForHumans() }}
                        </p>
                    </div>
                    
                    <div class="request-actions">
                        @if($request->status === 'pending')
                            <button class="btn btn-success approve-request" data-request-id="{{ $request->id }}">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-danger reject-request" data-request-id="{{ $request->id }}">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        @elseif($request->status === 'completed')
                            <button class="btn btn-secondary view-code" data-code="{{ $request->generated_code }}">
                                <i class="fas fa-qrcode"></i> View Code
                            </button>
                        @endif
                        <button class="btn btn-secondary view-details" data-request-id="{{ $request->id }}">
                            <i class="fas fa-eye"></i> Details
                        </button>
                    </div>
                </div>
                
                <div class="request-details">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Parent Email</span>
                            <span class="info-value">{{ $request->parent_email }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Parent Phone</span>
                            <span class="info-value">{{ $request->parent_phone ?: 'Not provided' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Child Birth Date</span>
                            <span class="info-value">{{ $request->child_birth_date->format('M j, Y') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Child Sex</span>
                            <span class="info-value">{{ $request->child_sex }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Barangay</span>
                            <span class="info-value">{{ $request->barangay }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address</span>
                            <span class="info-value">{{ $request->address }}</span>
                        </div>
                        @if($request->status === 'completed' && $request->generated_code)
                            <div class="info-item">
                                <span class="info-label">Generated Code</span>
                                <span class="info-value" style="font-family: monospace; font-size: 1.25rem; color: #059669;">
                                    {{ $request->generated_code }}
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Code Sent At</span>
                                <span class="info-value">{{ $request->code_sent_at->format('M j, Y g:i A') }}</span>
                            </div>
                        @endif
                        @if($request->status === 'rejected' && $request->rejection_reason)
                            <div class="info-item" style="grid-column: 1 / -1;">
                                <span class="info-label">Rejection Reason</span>
                                <span class="info-value" style="color: #dc2626;">{{ $request->rejection_reason }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-qrcode"></i>
                <h3>No Code Requests Found</h3>
                <p>Parents haven't submitted any registration code requests yet.</p>
                <a href="{{ route('code-request.form') }}" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-external-link-alt"></i> Share Request Form with Parents
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($requests->hasPages())
        <div style="margin-top: 2rem;">
            {{ $requests->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Approve request
    document.querySelectorAll('.approve-request').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.dataset.requestId;
            if (confirm('Are you sure you want to approve this request? This will create a patient record and send the registration code via email.')) {
                approveRequest(requestId);
            }
        });
    });
    
    // Reject request
    document.querySelectorAll('.reject-request').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.dataset.requestId;
            const reason = prompt('Please provide a reason for rejection:');
            if (reason && reason.trim()) {
                rejectRequest(requestId, reason.trim());
            }
        });
    });
    
    // View code
    document.querySelectorAll('.view-code').forEach(button => {
        button.addEventListener('click', function() {
            const code = this.dataset.code;
            alert(`Registration Code: ${code}\n\nThis code has been sent to the parent via email.`);
        });
    });
});

function approveRequest(requestId) {
    fetch(`/nutritionist/code-requests/${requestId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the request.');
    });
}

function rejectRequest(requestId, reason) {
    fetch(`/nutritionist/code-requests/${requestId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ rejection_reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the request.');
    });
}

function clearFilters() {
    document.getElementById('status-filter').value = '';
    document.getElementById('date-filter').value = '';
    // Could implement actual filtering logic here
}
</script>
@endpush