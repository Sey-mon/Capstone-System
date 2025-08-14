@extends('layouts.dashboard')

@section('title', 'Audit Logs')

@section('page-title', 'Audit Logs')
@section('page-subtitle', 'Monitor all system activities and user actions.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-audit-logs.css') }}">
@endpush

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clipboard-check"></i>
                System Audit Logs
            </h3>
            <div class="card-actions">
                <button class="btn btn-secondary" onclick="window.location.reload()">
                    <i class="fas fa-sync"></i>
                    Refresh
                </button>
            </div>
        </div>
        
        <div class="card-content">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <span class="badge badge-info">#{{ $log->log_id }}</span>
                                    </td>
                                    <td>
                                        <div class="user-info-cell">
                                            @if($log->user)
                                                <div class="user-avatar-small">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="user-details-small">
                                                    <div class="user-name">{{ $log->user->first_name }} {{ $log->user->last_name }}</div>
                                                    <div class="user-role">{{ $log->user->role->role_name ?? 'Unknown' }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $actionClass = match(strtolower($log->action)) {
                                                'create', 'insert' => 'success',
                                                'update', 'edit' => 'warning',
                                                'delete', 'remove' => 'danger',
                                                'login' => 'info',
                                                'logout' => 'secondary',
                                                default => 'primary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $actionClass }}">{{ ucfirst($log->action) }}</span>
                                    </td>
                                    <td>
                                        <div class="description-cell">
                                            {{ $log->description ?? $log->action }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="timestamp-cell">
                                            <div class="timestamp">{{ $log->log_timestamp ? $log->log_timestamp->format('M d, Y') : 'N/A' }}</div>
                                            <div class="time">{{ $log->log_timestamp ? $log->log_timestamp->format('h:i A') : 'N/A' }}</div>
                                            <small class="text-muted">{{ $log->log_timestamp ? $log->log_timestamp->diffForHumans() : 'N/A' }}</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h3 class="empty-state-title">No Audit Logs Found</h3>
                    <p class="empty-state-description">
                        No audit logs have been recorded yet. System activities will appear here once they start being logged.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection


