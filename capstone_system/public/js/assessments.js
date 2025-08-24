// Assessment Filters - Real-time Search and Pagination
class AssessmentManager {
    constructor() {
        this.currentPage = 1;
        this.currentSort = 'assessment_date';
        this.currentSortOrder = 'desc';
        this.searchTimeout = null;
        this.isLoading = false;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        // Get the route URLs from global variables set by the Blade template
        this.assessmentsUrl = window.assessmentsRoutes.assessments;
        this.quickAssessmentUrl = window.assessmentsRoutes.quickAssessment;
        
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
        
        const response = await fetch(window.assessmentsRoutes.quickAssessment, {
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