@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Welcome back, ' . Auth::user()->first_name . '! Here\'s what\'s happening today.')

@section('navigation')
    <ul>
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link active">
                <i class="fas fa-chart-pie"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.users') }}" class="nav-link">
                <i class="fas fa-users"></i>
                <span class="nav-text">Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.patients') }}" class="nav-link">
                <i class="fas fa-child"></i>
                <span class="nav-text">Patients</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.inventory') }}" class="nav-link">
                <i class="fas fa-boxes"></i>
                <span class="nav-text">Inventory</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.reports') }}" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span class="nav-text">Reports</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.audit.logs') }}" class="nav-link">
                <i class="fas fa-clipboard-check"></i>
                <span class="nav-text">Audit Logs</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.inventory.transactions') }}" class="nav-link">
                <i class="fas fa-exchange-alt"></i>
                <span class="nav-text">Inventory Transactions</span>
            </a>
        </li>
    </ul>
@endsection

@section('content')
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Users</div>
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_users'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+12% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Patients</div>
                <div class="stat-icon success">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_patients'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+8% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Assessments</div>
                <div class="stat-icon warning">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+15% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Inventory Items</div>
                <div class="stat-icon info">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_inventory_items'] }}</div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i>
                <span>-3% from last month</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
            <a href="{{ route('admin.reports') }}" class="btn btn-secondary">
                <i class="fas fa-chart-line"></i>
                View All Reports
            </a>
        </div>
        <div class="card-content">
            @forelse($stats['recent_audit_logs'] as $log)
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <strong>{{ $log->user->first_name ?? 'System' }}</strong> {{ $log->description }}
                            </div>
                            <div class="activity-time">{{ $log->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-3"></i>
                    <p class="text-gray-500">No recent activity found.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-dashboard.js') }}"></script>
@endpush
