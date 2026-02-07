@extends('layouts.dashboard')

@section('title', 'WHO Standards')

@section('page-title', 'WHO Growth Standards')
@section('page-subtitle', 'WHO Reference Data for Child Growth Assessment')

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
    <div class="who-page-header">
        <div class="header-background">
            <div class="header-pattern"></div>
        </div>
        <div class="header-content">
            <div class="breadcrumb-modern">
                <a href="{{ route('admin.api.management') }}" class="breadcrumb-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>API Management</span>
                </a>
                <div class="breadcrumb-separator">
                    <i class="fas fa-chevron-right"></i>
                </div>
                <div class="breadcrumb-current">WHO Standards</div>
            </div>
            <div class="header-main">
                <div class="header-icon-large">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="header-text">
                    <h1>WHO Growth Standards</h1>
                    <p>Comprehensive reference data for accurate child growth assessment and malnutrition detection</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-mini">
                    <span class="stat-number">{{ (isset($maleWfa) ? count($maleWfa['data'] ?? []) : 0) + (isset($femaleWfa) ? count($femaleWfa['data'] ?? []) : 0) + (isset($maleLhfa) ? count($maleLhfa['data'] ?? []) : 0) + (isset($femaleLhfa) ? count($femaleLhfa['data'] ?? []) : 0) }}</span>
                    <span class="stat-label">Total Records</span>
                </div>
                <div class="stat-mini">
                    <span class="stat-number">4</span>
                    <span class="stat-label">Standards</span>
                </div>
                <div class="stat-mini">
                    <span class="stat-number">0-60</span>
                    <span class="stat-label">Months Range</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Standards Overview -->
    <div class="standards-section-modern">
        <div class="section-header-who">
            <div class="section-title-who">
                <i class="fas fa-database"></i>
                <h2>Growth Standards Dataset</h2>
            </div>
            <p>Interactive reference data for precise malnutrition assessment calculations</p>
        </div>

        <div class="standards-grid-modern">
            @if(isset($maleWfa))
            <div class="standard-card-modern male-data" data-category="weight">
                <div class="card-decoration"></div>
                <div class="card-header-modern">
                    <div class="gender-icon male">
                        <i class="fas fa-mars"></i>
                    </div>
                    <div class="card-badge weight">WFA</div>
                </div>
                <div class="card-content-modern">
                    <h3>Male - Weight for Age</h3>
                    <p>Underweight assessment reference data</p>
                    <div class="data-metrics">
                        <div class="metric">
                            <span class="metric-value">{{ count($maleWfa['data'] ?? []) }}</span>
                            <span class="metric-label">Records</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">0-60</span>
                            <span class="metric-label">Months</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">{{ $maleWfa['last_updated'] ?? 'N/A' }}</span>
                            <span class="metric-label">Updated</span>
                        </div>
                    </div>
                </div>
                <div class="card-actions">
                    <button onclick="showStandardDataModern('male-wfa', 'Male Weight-for-Age')" class="btn-view-data" data-standard="{{ json_encode($maleWfa) }}">
                        <i class="fas fa-chart-area"></i>
                        <span>View Dataset</span>
                    </button>
                </div>
            </div>
            @endif

            @if(isset($femaleWfa))
            <div class="standard-card-modern female-data" data-category="weight">
                <div class="card-decoration"></div>
                <div class="card-header-modern">
                    <div class="gender-icon female">
                        <i class="fas fa-venus"></i>
                    </div>
                    <div class="card-badge weight">WFA</div>
                </div>
                <div class="card-content-modern">
                    <h3>Female - Weight for Age</h3>
                    <p>Underweight assessment reference data</p>
                    <div class="data-metrics">
                        <div class="metric">
                            <span class="metric-value">{{ count($femaleWfa['data'] ?? []) }}</span>
                            <span class="metric-label">Records</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">0-60</span>
                            <span class="metric-label">Months</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">{{ $femaleWfa['last_updated'] ?? 'N/A' }}</span>
                            <span class="metric-label">Updated</span>
                        </div>
                    </div>
                </div>
                <div class="card-actions">
                    <button onclick="showStandardDataModern('female-wfa', 'Female Weight-for-Age')" class="btn-view-data" data-standard="{{ json_encode($femaleWfa) }}">
                        <i class="fas fa-chart-area"></i>
                        <span>View Dataset</span>
                    </button>
                </div>
            </div>
            @endif

            @if(isset($maleLhfa))
            <div class="standard-card-modern male-data" data-category="height">
                <div class="card-decoration"></div>
                <div class="card-header-modern">
                    <div class="gender-icon male">
                        <i class="fas fa-mars"></i>
                    </div>
                    <div class="card-badge height">LFA</div>
                </div>
                <div class="card-content-modern">
                    <h3>Male - Length/Height for Age</h3>
                    <p>Stunting assessment reference data</p>
                    <div class="data-metrics">
                        <div class="metric">
                            <span class="metric-value">{{ count($maleLhfa['data'] ?? []) }}</span>
                            <span class="metric-label">Records</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">0-60</span>
                            <span class="metric-label">Months</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">{{ $maleLhfa['last_updated'] ?? 'N/A' }}</span>
                            <span class="metric-label">Updated</span>
                        </div>
                    </div>
                </div>
                <div class="card-actions">
                    <button onclick="showStandardDataModern('male-lhfa', 'Male Length/Height-for-Age')" class="btn-view-data" data-standard="{{ json_encode($maleLhfa) }}">
                        <i class="fas fa-chart-area"></i>
                        <span>View Dataset</span>
                    </button>
                </div>
            </div>
            @endif

            @if(isset($femaleLhfa))
            <div class="standard-card-modern female-data" data-category="height">
                <div class="card-decoration"></div>
                <div class="card-header-modern">
                    <div class="gender-icon female">
                        <i class="fas fa-venus"></i>
                    </div>
                    <div class="card-badge height">LFA</div>
                </div>
                <div class="card-content-modern">
                    <h3>Female - Length/Height for Age</h3>
                    <p>Stunting assessment reference data</p>
                    <div class="data-metrics">
                        <div class="metric">
                            <span class="metric-value">{{ count($femaleLhfa['data'] ?? []) }}</span>
                            <span class="metric-label">Records</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">0-60</span>
                            <span class="metric-label">Months</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value">{{ $femaleLhfa['last_updated'] ?? 'N/A' }}</span>
                            <span class="metric-label">Updated</span>
                        </div>
                    </div>
                </div>
                <div class="card-actions">
                    <button onclick="showStandardDataModern('female-lhfa', 'Female Length/Height-for-Age')" class="btn-view-data" data-standard="{{ json_encode($femaleLhfa) }}">
                        <i class="fas fa-chart-area"></i>
                        <span>View Dataset</span>
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Modern Information Section -->
    <div class="knowledge-section-modern">
        <div class="section-header-who">
            <div class="section-title-who">
                <i class="fas fa-graduation-cap"></i>
                <h2>Understanding WHO Standards</h2>
            </div>
            <p>Comprehensive guide to growth assessment methodologies and interpretation</p>
        </div>

        <div class="knowledge-grid-modern">
            <div class="knowledge-card-modern primary">
                <div class="knowledge-icon">
                    <i class="fas fa-weight"></i>
                </div>
                <div class="knowledge-content">
                    <h4>Weight for Age (WFA)</h4>
                    <p>Assesses underweight status by comparing body mass to chronological age</p>
                    <div class="classification-tags">
                        <div class="tag severe">Below -3 SD: Severely underweight</div>
                        <div class="tag moderate">Below -2 SD: Underweight</div>
                        <div class="tag normal">-2 to +2 SD: Normal</div>
                    </div>
                </div>
                <div class="knowledge-visual">
                    <div class="progress-indicator">
                        <div class="progress-bar wfa"></div>
                    </div>
                </div>
            </div>

            <div class="knowledge-card-modern success">
                <div class="knowledge-icon">
                    <i class="fas fa-ruler-vertical"></i>
                </div>
                <div class="knowledge-content">
                    <h4>Length/Height for Age (LFA/HFA)</h4>
                    <p>Evaluates stunting by measuring achieved growth relative to age</p>
                    <div class="classification-tags">
                        <div class="tag severe">Below -3 SD: Severely stunted</div>
                        <div class="tag moderate">Below -2 SD: Stunted</div>
                        <div class="tag normal">-2 to +2 SD: Normal</div>
                    </div>
                </div>
                <div class="knowledge-visual">
                    <div class="progress-indicator">
                        <div class="progress-bar lfa"></div>
                    </div>
                </div>
            </div>

            <div class="knowledge-card-modern info">
                <div class="knowledge-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="knowledge-content">
                    <h4>Z-Score Calculation</h4>
                    <p>Statistical measure of standard deviations from the reference median</p>
                    <div class="formula-display">
                        <code>Z-score = (Observed - Median) / SD</code>
                    </div>
                    <div class="interpretation-note">
                        <i class="fas fa-info-circle"></i>
                        <span>Indicates deviation from expected growth patterns</span>
                    </div>
                </div>
                <div class="knowledge-visual">
                    <div class="z-score-chart">
                        <div class="z-line severe-neg">-3</div>
                        <div class="z-line moderate-neg">-2</div>
                        <div class="z-line normal">0</div>
                        <div class="z-line moderate-pos">+2</div>
                        <div class="z-line severe-pos">+3</div>
                    </div>
                </div>
            </div>

            <div class="knowledge-card-modern warning">
                <div class="knowledge-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="knowledge-content">
                    <h4>Age-Based Measurement Guidelines</h4>
                    <p>Different measurement protocols for various age groups</p>
                    <div class="age-guidelines">
                        <div class="age-group">
                            <div class="age-range">0-24 months</div>
                            <div class="age-desc">Length-based measurements (recumbent)</div>
                        </div>
                        <div class="age-group">
                            <div class="age-range">24-60 months</div>
                            <div class="age-desc">Height-based measurements (standing)</div>
                        </div>
                    </div>
                </div>
                <div class="knowledge-visual">
                    <div class="age-timeline">
                        <div class="timeline-point" style="left: 0%">0m</div>
                        <div class="timeline-point" style="left: 40%">24m</div>
                        <div class="timeline-point" style="left: 100%">60m</div>
                        <div class="timeline-bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/who-standards.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/admin/who-standards.js') }}?v={{ time() }}"></script>
@endpush
