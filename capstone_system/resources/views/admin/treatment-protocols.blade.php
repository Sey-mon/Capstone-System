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

    @if($protocols && isset($protocols['protocols']))
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
                        <p class="summary-number">{{ isset($protocols['protocols']['protocols']) ? count($protocols['protocols']['protocols']) : 0 }}</p>
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
                @if(isset($protocols['protocols']['protocols']))
                    @foreach($protocols['protocols']['protocols'] as $index => $protocolName)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                id="protocol-{{ $index }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#protocol-{{ $index }}" 
                                type="button" 
                                role="tab">
                            {{ ucwords(str_replace('_', ' ', $protocolName)) }}
                        </button>
                    </li>
                    @endforeach
                @endif
            </ul>
            
            <div class="tab-content" id="protocolTabContent">
                @if(isset($protocols['protocols']['protocols']))
                    @foreach($protocols['protocols']['protocols'] as $index => $protocolName)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                         id="protocol-{{ $index }}" 
                         role="tabpanel" 
                         aria-labelledby="protocol-{{ $index }}-tab">
                        
                        <div class="protocol-content">
                            <h4>{{ ucwords(str_replace('_', ' ', $protocolName)) }}</h4>
                            
                            <div class="protocol-section">
                                <h5>Protocol Information</h5>
                                <p>This is the {{ $protocolName }} treatment protocol.</p>
                            </div>
                            
                            <!-- Raw Protocol Data (Collapsible) -->
                            <div class="protocol-section">
                                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#rawData{{ $index }}">
                                    <i class="fas fa-code"></i> View Full Protocol Data
                                </button>
                                <div class="collapse mt-2" id="rawData{{ $index }}">
                                    <div class="raw-data">
                                        <pre>{{ json_encode($protocols['protocols'], JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
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

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/treatment-protocols.css') }}">
@endpush
