@extends('layouts.dashboard')

@section('title', 'API Management')

@section('page-title', 'API Management')
@section('page-subtitle', 'Manage Malnutrition Assessment API and Reference Data')

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Error Display -->
    @if ($errors->any())
        <div class="alert alert-danger modern-alert">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-content">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="page-header-modern">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="header-text">
                <h1>API Control Center</h1>
                <p>Monitor and manage your malnutrition assessment API infrastructure</p>
            </div>
        </div>
        <div class="header-actions">
            <button onclick="checkApiStatus()" class="btn-modern btn-primary">
                <i class="fas fa-sync-alt"></i>
                Refresh Status
            </button>
        </div>
    </div>

    <!-- API Status Overview -->
    <div class="stats-grid-modern">
        <div class="stat-card-modern {{ $apiStatus['status'] === 'healthy' ? 'success' : 'danger' }}">
            <div class="stat-gradient-bg"></div>
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon {{ $apiStatus['status'] === 'healthy' ? 'success' : 'danger' }}">
                        <i class="fas {{ $apiStatus['status'] === 'healthy' ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                    </div>
                    <div class="stat-badge {{ $apiStatus['status'] === 'healthy' ? 'success' : 'danger' }}">
                        {{ $apiStatus['status'] === 'healthy' ? 'ONLINE' : 'OFFLINE' }}
                    </div>
                </div>
                <div class="stat-title">API Status</div>
                <div class="stat-value">{{ ucfirst($apiStatus['status']) }}</div>
                <div class="stat-description">
                    {{ $apiStatus['message'] ?? 'API is running normally' }}
                </div>
            </div>
        </div>
        
        <div class="stat-card-modern info">
            <div class="stat-gradient-bg"></div>
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon info">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <div class="stat-badge info">READY</div>
                </div>
                <div class="stat-title">Treatment Protocols</div>
                <div class="stat-value">Available</div>
                <div class="stat-description">
                    Clinical guidelines and protocols loaded
                </div>
            </div>
        </div>

        <div class="stat-card-modern primary">
            <div class="stat-gradient-bg"></div>
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-badge primary">ACTIVE</div>
                </div>
                <div class="stat-title">WHO Standards</div>
                <div class="stat-value">Active</div>
                <div class="stat-description">
                    Reference data synchronized and updated
                </div>
            </div>
        </div>

        <div class="stat-card-modern warning">
            <div class="stat-gradient-bg"></div>
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-code-branch"></i>
                    </div>
                    <div class="stat-badge warning">v{{ $apiStatus['version'] ?? '1.0.0' }}</div>
                </div>
                <div class="stat-title">API Version</div>
                <div class="stat-value">{{ $apiStatus['version'] ?? '1.0.0' }}</div>
                <div class="stat-description">
                    Current stable release version
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="content-section-modern">
        <div class="section-header-modern">
            <div class="section-title">
                <i class="fas fa-bolt"></i>
                <h2>Quick Actions</h2>
            </div>
            <p>Manage API components and reference data with ease</p>
        </div>

        <div class="action-grid-modern">
            <a href="{{ route('admin.who.standards') }}" class="action-card-modern success">
                <div class="action-decoration"></div>
                <div class="action-icon-modern">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="action-content-modern">
                    <h3>WHO Standards</h3>
                    <p>View and manage WHO growth standards reference data</p>
                    <div class="action-meta">
                        <span class="action-label">Reference Data</span>
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                </div>
                <div class="action-hover-effect"></div>
            </a>

            <a href="{{ route('admin.treatment.protocols') }}" class="action-card-modern info">
                <div class="action-decoration"></div>
                <div class="action-icon-modern">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div class="action-content-modern">
                    <h3>Treatment Protocols</h3>
                    <p>View available treatment and intervention protocols</p>
                    <div class="action-meta">
                        <span class="action-label">Clinical Guidelines</span>
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                </div>
                <div class="action-hover-effect"></div>
            </a>

            <input type="hidden" id="apiManagementStatusRoute" value="{{ route('admin.api.status') }}">
            <button onclick="checkApiStatus()" class="action-card-modern warning clickable">
                <div class="action-decoration"></div>
                <div class="action-icon-modern">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="action-content-modern">
                    <h3>API Health Check</h3>
                    <p>Check current API connectivity and health status</p>
                    <div class="action-meta">
                        <span class="action-label">System Monitor</span>
                        <i class="fas fa-sync-alt"></i>
                    </div>
                </div>
                <div class="action-hover-effect"></div>
            </button>
        </div>
    </div>

            
            <div class="config-card-modern info full-width">
                <div class="config-header">
                    <div class="config-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <h3>Available Endpoints</h3>
                </div>
                <div class="config-content">
                    <div class="endpoints-grid">
                        <div class="endpoint-item">
                            <div class="endpoint-method post">POST</div>
                            <div class="endpoint-path">/assess/complete</div>
                            <div class="endpoint-desc">Full malnutrition assessment</div>
                        </div>
                        <div class="endpoint-item">
                            <div class="endpoint-method post">POST</div>
                            <div class="endpoint-path">/assess/malnutrition-only</div>
                            <div class="endpoint-desc">Quick assessment screening</div>
                        </div>
                        <div class="endpoint-item">
                            <div class="endpoint-method get">GET</div>
                            <div class="endpoint-path">/reference/who-standards</div>
                            <div class="endpoint-desc">WHO reference data</div>
                        </div>
                        <div class="endpoint-item">
                            <div class="endpoint-method get">GET</div>
                            <div class="endpoint-path">/reference/treatment-protocols</div>
                            <div class="endpoint-desc">Treatment protocols</div>
                        </div>
                        <div class="endpoint-item">
                            <div class="endpoint-method get">GET</div>
                            <div class="endpoint-path">/health</div>
                            <div class="endpoint-desc">API health status</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/api-management.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/api-management.js') }}"></script>
@endpush
