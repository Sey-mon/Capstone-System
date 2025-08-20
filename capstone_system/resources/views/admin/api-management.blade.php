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
<script>
async function checkApiStatus() {
    try {
        const response = await fetch('{{ route("admin.api.status") }}');
        const data = await response.json();
        
        if (data.success) {
            alert('API Status: ' + data.data.status + '\nMessage: ' + (data.data.message || 'API is running normally'));
        } else {
            alert('API Check Failed: ' + data.error);
        }
    } catch (error) {
        alert('Error checking API status: ' + error.message);
    }
}
</script>

<style>
.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.action-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    text-decoration: none;
    color: inherit;
}

.action-card.clickable {
    cursor: pointer;
    border: none;
    background: white;
    width: 100%;
    text-align: left;
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.action-icon.success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.action-icon.info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.action-icon.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

.action-content {
    flex: 1;
}

.action-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.action-content p {
    margin: 0;
    color: #6b7280;
    font-size: 0.9rem;
}

.action-arrow {
    color: #9ca3af;
    margin-left: 1rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.info-card {
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.info-card h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
}

.info-card p.code {
    background: #f3f4f6;
    padding: 0.5rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.9rem;
    color: #374151;
    margin: 0;
}

.info-card ul {
    margin: 0;
    padding-left: 1.2rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #6b7280;
}

.info-card code {
    background: #f3f4f6;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-size: 0.85rem;
    color: #374151;
}

.protocols-preview {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    overflow-x: auto;
}

.protocols-preview pre {
    margin: 0;
    font-size: 0.9rem;
    color: #495057;
    white-space: pre-wrap;
}

.stat-card.success { border-left: 4px solid #22c55e; }
.stat-card.danger { border-left: 4px solid #ef4444; }
.stat-card.info { border-left: 4px solid #3b82f6; }
.stat-card.primary { border-left: 4px solid #8b5cf6; }
.stat-card.warning { border-left: 4px solid #f59e0b; }
</style>
@endsection
