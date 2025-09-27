<!-- Patient Edit Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPatientModalLabel">Edit Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="patientForm">
                    <input type="hidden" id="patient_id" name="patient_id">
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent</label>
                        <select id="parent_id" name="parent_id" class="form-control" required>
                            <!-- Populate with parent options -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="barangay_id" class="form-label">Barangay</label>
                        <select id="barangay_id" name="barangay_id" class="form-control" required>
                            <!-- Populate with barangay options -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="age_months" class="form-label">Age (months)</label>
                        <input type="number" id="age_months" name="age_months" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sex" class="form-label">Sex</label>
                        <select id="sex" name="sex" class="form-control" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date_of_admission" class="form-label">Date of Admission</label>
                        <input type="date" id="date_of_admission" name="date_of_admission" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="weight_kg" class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" id="weight_kg" name="weight_kg" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="height_cm" class="form-label">Height (cm)</label>
                        <input type="number" step="0.01" id="height_cm" name="height_cm" class="form-control" required>
                    </div>
                    <!-- Add other fields as needed -->
                    <button type="submit" class="modern-btn w-100">Update Patient</button>
                </form>
            </div>
        </div>
    </div>
</div>
@extends('layouts.dashboard')

@section('title', 'My Children')

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

<style>
    :root {
        --primary-green: #059669;
        --secondary-green: #10b981;
        --light-green: #d1fae5;
        --accent-green: #6ee7b7;
        --dark-text: #1f2937;
        --gray-text: #6b7280;
        --light-gray: #f9fafb;
        --border-gray: #e5e7eb;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    .page-container {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .modern-page-header,
    .children-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        border-radius: 24px;
        padding: 0.75rem 2rem;
        margin-bottom: 1rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .modern-page-header::before,
    .children-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }

    .header-content {
        position: relative;
        z-index: 1;
    }

    .header-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .header-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 300;
        margin-bottom: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-gray);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary-green), var(--secondary-green));
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--light-green) 0%, var(--accent-green) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-green);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--gray-text);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .children-grid {
        display: grid;
        gap: 2rem;
    }

    .child-card {
        background: white;
        border-radius: 24px;
        padding: 0;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-gray);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .child-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
    }

    .child-card-header {
        background: linear-gradient(135deg, var(--light-green) 0%, rgba(16, 185, 129, 0.1) 100%);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--border-gray);
    }

    .child-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        box-shadow: var(--shadow-md);
    }

    .child-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-text);
        margin-bottom: 0.25rem;
    }

    .child-subtitle {
        color: var(--gray-text);
        font-size: 0.95rem;
    }

    .child-card-body {
        padding: 2rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-item {
        text-align: center;
        padding: 1rem;
        background: var(--light-gray);
        border-radius: 16px;
        border: 1px solid var(--border-gray);
    }

    .info-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }

    .info-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-text);
        margin-bottom: 0.25rem;
    }

    .info-label {
        font-size: 0.8rem;
        color: var(--gray-text);
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-modern {
        flex: 1;
        min-width: 120px;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        color: white;
        box-shadow: var(--shadow-md);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        color: white;
    }

    .btn-outline {
        background: white;
        color: var(--primary-green);
        border: 2px solid var(--primary-green);
    }

    .btn-outline:hover {
        background: var(--primary-green);
        color: white;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 24px;
        box-shadow: var(--shadow-md);
        border: 1px dashed var(--border-gray);
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--light-green) 0%, var(--accent-green) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .empty-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark-text);
        margin-bottom: 0.5rem;
    }

    .empty-subtitle {
        color: var(--gray-text);
        font-size: 1rem;
        line-height: 1.6;
    }

    /* Modal Modernization */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: var(--shadow-xl);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--light-green) 0%, rgba(16, 185, 129, 0.1) 100%);
        border-bottom: 1px solid var(--border-gray);
        padding: 1.5rem 2rem;
    }

    .modal-title {
        color: var(--primary-green);
        font-weight: 700;
        font-size: 1.25rem;
    }

    .btn-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--gray-text);
        opacity: 0.7;
        transition: all 0.3s ease;
    }

    .btn-close:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .modal-body {
        padding: 2rem;
        max-height: 60vh;
        overflow-y: auto;
    }

    .detail-grid {
        display: grid;
        gap: 1rem;
    }

    .detail-item {
        padding: 1rem;
        background: var(--light-gray);
        border-radius: 12px;
        border: 1px solid var(--border-gray);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detail-label {
        font-weight: 600;
        color: var(--primary-green);
        font-size: 0.9rem;
    }

    .detail-value {
        color: var(--dark-text);
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .header-title {
            font-size: 2rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-modern {
            min-width: auto;
        }
    }

    /* Text Color Utilities */
    .text-primary-green {
        color: var(--primary-green) !important;
    }

    /* Additional Modal Styling */
    .modal-lg {
        max-width: 900px;
    }

    .modal-body h6 {
        display: flex;
        align-items: center;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--light-green);
        margin-bottom: 1rem;
    }

    /* Scrollbar Styling */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: var(--light-gray);
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--primary-green);
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-green);
    }

    /* Loading Animation */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .loading {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* Animation for page load */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .modern-page-header,
    .children-header {
        animation: slideInLeft 0.8s ease forwards;
    }

    .stat-card {
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }

    .child-card {
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }

    .child-card:nth-child(1) { animation-delay: 0.4s; }
    .child-card:nth-child(2) { animation-delay: 0.5s; }
    .child-card:nth-child(3) { animation-delay: 0.6s; }
    .child-card:nth-child(4) { animation-delay: 0.7s; }

    .empty-state {
        animation: fadeInUp 0.8s ease forwards;
    }

    /* Hover Effects */
    .child-card:hover .child-avatar {
        transform: scale(1.1);
        box-shadow: var(--shadow-lg);
    }

    .child-avatar {
        transition: all 0.3s ease;
    }

    /* Focus States */
    .btn-modern:focus {
        outline: 2px solid var(--primary-green);
        outline-offset: 2px;
    }

    /* Print Styles */
    @media print {
        .page-container {
            background: white !important;
            padding: 0 !important;
        }
        
        .children-header {
            background: white !important;
            color: black !important;
            border: 2px solid var(--primary-green) !important;
        }
        
        .child-card {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        
        .action-buttons {
            display: none !important;
        }
    }
</style>

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
                        <div class="modal-dialog modal-lg">
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
                                        <div class="mb-4">
                                            <h6 class="text-primary-green fw-bold mb-3">
                                                <svg width="16" height="16" class="me-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/>
                                                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                                Personal Information
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Full Name</span>
                                                        <span class="detail-value">{{ $child->first_name }} {{ $child->middle_name ?? '' }} {{ $child->last_name }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Age</span>
                                                        <span class="detail-value">{{ $child->age_months ?? $child->age }} {{ $child->age_months ? 'months' : 'years' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Gender</span>
                                                        <span class="detail-value">{{ $child->gender ?? $child->sex ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Birthdate</span>
                                                        <span class="detail-value">{{ $child->birthdate ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact & Location Section -->
                                        <div class="mb-4">
                                            <h6 class="text-primary-green fw-bold mb-3">
                                                <svg width="16" height="16" class="me-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2"/>
                                                    <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                                Contact & Location
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Barangay</span>
                                                        <span class="detail-value">{{ $child->barangay->barangay_name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Contact Number</span>
                                                        <span class="detail-value">{{ $child->contact_number ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Health Metrics Section -->
                                        <div class="mb-4">
                                            <h6 class="text-primary-green fw-bold mb-3">
                                                <svg width="16" height="16" class="me-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                                Health Metrics
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Weight</span>
                                                        <span class="detail-value">{{ $child->weight_kg ?? 'N/A' }} {{ $child->weight_kg ? 'kg' : '' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Height</span>
                                                        <span class="detail-value">{{ $child->height_cm ?? 'N/A' }} {{ $child->height_cm ? 'cm' : '' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">BMI for Age</span>
                                                        <span class="detail-value">{{ $child->bmi_for_age ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Weight for Age</span>
                                                        <span class="detail-value">{{ $child->weight_for_age ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Height for Age</span>
                                                        <span class="detail-value">{{ $child->height_for_age ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Care Information Section -->
                                        <div class="mb-4">
                                            <h6 class="text-primary-green fw-bold mb-3">
                                                <svg width="16" height="16" class="me-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7z" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                                Care Information
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Nutritionist</span>
                                                        <span class="detail-value">{{ $child->nutritionist ? ($child->nutritionist->first_name . ' ' . $child->nutritionist->last_name) : 'Not assigned' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Date of Admission</span>
                                                        <span class="detail-value">{{ $child->date_of_admission ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Total Assessments</span>
                                                        <span class="detail-value">{{ $child->assessments->count() }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">4Ps Beneficiary</span>
                                                        <span class="detail-value">{{ isset($child->is_4ps_beneficiary) ? ($child->is_4ps_beneficiary ? 'Yes' : 'No') : 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($child->other_medical_problems || $child->breastfeeding || $child->edema)
                                        <!-- Additional Information Section -->
                                        <div class="mb-4">
                                            <h6 class="text-primary-green fw-bold mb-3">
                                                <svg width="16" height="16" class="me-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                                Additional Information
                                            </h6>
                                            <div class="row g-3">
                                                @if($child->breastfeeding)
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Breastfeeding</span>
                                                        <span class="detail-value">{{ $child->breastfeeding }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                                @if($child->edema)
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Edema</span>
                                                        <span class="detail-value">{{ $child->edema }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                                @if($child->other_medical_problems)
                                                <div class="col-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Medical Problems</span>
                                                        <span class="detail-value">{{ $child->other_medical_problems }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
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


@endsection
