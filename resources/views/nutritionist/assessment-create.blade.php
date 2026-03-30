@extends('layouts.dashboard')

@section('title', 'New Assessment')

@section('page-title', 'Create New Assessment')
@section('page-subtitle', 'Select a patient to begin assessment')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <div class="assessment-create-container">
        <!-- Header -->
        <div class="assessment-header">
            <div class="header-top-row">
                <div class="header-left-actions">
                    <a href="{{ route('nutritionist.assessments') }}" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to Assessments
                    </a>
                </div>
            </div>
        </div>

        <!-- Patient Selection -->
        <div class="patient-selection-card">
            <div class="card-header">
                <h3><i class="fas fa-user-check"></i> Select Patient for Assessment</h3>
                <p class="text-muted">Choose a patient from your list to begin a new malnutrition assessment</p>
            </div>

            <div class="card-content">
                @if($patients->count() > 0)
                    <!-- Search Bar -->
                    <div class="search-container mb-4">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="patientSearch" class="form-control" placeholder="Search patients by name, parent, or contact...">
                        </div>
                    </div>

                    <!-- Patients Grid -->
                    <div class="patients-grid" id="patientsGrid">
                        @foreach($patients as $patient)
                            <div class="patient-card" data-search="{{ strtolower($patient->first_name . ' ' . $patient->last_name . ' ' . ($patient->parent->first_name ?? '') . ' ' . ($patient->parent->last_name ?? '') . ' ' . $patient->contact_number) }}">
                                <div class="patient-info">
                                    <div class="patient-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="patient-details">
                                        <h4 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</h4>
                                        <div class="patient-meta">
                                            <span class="meta-item">
                                                <i class="fas fa-calendar"></i>
                                                {{ $patient->age_months }} months old
                                            </span>
                                            <span class="meta-item">
                                                <i class="fas fa-{{ $patient->sex == 'Male' ? 'mars' : 'venus' }}"></i>
                                                {{ $patient->sex }}
                                            </span>
                                        </div>
                                        <div class="patient-contact">
                                            <span class="meta-item">
                                                <i class="fas fa-user"></i>
                                                {{ $patient->parent->first_name ?? 'N/A' }} {{ $patient->parent->last_name ?? '' }}
                                            </span>
                                            <span class="meta-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ $patient->barangay->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="patient-actions">
                                    <a href="{{ route('nutritionist.patients.assess', $patient->patient_id) }}" class="btn btn-primary">
                                        <i class="fas fa-clipboard-check"></i>
                                        Start Assessment
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- No Results Message -->
                    <div class="no-results" id="noResults" style="display: none;">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4>No patients found</h4>
                            <p class="text-muted">Try adjusting your search criteria</p>
                        </div>
                    </div>
                @else
                    <!-- No Patients State -->
                    <div class="empty-state">
                        <div class="text-center py-5">
                            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                            <h4>No Patients Available</h4>
                            <p class="text-muted">You need to have patients assigned to you before you can create assessments.</p>
                            <a href="{{ route('nutritionist.patients') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus"></i> Manage Patients
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patientSearch');
    const patientsGrid = document.getElementById('patientsGrid');
    const noResults = document.getElementById('noResults');
    const patientCards = document.querySelectorAll('.patient-card');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;

            patientCards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && searchTerm !== '') {
                if (noResults) noResults.style.display = 'block';
                if (patientsGrid) patientsGrid.style.display = 'none';
            } else {
                if (noResults) noResults.style.display = 'none';
                if (patientsGrid) patientsGrid.style.display = 'grid';
            }
        });
    }
});
</script>

<style>
.assessment-create-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.assessment-header {
    margin-bottom: 30px;
}

.header-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f8f9fa;
    color: #495057;
    text-decoration: none;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.back-button:hover {
    background: #e9ecef;
    color: #495057;
    text-decoration: none;
}

.patient-selection-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 25px;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
}

.card-header h3 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-content {
    padding: 25px;
}

.search-container {
    max-width: 400px;
}

.search-input-wrapper {
    position: relative;
}

.search-input-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-input-wrapper .form-control {
    padding-left: 40px;
    border-radius: 8px;
    border: 1px solid #ced4da;
}

.patients-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.patient-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.patient-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.15);
    transform: translateY(-2px);
}

.patient-info {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.patient-avatar {
    flex-shrink: 0;
}

.patient-avatar i {
    font-size: 40px;
    color: #6c757d;
}

.patient-details {
    flex: 1;
}

.patient-name {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 18px;
    font-weight: 600;
}

.patient-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 8px;
}

.patient-contact {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #6c757d;
}

.meta-item i {
    width: 14px;
    font-size: 12px;
}

.patient-actions {
    text-align: right;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #007bff;
    color: white;
    border: 1px solid #007bff;
}

.btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    color: white;
    text-decoration: none;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    opacity: 0.5;
}

.no-results {
    text-align: center;
    padding: 40px 20px;
}

@media (max-width: 768px) {
    .patients-grid {
        grid-template-columns: 1fr;
    }
    
    .patient-meta {
        flex-direction: column;
        gap: 4px;
    }
    
    .assessment-create-container {
        padding: 15px;
    }
}
</style>
@endsection
