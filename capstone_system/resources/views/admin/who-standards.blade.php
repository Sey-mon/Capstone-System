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

    <!-- Standards Overview -->
    <div class="content-section">
        <div class="section-header">
            <h2>WHO Growth Standards Overview</h2>
            <p>Reference data used for malnutrition assessment calculations</p>
        </div>

        <div class="standards-grid">
            @if(isset($maleWfa))
            <div class="standard-card">
                <h3>Male - Weight for Age</h3>
                <div class="standard-info">
                    <p><strong>Records:</strong> {{ count($maleWfa['data'] ?? []) }}</p>
                    <p><strong>Age Range:</strong> 0-60 months</p>
                    <p><strong>Last Updated:</strong> {{ $maleWfa['last_updated'] ?? 'N/A' }}</p>
                </div>
                <button onclick="showStandardData('male-wfa', {{ json_encode($maleWfa) }})" class="btn btn-outline-primary btn-sm">
                    View Data
                </button>
            </div>
            @endif

            @if(isset($femaleWfa))
            <div class="standard-card">
                <h3>Female - Weight for Age</h3>
                <div class="standard-info">
                    <p><strong>Records:</strong> {{ count($femaleWfa['data'] ?? []) }}</p>
                    <p><strong>Age Range:</strong> 0-60 months</p>
                    <p><strong>Last Updated:</strong> {{ $femaleWfa['last_updated'] ?? 'N/A' }}</p>
                </div>
                <button onclick="showStandardData('female-wfa', {{ json_encode($femaleWfa) }})" class="btn btn-outline-primary btn-sm">
                    View Data
                </button>
            </div>
            @endif

            @if(isset($maleLhfa))
            <div class="standard-card">
                <h3>Male - Length/Height for Age</h3>
                <div class="standard-info">
                    <p><strong>Records:</strong> {{ count($maleLhfa['data'] ?? []) }}</p>
                    <p><strong>Age Range:</strong> 0-60 months</p>
                    <p><strong>Last Updated:</strong> {{ $maleLhfa['last_updated'] ?? 'N/A' }}</p>
                </div>
                <button onclick="showStandardData('male-lhfa', {{ json_encode($maleLhfa) }})" class="btn btn-outline-primary btn-sm">
                    View Data
                </button>
            </div>
            @endif

            @if(isset($femaleLhfa))
            <div class="standard-card">
                <h3>Female - Length/Height for Age</h3>
                <div class="standard-info">
                    <p><strong>Records:</strong> {{ count($femaleLhfa['data'] ?? []) }}</p>
                    <p><strong>Age Range:</strong> 0-60 months</p>
                    <p><strong>Last Updated:</strong> {{ $femaleLhfa['last_updated'] ?? 'N/A' }}</p>
                </div>
                <button onclick="showStandardData('female-lhfa', {{ json_encode($femaleLhfa) }})" class="btn btn-outline-primary btn-sm">
                    View Data
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Information Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>About WHO Standards</h2>
            <p>Understanding the reference data used in assessments</p>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <h4>Weight for Age (WFA)</h4>
                <p>Used to assess underweight status. Reflects body mass relative to chronological age.</p>
                <ul>
                    <li>Below -2 SD: Underweight</li>
                    <li>Below -3 SD: Severely underweight</li>
                </ul>
            </div>

            <div class="info-item">
                <h4>Length/Height for Age (LFA/HFA)</h4>
                <p>Used to assess stunting. Reflects achieved growth in length/height relative to age.</p>
                <ul>
                    <li>Below -2 SD: Stunted</li>
                    <li>Below -3 SD: Severely stunted</li>
                </ul>
            </div>

            <div class="info-item">
                <h4>Z-Score Calculation</h4>
                <p>Standard deviations from the median of the reference population.</p>
                <ul>
                    <li>Z-score = (Observed value - Median) / SD</li>
                    <li>Indicates how many standard deviations away from normal</li>
                </ul>
            </div>

            <div class="info-item">
                <h4>Age Ranges</h4>
                <p>Different standards apply to different age groups:</p>
                <ul>
                    <li>0-24 months: Length-based measurements</li>
                    <li>24-60 months: Height-based measurements</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

<!-- Modal for displaying standard data -->
<div class="modal fade" id="standardModal" tabindex="-1" aria-labelledby="standardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="standardModalLabel">WHO Standard Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="standardDataContent"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/who-standards.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/who-standards.js') }}"></script>
@endpush
