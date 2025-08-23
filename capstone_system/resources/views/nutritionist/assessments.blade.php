@extends('layouts.dashboard')

@section('title', 'Assessments')

@section('page-title', 'Patient Assessments')
@section('page-subtitle', 'View and manage latest malnutrition assessments for each patient')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Enhanced Single Line Filter Bar -->
    <div class="assessment-filters-container">
        <div class="filters-row">
            <input type="text" 
                   id="searchInput" 
                   class="filter-control search-input" 
                   placeholder="Search patients, diagnosis..."
                   value="{{ request('search') }}">
            
            <select id="statusFilter" class="filter-control">
                <option value="">All Status</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
            
            <select id="diagnosisFilter" class="filter-control">
                <option value="">All Diagnoses</option>
                <option value="Normal">Normal</option>
                <option value="Moderate">Moderate</option>
                <option value="Severe">Severe</option>
                <option value="Stunted">Stunted</option>
                <option value="Wasted">Wasted</option>
            </select>
            
            <input type="date" 
                   id="dateFrom" 
                   class="filter-control date-input"
                   value="{{ request('date_from') }}">
            
            <input type="date" 
                   id="dateTo" 
                   class="filter-control date-input"
                   value="{{ request('date_to') }}">
            
            <select id="perPage" class="filter-control small-select">
                <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                <option value="15" {{ request('per_page') == '15' || !request('per_page') ? 'selected' : '' }}>15</option>
                <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
            </select>
            
            <div class="action-buttons">
                <button type="button" id="clearFilters" class="btn-action btn-clear" title="Clear Filters">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" id="exportBtn" class="btn-action btn-export" title="Export">
                    <i class="fas fa-download"></i>
                </button>
                <a href="{{ route('nutritionist.patients') }}" class="btn-action btn-primary" title="New Assessment">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading assessments...</span>
        </div>
    </div>

    <!-- Assessments Table Container -->
    <div class="assessments-card">
        <div class="card-header">
            <h3>Assessment History</h3>
            <div class="header-info">
                <span class="badge bg-info text-white me-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing latest assessment per patient
                </span>
                <span id="resultsInfo" class="results-count"></span>
            </div>
        </div>
        <div id="assessmentsContainer" class="card-content">
            @include('nutritionist.partials.assessments-table', ['assessments' => $assessments])
        </div>
    </div>

    <!-- Quick Assessment Button -->
    <div class="mb-4">
        <button type="button" class="btn btn-primary btn-lg" onclick="openQuickAssessmentModal()">
            <i class="fas fa-bolt"></i>
            Quick Assessment Tool
        </button>
    </div>

    <!-- Quick Assessment Modal -->
    <div class="modal fade" id="quickAssessmentModal" tabindex="-1" aria-labelledby="quickAssessmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="quickAssessmentModalLabel">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Assessment Tool
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeQuickAssessmentModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">Perform a quick malnutrition assessment without saving to patient records</p>
                    
                    <form id="quickAssessmentForm" class="quick-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="quick_age" class="form-label">Age (months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quick_age" name="age_months" min="0" max="60" required>
                                <div class="form-text">Age in months (0-60)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="quick_gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="quick_gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quick_weight" class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control" id="quick_weight" name="weight_kg" min="1" max="50" required>
                                <div class="form-text">Weight in kilograms</div>
                            </div>
                            <div class="col-md-6">
                                <label for="quick_height" class="form-label">Height (cm) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control" id="quick_height" name="height_cm" min="30" max="150" required>
                                <div class="form-text">Height in centimeters</div>
                            </div>
                        </div>
                    </form>

                    <!-- Assessment Result -->
                    <div id="quickAssessmentResult" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Assessment Result
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="quickResultContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeQuickAssessmentModal()">
                        <i class="fas fa-times me-1"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-success" onclick="performQuickAssessment()">
                        <i class="fas fa-bolt me-1"></i>
                        Perform Assessment
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
// Assessment Filters - Real-time Search and Pagination
class AssessmentManager {
    constructor() {
        this.currentPage = 1;
        this.currentSort = 'assessment_date';
        this.currentSortOrder = 'desc';
        this.searchTimeout = null;
        this.isLoading = false;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        this.assessmentsUrl = '{{ route("nutritionist.assessments") }}';
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateResultsInfo();
    }
    
    bindEvents() {
        // Real-time search with debouncing
        document.getElementById('searchInput').addEventListener('input', () => {
            this.handleSearch();
        });
        
        // Filter changes
        document.getElementById('statusFilter').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        document.getElementById('diagnosisFilter').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        document.getElementById('dateFrom').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        document.getElementById('dateTo').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        document.getElementById('perPage').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        // Clear filters
        document.getElementById('clearFilters').addEventListener('click', () => {
            this.clearAllFilters();
        });
        
        // Export
        document.getElementById('exportBtn').addEventListener('click', () => {
            this.exportAssessments();
        });
        
        // Pagination clicks (delegate)
        document.addEventListener('click', (e) => {
            if (e.target.matches('.page-link[data-page]')) {
                e.preventDefault();
                this.currentPage = parseInt(e.target.getAttribute('data-page'));
                this.loadAssessments();
            }
        });
        
        // Sorting clicks (delegate)
        document.addEventListener('click', (e) => {
            const sortLink = e.target.closest('.sort-link[data-sort]');
            if (sortLink) {
                e.preventDefault();
                this.handleSort(sortLink.getAttribute('data-sort'));
            }
        });
    }
    
    handleSearch() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.resetPageAndLoad();
        }, 300); // 300ms debounce
    }
    
    resetPageAndLoad() {
        this.currentPage = 1;
        this.loadAssessments();
    }
    
    handleSort(sortField) {
        if (this.currentSort === sortField) {
            this.currentSortOrder = this.currentSortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            this.currentSort = sortField;
            this.currentSortOrder = 'asc';
        }
        this.updateSortIcons();
        this.loadAssessments();
    }
    
    async loadAssessments() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading(true);
        
        try {
            const formData = this.buildFormData();
            
            const response = await fetch(this.assessmentsUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('assessmentsContainer').innerHTML = data.html;
                this.updateResultsInfo(data.pagination);
                this.updateURL();
            } else {
                this.showError('Failed to load assessments');
            }
        } catch (error) {
            console.error('Error loading assessments:', error);
            this.showError('An error occurred while loading assessments');
        } finally {
            this.isLoading = false;
            this.showLoading(false);
        }
    }
    
    buildFormData() {
        const formData = new FormData();
        formData.append('search', document.getElementById('searchInput').value);
        formData.append('status', document.getElementById('statusFilter').value);
        formData.append('diagnosis', document.getElementById('diagnosisFilter').value);
        formData.append('date_from', document.getElementById('dateFrom').value);
        formData.append('date_to', document.getElementById('dateTo').value);
        formData.append('per_page', document.getElementById('perPage').value);
        formData.append('page', this.currentPage);
        formData.append('sort_by', this.currentSort);
        formData.append('sort_order', this.currentSortOrder);
        return formData;
    }
    
    clearAllFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('diagnosisFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('perPage').value = '15';
        
        this.currentPage = 1;
        this.currentSort = 'assessment_date';
        this.currentSortOrder = 'desc';
        
        this.updateSortIcons();
        this.loadAssessments();
    }
    
    showLoading(show) {
        const loadingIndicator = document.getElementById('loadingIndicator');
        const assessmentsContainer = document.getElementById('assessmentsContainer');
        
        if (show) {
            loadingIndicator.style.display = 'flex';
            assessmentsContainer.style.opacity = '0.5';
        } else {
            loadingIndicator.style.display = 'none';
            assessmentsContainer.style.opacity = '1';
        }
    }
    
    updateResultsInfo(pagination = null) {
        const resultsInfo = document.getElementById('resultsInfo');
        if (pagination && resultsInfo) {
            const from = pagination.from || 0;
            const to = pagination.to || 0;
            const total = pagination.total || 0;
            resultsInfo.textContent = `${from}-${to} of ${total} assessments`;
        }
    }
    
    updateSortIcons() {
        document.querySelectorAll('.sort-icon').forEach(icon => {
            icon.className = 'fas fa-sort sort-icon';
        });
        
        const currentSortIcon = document.querySelector(`[data-sort="${this.currentSort}"] .sort-icon`);
        if (currentSortIcon) {
            currentSortIcon.className = `fas fa-sort-${this.currentSortOrder === 'asc' ? 'up' : 'down'} sort-icon`;
        }
    }
    
    updateURL() {
        const params = new URLSearchParams();
        
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const diagnosis = document.getElementById('diagnosisFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const perPage = document.getElementById('perPage').value;
        
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (diagnosis) params.set('diagnosis', diagnosis);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);
        if (perPage !== '15') params.set('per_page', perPage);
        if (this.currentPage > 1) params.set('page', this.currentPage);
        if (this.currentSort !== 'assessment_date') params.set('sort_by', this.currentSort);
        if (this.currentSortOrder !== 'desc') params.set('sort_order', this.currentSortOrder);
        
        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newURL);
    }
    
    exportAssessments() {
        const params = new URLSearchParams();
        params.set('export', 'csv');
        params.set('search', document.getElementById('searchInput').value);
        params.set('status', document.getElementById('statusFilter').value);
        params.set('diagnosis', document.getElementById('diagnosisFilter').value);
        params.set('date_from', document.getElementById('dateFrom').value);
        params.set('date_to', document.getElementById('dateTo').value);
        
        window.location.href = this.assessmentsUrl + '?' + params.toString();
    }
    
    showError(message) {
        const existingError = document.getElementById('errorMessage');
        if (existingError) existingError.remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.id = 'errorMessage';
        errorDiv.className = 'alert alert-danger';
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        document.querySelector('.assessment-filters-container').appendChild(errorDiv);
        
        setTimeout(() => {
            if (errorDiv.parentElement) errorDiv.remove();
        }, 5000);
    }
}

// Additional utility functions
function viewAssessment(assessmentId) {
    alert('View assessment functionality would be implemented here for assessment ID: ' + assessmentId);
}

function printAssessment(assessmentId) {
    alert('Print assessment functionality would be implemented here for assessment ID: ' + assessmentId);
}

// Function to open the modal manually
function openQuickAssessmentModal() {
    const modal = document.getElementById('quickAssessmentModal');
    if (modal) {
        if (typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            // Fallback: show modal without Bootstrap
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modalBackdrop';
            document.body.appendChild(backdrop);
        }
    }
}

// Function to close modal manually (fallback)
function closeQuickAssessmentModal() {
    const modal = document.getElementById('quickAssessmentModal');
    const backdrop = document.getElementById('modalBackdrop');
    
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.style.overflow = '';
        
        if (backdrop) {
            backdrop.remove();
        }
    }
}

// Quick Assessment Modal Functions
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
        showModalAlert('Please fill in required fields: ' + missingFields.join(', '), 'warning');
        return;
    }
    
    try {
        // Show loading
        const resultDiv = document.getElementById('quickAssessmentResult');
        const contentDiv = document.getElementById('quickResultContent');
        
        contentDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Performing assessment...</div>';
        resultDiv.style.display = 'block';
        
        const response = await fetch('{{ route("nutritionist.assessment.quick") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayQuickAssessmentResult(data.data);
        } else {
            contentDiv.innerHTML = `<div class="alert alert-danger mb-0">Assessment failed: ${data.error}</div>`;
        }
    } catch (error) {
        document.getElementById('quickResultContent').innerHTML = 
            `<div class="alert alert-danger mb-0">Error: ${error.message}</div>`;
    }
}

function displayQuickAssessmentResult(data) {
    const contentDiv = document.getElementById('quickResultContent');
    
    let html = '<div class="assessment-summary">';
    
    // Primary Diagnosis
    html += `<div class="result-item diagnosis-result ${getDiagnosisBadgeClass(data.primary_diagnosis)}">`;
    html += `<div class="result-label"><i class="fas fa-stethoscope me-2"></i>Primary Diagnosis</div>`;
    html += `<div class="result-value">${data.primary_diagnosis}</div>`;
    html += '</div>';
    
    // Risk Level
    if (data.risk_level) {
        html += `<div class="result-item risk-result ${getRiskBadgeClass(data.risk_level)}">`;
        html += `<div class="result-label"><i class="fas fa-exclamation-triangle me-2"></i>Risk Level</div>`;
        html += `<div class="result-value">${data.risk_level}</div>`;
        html += '</div>';
    }
    
    // Confidence Level
    if (data.confidence) {
        html += `<div class="result-item confidence-result">`;
        html += `<div class="result-label"><i class="fas fa-percentage me-2"></i>Confidence</div>`;
        html += `<div class="result-value">${Math.round(data.confidence * 100)}%</div>`;
        html += '</div>';
    }
    
    // Additional metrics if available
    if (data.z_scores) {
        html += '<div class="mt-3"><h6 class="text-muted">Z-Scores</h6>';
        if (data.z_scores.wfa) html += `<small class="d-block">Weight-for-Age: ${data.z_scores.wfa.toFixed(2)}</small>`;
        if (data.z_scores.hfa) html += `<small class="d-block">Height-for-Age: ${data.z_scores.hfa.toFixed(2)}</small>`;
        if (data.z_scores.wfh) html += `<small class="d-block">Weight-for-Height: ${data.z_scores.wfh.toFixed(2)}</small>`;
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

function showModalAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const modalBody = document.querySelector('#quickAssessmentModal .modal-body');
    const existingAlert = modalBody.querySelector('.alert');
    if (existingAlert) existingAlert.remove();
    
    modalBody.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentElement) alertDiv.remove();
    }, 5000);
}

// Reset form when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('quickAssessmentModal');
    if (modal) {
        // Initialize Bootstrap modal
        const bsModal = new bootstrap.Modal(modal);
        
        modal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('quickAssessmentForm').reset();
            document.getElementById('quickAssessmentResult').style.display = 'none';
            const alerts = modal.querySelectorAll('.alert');
            alerts.forEach(alert => alert.remove());
        });
    }
    
    // Check if Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded! Modal functionality will not work.');
    } else {
        console.log('Bootstrap is loaded successfully.');
    }
});

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    new AssessmentManager();
});

// Make functions globally available
window.openQuickAssessmentModal = openQuickAssessmentModal;
window.closeQuickAssessmentModal = closeQuickAssessmentModal;
window.viewAssessment = viewAssessment;
window.printAssessment = printAssessment;
window.performQuickAssessment = performQuickAssessment;

// Debug logging
console.log('Assessment page functions loaded:', {
    openQuickAssessmentModal: typeof window.openQuickAssessmentModal,
    closeQuickAssessmentModal: typeof window.closeQuickAssessmentModal,
    bootstrap: typeof bootstrap
});
</script>
@endsection

@push('styles')
<style>
/* Assessment Filter Styles */
.assessment-filters-container {
    background: white;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.filters-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 15px;
    margin-bottom: 0;
}

.filter-control {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.filter-control label {
    font-size: 12px;
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 3px;
    white-space: nowrap;
}

.filter-control input,
.filter-control select {
    border: 1px solid #d1d3e2;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 14px;
    background: white;
    min-width: 120px;
    height: 38px;
}

.filter-control input:focus,
.filter-control select:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    outline: 0;
}

/* Specific widths for different filter types */
.filter-control.search-control input {
    min-width: 200px;
}

.filter-control.date-control input {
    min-width: 140px;
}

.filter-control.select-control select {
    min-width: 130px;
}

.filter-control.perpage-control select {
    min-width: 80px;
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    margin-left: auto;
}

.filter-actions .btn {
    height: 38px;
    padding: 8px 16px;
    font-size: 14px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.btn-clear {
    background: #6c757d;
    color: white;
}

.btn-clear:hover {
    background: #5a6268;
}

.btn-export {
    background: #28a745;
    color: white;
}

.btn-export:hover {
    background: #218838;
}

/* Responsive behavior */
@media (max-width: 1200px) {
    .filters-row {
        flex-wrap: wrap;
    }
    
    .filter-actions {
        margin-left: 0;
        margin-top: 10px;
    }
}

@media (max-width: 768px) {
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-control {
        width: 100%;
    }
    
    .filter-control input,
    .filter-control select {
        min-width: 100%;
    }
    
    .filter-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .filter-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Loading indicator */
#loadingIndicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: none;
    align-items: center;
    gap: 10px;
}

/* Results info */
.results-info {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 15px;
}

/* Assessment table container */
#assessmentsContainer {
    position: relative;
    transition: opacity 0.3s ease;
}

/* Error message styling */
.alert {
    padding: 12px 16px;
    margin-top: 15px;
    border: 1px solid transparent;
    border-radius: 4px;
    position: relative;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.btn-close {
    position: absolute;
    top: 8px;
    right: 12px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: inherit;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

/* Enhanced Card */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
    overflow: hidden;
}

.card-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.header-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-info .badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
}

.results-count {
    color: #6c757d;
    font-size: 0.9rem;
}

/* Enhanced Table */
.table-responsive {
    margin: 0;
    border: none;
}

.table {
    margin: 0;
    font-size: 0.9rem;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding: 1rem 1.5rem;
    border-top: none;
    white-space: nowrap;
}

.table td {
    padding: 1rem 1.5rem;
    vertical-align: middle;
    border-top: 1px solid #f1f3f4;
}

.table tbody tr:hover {
    background-color: rgba(59, 130, 246, 0.02);
}

/* Sortable Column Headers */
.sort-link {
    color: #495057;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.2s ease;
}

.sort-link:hover {
    color: #3b82f6;
    text-decoration: none;
}

.sort-icon {
    font-size: 0.8rem;
    opacity: 0.7;
    transition: all 0.2s ease;
}

.sort-link:hover .sort-icon {
    opacity: 1;
}

/* Patient Info */
.patient-info strong {
    color: #1f2937;
    font-size: 0.95rem;
    display: block;
}

.patient-info small {
    color: #6b7280;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

/* Enhanced Badges */
.diagnosis-badge, .status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    text-align: center;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    border: 1px solid transparent;
}

.diagnosis-badge.success, .status-badge.completed {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border-color: rgba(16, 185, 129, 0.2);
}

.diagnosis-badge.warning {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
    border-color: rgba(245, 158, 11, 0.2);
}

.diagnosis-badge.danger {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border-color: rgba(239, 68, 68, 0.2);
}

.diagnosis-badge.info {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
    border-color: rgba(59, 130, 246, 0.2);
}

.status-badge.pending {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
    border-color: rgba(245, 158, 11, 0.2);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s ease;
}

.btn-info {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.btn-info:hover {
    background: #2563eb;
    border-color: #2563eb;
    transform: translateY(-1px);
}

.btn-success {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
    border-color: #059669;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6b7280;
    border-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
    transform: translateY(-1px);
}

/* Quick Assessment Modal Styles */
#quickAssessmentModal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-bottom: none;
}

#quickAssessmentModal .modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Ensure modal appears properly */
.modal {
    z-index: 1050 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    overflow: hidden !important;
    outline: 0 !important;
}

.modal-backdrop {
    z-index: 1040 !important;
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: 0 !important;
    background-color: #000 !important;
}

.modal-dialog {
    position: relative !important;
    width: auto !important;
    margin: 0.5rem !important;
    pointer-events: none !important;
}

.modal.show .modal-dialog {
    transform: none !important;
}

.modal-content {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    width: 100% !important;
    pointer-events: auto !important;
    background-color: #fff !important;
    background-clip: padding-box !important;
    border: 1px solid rgba(0,0,0,.2) !important;
    border-radius: 0.3rem !important;
    outline: 0 !important;
}

#quickAssessmentModal .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

#quickAssessmentModal .form-control,
#quickAssessmentModal .form-select {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.2s ease;
}

#quickAssessmentModal .form-control:focus,
#quickAssessmentModal .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

#quickAssessmentModal .form-text {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* Assessment Result Styles */
.assessment-summary {
    display: grid;
    gap: 1rem;
}

.result-item {
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.result-label {
    font-weight: 600;
    font-size: 0.9rem;
}

.result-value {
    font-weight: 700;
    font-size: 1.1rem;
}

.result-item.success {
    background-color: #ecfdf5;
    border-color: #10b981;
    color: #065f46;
}

.result-item.warning {
    background-color: #fffbeb;
    border-color: #f59e0b;
    color: #92400e;
}

.result-item.danger {
    background-color: #fef2f2;
    border-color: #ef4444;
    color: #991b1b;
}

.result-item.info {
    background-color: #eff6ff;
    border-color: #3b82f6;
    color: #1e40af;
}

/* Modal Button Styles */
#quickAssessmentModal .modal-footer .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
}

#quickAssessmentModal .btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

#quickAssessmentModal .btn-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
}

/* Enhanced Pagination */
.pagination-wrapper {
    padding: 1.5rem 2rem;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

.pagination-info {
    padding: 1rem 2rem;
    background: #f8f9fa;
    border-top: 1px solid #eee;
    font-size: 0.9rem;
}

.pagination {
    margin: 0;
    justify-content: center;
}

.page-item .page-link {
    border: 1px solid #e5e7eb;
    color: #6b7280;
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.page-item .page-link:hover {
    background-color: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
}

.page-item.active .page-link {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.page-item.disabled .page-link {
    background-color: #f9fafb;
    border-color: #f3f4f6;
    color: #d1d5db;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.empty-state h4 {
    color: #374151;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 2rem;
    font-size: 1rem;
}

/* Quick Assessment Card */
.quick-assessment-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
    margin-top: 2rem;
}

.quick-assessment-card h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-weight: 600;
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
    font-weight: 600;
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
    margin-bottom: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .filter-section {
        padding: 1rem;
    }
    
    .filter-section .row {
        margin: 0;
    }
    
    .filter-section .col-md-3,
    .filter-section .col-md-2,
    .filter-section .col-md-1 {
        padding: 0 0.5rem;
        margin-bottom: 1rem;
    }
    
    .card-header {
        padding: 1rem;
    }
    
    .table th,
    .table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .btn-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
    }
}

/* Alert Messages */
.alert {
    border-radius: 8px;
    border: none;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}

.alert-danger {
    background-color: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: #059669;
    border-left: 4px solid #059669;
}

.alert-dismissible .close {
    background: none;
    border: none;
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1;
    color: inherit;
    opacity: 0.7;
    padding: 0;
}

.alert-dismissible .close:hover {
    opacity: 1;
}
</style>
@endpush
