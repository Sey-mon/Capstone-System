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

    <!-- Modern Page Header -->
    <div class="protocols-page-header">
        <div class="header-background">
            <div class="medical-pattern"></div>
        </div>
        <div class="header-content">
            <div class="header-top-row">
                <div class="breadcrumb-modern">
                    <a href="{{ route('admin.api.management') }}" class="breadcrumb-link">
                        <i class="fas fa-arrow-left"></i>
                        <span>API Management</span>
                    </a>
                    <div class="breadcrumb-separator">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="breadcrumb-current">Treatment Protocols</div>
                </div>
                <div class="header-badge">
                    <span class="quality-badge">
                        <i class="fas fa-shield-check"></i>
                        WHO Certified
                    </span>
                </div>
            </div>
            <div class="header-main">
                <div class="header-icon-large">
                    <i class="fas fa-file-medical-alt"></i>
                </div>
                <div class="header-text">
                    <h1>Clinical Treatment Protocols</h1>
                    <p>Evidence-based medical interventions for malnutrition management following WHO guidelines</p>
                </div>
            </div>
        </div>
    </div>

    @if($protocols && isset($protocols['protocols']))
    <!-- Modern Protocol Overview -->
    <div class="protocols-overview-section">
        <div class="section-header-protocols">
            <div class="section-title-protocols">
                <i class="fas fa-clipboard-list"></i>
                <h2>Available Treatment Protocols</h2>
            </div>
            <p>Comprehensive evidence-based protocols for malnutrition intervention and management</p>
        </div>

        <div class="protocol-metrics-modern">
            <div class="metrics-grid">
                <div class="metric-card-modern primary">
                    <div class="metric-decoration"></div>
                    <div class="metric-icon">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ isset($protocols['protocols']['protocols']) ? count($protocols['protocols']['protocols']) : 0 }}</div>
                        <div class="metric-label">Treatment Protocols</div>
                        <div class="metric-description">Available clinical protocols</div>
                    </div>
                    <div class="metric-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>Active</span>
                    </div>
                </div>
                
                <div class="metric-card-modern success">
                    <div class="metric-decoration"></div>
                    <div class="metric-icon">
                        <i class="fas fa-baby"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">0-60</div>
                        <div class="metric-label">Age Coverage (Months)</div>
                        <div class="metric-description">Pediatric age range</div>
                    </div>
                    <div class="metric-trend">
                        <i class="fas fa-check-circle"></i>
                        <span>Complete</span>
                    </div>
                </div>
                
                <div class="metric-card-modern info">
                    <div class="metric-decoration"></div>
                    <div class="metric-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">WHO</div>
                        <div class="metric-label">Evidence Standard</div>
                        <div class="metric-description">Certified guidelines</div>
                    </div>
                    <div class="metric-trend">
                        <i class="fas fa-shield-check"></i>
                        <span>Verified</span>
                    </div>
                </div>

                <div class="metric-card-modern warning">
                    <div class="metric-decoration"></div>
                    <div class="metric-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">2024</div>
                        <div class="metric-label">Last Updated</div>
                        <div class="metric-description">Latest clinical revision</div>
                    </div>
                    <div class="metric-trend">
                        <i class="fas fa-clock"></i>
                        <span>Current</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Protocol Details -->
        <div class="protocols-details-modern">
            <div class="protocols-navigation">
                <div class="nav-header">
                    <h3>Clinical Protocols</h3>
                    <span class="protocol-count">{{ isset($protocols['protocols']['protocols']) ? count($protocols['protocols']['protocols']) : 0 }} Available</span>
                </div>
                <div class="nav-tabs-modern" id="protocolTabs" role="tablist">
                    @if(isset($protocols['protocols']['protocols']))
                        @foreach($protocols['protocols']['protocols'] as $index => $protocolName)
                        <button class="nav-tab-modern {{ $index === 0 ? 'active' : '' }}" 
                                id="protocol-{{ $index }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#protocol-{{ $index }}" 
                                type="button" 
                                role="tab">
                            <div class="tab-icon">
                                <i class="fas {{ $index === 0 ? 'fa-stethoscope' : ($index === 1 ? 'fa-user-md' : 'fa-heartbeat') }}"></i>
                            </div>
                            <div class="tab-content-text">
                                <span class="tab-title">{{ ucwords(str_replace('_', ' ', $protocolName)) }}</span>
                                <span class="tab-subtitle">{{ $index === 0 ? 'Severe Acute' : ($index === 1 ? 'Moderate Acute' : 'Standard Care') }}</span>
                            </div>
                            <div class="tab-indicator"></div>
                        </button>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <div class="tab-content-modern" id="protocolTabContent">
                @if(isset($protocols['protocols']['protocols']))
                    @foreach($protocols['protocols']['protocols'] as $index => $protocolName)
                    <div class="tab-pane-modern fade {{ $index === 0 ? 'show active' : '' }}" 
                         id="protocol-{{ $index }}" 
                         role="tabpanel" 
                         aria-labelledby="protocol-{{ $index }}-tab">
                        
                        <div class="protocol-content-modern">
                            <div class="protocol-header-modern">
                                <div class="protocol-title-section">
                                    <h4>{{ ucwords(str_replace('_', ' ', $protocolName)) }}</h4>
                                    <div class="protocol-badges">
                                        <span class="protocol-badge evidence">Evidence-Based</span>
                                        <span class="protocol-badge who">WHO Guidelines</span>
                                        <span class="protocol-badge pediatric">Pediatric</span>
                                    </div>
                                </div>
                                <div class="protocol-actions">
                                    <button class="btn-protocol-action" onclick="printProtocol('{{ $protocolName }}')">
                                        <i class="fas fa-print"></i>
                                        Print
                                    </button>
                                    <button class="btn-protocol-action" onclick="downloadProtocol('{{ $protocolName }}')">
                                        <i class="fas fa-download"></i>
                                        Download
                                    </button>
                                </div>
                            </div>
                            
                            <div class="protocol-body-modern">
                                <div class="protocol-overview">
                                    <div class="overview-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="overview-content">
                                        <h5>Protocol Overview</h5>
                                        <p>{{ $protocolName === 'sam_protocol' ? 'Comprehensive treatment protocol for children with Severe Acute Malnutrition (SAM). Includes detailed intervention strategies, medication protocols, and monitoring guidelines.' : ($protocolName === 'mam_protocol' ? 'Evidence-based protocol for managing Moderate Acute Malnutrition (MAM). Focuses on nutritional rehabilitation and preventive care measures.' : 'Standard care protocol for normal nutritional status with preventive measures and growth monitoring guidelines.') }}</p>
                                    </div>
                                </div>

                                <div class="protocol-sections">
                                    <div class="protocol-card-section">
                                        <div class="section-header-card">
                                            <i class="fas fa-clipboard-check"></i>
                                            <h6>Treatment Guidelines</h6>
                                        </div>
                                        <div class="section-content-card">
                                            <ul class="treatment-steps">
                                                <li><strong>Initial Assessment:</strong> Comprehensive nutritional and medical evaluation</li>
                                                <li><strong>Intervention Plan:</strong> Customized treatment based on severity and complications</li>
                                                <li><strong>Monitoring:</strong> Regular follow-up and progress evaluation</li>
                                                <li><strong>Family Education:</strong> Caregiver training and support</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="protocol-card-section">
                                        <div class="section-header-card">
                                            <i class="fas fa-users"></i>
                                            <h6>Target Population</h6>
                                        </div>
                                        <div class="section-content-card">
                                            <div class="population-specs">
                                                <div class="spec-item">
                                                    <span class="spec-label">Age Range:</span>
                                                    <span class="spec-value">0-60 months</span>
                                                </div>
                                                <div class="spec-item">
                                                    <span class="spec-label">Condition:</span>
                                                    <span class="spec-value">{{ $protocolName === 'sam_protocol' ? 'Severe Acute Malnutrition' : ($protocolName === 'mam_protocol' ? 'Moderate Acute Malnutrition' : 'Normal Growth Monitoring') }}</span>
                                                </div>
                                                <div class="spec-item">
                                                    <span class="spec-label">Setting:</span>
                                                    <span class="spec-value">{{ $protocolName === 'sam_protocol' ? 'Hospital/Clinic' : 'Community/Outpatient' }}</span>
                                                </div>
                                            </div>
                                        </div>
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
    <!-- Modern No Protocols Available -->
    <div class="no-protocols-modern">
        <div class="no-protocols-container">
            <div class="no-protocols-visual">
                <div class="error-icon-modern">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="error-animation">
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring delay-1"></div>
                    <div class="pulse-ring delay-2"></div>
                </div>
            </div>
            <div class="no-protocols-content">
                <h3>No Treatment Protocols Available</h3>
                <p>Unable to retrieve treatment protocols from the API. This could be due to a connection issue or the API service being temporarily unavailable.</p>
                <div class="error-actions">
                    <button class="btn-retry" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                        Retry Connection
                    </button>
                    <a href="{{ route('admin.api.management') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Back to API Management
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modern Information Section -->
    <div class="clinical-info-section">
        <div class="section-header-protocols">
            <div class="section-title-protocols">
                <i class="fas fa-book-medical"></i>
                <h2>Clinical Protocol Framework</h2>
            </div>
            <p>Understanding evidence-based malnutrition treatment methodologies and implementation guidelines</p>
        </div>

        <div class="clinical-info-grid">
            <div class="clinical-info-card primary">
                <div class="info-decoration"></div>
                <div class="info-header">
                    <div class="info-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h4>WHO Guidelines Compliance</h4>
                </div>
                <div class="info-content">
                    <p>All protocols strictly adhere to World Health Organization guidelines for the management of severe acute malnutrition (SAM) and moderate acute malnutrition (MAM) in pediatric populations.</p>
                    <div class="info-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>WHO 2013 Guidelines</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>International Standards</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clinical-info-card success">
                <div class="info-decoration"></div>
                <div class="info-header">
                    <div class="info-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h4>Age-Specific Interventions</h4>
                </div>
                <div class="info-content">
                    <p>Tailored treatment protocols designed for different pediatric age groups, considering developmental needs, nutritional requirements, and clinical complications.</p>
                    <div class="info-features">
                        <div class="feature-item">
                            <i class="fas fa-baby"></i>
                            <span>0-6 months (Infants)</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-child"></i>
                            <span>6-60 months (Children)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clinical-info-card info">
                <div class="info-decoration"></div>
                <div class="info-header">
                    <div class="info-icon">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <h4>Evidence-Based Practice</h4>
                </div>
                <div class="info-content">
                    <p>Treatment recommendations based on the latest scientific research, clinical trials, and best practices in pediatric nutrition and malnutrition management.</p>
                    <div class="info-features">
                        <div class="feature-item">
                            <i class="fas fa-flask"></i>
                            <span>Clinical Research</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Outcome Studies</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clinical-info-card warning">
                <div class="info-decoration"></div>
                <div class="info-header">
                    <div class="info-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h4>Continuous Updates</h4>
                </div>
                <div class="info-content">
                    <p>Protocols undergo regular review and updates to incorporate new research findings, clinical guidelines, and technological advances in malnutrition treatment.</p>
                    <div class="info-features">
                        <div class="feature-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>Quarterly Reviews</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-bell"></i>
                            <span>Update Notifications</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/treatment-protocols.css') }}?v={{ time() }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/treatment-protocols.js') }}?v={{ time() }}"></script>
@endpush
