
@extends('layouts.dashboard')

@section('title', 'My Children')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/parent/children.css') }}">
@endpush

@section('page-title')
    <div class="modern-page-header">
        <div class="header-content">
            <h1 class="header-title">My Children</h1>
            <p class="header-subtitle">Monitor your children's health and nutrition journey</p>
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
                            <div class="child-avatar">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="7" r="4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 class="child-name">{{ $child->first_name }} {{ $child->last_name }}</h3>
                            <p class="child-subtitle">
                                {{ $child->age_months ? ($child->age_months . ' months old') : ($child->age . ($child->age == 1 ? ' year old' : ' years old')) }}
                                • {{ $child->gender ?? $child->sex ?? 'Gender not specified' }}
                            </p>
                        </div>

                        <!-- Card Body -->
                        <div class="child-card-body">
                            <!-- Info Grid -->
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2L2 7h20L12 2z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M17 13v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div class="info-value">{{ $child->barangay->barangay_name ?? 'N/A' }}</div>
                                    <div class="info-label">Barangay</div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div class="info-value">{{ $child->assessments->count() }}</div>
                                    <div class="info-label">Assessments</div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="9" cy="7" r="4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="m22 21-3-3m1-4a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div class="info-value">{{ $child->nutritionist ? 'Assigned' : 'None' }}</div>
                                    <div class="info-label">Nutritionist</div>
                                </div>
                                
                                @if($child->weight_kg && $child->height_cm)
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div class="info-value">{{ $child->weight_kg }}kg</div>
                                    <div class="info-label">Weight</div>
                                </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="action-buttons">
                                <button type="button" class="btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#childDetailsModal{{ $child->id }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    View Details
                                </button>
                                
                                @if($child->assessments->count() > 0)
                                <a href="#" class="btn-modern btn-outline">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    View Reports
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Modern Child Details Modal -->
                    <div class="modal fade" id="childDetailsModal{{ $child->id }}" tabindex="-1" aria-labelledby="childDetailsModalLabel{{ $child->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div class="d-flex align-items-center">
                                        <div class="child-avatar me-3" style="width: 48px; height: 48px;">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="12" cy="7" r="4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="modal-title mb-0" id="childDetailsModalLabel{{ $child->id }}">{{ $child->first_name }} {{ $child->last_name }}</h5>
                                            <small class="text-muted">Complete Health Information</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="detail-grid">
                                        <!-- Personal Information Section -->
                                        <div class="detail-section">
                                            <div class="detail-section-title">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/>
                                                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                                                </svg>
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
                                            </div>
                                            <div class="detail-row">
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
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2"/>
                                                    <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                                                </svg>
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
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="currentColor" stroke-width="2"/>
                                                </svg>
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
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7z" stroke="currentColor" stroke-width="2"/>
                                                </svg>
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
                                            </div>
                                            <div class="detail-row">
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
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="2"/>
                                                </svg>
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
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="empty-title">No Children Registered</h3>
                <p class="empty-subtitle">Your children's information will appear here once they are registered with the nutrition program. Contact your local health center for assistance with registration.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/parent/children.js') }}"></script>
@endpush

@endsection
