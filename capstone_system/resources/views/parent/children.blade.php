
@extends('layouts.dashboard')

@section('title', 'My Children')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/parent/children.css') }}">
@endpush

@section('page-title')
    <div class="modern-page-header">
        <div class="header-content">
            <div class="breadcrumb">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
                <i class="fas fa-chevron-right"></i>
                <span class="active">My Children</span>
            </div>
            <h1 class="header-title">My Children</h1>
            <p class="header-subtitle">Monitor your children's health and nutrition journey with comprehensive tracking and detailed assessments</p>
        </div>
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-child"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">{{ count($children ?? []) }}</div>
                    <div class="stat-label">Total Children</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">{{ $children ? $children->filter(function($child) { return $child->nutritionist !== null; })->count() : 0 }}</div>
                    <div class="stat-label">Assigned to Nutritionist</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">{{ $children ? $children->sum(function($child) { return $child->assessments->count(); }) : 0 }}</div>
                    <div class="stat-label">Total Assessments</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')

<div class="page-container">
    <div class="container-fluid px-4">
        @if(isset($children) && count($children) > 0)
            <!-- Children Grid -->
            <div class="children-grid">
                @foreach($children as $child)
                    <div class="child-card">
                        <!-- Card Header -->
                        <div class="child-card-header">
                            <div class="child-profile-section">
                                <div class="child-avatar">
                                    <i class="fas fa-child"></i>
                                </div>
                                <div class="child-info">
                                    <h3 class="child-name">{{ $child->first_name }} {{ $child->last_name }}</h3>
                                    <div class="child-meta">
                                        <span class="meta-item">
                                            <i class="fas fa-birthday-cake"></i>
                                            {{ $child->age_months ? ($child->age_months . ' months old') : ($child->age . ($child->age == 1 ? ' year old' : ' years old')) }}
                                        </span>
                                        <span class="meta-divider">•</span>
                                        <span class="meta-item">
                                            <i class="fas fa-{{ ($child->gender ?? $child->sex ?? '') === 'Male' ? 'mars' : 'venus' }}"></i>
                                            {{ $child->gender ?? $child->sex ?? 'Gender not specified' }}
                                        </span>
                                        @if($child->barangay)
                                        <span class="meta-divider">•</span>
                                        <span class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $child->barangay->barangay_name }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="child-actions">
                                <button type="button" class="btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#childDetailsModal{{ $child->id }}">
                                    <i class="fas fa-eye"></i>
                                    View Full Profile
                                </button>
                                @if($child->assessments->count() > 0)
                                <a href="{{ route('parent.assessments') }}" class="btn-modern btn-secondary">
                                    <i class="fas fa-file-medical-alt"></i>
                                    Assessment History
                                </a>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="child-card-body">
                            <!-- Health Metrics Row -->
                            <div class="metrics-row">
                                <div class="metric-card">
                                    <div class="metric-icon-wrapper weight">
                                        <i class="fas fa-weight"></i>
                                    </div>
                                    <div class="metric-content">
                                        <span class="metric-label">Weight</span>
                                        <span class="metric-value">{{ $child->weight_kg ?? 'N/A' }} <small>{{ $child->weight_kg ? 'kg' : '' }}</small></span>
                                    </div>
                                </div>
                                
                                <div class="metric-card">
                                    <div class="metric-icon-wrapper height">
                                        <i class="fas fa-ruler-vertical"></i>
                                    </div>
                                    <div class="metric-content">
                                        <span class="metric-label">Height</span>
                                        <span class="metric-value">{{ $child->height_cm ?? 'N/A' }} <small>{{ $child->height_cm ? 'cm' : '' }}</small></span>
                                    </div>
                                </div>
                                
                                <div class="metric-card">
                                    <div class="metric-icon-wrapper assessments">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                    <div class="metric-content">
                                        <span class="metric-label">Assessments</span>
                                        <span class="metric-value">{{ $child->assessments->count() }}</span>
                                    </div>
                                </div>
                                
                                <div class="metric-card">
                                    <div class="metric-icon-wrapper nutritionist">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div class="metric-content">
                                        <span class="metric-label">Care Status</span>
                                        <span class="metric-value-small">{{ $child->nutritionist ? 'Under Care' : 'Unassigned' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Info Section -->
                            @if($child->nutritionist || $child->is_4ps_beneficiary !== null)
                            <div class="additional-info-section">
                                @if($child->nutritionist)
                                <div class="info-badge">
                                    <i class="fas fa-user-nurse"></i>
                                    <span>Nutritionist: {{ $child->nutritionist->first_name }} {{ $child->nutritionist->last_name }}</span>
                                </div>
                                @endif
                                @if($child->is_4ps_beneficiary)
                                <div class="info-badge highlight">
                                    <i class="fas fa-hands-helping"></i>
                                    <span>4Ps Beneficiary</span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- Modern Child Details Modal -->
                    <div class="modal fade child-modal" id="childDetailsModal{{ $child->id }}" tabindex="-1" aria-labelledby="childDetailsModalLabel{{ $child->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div class="modal-header-content">
                                        <div class="modal-child-avatar">
                                            <i class="fas fa-child"></i>
                                        </div>
                                        <div class="modal-title-section">
                                            <h5 class="modal-title" id="childDetailsModalLabel{{ $child->id }}">
                                                <i class="fas fa-user-circle"></i>
                                                {{ $child->first_name }} {{ $child->last_name }}
                                            </h5>
                                            <p class="modal-subtitle">Complete Health and Nutrition Profile</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="detail-grid">
                                        <!-- Personal Information Section -->
                                        <div class="detail-section">
                                            <div class="detail-section-title">
                                                <i class="fas fa-user"></i>
                                                Personal Information
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-item">
                                                    <span class="detail-label">Full Name</span>
                                                    <span class="detail-value detail-value-large">{{ $child->first_name }} {{ $child->middle_name ?? '' }} {{ $child->last_name }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Age</span>
                                                    <span class="detail-value detail-value-highlight">{{ $child->age_months ?? $child->age }} {{ $child->age_months ? 'months' : 'years' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Gender</span>
                                                    <span class="detail-value">{{ $child->gender ?? $child->sex ?? 'N/A' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Birthdate</span>
                                                    <span class="detail-value">{{ $child->birthdate ? \Carbon\Carbon::parse($child->birthdate)->format('F j, Y') : 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact & Location Section -->
                                        <div class="detail-section">
                                            <div class="detail-section-title">
                                                <i class="fas fa-map-marker-alt"></i>
                                                Contact & Location
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-item">
                                                    <span class="detail-label">Barangay</span>
                                                    <span class="detail-value detail-value-highlight">{{ $child->barangay->barangay_name ?? 'N/A' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Contact Number</span>
                                                    <span class="detail-value">{{ $child->contact_number ?? 'Not provided' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Health Metrics Section -->
                                        <div class="detail-section">
                                            <div class="detail-section-title">
                                                <i class="fas fa-heartbeat"></i>
                                                Health Metrics
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-item">
                                                    <span class="detail-label">Weight</span>
                                                    <span class="detail-value detail-value-large">{{ $child->weight_kg ?? 'N/A' }}{{ $child->weight_kg ? ' kg' : '' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Height</span>
                                                    <span class="detail-value detail-value-large">{{ $child->height_cm ?? 'N/A' }}{{ $child->height_cm ? ' cm' : '' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">BMI for Age</span>
                                                    <span class="detail-value">
                                                        @if($child->bmi_for_age)
                                                            <span class="status-indicator status-positive">{{ $child->bmi_for_age }}</span>
                                                        @else
                                                            <span class="status-indicator status-neutral">Not assessed</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-item">
                                                    <span class="detail-label">Weight for Age</span>
                                                    <span class="detail-value">
                                                        @if($child->weight_for_age)
                                                            <span class="status-indicator status-positive">{{ $child->weight_for_age }}</span>
                                                        @else
                                                            <span class="status-indicator status-neutral">Not assessed</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Height for Age</span>
                                                    <span class="detail-value">
                                                        @if($child->height_for_age)
                                                            <span class="status-indicator status-positive">{{ $child->height_for_age }}</span>
                                                        @else
                                                            <span class="status-indicator status-neutral">Not assessed</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Care Information Section -->
                                        <div class="detail-section">
                                            <div class="detail-section-title">
                                                <i class="fas fa-user-nurse"></i>
                                                Care Information
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-item">
                                                    <span class="detail-label">Assigned Nutritionist</span>
                                                    <span class="detail-value detail-value-highlight">
                                                        {{ $child->nutritionist ? ($child->nutritionist->first_name . ' ' . $child->nutritionist->last_name) : 'Not assigned' }}
                                                    </span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Date of Admission</span>
                                                    <span class="detail-value">{{ $child->date_of_admission ? \Carbon\Carbon::parse($child->date_of_admission)->format('F j, Y') : 'Not recorded' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Total Assessments</span>
                                                    <span class="detail-value">
                                                        <span class="status-indicator {{ $child->assessments->count() > 0 ? 'status-positive' : 'status-neutral' }}">
                                                            {{ $child->assessments->count() }} {{ $child->assessments->count() == 1 ? 'Assessment' : 'Assessments' }}
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">4Ps Beneficiary Status</span>
                                                    <span class="detail-value">
                                                        @if(isset($child->is_4ps_beneficiary))
                                                            <span class="status-indicator {{ $child->is_4ps_beneficiary ? 'status-positive' : 'status-neutral' }}">
                                                                {{ $child->is_4ps_beneficiary ? 'Yes' : 'No' }}
                                                            </span>
                                                        @else
                                                            <span class="status-indicator status-neutral">Not specified</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @if($child->other_medical_problems || $child->breastfeeding || $child->edema)
                                        <!-- Additional Information Section -->
                                        <div class="detail-section">
                                            <div class="detail-section-title">
                                                <i class="fas fa-notes-medical"></i>
                                                Additional Health Information
                                            </div>
                                            <div class="detail-row">
                                                @if($child->breastfeeding)
                                                <div class="detail-item">
                                                    <span class="detail-label">Breastfeeding Status</span>
                                                    <span class="detail-value">
                                                        <span class="status-indicator status-positive">{{ $child->breastfeeding }}</span>
                                                    </span>
                                                </div>
                                                @endif
                                                @if($child->edema)
                                                <div class="detail-item">
                                                    <span class="detail-label">Edema Present</span>
                                                    <span class="detail-value">
                                                        <span class="status-indicator status-negative">{{ $child->edema }}</span>
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                            @if($child->other_medical_problems)
                                            <div class="detail-row">
                                                <div class="detail-item detail-item-full">
                                                    <span class="detail-label">Other Medical Problems</span>
                                                    <span class="detail-value">{{ $child->other_medical_problems }}</span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Modern Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-child"></i>
                </div>
                <h3 class="empty-title">No Children Registered</h3>
                <p class="empty-subtitle">Your children's information will appear here once they are registered with the nutrition program. Contact your local health center for assistance with registration.</p>
                <div class="empty-actions">
                    <button class="btn-modern btn-primary">
                        <i class="fas fa-phone"></i>
                        Contact Health Center
                    </button>
                    <button class="btn-modern btn-secondary">
                        <i class="fas fa-info-circle"></i>
                        Learn More
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/parent/children.js') }}"></script>
@endpush

@endsection
