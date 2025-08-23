/**
 * Nutritionist Assessments - Real-time Filtering and Pagination
 * Handles search, filters, sorting, and pagination for the assessments table
 */

class AssessmentFilters {
    constructor() {
        this.currentPage = 1;
        this.currentSort = 'assessment_date';
        this.currentSortOrder = 'desc';
        this.searchTimeout = null;
        this.isLoading = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateResultsInfo();
        this.updateSortIcons();
    }

    bindEvents() {
        // Real-time search with debouncing
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                this.handleSearch();
            });
        }

        // Filter change events
        const filters = [
            'status-filter',
            'diagnosis-filter', 
            'date-from',
            'date-to',
            'per-page'
        ];

        filters.forEach(filterId => {
            const element = document.getElementById(filterId);
            if (element) {
                element.addEventListener('change', () => {
                    this.currentPage = 1;
                    this.loadAssessments();
                });
            }
        });

        // Clear filters button
        const clearBtn = document.getElementById('clear-filters');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearAllFilters();
            });
        }

        // Export button
        const exportBtn = document.getElementById('export-assessments');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportAssessments();
            });
        }

        // Delegate pagination clicks
        document.addEventListener('click', (e) => {
            if (e.target.matches('.page-link[data-page]')) {
                e.preventDefault();
                this.currentPage = parseInt(e.target.getAttribute('data-page'));
                this.loadAssessments();
            }
        });

        // Delegate sorting clicks
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
            this.currentPage = 1;
            this.loadAssessments();
        }, 300); // 300ms debounce
    }

    handleSort(newSort) {
        if (this.currentSort === newSort) {
            this.currentSortOrder = this.currentSortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            this.currentSort = newSort;
            this.currentSortOrder = 'asc';
        }
        
        this.updateSortIcons();
        this.loadAssessments();
    }

    async loadAssessments() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading(true);

        const formData = this.buildFormData();

        try {
            const response = await fetch(window.routes.assessments, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.updateAssessmentsTable(data.html);
                this.updateResultsInfo(data.pagination);
                this.updateURL();
            } else {
                this.showErrorMessage('Failed to load assessments');
            }
        } catch (error) {
            console.error('Error loading assessments:', error);
            this.showErrorMessage('An error occurred while loading assessments');
        } finally {
            this.isLoading = false;
            this.showLoading(false);
        }
    }

    buildFormData() {
        const formData = new FormData();
        
        const getValue = (id) => {
            const element = document.getElementById(id);
            return element ? element.value : '';
        };

        formData.append('search', getValue('search-input'));
        formData.append('status', getValue('status-filter'));
        formData.append('diagnosis', getValue('diagnosis-filter'));
        formData.append('date_from', getValue('date-from'));
        formData.append('date_to', getValue('date-to'));
        formData.append('per_page', getValue('per-page') || '15');
        formData.append('page', this.currentPage);
        formData.append('sort_by', this.currentSort);
        formData.append('sort_order', this.currentSortOrder);

        return formData;
    }

    updateAssessmentsTable(html) {
        const container = document.getElementById('assessments-container');
        if (container) {
            container.innerHTML = html;
        }
    }

    showLoading(show) {
        const loadingIndicator = document.getElementById('loading-indicator');
        const assessmentsContainer = document.getElementById('assessments-container');
        
        if (loadingIndicator) {
            loadingIndicator.style.display = show ? 'block' : 'none';
        }
        
        if (assessmentsContainer) {
            assessmentsContainer.style.opacity = show ? '0.5' : '1';
        }
    }

    clearAllFilters() {
        // Clear form inputs
        const inputs = [
            'search-input',
            'status-filter',
            'diagnosis-filter',
            'date-from',
            'date-to'
        ];

        inputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.value = '';
            }
        });

        // Reset per-page to default
        const perPageSelect = document.getElementById('per-page');
        if (perPageSelect) {
            perPageSelect.value = '15';
        }

        // Reset pagination and sorting
        this.currentPage = 1;
        this.currentSort = 'assessment_date';
        this.currentSortOrder = 'desc';
        
        this.updateSortIcons();
        this.loadAssessments();
    }

    updateResultsInfo(pagination = null) {
        const resultsInfo = document.getElementById('results-info');
        if (resultsInfo && pagination) {
            const from = pagination.from || 0;
            const to = pagination.to || 0;
            const total = pagination.total || 0;
            resultsInfo.textContent = `Showing ${from} to ${to} of ${total} assessments`;
        }
    }

    updateSortIcons() {
        // Reset all sort icons
        document.querySelectorAll('.sort-icon').forEach(icon => {
            icon.className = 'fas fa-sort sort-icon';
        });
        
        // Update current sort icon
        const currentSortIcon = document.querySelector(`[data-sort="${this.currentSort}"] .sort-icon`);
        if (currentSortIcon) {
            currentSortIcon.className = `fas fa-sort-${this.currentSortOrder === 'asc' ? 'up' : 'down'} sort-icon`;
        }
    }

    updateURL() {
        const params = new URLSearchParams();
        
        const getValue = (id) => {
            const element = document.getElementById(id);
            return element ? element.value : '';
        };

        const search = getValue('search-input');
        const status = getValue('status-filter');
        const diagnosis = getValue('diagnosis-filter');
        const dateFrom = getValue('date-from');
        const dateTo = getValue('date-to');
        const perPage = getValue('per-page');
        
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (diagnosis) params.set('diagnosis', diagnosis);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);
        if (perPage && perPage !== '15') params.set('per_page', perPage);
        if (this.currentPage > 1) params.set('page', this.currentPage);
        if (this.currentSort !== 'assessment_date') params.set('sort_by', this.currentSort);
        if (this.currentSortOrder !== 'desc') params.set('sort_order', this.currentSortOrder);
        
        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newURL);
    }

    exportAssessments() {
        const getValue = (id) => {
            const element = document.getElementById(id);
            return element ? element.value : '';
        };

        const params = new URLSearchParams();
        params.set('export', 'csv');
        params.set('search', getValue('search-input'));
        params.set('status', getValue('status-filter'));
        params.set('diagnosis', getValue('diagnosis-filter'));
        params.set('date_from', getValue('date-from'));
        params.set('date_to', getValue('date-to'));
        
        window.location.href = window.routes.assessments + '?' + params.toString();
    }

    showErrorMessage(message) {
        // Remove existing error message
        const existingError = document.getElementById('error-message');
        if (existingError) {
            existingError.remove();
        }

        // Create new error message
        const errorDiv = document.createElement('div');
        errorDiv.id = 'error-message';
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="close" onclick="this.parentElement.remove()" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        const filterSection = document.querySelector('.filter-section');
        if (filterSection) {
            filterSection.appendChild(errorDiv);
        }
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentElement) {
                errorDiv.remove();
            }
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.routes === 'undefined') {
        console.error('Routes not defined. Make sure to include route definitions.');
        return;
    }
    
    new AssessmentFilters();
});

// Export for global access if needed
window.AssessmentFilters = AssessmentFilters;
