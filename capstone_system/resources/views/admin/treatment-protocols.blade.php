@extends('layouts.dashboard')

@section('title', 'Treatment Protocols')

@section('page-title', 'Treatment Protocols')
@section('page-subtitle', 'Evidence-based Treatment and Intervention Protocols')

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

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('admin.api.management') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to API Management
        </a>
    </div>

    @if($protocols)
    <!-- Protocol Overview -->
    <div class="content-section">
        <div class="section-header">
            <h2>Available Treatment Protocols</h2>
            <p>Comprehensive treatment protocols available in the system</p>
        </div>

        <div class="protocol-summary">
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-icon success">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Total Protocols</h3>
                        <p class="summary-number">{{ count($protocols) }}</p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon info">
                        <i class="fas fa-child"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Age Groups Covered</h3>
                        <p class="summary-number">0-60 months</p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon warning">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Evidence-Based</h3>
                        <p class="summary-number">WHO Standards</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Protocol Details -->
        <div class="protocol-details">
            <ul class="nav nav-tabs" id="protocolTabs" role="tablist">
                @foreach($protocols as $index => $protocol)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                            id="protocol-{{ $index }}-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#protocol-{{ $index }}" 
                            type="button" 
                            role="tab">
                        {{ $protocol['name'] ?? "Protocol " . ($index + 1) }}
                    </button>
                </li>
                @endforeach
            </ul>
            
            <div class="tab-content" id="protocolTabContent">
                @foreach($protocols as $index => $protocol)
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                     id="protocol-{{ $index }}" 
                     role="tabpanel" 
                     aria-labelledby="protocol-{{ $index }}-tab">
                    
                    <div class="protocol-content">
                        <h4>{{ $protocol['name'] ?? "Protocol " . ($index + 1) }}</h4>
                        
                        @if(isset($protocol['description']))
                        <div class="protocol-section">
                            <h5>Description</h5>
                            <p>{{ $protocol['description'] }}</p>
                        </div>
                        @endif
                        
                        @if(isset($protocol['criteria']))
                        <div class="protocol-section">
                            <h5>Application Criteria</h5>
                            <ul>
                                @if(is_array($protocol['criteria']))
                                    @foreach($protocol['criteria'] as $criterion)
                                    <li>{{ $criterion }}</li>
                                    @endforeach
                                @else
                                    <li>{{ $protocol['criteria'] }}</li>
                                @endif
                            </ul>
                        </div>
                        @endif
                        
                        @if(isset($protocol['interventions']))
                        <div class="protocol-section">
                            <h5>Interventions</h5>
                            @if(is_array($protocol['interventions']))
                                <ul>
                                    @foreach($protocol['interventions'] as $intervention)
                                    <li>{{ is_array($intervention) ? json_encode($intervention) : $intervention }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>{{ $protocol['interventions'] }}</p>
                            @endif
                        </div>
                        @endif
                        
                        @if(isset($protocol['monitoring']))
                        <div class="protocol-section">
                            <h5>Monitoring Schedule</h5>
                            @if(is_array($protocol['monitoring']))
                                <ul>
                                    @foreach($protocol['monitoring'] as $item)
                                    <li>{{ is_array($item) ? json_encode($item) : $item }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>{{ $protocol['monitoring'] }}</p>
                            @endif
                        </div>
                        @endif
                        
                        <!-- Raw Protocol Data (Collapsible) -->
                        <div class="protocol-section">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#rawData{{ $index }}">
                                <i class="fas fa-code"></i> View Raw Data
                            </button>
                            <div class="collapse mt-2" id="rawData{{ $index }}">
                                <div class="raw-data">
                                    <pre>{{ json_encode($protocol, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    @else
    <!-- No Protocols Available -->
    <div class="content-section">
        <div class="no-data">
            <div class="no-data-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>No Treatment Protocols Available</h3>
            <p>Unable to retrieve treatment protocols from the API. Please check the API connection.</p>
            <a href="{{ route('admin.api.management') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to API Management
            </a>
        </div>
    </div>
    @endif

    <!-- Information Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>About Treatment Protocols</h2>
            <p>Understanding evidence-based malnutrition treatment</p>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <h4>WHO Guidelines</h4>
                <p>All protocols follow WHO guidelines for the management of severe acute malnutrition and moderate acute malnutrition in children.</p>
            </div>

            <div class="info-item">
                <h4>Age-Specific Protocols</h4>
                <p>Different protocols apply based on the child's age, nutritional status, and presence of complications.</p>
            </div>

            <div class="info-item">
                <h4>Evidence-Based</h4>
                <p>All treatment recommendations are based on current scientific evidence and best practices in pediatric nutrition.</p>
            </div>

            <div class="info-item">
                <h4>Regular Updates</h4>
                <p>Protocols are regularly updated to reflect the latest research and clinical guidelines.</p>
            </div>
        </div>
    </div>
@endsection

<style>
.protocol-summary {
    margin-bottom: 2rem;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
    display: flex;
    align-items: center;
}

.summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.summary-icon.success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.summary-icon.info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.summary-icon.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

.summary-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
}

.summary-number {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.protocol-details {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
    overflow: hidden;
}

.nav-tabs {
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 0;
}

.nav-tabs .nav-link {
    border: none;
    padding: 1rem 1.5rem;
    color: #6b7280;
    border-bottom: 3px solid transparent;
}

.nav-tabs .nav-link.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
    background: none;
}

.protocol-content {
    padding: 2rem;
}

.protocol-content h4 {
    margin: 0 0 1.5rem 0;
    color: #1f2937;
    font-size: 1.3rem;
    font-weight: 600;
}

.protocol-section {
    margin-bottom: 2rem;
}

.protocol-section h5 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1.1rem;
    font-weight: 600;
}

.protocol-section p {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.protocol-section ul {
    margin: 0;
    padding-left: 1.2rem;
}

.protocol-section li {
    color: #6b7280;
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.raw-data {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    overflow-x: auto;
}

.raw-data pre {
    margin: 0;
    font-size: 0.85rem;
    color: #495057;
    white-space: pre-wrap;
}

.no-data {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.no-data-icon {
    font-size: 4rem;
    color: #f59e0b;
    margin-bottom: 1.5rem;
}

.no-data h3 {
    margin-bottom: 1rem;
    color: #374151;
}

.no-data p {
    color: #6b7280;
    margin-bottom: 2rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.info-item {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.info-item h4 {
    color: #1f2937;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.info-item p {
    color: #6b7280;
    margin: 0;
    line-height: 1.6;
}
</style>
@endsection
