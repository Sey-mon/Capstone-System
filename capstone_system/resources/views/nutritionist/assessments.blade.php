@extends('layouts.dashboard')

@section('title', 'Assessments')

@section('page-title', 'Patient Assessments')
@section('page-subtitle', 'View and manage malnutrition assessments')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Search and Filter Bar -->
    <div class="action-bar">
        <div class="action-left">
            <form method="GET" action="{{ route('nutritionist.assessments') }}" class="search-form">
                <div class="search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search assessments..." class="search-input">
                    <select name="status" class="form-control" style="width: auto; display: inline-block; margin-left: 1rem;">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="action-right">
            <a href="{{ route('nutritionist.patients') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                New Assessment
            </a>
        </div>
    </div>

    <!-- Assessments Table -->
    <div class="card">
        <div class="card-header">
            <h3>Assessment History</h3>
        </div>
        <div class="card-body">
            @if($assessments->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Assessment Date</th>
                                <th>Diagnosis</th>
                                <th>Weight (kg)</th>
                                <th>Height (cm)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments as $assessment)
                                <tr>
                                    <td>
                                        <div class="patient-info">
                                            <strong>{{ $assessment->patient->first_name }} {{ $assessment->patient->last_name }}</strong>
                                            <small class="d-block text-muted">{{ $assessment->patient->age_months }} months old</small>
                                        </div>
                                    </td>
                                    <td>{{ $assessment->assessment_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="diagnosis-badge {{ getDiagnosisBadgeClass($assessment->diagnosis) }}">
                                            {{ $assessment->diagnosis }}
                                        </span>
                                    </td>
                                    <td>{{ $assessment->weight_kg }} kg</td>
                                    <td>{{ $assessment->height_cm }} cm</td>
                                    <td>
                                        @if($assessment->completed_at)
                                            <span class="status-badge completed">
                                                <i class="fas fa-check-circle"></i>
                                                Completed
                                            </span>
                                        @else
                                            <span class="status-badge pending">
                                                <i class="fas fa-clock"></i>
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if($assessment->completed_at)
                                                <button class="btn btn-sm btn-info" onclick="viewAssessment({{ $assessment->assessment_id }})" title="View Results">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('nutritionist.patients.assess', $assessment->patient_id) }}" class="btn btn-sm btn-success" title="New Assessment">
                                                <i class="fas fa-redo"></i>
                                            </a>
                                            <button class="btn btn-sm btn-secondary" onclick="printAssessment({{ $assessment->assessment_id }})" title="Print">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $assessments->appends(request()->query())->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h4>No Assessments Found</h4>
                    <p>{{ request('search') ? 'No assessments match your search criteria.' : 'You haven\'t performed any assessments yet.' }}</p>
                    <a href="{{ route('nutritionist.patients') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Start Your First Assessment
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Assessment Tool -->
    <div class="quick-assessment-card">
        <h4>Quick Assessment Tool</h4>
        <p>Perform a quick malnutrition assessment without saving to patient records</p>
        
        <form id="quickAssessmentForm" class="quick-form">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="quick_age">Age (months)</label>
                    <input type="number" class="form-control" id="quick_age" name="age_months" min="0" max="60" required>
                </div>
                <div class="form-group">
                    <label for="quick_gender">Gender</label>
                    <select class="form-control" id="quick_gender" name="gender" required>
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quick_weight">Weight (kg)</label>
                    <input type="number" step="0.1" class="form-control" id="quick_weight" name="weight_kg" min="1" max="50" required>
                </div>
                <div class="form-group">
                    <label for="quick_height">Height (cm)</label>
                    <input type="number" step="0.1" class="form-control" id="quick_height" name="height_cm" min="30" max="150" required>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary" onclick="performQuickAssessment()">
                        <i class="fas fa-bolt"></i>
                        Quick Assess
                    </button>
                </div>
            </div>
        </form>

        <div id="quickAssessmentResult" class="quick-result" style="display: none;">
            <h5>Quick Assessment Result</h5>
            <div id="quickResultContent"></div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function viewAssessment(assessmentId) {
    // This would need a view assessment endpoint
    alert('View assessment functionality would be implemented here for assessment ID: ' + assessmentId);
}

function printAssessment(assessmentId) {
    // This would need a print assessment endpoint
    alert('Print assessment functionality would be implemented here for assessment ID: ' + assessmentId);
}

async function performQuickAssessment() {
    const form = document.getElementById('quickAssessmentForm');
    const formData = new FormData(form);
    
    // Validate required fields
    const requiredFields = ['age_months', 'weight_kg', 'height_cm', 'gender'];
    const missingFields = [];
    
    requiredFields.forEach(field => {
        if (!formData.get(field)) {
            missingFields.push(field.replace('_', ' ').toUpperCase());
        }
    });
    
    if (missingFields.length > 0) {
        alert('Please fill in required fields: ' + missingFields.join(', '));
        return;
    }
    
    try {
        // Show loading
        const resultDiv = document.getElementById('quickAssessmentResult');
        const contentDiv = document.getElementById('quickResultContent');
        
        contentDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Assessing...</div>';
        resultDiv.style.display = 'block';
        
        const response = await fetch('{{ route("nutritionist.assessment.quick") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayQuickAssessmentResult(data.data);
        } else {
            contentDiv.innerHTML = `<div class="alert alert-danger">Assessment failed: ${data.error}</div>`;
        }
    } catch (error) {
        document.getElementById('quickResultContent').innerHTML = 
            `<div class="alert alert-danger">Error: ${error.message}</div>`;
    }
}

function displayQuickAssessmentResult(data) {
    const contentDiv = document.getElementById('quickResultContent');
    
    let html = '<div class="quick-assessment-summary">';
    html += `<div class="diagnosis-result ${getDiagnosisBadgeClass(data.primary_diagnosis)}">`;
    html += `<strong>Diagnosis:</strong> ${data.primary_diagnosis}`;
    html += '</div>';
    
    if (data.risk_level) {
        html += `<div class="risk-result ${getRiskBadgeClass(data.risk_level)}">`;
        html += `<strong>Risk Level:</strong> ${data.risk_level}`;
        html += '</div>';
    }
    
    if (data.confidence) {
        html += `<div class="confidence-result">`;
        html += `<strong>Confidence:</strong> ${Math.round(data.confidence * 100)}%`;
        html += '</div>';
    }
    
    html += '</div>';
    
    contentDiv.innerHTML = html;
}

function getDiagnosisBadgeClass(diagnosis) {
    if (diagnosis.toLowerCase().includes('normal')) return 'success';
    if (diagnosis.toLowerCase().includes('severe')) return 'danger';
    if (diagnosis.toLowerCase().includes('moderate')) return 'warning';
    return 'info';
}

function getRiskBadgeClass(risk) {
    if (risk.toLowerCase() === 'low') return 'success';
    if (risk.toLowerCase() === 'high') return 'danger';
    if (risk.toLowerCase() === 'medium') return 'warning';
    return 'info';
}
</script>

<style>
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 1rem;
}

.search-form {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.search-wrapper {
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.search-input {
    border: none;
    padding: 0.75rem 1rem;
    outline: none;
    min-width: 250px;
}

.search-btn {
    border: none;
    background: #3b82f6;
    color: white;
    padding: 0.75rem 1rem;
    cursor: pointer;
}

.search-btn:hover {
    background: #2563eb;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
    margin-bottom: 2rem;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
}

.card-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 1.2rem;
}

.card-body {
    padding: 1.5rem;
}

.patient-info strong {
    color: #1f2937;
    font-size: 0.95rem;
}

.patient-info small {
    color: #6b7280;
    font-size: 0.8rem;
}

.diagnosis-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    text-align: center;
    display: inline-block;
}

.diagnosis-badge.success {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.diagnosis-badge.warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.diagnosis-badge.danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.diagnosis-badge.info {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.status-badge.completed {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.status-badge.pending {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state h4 {
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 2rem;
}

.quick-assessment-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.quick-assessment-card h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
}

.quick-assessment-card p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.quick-form .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    align-items: end;
}

.quick-form .form-group {
    display: flex;
    flex-direction: column;
}

.quick-form label {
    margin-bottom: 0.5rem;
    color: #374151;
    font-weight: 500;
    font-size: 0.9rem;
}

.quick-form .form-control {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
}

.quick-result {
    margin-top: 1.5rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.quick-result h5 {
    margin: 0 0 1rem 0;
    color: #1f2937;
}

.quick-assessment-summary {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.diagnosis-result, .risk-result {
    padding: 0.75rem;
    border-radius: 8px;
    font-weight: 500;
}

.diagnosis-result.success, .risk-result.success {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.diagnosis-result.warning, .risk-result.warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.diagnosis-result.danger, .risk-result.danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.diagnosis-result.info, .risk-result.info {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.confidence-result {
    padding: 0.5rem;
    background: white;
    border-radius: 6px;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.loading {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.loading i {
    font-size: 1.5rem;
}

.pagination-wrapper {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

.table-responsive {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.table {
    margin: 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding: 1rem 0.75rem;
}

.table td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.02);
}

@php
function getDiagnosisBadgeClass($diagnosis) {
    if (strpos(strtolower($diagnosis), 'normal') !== false) return 'success';
    if (strpos(strtolower($diagnosis), 'severe') !== false) return 'danger';
    if (strpos(strtolower($diagnosis), 'moderate') !== false) return 'warning';
    return 'info';
}
@endphp
</style>
@endsection
