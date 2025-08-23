@extends('layouts.dashboard')

@section('title', 'Meal Plans')

@section('page-title', 'Meal Plans')
@section('page-subtitle', 'Generate and manage AI-powered meal plans for your patients.')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/meal-plans.css') }}">
@endpush

@section('content')
    <!-- Quick Actions -->
    <div class="actions-grid">
        <div class="action-card">
            <div class="action-icon">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="action-content">
                <h3>Generate Meal Plan</h3>
                <p>Create personalized meal plans using AI analysis</p>
            </div>
        </div>
        
        <div class="action-card">
            <div class="action-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="action-content">
                <h3>Nutrition Analysis</h3>
                <p>Get comprehensive nutrition insights for patients</p>
            </div>
        </div>
        
        <div class="action-card">
            <div class="action-icon">
                <i class="fas fa-apple-alt"></i>
            </div>
            <div class="action-content">
                <h3>Food Database</h3>
                <p>Browse available foods and nutritional information</p>
            </div>
        </div>
        
        <div class="action-card">
            <div class="action-icon">
                <i class="fas fa-brain"></i>
            </div>
            <div class="action-content">
                <h3>Knowledge Base</h3>
                <p>Access nutrition guidelines and recommendations</p>
            </div>
        </div>
    </div>

    <!-- Patient Selection -->
    <div class="main-content">
        <div class="content-card">
            <div class="card-header">
                <h3>Select Patient for Meal Planning</h3>
                <div class="card-actions">
                    <button type="button" class="btn btn-secondary" id="test-api-btn">
                        <i class="fas fa-plug"></i> Test API Connection
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                @if($patients->count() > 0)
                    <div class="patients-grid">
                        @foreach($patients as $patient)
                            <div class="patient-card" data-patient-id="{{ $patient->patient_id }}">
                                <div class="patient-info">
                                    <div class="patient-name">
                                        {{ $patient->first_name }} {{ $patient->last_name }}
                                    </div>
                                    <div class="patient-details">
                                        <span class="detail">
                                            <i class="fas fa-calendar"></i>
                                            {{ $patient->age_months }} months
                                        </span>
                                        <span class="detail">
                                            <i class="fas fa-{{ $patient->sex === 'Male' ? 'mars' : 'venus' }}"></i>
                                            {{ $patient->sex }}
                                        </span>
                                        <span class="detail">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $patient->barangay->barangay_name ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="patient-meta">
                                        <span class="weight">{{ $patient->weight_kg }}kg</span>
                                        <span class="height">{{ $patient->height_cm }}cm</span>
                                    </div>
                                </div>
                                <div class="patient-actions">
                                    <button type="button" class="btn btn-primary btn-sm generate-analysis-btn" 
                                            data-patient-id="{{ $patient->patient_id }}">
                                        <i class="fas fa-chart-line"></i> Analysis
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm generate-meal-plan-btn" 
                                            data-patient-id="{{ $patient->patient_id }}">
                                        <i class="fas fa-utensils"></i> Meal Plan
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm view-meal-plans-btn" 
                                            data-patient-id="{{ $patient->patient_id }}">
                                        <i class="fas fa-history"></i> History
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="empty-title">No Patients Assigned</div>
                        <div class="empty-subtitle">You don't have any patients assigned to you yet.</div>
                        <a href="{{ route('nutritionist.patients') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Manage Patients
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div id="results-section" class="content-card" style="display: none;">
        <div class="card-header">
            <h3 id="results-title">Results</h3>
            <button type="button" class="btn btn-secondary btn-sm" id="close-results-btn">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        <div class="card-body">
            <div id="results-content">
                <!-- Results will be loaded here dynamically -->
            </div>
        </div>
    </div>

    <!-- Meal Plan Generation Modal -->
    <div class="modal fade" id="mealPlanModal" tabindex="-1" aria-labelledby="mealPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mealPlanModalLabel">Generate Meal Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="mealPlanForm">
                        <input type="hidden" id="modal-patient-id" name="patient_id">
                        
                        <div class="mb-3">
                            <label for="available-foods" class="form-label">Available Foods (Optional)</label>
                            <textarea class="form-control" id="available-foods" name="available_foods" rows="3" 
                                      placeholder="List any specific foods available at home or in the area..."></textarea>
                            <div class="form-text">This will help customize the meal plan based on local availability.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="generate-meal-plan-submit">
                        <i class="fas fa-utensils"></i> Generate Meal Plan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/nutritionist/meal-plans.js') }}"></script>
@endpush
