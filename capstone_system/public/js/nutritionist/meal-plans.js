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
        this.setupSearch();
    }

    setupCSRF() {
        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    setupSearch() {
        // Patient search functionality
        $('#patient-search').on('focus', () => {
            $('#patients-list-container').slideDown(300);
        });

        $('#patient-search').on('input', (e) => {
            const searchTerm = $(e.target).val().toLowerCase();
            if (searchTerm.length > 0) {
                $('#patients-list-container').slideDown(300);
            }
            this.filterPatients(searchTerm);
        });

        // Hide list when clicking outside
        $(document).on('click', (e) => {
            if (!$(e.target).closest('.search-container, .patients-list-container').length) {
                $('#patients-list-container').slideUp(300);
            }
        });
    }

    filterPatients(searchTerm) {
        $('.patient-item').each(function() {
            const name = $(this).data('name');
            const age = $(this).data('age').toString();
            const location = $(this).data('location');
            
            const matches = name.includes(searchTerm) || 
                          age.includes(searchTerm) || 
                          location.includes(searchTerm);
            
            $(this).toggleClass('hidden', !matches);
        });
    }

    bindEvents() {
        // Test API connection
        $(document).on('click', '#test-api-btn', () => this.testAPIConnection());

        // Generate nutrition analysis
        $(document).on('click', '.generate-analysis-btn', (e) => {
            e.stopPropagation();
            const patientId = $(e.currentTarget).data('patient-id');
            this.generateNutritionAnalysis(patientId);
        });

        // Show meal plan modal
        $(document).on('click', '.generate-meal-plan-btn', (e) => {
            e.stopPropagation();
            const patientId = $(e.currentTarget).data('patient-id');
            this.showMealPlanModal(patientId);
        });

        // View meal plan history
        $(document).on('click', '.view-meal-plans-btn', (e) => {
            e.stopPropagation();
            const patientId = $(e.currentTarget).data('patient-id');
            this.viewMealPlanHistory(patientId);
        });

        // Close results
        $(document).on('click', '#close-results-btn', () => this.hideResults());

        // Feeding Program - Open modal
        $(document).on('click', '#open-feeding-program-btn', () => this.showFeedingProgramModal());
    }

    async showFeedingProgramModal() {
        // Get barangays data from the page
        const barangaysData = JSON.parse(document.getElementById('barangays-data')?.textContent || '[]');
        
        // Build barangay options
        let barangayOptions = '<option value="">Select barangay (optional)</option>';
        barangaysData.forEach(barangay => {
            barangayOptions += `<option value="${barangay.barangay_name}">${barangay.barangay_name}</option>`;
        });
        
        const { value: formValues } = await Swal.fire({
            title: '<i class="fas fa-users"></i> Generate Feeding Program',
            html: `
                <div style="text-align: left; padding: 0 1.5rem;">
                    <div class="row" style="display: flex; gap: 1rem; margin-bottom: 1.25rem;">
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #2c3e50;">
                                <i class="fas fa-child"></i> Target Age Group
                            </label>
                            <select id="swal-age-group" class="swal2-input" style="width: 100%; padding: 0.625rem;">
                                <option value="all">All Ages (6 months - 5 years) - Mixed group</option>
                                <option value="6-12months">Infants (6-12 months)</option>
                                <option value="12-24months">Toddlers (12-24 months)</option>
                                <option value="24-60months">Preschoolers (24-60 months)</option>
                            </select>
                        </div>
                        
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #2c3e50;">
                                <i class="fas fa-users"></i> Number of Children
                            </label>
                            <input id="swal-total-children" type="number" class="swal2-input" placeholder="e.g., 50" min="1" style="width: 100%;">
                            <small style="color: #6c757d; font-size: 0.75rem; display: block; margin-top: 0.25rem;">For shopping list quantities</small>
                        </div>
                    </div>
                    
                    <div class="row" style="display: flex; gap: 1rem; margin-bottom: 1.25rem;">
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #2c3e50;">
                                <i class="fas fa-calendar-alt"></i> Program Duration
                            </label>
                            <select id="swal-duration" class="swal2-input" style="width: 100%; padding: 0.625rem;">
                                <option value="1">1 Day</option>
                                <option value="2">2 Days</option>
                                <option value="3">3 Days</option>
                                <option value="4">4 Days</option>
                                <option value="5" selected>5 Days (Max)</option>
                            </select>
                        </div>
                        
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #2c3e50;">
                                <i class="fas fa-wallet"></i> Budget Level
                            </label>
                            <select id="swal-budget" class="swal2-input" style="width: 100%; padding: 0.625rem;">
                                <option value="low">Low - Cost-effective (₱50-100/child/day)</option>
                                <option value="moderate" selected>Moderate - Balanced (₱100-150/child/day)</option>
                                <option value="high">High - Optimal (₱150-200/child/day)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #2c3e50;">
                            <i class="fas fa-map-marker-alt"></i> Barangay (Optional)
                        </label>
                        <select id="swal-barangay" class="swal2-input" style="width: 100%; padding: 0.625rem;">
                            ${barangayOptions}
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 0.5rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #2c3e50;">
                            <i class="fas fa-shopping-basket"></i> Available Ingredients (Optional)
                        </label>
                        <textarea id="swal-ingredients" class="swal2-textarea" placeholder="List available ingredients from suppliers or donations (e.g., manok, bangus, monggo, kangkong, saging, kalabasa)" style="width: 100%; min-height: 100px; resize: vertical;"></textarea>
                        <small style="color: #6c757d; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Comma-separated list helps customize meal plans based on what you have</small>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f4 100%); padding: 1rem; border-radius: 8px; border-left: 4px solid #2d7a4f; margin-top: 1.25rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <i class="fas fa-lightbulb" style="color: #2d7a4f; font-size: 1.25rem; margin-top: 0.125rem;"></i>
                            <div>
                                <strong style="color: #2d7a4f; display: block; margin-bottom: 0.5rem;">Benefits:</strong>
                                <ul style="margin: 0; padding-left: 1.25rem; color: #2c3e50; font-size: 0.875rem; line-height: 1.6;">
                                    <li>NO dish repetition across entire program</li>
                                    <li>Budget-conscious Filipino cuisine</li>
                                    <li>Age-appropriate texture adaptations</li>
                                    <li>Shopping lists for bulk purchasing</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            width: '900px',
            padding: '2rem 1rem',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Generate Plan',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#2d7a4f',
            cancelButtonColor: '#95a5a6',
            focusConfirm: false,
            preConfirm: () => {
                return {
                    ageGroup: document.getElementById('swal-age-group').value,
                    totalChildren: document.getElementById('swal-total-children').value,
                    duration: document.getElementById('swal-duration').value,
                    budget: document.getElementById('swal-budget').value,
                    barangay: document.getElementById('swal-barangay').value,
                    ingredients: document.getElementById('swal-ingredients').value
                }
            }
        });

        if (formValues) {
            this.generateFeedingProgram(formValues);
        }
    }

    async showMealPlanModal(patientId) {
        const { value: availableFoods } = await Swal.fire({
            title: '<i class="fas fa-utensils"></i> Generate Individual Meal Plan',
            html: `
                <div style="text-align: left; padding: 0 1.5rem;">
                    <div style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f4 100%); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #2d7a4f;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-info-circle" style="color: #2d7a4f; font-size: 1.25rem;"></i>
                            <div>
                                <strong style="color: #2d7a4f; display: block; margin-bottom: 0.25rem;">Personalized Meal Plan</strong>
                                <p style="margin: 0; color: #2c3e50; font-size: 0.875rem;">Creating a customized meal plan based on the patient's nutritional assessment and growth data.</p>
                            </div>
                        </div>
                    </div>
                    
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #2c3e50;">
                        <i class="fas fa-shopping-basket"></i> Available Foods (Optional)
                    </label>
                    <textarea id="swal-available-foods" class="swal2-textarea" 
                              placeholder="List any specific foods available at home or in the area (e.g., manok, bangus, monggo, kangkong, saging, gatas, itlog)"
                              style="width: 100%; min-height: 120px; resize: vertical; font-size: 0.9375rem;"></textarea>
                    <small style="color: #6c757d; font-size: 0.8125rem; display: block; margin-top: 0.5rem;">
                        <i class="fas fa-lightbulb"></i> This will help customize the meal plan based on local availability and family preferences.
                    </small>
                    
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 1.25rem; border: 1px solid #e1e8ed;">
                        <strong style="color: #2c3e50; display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">
                            <i class="fas fa-check-circle" style="color: #2d7a4f;"></i> What you'll get:
                        </strong>
                        <ul style="margin: 0; padding-left: 1.5rem; color: #6c757d; font-size: 0.8125rem; line-height: 1.6;">
                            <li>Age-appropriate meal plan</li>
                            <li>Nutritional analysis per meal</li>
                            <li>Filipino-friendly recipes</li>
                            <li>Preparation instructions</li>
                        </ul>
                    </div>
                </div>
            `,
            width: '700px',
            padding: '2rem 1rem',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Generate Meal Plan',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#2d7a4f',
            cancelButtonColor: '#95a5a6',
            focusConfirm: false,
            customClass: {
                confirmButton: 'swal-confirm-btn',
                cancelButton: 'swal-cancel-btn'
            },
            preConfirm: () => {
                return document.getElementById('swal-available-foods').value;
            }
        });

        if (availableFoods !== undefined) {
            this.generateMealPlan(patientId, availableFoods);
        }
    }

    async generateFeedingProgram(formData) {
        const days = parseInt(formData.duration);

        Swal.fire({
            title: 'Generating Meal Plan...',
            html: `Creating ${days}-day feeding program for Filipino children...`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const response = await $.ajax({
                url: '/nutritionist/feeding-program/generate',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    target_age_group: formData.ageGroup,
                    total_children: formData.totalChildren ? parseInt(formData.totalChildren) : null,
                    program_duration_days: days,
                    budget_level: formData.budget,
                    barangay: formData.barangay || null,
                    available_ingredients: formData.ingredients || null
                })
            });

            if (response.success) {
                // Save to database
                await this.saveFeedingProgramToDatabase(formData, response.data);
                
                const programHtml = this.formatFeedingProgram(response.data);
                Swal.fire({
                    title: '<i class="fas fa-check-circle" style="color: #2d7a4f;"></i> Success!',
                    html: programHtml,
                    width: '90%',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#2d7a4f',
                    customClass: {
                        popup: 'meal-plan-result'
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Generation Failed',
                    text: 'Failed to generate feeding program meal plan',
                    confirmButtonColor: '#2d7a4f'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.responseJSON?.detail || 'Failed to connect to AI service. Check if FastAPI server is running on port 8002.',
                confirmButtonColor: '#2d7a4f'
            });
        }
    }

    async saveFeedingProgramToDatabase(formData, apiResponse) {
        try {
            await $.ajax({
                url: '/nutritionist/feeding-program/save',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    target_age_group: formData.ageGroup,
                    total_children: formData.totalChildren ? parseInt(formData.totalChildren) : null,
                    program_duration_days: parseInt(formData.duration),
                    budget_level: formData.budget,
                    barangay: formData.barangay || null,
                    available_ingredients: formData.ingredients || null,
                    meal_plan: apiResponse.meal_plan  // This is just the string, not the whole response
                })
            });
            console.log('Feeding program saved to database');
        } catch (error) {
            console.error('Failed to save to database:', error);
            // Don't show error to user - plan was still generated successfully
        }
    }

    async generateMealPlan(patientId, availableFoods) {
        Swal.fire({
            title: 'Generating Meal Plan...',
            html: 'Creating personalized meal plan for patient...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const apiUrl = window.API_CONFIG?.LLM_API_URL || 'http://127.0.0.1:8002';
            const response = await $.ajax({
                url: `${apiUrl}/generate_meal_plan`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    patient_id: patientId,
                    available_foods: availableFoods || null
                })
            });

            if (response.meal_plan) {
                const mealPlanHtml = this.formatIndividualMealPlan(response.meal_plan);
                Swal.fire({
                    title: '<i class="fas fa-check-circle" style="color: #2d7a4f;"></i> Meal Plan Generated!',
                    html: mealPlanHtml,
                    width: '90%',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#2d7a4f',
                    customClass: {
                        popup: 'meal-plan-result'
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Generation Failed',
                    text: 'Failed to generate meal plan',
                    confirmButtonColor: '#2d7a4f'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.responseJSON?.error || 'Failed to connect to AI service',
                confirmButtonColor: '#2d7a4f'
            });
        }
    }

    formatFeedingProgram(response) {
        let html = '<div class="feeding-program-results">';
        
        // Program Header
        const ageGroupLabels = {
            'all': 'All Ages (6 months - 5 years)',
            '6-12months': 'Infants (6-12 months)',
            '12-24months': 'Toddlers (12-24 months)',
            '24-60months': 'Preschoolers (24-60 months)'
        };
        
        const childrenText = response.total_children ? `${response.total_children} children` : 'Variable';
        const barangayText = response.barangay || 'General Philippines';
        const ingredientsText = response.available_ingredients || 'Budget recommendations';
        
        html += `
            <div class="program-header" style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f4 100%); padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #2d7a4f;">
                <h4 style="color: #2d7a4f; margin-bottom: 15px;"><i class="fas fa-users"></i> Generic Feeding Program Details</h4>
                <div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div><strong style="color: #2d7a4f;">Target Group:</strong><br>${ageGroupLabels[response.target_age_group] || response.target_age_group}</div>
                    <div><strong style="color: #2d7a4f;">Children:</strong><br>${childrenText}</div>
                    <div><strong style="color: #2d7a4f;">Duration:</strong><br>${response.program_duration_days} days</div>
                    <div><strong style="color: #2d7a4f;">Budget:</strong><br>${response.budget_level.charAt(0).toUpperCase() + response.budget_level.slice(1)}</div>
                    <div><strong style="color: #2d7a4f;">Location:</strong><br>${barangayText}</div>
                    <div><strong style="color: #2d7a4f;">Generated:</strong><br>${new Date(response.generated_at).toLocaleDateString()}</div>
                </div>
                ${response.available_ingredients ? `
                <div style="margin-top: 15px; padding: 12px; background: white; border-radius: 8px; border-left: 3px solid #52c785;">
                    <strong style="color: #2d7a4f;"><i class="fas fa-shopping-basket"></i> Available Ingredients:</strong><br>
                    <span style="color: #34495e; font-size: 0.9em;">${response.available_ingredients}</span>
                </div>
                ` : ''}
            </div>
        `;
        
        // Parse and format meal plan into table
        html += this.parseMealPlanToTable(response.meal_plan, response.program_duration_days);
        
        // Action Buttons
        html += `
            <div class="action-buttons" style="margin-top: 20px; text-align: right;">
                <button class="btn btn-success" onclick="window.print()" style="background-color: #2d7a4f; border-color: #2d7a4f;">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        `;
        
        html += '</div>';
        return html;
    }

    parseMealPlanToTable(mealPlanText, days) {
        
        let html = `
            <div class="meal-plan-section" style="margin-top: 20px; max-height: 600px; overflow-y: auto;">
                <h4 style="color: #2d7a4f; margin-bottom: 15px;"><i class="fas fa-calendar-alt"></i> Daily Meal Plan</h4>
                <div class="table-responsive" style="margin-top: 15px;">
                    <table class="meal-plan-table" style="width: 100%; background: white; border-collapse: collapse; border: 1px solid #ddd;">
                        <thead style="background-color: #2d7a4f; color: white; position: sticky; top: 0;">
                            <tr>
                                <th style="width: 8%; padding: 12px; text-align: center; border: 1px solid #ddd; font-weight: 600;">Day</th>
                                <th style="width: 12%; padding: 12px; text-align: center; border: 1px solid #ddd; font-weight: 600;">Meal Type</th>
                                <th style="width: 25%; padding: 12px; text-align: left; border: 1px solid #ddd; font-weight: 600;">Dish Name</th>
                                <th style="width: 55%; padding: 12px; text-align: left; border: 1px solid #ddd; font-weight: 600;">Ingredients & Details</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        let mealEntries = [];

        // Handle if mealPlanText is already an object or array
        let parsedData = mealPlanText;
        if (typeof mealPlanText === 'string') {
            // First, try to extract JSON from markdown code blocks
            let jsonString = mealPlanText.trim();
            
            // Check if it's wrapped in markdown code blocks (```json ... ```)
            const codeBlockMatch = jsonString.match(/```(?:json)?\s*\n?([\s\S]*?)\n?```/);
            if (codeBlockMatch) {
                jsonString = codeBlockMatch[1].trim();
            }
            
            // Now try to parse the JSON
            try {
                parsedData = JSON.parse(jsonString);
            } catch (e) {
                // Keep as string for markdown parsing
                parsedData = mealPlanText;
            }
        }

        // If it's an object or array, try to extract meal_plan data
        if (typeof parsedData === 'object' && parsedData !== null) {
            try {
                let mealPlanArray = null;
                
                // Check if there's a nested meal_plan property
                if (parsedData.meal_plan && Array.isArray(parsedData.meal_plan)) {
                    mealPlanArray = parsedData.meal_plan;
                } else if (Array.isArray(parsedData)) {
                    mealPlanArray = parsedData;
                }
                
                // Parse JSON format if it's an array
                if (Array.isArray(mealPlanArray) && mealPlanArray.length > 0) {
                    
                    mealPlanArray.forEach(dayData => {
                        
                        if (dayData.meals && Array.isArray(dayData.meals)) {
                            dayData.meals.forEach(meal => {
                                // Build ingredients string from array or object
                                let ingredientsStr = '';
                                if (Array.isArray(meal.ingredients)) {
                                    ingredientsStr = meal.ingredients.join(', ');
                                } else if (typeof meal.ingredients === 'object' && meal.ingredients !== null) {
                                    // Handle if ingredients is an object (e.g., {0: "item1", 1: "item2"})
                                    ingredientsStr = Object.values(meal.ingredients).join(', ');
                                } else if (typeof meal.ingredients === 'string') {
                                    ingredientsStr = meal.ingredients;
                                }
                                
                                mealEntries.push({
                                    day: `Day ${dayData.day}`,
                                    mealType: this.normalizeMealType(meal.meal_type || meal.mealType || ''),
                                    dishName: meal.dish_name || meal.dishName || 'N/A',
                                    ingredients: ingredientsStr
                                });
                            });
                        }
                    });
                    
                }
            } catch (e) {
            }
        }
        
        // If no entries found and we have a string, try markdown parsing
        if (mealEntries.length === 0 && typeof parsedData === 'string') {
            
            const lines = mealPlanText.split('\n');
            let currentDay = '';
            let currentMealType = '';
            let currentDishName = '';
            let currentIngredients = [];
            let inIngredientsSection = false;

            for (let i = 0; i < lines.length; i++) {
                const line = lines[i];
                const trimmedLine = line.trim();
                
                if (!trimmedLine || trimmedLine.startsWith('---') || trimmedLine.startsWith('===')) {
                    continue;
                }

                if (trimmedLine.match(/^##\s+Day\s+\d+/i)) {
                    if (currentMealType && currentDay) {
                        mealEntries.push({
                            day: currentDay,
                            mealType: this.normalizeMealType(currentMealType),
                            dishName: currentDishName || 'N/A',
                            ingredients: currentIngredients.join(', ')
                        });
                    }
                    
                    currentDay = trimmedLine.replace(/^##\s+/i, '').trim();
                    currentMealType = '';
                    currentDishName = '';
                    currentIngredients = [];
                    inIngredientsSection = false;
                    continue;
                }

                const mealMatch = trimmedLine.match(/^\*\*(almusal|breakfast|tanghalian|lunch|meryenda|snack|hapunan|dinner)(?:\s*\([^)]*\))?\s*:\*\*/i);
                if (mealMatch && currentDay) {
                    if (currentMealType) {
                        mealEntries.push({
                            day: currentDay,
                            mealType: this.normalizeMealType(currentMealType),
                            dishName: currentDishName || 'N/A',
                            ingredients: currentIngredients.join(', ')
                        });
                    }
                    
                    currentMealType = mealMatch[1];
                    currentDishName = '';
                    currentIngredients = [];
                    inIngredientsSection = false;
                    continue;
                }

                if (currentMealType && !currentDishName) {
                    const dishMatch = trimmedLine.match(/^-\s*(?:main\s+dish|snack)\s*:\s*(.+)$/i);
                    if (dishMatch) {
                        currentDishName = dishMatch[1].trim();
                        continue;
                    }
                }

                if (trimmedLine.match(/^-\s*ingredients?\s*(?:\([^)]*\))?\s*:?\s*$/i)) {
                    inIngredientsSection = true;
                    continue;
                }

                if (trimmedLine.match(/^-\s*(preparation|age\s+adaptations?|portions?|description|approximate)/i)) {
                    inIngredientsSection = false;
                    continue;
                }

                if (inIngredientsSection && line.match(/^\s{2,}-\s+/)) {
                    const ingredient = line.replace(/^\s{2,}-\s+/, '').trim();
                    if (ingredient && ingredient.length > 0) {
                        currentIngredients.push(ingredient);
                    }
                }
            }

            if (currentMealType && currentDay) {
                mealEntries.push({
                    day: currentDay,
                    mealType: this.normalizeMealType(currentMealType),
                    dishName: currentDishName || 'N/A',
                    ingredients: currentIngredients.join(', ')
                });
            }
        }

        // Render table
        if (mealEntries.length === 0) {
            html += `
                <tr>
                    <td colspan="4" style="padding: 20px; text-align: center;">
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <p style="color: #856404; margin: 0;"><i class="fas fa-info-circle"></i> Unable to parse meal plan</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            const mealsByDay = {};
            mealEntries.forEach(entry => {
                if (!mealsByDay[entry.day]) {
                    mealsByDay[entry.day] = [];
                }
                mealsByDay[entry.day].push(entry);
            });

            Object.keys(mealsByDay).forEach((day, dayIndex) => {
                const meals = mealsByDay[day];
                
                meals.forEach((meal, mealIndex) => {
                    const isFirstMeal = mealIndex === 0;
                    const rowspan = meals.length;
                    
                    html += '<tr style="border-bottom: 1px solid #ddd;">';
                    
                    if (isFirstMeal) {
                        html += `<td rowspan="${rowspan}" style="background-color: #e8f5e9; text-align: center; font-weight: bold; vertical-align: middle; color: #2d7a4f; font-size: 0.95em; padding: 12px; border: 1px solid #ddd;">${day}</td>`;
                    }
                    
                    const ingredientsList = meal.ingredients ? meal.ingredients.split(',').map(ing => ing.trim()).filter(ing => ing) : [];
                    
                    html += `
                        <td style="font-weight: 600; color: #2c3e50; padding: 12px; border: 1px solid #ddd; text-align: center; font-size: 0.95em;">${meal.mealType}</td>
                        <td style="color: #34495e; padding: 12px; border: 1px solid #ddd; font-size: 0.95em; word-break: break-word;">
                            <strong>${meal.dishName}</strong>
                        </td>
                        <td style="color: #555; padding: 12px; border: 1px solid #ddd; font-size: 0.9em;">
                            ${ingredientsList.length > 0 ? `
                                <div style="max-height: 140px; overflow-y: auto; border: 1px solid #e0e0e0; padding: 10px; background: #fafafa; border-radius: 4px; line-height: 1.6;">
                                    ${ingredientsList.map(ing => `<div style="margin: 5px 0; padding: 5px 8px; border-bottom: 1px solid #e8e8e8; word-wrap: break-word;">• ${ing}</div>`).join('')}
                                </div>
                            ` : '<em style="color: #999;">—</em>'}
                        </td>
                    `;
                    
                    html += '</tr>';
                });
            });
        }

        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        return html;
    }

    normalizeMealType(mealType) {
        const normalized = mealType.toLowerCase();
        if (normalized.includes('almusal') || normalized.includes('breakfast')) {
            return 'Breakfast';
        } else if (normalized.includes('tanghalian') || normalized.includes('lunch')) {
            return 'Lunch';
        } else if (normalized.includes('meryenda') || normalized.includes('snack')) {
            return 'Snack';
        } else if (normalized.includes('hapunan') || normalized.includes('dinner')) {
            return 'Dinner';
        }
        return mealType.charAt(0).toUpperCase() + mealType.slice(1);
    }

    extractDishName(dishName) {
        if (!dishName) return '';
        return dishName.replace(/\*\*/g, '').replace(/^[-•]\s*/, '').trim();
    }

    formatMealPlanMarkdown(text) {
        // Convert markdown-style formatting to HTML
        return text
            .replace(/# (.*)/g, '<h3 style="color: #2c3e50; margin-top: 20px;">$1</h3>')
            .replace(/## (.*)/g, '<h4 style="color: #34495e; margin-top: 15px;">$1</h4>')
            .replace(/### (.*)/g, '<h5 style="color: #7f8c8d;">$1</h5>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
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

    formatIndividualMealPlan(mealPlanText) {
        // Format individual patient meal plan from FastAPI
        let html = `
            <div class="individual-meal-plan-container" style="background: #f8f9fa; padding: 2rem; border-radius: 12px;">
                <div style="background: linear-gradient(135deg, #2d7a4f 0%, #1e5a3a 100%); color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0; font-size: 1.5rem;">
                        <i class="fas fa-utensils"></i> Personalized Meal Plan
                    </h3>
                    <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Customized nutrition plan based on patient's needs</p>
                </div>
        `;

        // Parse and display sections
        const sections = mealPlanText.split(/(?=[A-Z][A-Z\s]*:)/).filter(s => s.trim());
        
        sections.forEach(section => {
            if (section.trim()) {
                const colonIndex = section.indexOf(':');
                if (colonIndex > 0) {
                    const title = section.substring(0, colonIndex).trim();
                    const content = section.substring(colonIndex + 1).trim();
                    
                    html += `
                        <div class="meal-plan-section" style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <h4 style="color: #2d7a4f; margin-top: 0; margin-bottom: 1rem; font-size: 1.2rem; border-bottom: 2px solid #2d7a4f; padding-bottom: 0.5rem;">
                                ${title}
                            </h4>
                            <div style="color: #2c3e50; line-height: 1.8; white-space: pre-wrap;">
                                ${content}
                            </div>
                        </div>
                    `;
                }
            }
        });

        html += `
                <div class="action-buttons" style="margin-top: 1.5rem; text-align: right;">
                    <button class="btn btn-success" onclick="window.print()" style="background-color: #2d7a4f; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; color: white; font-weight: 500; cursor: pointer;">
                        <i class="fas fa-print"></i> Print Meal Plan
                    </button>
                </div>
            </div>
        `;
        
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
    }
}

// Initialize when document is ready
if (typeof jQuery !== 'undefined') {
    $(document).ready(() => {
        window.mealPlansManager = new MealPlansManager();
    });
} else {
    console.error('jQuery is not loaded. Meal Plans Manager cannot initialize.');
    // Try to initialize after a delay in case jQuery loads later
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof jQuery !== 'undefined') {
            window.mealPlansManager = new MealPlansManager();
        } else {
            console.error('jQuery still not available after DOM load.');
        }
    });
}

// ========================================
// SAVED FEEDING PROGRAM PLANS - VIEW & DELETE
// ========================================

$(document).ready(function() {
    // View saved plan
    $('.view-program-plan-btn').on('click', function() {
        const planId = $(this).data('plan-id');
        viewFeedingProgramPlan(planId);
    });

    // Delete saved plan
    $('.delete-program-plan-btn').on('click', function() {
        const planId = $(this).data('plan-id');
        deleteFeedingProgramPlan(planId);
    });

    // Program plans search functionality
    $('#program-search').on('input', function() {
        // Show container when user starts typing
        if ($(this).val().length > 0) {
            $('#program-plans-list-container').show();
            $('.program-plan-item').show(); // Show all items first
        } else {
            // When search is cleared, show only the latest
            showOnlyLatestProgramPlan();
        }
        filterProgramPlans();
    });

    // Program plans filter functionality
    $('#program-budget-filter, #program-age-filter').on('change', function() {
        // Show all items when filter is changed
        $('#program-plans-list-container').show();
        $('.program-plan-item').show();
        filterProgramPlans();
    });

    // Program plans sort functionality
    $('#program-sort').on('change', function() {
        $('#program-plans-list-container').show();
        $('.program-plan-item').show();
        sortProgramPlans($(this).val());
    });

    // Show only the latest program plan on initial load
    showOnlyLatestProgramPlan();

    // Reset filters button
    $('#reset-program-filters').on('click', function() {
        // Clear search input
        $('#program-search').val('');
        
        // Reset all filter selects
        $('#program-budget-filter').val('');
        $('#program-age-filter').val('');
        $('#program-sort').val('newest');
        
        // Show only the latest plan
        showOnlyLatestProgramPlan();
    });
});

/**
 * Show only the most recent program plan
 */
function showOnlyLatestProgramPlan() {
    const $items = $('.program-plan-item');
    
    if ($items.length > 0) {
        // Find the item with the highest timestamp
        let latestItem = null;
        let latestTimestamp = 0;
        
        $items.each(function() {
            const timestamp = $(this).data('timestamp');
            if (timestamp > latestTimestamp) {
                latestTimestamp = timestamp;
                latestItem = this;
            }
        });
        
        // Hide all items
        $items.hide();
        
        // Show only the latest
        if (latestItem) {
            $(latestItem).show();
        }
        
        // Show the container
        $('#program-plans-list-container').show();
    }
}

/**
 * Filter program plans based on search and filters
 */
function filterProgramPlans() {
    const searchTerm = $('#program-search').val().toLowerCase();
    const budgetFilter = $('#program-budget-filter').val().toLowerCase();
    const ageFilter = $('#program-age-filter').val().toLowerCase();
    
    $('.program-plan-item').each(function() {
        const $item = $(this);
        const budget = $item.data('budget').toString().toLowerCase();
        const ageGroup = $item.data('age-group').toString().toLowerCase();
        const barangay = $item.data('barangay').toString().toLowerCase();
        
        // Check search term
        const matchesSearch = !searchTerm || 
            budget.includes(searchTerm) ||
            ageGroup.includes(searchTerm) ||
            barangay.includes(searchTerm);
        
        // Check budget filter
        const matchesBudget = !budgetFilter || budget === budgetFilter;
        
        // Check age filter
        const matchesAge = !ageFilter || ageGroup === ageFilter;
        
        // Show/hide based on all filters
        if (matchesSearch && matchesBudget && matchesAge) {
            $item.show();
        } else {
            $item.hide();
        }
    });
    
    // Show "no results" message if needed
    const visibleCount = $('.program-plan-item:visible').length;
    if (visibleCount === 0) {
        if (!$('#program-no-results').length) {
            $('#program-plans-list').append(`
                <div id="program-no-results" class="empty-state" style="padding: 2rem; text-align: center; color: #6c757d;">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; display: block; color: #dee2e6;"></i>
                    <p style="margin: 0;">No feeding program plans match your search criteria.</p>
                </div>
            `);
        }
    } else {
        $('#program-no-results').remove();
    }
}

/**
 * Sort program plans
 */
function sortProgramPlans(sortBy) {
    const $container = $('#program-plans-list');
    const $items = $container.find('.program-plan-item').get();
    
    $items.sort(function(a, b) {
        const $a = $(a);
        const $b = $(b);
        
        switch(sortBy) {
            case 'newest':
                return $b.data('timestamp') - $a.data('timestamp');
            case 'oldest':
                return $a.data('timestamp') - $b.data('timestamp');
            case 'duration-asc':
                return $a.data('duration') - $b.data('duration');
            case 'duration-desc':
                return $b.data('duration') - $a.data('duration');
            default:
                return 0;
        }
    });
    
    $.each($items, function(i, item) {
        $container.append(item);
    });
}


/**
 * View saved feeding program plan
 */
function viewFeedingProgramPlan(planId) {
    Swal.fire({
        title: 'Loading Plan...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: `/nutritionist/feeding-program/${planId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const plan = response.plan;
                
                // plan_details is auto-decoded from JSON by Laravel
                let mealPlanData = plan.plan_details;
                

                
                // Parse the meal plan - parseMealPlanToTable handles both JSON and string formats
                let mealPlanHtml;
                if (window.mealPlansManager && typeof window.mealPlansManager.parseMealPlanToTable === 'function') {
                    // Pass the data as-is, whether it's an object, array, or string
                    mealPlanHtml = window.mealPlansManager.parseMealPlanToTable(mealPlanData, plan.program_duration_days);
                } else {
                    // Fallback: display raw content
                    const displayText = typeof mealPlanData === 'object' ? JSON.stringify(mealPlanData, null, 2) : mealPlanData;
                    mealPlanHtml = `<div class="feeding-program-results"><pre style="white-space: pre-wrap; font-family: inherit;">${displayText}</pre></div>`;
                }
                
                // Create metadata display
                const metadata = `
                    <div class="feeding-program-header">
                        <div class="program-info-grid">
                            <div class="program-info-item">
                                <strong>Age Group</strong>
                                <span>${getAgeGroupLabel(plan.target_age_group)}</span>
                            </div>
                            <div class="program-info-item">
                                <strong>Duration</strong>
                                <span>${plan.program_duration_days} Days</span>
                            </div>
                            <div class="program-info-item">
                                <strong>Budget</strong>
                                <span>${plan.budget_level.charAt(0).toUpperCase() + plan.budget_level.slice(1)}</span>
                            </div>
                            ${plan.barangay ? `
                            <div class="program-info-item">
                                <strong>Barangay</strong>
                                <span>${plan.barangay}</span>
                            </div>
                            ` : ''}
                            ${plan.total_children ? `
                            <div class="program-info-item">
                                <strong>Children</strong>
                                <span>${plan.total_children}</span>
                            </div>
                            ` : ''}
                            ${plan.available_ingredients ? `
                            <div class="program-info-full">
                                <strong>Available Ingredients</strong>
                                <div class="ingredients-list">${plan.available_ingredients}</div>
                            </div>
                            ` : ''}
                        </div>
                        <div class="program-timestamp">
                            <i class="fas fa-clock"></i> Generated: ${plan.generated_at ? new Date(plan.generated_at).toLocaleString() : 'N/A'}
                        </div>
                    </div>
                `;
                
                Swal.fire({
                    title: 'Feeding Program Meal Plan',
                    html: metadata + mealPlanHtml,
                    width: '90%',
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        container: 'feeding-program-modal',
                        popup: 'feeding-program-popup'
                    }
                });
            } else {
                Swal.fire('Error', 'Failed to load meal plan', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error loading plan:', xhr);
            Swal.fire('Error', 'Failed to load meal plan', 'error');
        }
    });
}

function getAgeGroupLabel(ageGroup) {
    const labels = {
        'all': 'All Ages (6 months - 5 years)',
        '6-12months': 'Infants (6-12 months)',
        '12-24months': 'Toddlers (12-24 months)',
        '24-60months': 'Preschoolers (24-60 months)'
    };
    return labels[ageGroup] || ageGroup;
}

/**
 * Delete saved feeding program plan
 */
function deleteFeedingProgramPlan(planId) {
    Swal.fire({
        title: 'Delete Meal Plan?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/nutritionist/feeding-program/${planId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'Meal plan has been deleted.', 'success')
                            .then(() => {
                                // Remove the item from the list
                                $(`.program-plan-item[data-plan-id="${planId}"]`).fadeOut(300, function() {
                                    $(this).remove();
                                    
                                    // Check if there are any plans left
                                    if ($('.program-plan-item').length === 0) {
                                        $('#program-plans-list-container').replaceWith(`
                                            <div class="empty-plans-state">
                                                <i class="fas fa-clipboard-list"></i>
                                                <p>No saved feeding program plans yet. Create one to get started!</p>
                                            </div>
                                        `);
                                    }
                                });
                            });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to delete meal plan', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error deleting plan:', xhr);
                    Swal.fire('Error', 'Failed to delete meal plan', 'error');
                }
            });
        }
    });
}
