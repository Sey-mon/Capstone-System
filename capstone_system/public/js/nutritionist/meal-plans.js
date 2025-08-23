/**
 * Meal Plans JavaScript
 * Handles all meal plan generation and nutrition analysis functionality
 */

class MealPlansManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupCSRF();
    }

    setupCSRF() {
        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    bindEvents() {
        // Test API connection
        $(document).on('click', '#test-api-btn', () => this.testAPIConnection());

        // Generate nutrition analysis
        $(document).on('click', '.generate-analysis-btn', (e) => {
            const patientId = $(e.target).data('patient-id');
            this.generateNutritionAnalysis(patientId);
        });

        // Show meal plan modal
        $(document).on('click', '.generate-meal-plan-btn', (e) => {
            const patientId = $(e.target).data('patient-id');
            this.showMealPlanModal(patientId);
        });

        // Generate meal plan from modal
        $(document).on('click', '#generate-meal-plan-submit', () => this.generateMealPlan());

        // View meal plan history
        $(document).on('click', '.view-meal-plans-btn', (e) => {
            const patientId = $(e.target).data('patient-id');
            this.viewMealPlanHistory(patientId);
        });

        // Close results
        $(document).on('click', '#close-results-btn', () => this.hideResults());

        // Patient card clicks for quick analysis
        $(document).on('click', '.patient-card', (e) => {
            if (!$(e.target).hasClass('btn') && !$(e.target).parent().hasClass('btn')) {
                const patientId = $(e.currentTarget).data('patient-id');
                this.generateNutritionAnalysis(patientId);
            }
        });
    }

    showLoading(message = 'Processing...') {
        const loadingHtml = `
            <div class="loading">
                <i class="fas fa-spinner"></i>
                <div class="loading-text">${message}</div>
            </div>
        `;
        $('#results-content').html(loadingHtml);
        $('#results-section').show();
        this.scrollToResults();
    }

    showError(message) {
        const errorHtml = `
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error:</strong> ${message}
            </div>
        `;
        $('#results-content').html(errorHtml);
        $('#results-section').show();
        this.scrollToResults();
    }

    showSuccess(title, content) {
        $('#results-title').text(title);
        $('#results-content').html(content);
        $('#results-section').show();
        this.scrollToResults();
    }

    hideResults() {
        $('#results-section').hide();
    }

    scrollToResults() {
        setTimeout(() => {
            $('html, body').animate({
                scrollTop: $('#results-section').offset().top - 20
            }, 500);
        }, 100);
    }

    async testAPIConnection() {
        try {
            $('#test-api-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');

            const response = await $.get('/nutritionist/nutrition/test-api');
            
            if (response.success) {
                const statusClass = response.connected ? 'connected' : 'disconnected';
                const statusIcon = response.connected ? 'fa-check-circle' : 'fa-times-circle';
                
                const statusHtml = `
                    <div class="api-status ${statusClass}">
                        <i class="fas ${statusIcon}"></i>
                        ${response.message}
                    </div>
                `;
                
                this.showSuccess('API Status', statusHtml);
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('Failed to test API connection');
            console.error('API test error:', error);
        } finally {
            $('#test-api-btn').prop('disabled', false).html('<i class="fas fa-plug"></i> Test API Connection');
        }
    }

    async generateNutritionAnalysis(patientId) {
        this.showLoading('Generating nutrition analysis...');

        try {
            const response = await $.post('/nutritionist/nutrition/analysis', {
                patient_id: patientId
            });

            if (response.success) {
                const analysisHtml = this.formatNutritionAnalysis(response.data.nutrition_analysis);
                this.showSuccess('Nutrition Analysis', analysisHtml);
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('Failed to generate nutrition analysis');
            console.error('Nutrition analysis error:', error);
        }
    }

    formatNutritionAnalysis(analysis) {
        let html = '<div class="analysis-results">';
        
        for (const [key, value] of Object.entries(analysis)) {
            const title = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            html += `
                <div class="analysis-section">
                    <h4>${title}</h4>
                    <p>${value}</p>
                </div>
            `;
        }
        
        html += '</div>';
        return html;
    }

    showMealPlanModal(patientId) {
        $('#modal-patient-id').val(patientId);
        $('#available-foods').val('');
        
        // Get patient name for modal title
        const patientCard = $(`.patient-card[data-patient-id="${patientId}"]`);
        const patientName = patientCard.find('.patient-name').text();
        $('#mealPlanModalLabel').text(`Generate Meal Plan - ${patientName}`);
        
        $('#mealPlanModal').modal('show');
    }

    async generateMealPlan() {
        const patientId = $('#modal-patient-id').val();
        const availableFoods = $('#available-foods').val();

        $('#mealPlanModal').modal('hide');
        this.showLoading('Generating personalized meal plan...');

        try {
            const response = await $.post('/nutritionist/nutrition/meal-plan', {
                patient_id: patientId,
                available_foods: availableFoods
            });

            if (response.success) {
                const mealPlanHtml = this.formatMealPlan(response.data.meal_plan);
                this.showSuccess('Generated Meal Plan', mealPlanHtml);
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('Failed to generate meal plan');
            console.error('Meal plan generation error:', error);
        }
    }

    formatMealPlan(mealPlanText) {
        // Parse the meal plan text and format it nicely
        const sections = mealPlanText.split(/(?=[A-Z][A-Z\s]*:)/);
        let html = '<div class="meal-plan-content">';
        
        sections.forEach(section => {
            if (section.trim()) {
                const [title, ...contentParts] = section.split(':');
                const content = contentParts.join(':').trim();
                
                if (title && content) {
                    html += `
                        <div class="meal-plan-day">
                            <div class="day-title">${title.trim()}</div>
                            <div class="meal-description">${content}</div>
                        </div>
                    `;
                }
            }
        });
        
        html += '</div>';
        return html;
    }

    async viewMealPlanHistory(patientId) {
        this.showLoading('Loading meal plan history...');

        try {
            const response = await $.post('/nutritionist/nutrition/patient-meal-plans', {
                patient_id: patientId,
                most_recent: false
            });

            if (response.success) {
                const historyHtml = this.formatMealPlanHistory(response.data.meal_plans);
                this.showSuccess('Meal Plan History', historyHtml);
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('Failed to load meal plan history');
            console.error('Meal plan history error:', error);
        }
    }

    formatMealPlanHistory(mealPlans) {
        if (!mealPlans || mealPlans.length === 0) {
            return '<div class="empty-state"><p>No meal plans found for this patient.</p></div>';
        }

        let html = '<div class="meal-plan-history">';
        
        mealPlans.forEach(plan => {
            const createdDate = new Date(plan.created_at).toLocaleDateString();
            html += `
                <div class="history-item">
                    <div class="history-header">
                        <strong>Meal Plan - ${createdDate}</strong>
                        <span class="badge badge-info">${plan.duration_days || 7} days</span>
                    </div>
                    <div class="history-content">
                        ${this.formatMealPlan(plan.plan_details || 'No details available')}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    // Utility methods
    showNotification(message, type = 'info') {
        // You can implement a notification system here
        console.log(`${type}: ${message}`);
    }
}

// Initialize when document is ready
$(document).ready(() => {
    window.mealPlansManager = new MealPlansManager();
});
