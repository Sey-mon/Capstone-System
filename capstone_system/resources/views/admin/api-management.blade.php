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
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- API Status Overview -->
    <div class="stats-grid">
        <div class="stat-card {{ $apiStatus['status'] === 'healthy' ? 'success' : 'danger' }}">
            <div class="stat-header">
                <div class="stat-title">API Status</div>
                <div class="stat-icon {{ $apiStatus['status'] === 'healthy' ? 'success' : 'danger' }}">
                    <i class="fas {{ $apiStatus['status'] === 'healthy' ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                </div>
            </div>
            <div class="stat-value">{{ ucfirst($apiStatus['status']) }}</div>
            <div class="stat-change">
                <span>{{ $apiStatus['message'] ?? 'API is running normally' }}</span>
            </div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-header">
                <div class="stat-title">Treatment Protocols</div>
                <div class="stat-icon info">
                    <i class="fas fa-procedures"></i>
                </div>
            </div>
            <div class="stat-value">{{ $protocols ? count($protocols) : 'N/A' }}</div>
            <div class="stat-change">
                <span>Available protocols</span>
            </div>
        </div>

        <div class="stat-card primary">
            <div class="stat-header">
                <div class="stat-title">WHO Standards</div>
                <div class="stat-icon primary">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="stat-value">Active</div>
            <div class="stat-change">
                <span>Reference data loaded</span>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-title">API Version</div>
                <div class="stat-icon warning">
                    <i class="fas fa-code-branch"></i>
                </div>
            </div>
            <div class="stat-value">{{ $apiStatus['version'] ?? '1.0.0' }}</div>
            <div class="stat-change">
                <span>Current version</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="content-section">
        <div class="section-header">
            <h2>Quick Actions</h2>
            <p>Manage API components and reference data</p>
        </div>

        <div class="action-grid">
            <a href="{{ route('admin.who.standards') }}" class="action-card">
                <div class="action-icon success">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="action-content">
                    <h3>WHO Standards</h3>
                    <p>View and manage WHO growth standards reference data</p>
                </div>
                <div class="action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="{{ route('admin.treatment.protocols') }}" class="action-card">
                <div class="action-icon info">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div class="action-content">
                    <h3>Treatment Protocols</h3>
                    <p>View available treatment and intervention protocols</p>
                </div>
                <div class="action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <button onclick="checkApiStatus()" class="action-card clickable">
                <div class="action-icon warning">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="action-content">
                    <h3>API Health Check</h3>
                    <p>Check current API connectivity and health status</p>
                </div>
                <div class="action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </button>
        </div>
    </div>

    <!-- API Information -->
    <div class="content-section">
        <div class="section-header">
            <h2>API Information</h2>
            <p>Current configuration and endpoints</p>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>API Endpoint</h3>
                <p class="code">{{ config('services.malnutrition_api.base_url') }}</p>
            </div>
            
            <div class="info-card">
                <h3>Timeout Setting</h3>
                <p>{{ config('services.malnutrition_api.timeout') }} seconds</p>
            </div>
            
            <div class="info-card">
                <h3>Available Endpoints</h3>
                <ul>
                    <li><code>POST /assess/complete</code> - Full assessment</li>
                    <li><code>POST /assess/malnutrition-only</code> - Quick assessment</li>
                    <li><code>GET /reference/who-standards</code> - WHO data</li>
                    <li><code>GET /reference/treatment-protocols</code> - Protocols</li>
                    <li><code>GET /health</code> - Health check</li>
                </ul>
            </div>
        </div>
    </div>

    @if($protocols)
    <!-- Recent API Activity -->
    <div class="content-section">
        <div class="section-header">
            <h2>Treatment Protocols Overview</h2>
            <p>Available treatment protocols in the system</p>
        </div>

        <div class="table-container">
            <div class="protocols-preview">
                <pre>{{ json_encode($protocols, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
<script src="{{ asset('js/api-management.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/api-management.css') }}">
@endsection
