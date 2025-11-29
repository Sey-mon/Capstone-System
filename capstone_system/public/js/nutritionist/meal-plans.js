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

        // View all patients
        $(document).on('click', '#view-all-patients-btn', () => {
            $('#patient-search').val('');
            this.filterPatients('');
            $('#patients-list-container').slideDown(300);
            $('#patient-search').focus();
        });
    }

    async showFeedingProgramModal() {
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
                                <option value="all">All Ages (0-5 years) - Mixed group</option>
                                <option value="0-12months">Infants (0-12 months)</option>
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
                                <option value="5">5 Days</option>
                                <option value="6">6 Days</option>
                                <option value="7" selected>7 Days (1 Week)</option>
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
                        <input id="swal-barangay" type="text" class="swal2-input" placeholder="Enter barangay name for location-specific recommendations" style="width: 100%;">
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
                url: 'http://127.0.0.1:8002/feeding_program/meal_plan',
                method: 'POST',
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
                await this.saveFeedingProgramToDatabase(formData, response);
                
                const programHtml = this.formatFeedingProgram(response);
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
                    meal_plan: apiResponse.meal_plan
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
            const response = await $.ajax({
                url: `http://127.0.0.1:8002/patients/${patientId}/meal_plan`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    available_foods: availableFoods || null
                })
            });

            if (response.success) {
                const mealPlanHtml = this.formatMealPlan(response);
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
            'all': 'All Ages (0-5 years)',
            '0-12months': 'Infants (0-12 months)',
            '12-24months': 'Toddlers (12-24 months)',
            '24-60months': 'Preschoolers (24-60 months)'
        };
        
        html += `
            <div class="program-header" style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f4 100%); padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #2d7a4f;">
                <h4 style="color: #2d7a4f; margin-bottom: 15px;"><i class="fas fa-users"></i> Generic Feeding Program Details</h4>
                <div class="row">
                    <div class="col-md-3"><strong style="color: #2d7a4f;">Target Group:</strong><br>${ageGroupLabels[response.target_age_group] || response.target_age_group}</div>
                    <div class="col-md-3"><strong style="color: #2d7a4f;">Duration:</strong><br>${response.program_duration_days} days</div>
                    <div class="col-md-3"><strong style="color: #2d7a4f;">Budget Level:</strong><br>${response.budget_level.charAt(0).toUpperCase() + response.budget_level.slice(1)}</div>
                    <div class="col-md-3"><strong style="color: #2d7a4f;">Generated:</strong><br>${new Date(response.generated_at).toLocaleDateString()}</div>
                </div>
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
            <div class="meal-plan-section" style="margin-top: 20px;">
                <h4 style="color: #2d7a4f;"><i class="fas fa-calendar-alt"></i> Daily Meal Plan</h4>
                <div class="table-responsive" style="margin-top: 15px;">
                    <table class="table table-bordered" style="background: white; border: 2px solid #2d7a4f;">
                        <thead style="background-color: #2d7a4f; color: white;">
                            <tr>
                                <th style="width: 10%;">Day</th>
                                <th style="width: 15%;">Meal Type</th>
                                <th style="width: 30%;">Dish Name</th>
                                <th style="width: 45%;">Ingredients & Details</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        // Parse the meal plan text
        const lines = mealPlanText.split('\n');
        let currentDay = '';
        let mealEntries = [];

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();
            
            // Match day headers (Day 1, Monday, etc.)
            const dayMatch = line.match(/^#+\s*(?:day\s+(\d+)|(\w+day))/i) || 
                           line.match(/^(?:day\s+(\d+)|(\w+day))\s*:?\s*$/i);
            if (dayMatch) {
                currentDay = dayMatch[1] || dayMatch[2] || '';
                if (!currentDay.match(/\d/)) {
                    // Convert day name to number
                    const dayNames = {'monday': 1, 'tuesday': 2, 'wednesday': 3, 'thursday': 4, 'friday': 5, 'saturday': 6, 'sunday': 7};
                    currentDay = dayNames[currentDay.toLowerCase()] || currentDay;
                }
                continue;
            }

            // Match meal entries - supports both English and Tagalog
            const mealMatch = line.match(/^\*\*?(almusal|breakfast|tanghalian|lunch|meryenda|am snack|pm snack|hapunan|dinner)\s*(?:\([^)]*\))?\s*:?\*\*?\s*$/i);
            if (mealMatch && currentDay) {
                const mealType = mealMatch[1];
                let dishName = '';
                let ingredients = '';
                let details = [];

                // Look ahead for dish details
                for (let j = i + 1; j < lines.length && j < i + 15; j++) {
                    const nextLine = lines[j].trim();
                    
                    // Stop if we hit another meal type or day
                    if (nextLine.match(/^\*\*?(almusal|breakfast|tanghalian|lunch|meryenda|am snack|pm snack|hapunan|dinner|day\s+\d+|\w+day)/i)) {
                        break;
                    }

                    // Match "Main Dish:" or "- Main Dish:" or just dish after meal type
                    const dishMatch = nextLine.match(/^-?\s*(?:main\s+dish|dish\s*name|ulam)?\s*:?\s*(.+)/i);
                    if (dishMatch && !dishName && !nextLine.match(/^-?\s*(ingredients?|age|portions?|preparation)/i)) {
                        dishName = dishMatch[1].replace(/\*\*/g, '').replace(/^[-•]\s*/, '').trim();
                        continue;
                    }

                    // Match ingredients
                    const ingredientsMatch = nextLine.match(/^-?\s*ingredients?\s*:?\s*(.+)/i);
                    if (ingredientsMatch) {
                        ingredients = ingredientsMatch[1].trim();
                        // Collect multi-line ingredients
                        for (let k = j + 1; k < lines.length && k < j + 5; k++) {
                            const ingLine = lines[k].trim();
                            if (ingLine && !ingLine.match(/^-?\s*(age|portions?|preparation|main)/i) && !ingLine.match(/^\*\*/)) {
                                ingredients += ' ' + ingLine;
                            } else {
                                break;
                            }
                        }
                        continue;
                    }

                    // Collect other details (age adaptations, portions, etc.)
                    if (nextLine && !nextLine.match(/^#+/) && nextLine.length > 2) {
                        details.push(nextLine);
                    }
                }

                // Normalize meal type
                let normalizedMealType = mealType.toLowerCase();
                if (normalizedMealType.includes('almusal') || normalizedMealType.includes('breakfast')) {
                    normalizedMealType = 'Breakfast';
                } else if (normalizedMealType.includes('tanghalian') || normalizedMealType.includes('lunch')) {
                    normalizedMealType = 'Lunch';
                } else if (normalizedMealType.includes('meryenda') || normalizedMealType.includes('snack')) {
                    normalizedMealType = normalizedMealType.includes('am') ? 'AM Snack' : 'PM Snack';
                } else if (normalizedMealType.includes('hapunan') || normalizedMealType.includes('dinner')) {
                    normalizedMealType = 'Dinner';
                }

                mealEntries.push({
                    day: currentDay,
                    mealType: normalizedMealType,
                    dishName: dishName || 'See details',
                    ingredients: ingredients,
                    details: details.join('<br>')
                });
            }
        }

        // If no meals found, try simpler parsing
        if (mealEntries.length === 0) {
            // Fallback: just show the text in a single row
            html += `
                <tr>
                    <td colspan="4" style="padding: 20px;">
                        <div style="white-space: pre-wrap; font-family: monospace; font-size: 0.9em;">${mealPlanText}</div>
                    </td>
                </tr>
            `;
        } else {
            // Group by day
            const mealsByDay = {};
            mealEntries.forEach(entry => {
                if (!mealsByDay[entry.day]) {
                    mealsByDay[entry.day] = [];
                }
                mealsByDay[entry.day].push(entry);
            });

            // Generate table rows
            Object.keys(mealsByDay).sort((a, b) => {
                const aNum = parseInt(a) || 0;
                const bNum = parseInt(b) || 0;
                return aNum - bNum;
            }).forEach(day => {
                const meals = mealsByDay[day];
                meals.forEach((meal, index) => {
                    const isFirstMeal = index === 0;
                    const rowspan = meals.length;
                    
                    html += '<tr>';
                    
                    if (isFirstMeal) {
                        html += `<td rowspan="${rowspan}" style="background-color: #f1f8f4; text-align: center; font-weight: bold; vertical-align: middle; color: #2d7a4f; font-size: 1.1em;">Day ${day}</td>`;
                    }
                    
                    const combinedDetails = [meal.ingredients, meal.details].filter(d => d).join('<br><br>');
                    
                    html += `
                        <td data-label="Meal Type" style="font-weight: 600; color: #2c3e50;">${meal.mealType}</td>
                        <td data-label="Dish Name" style="color: #34495e;"><strong>${meal.dishName}</strong></td>
                        <td data-label="Details" style="font-size: 0.9em; color: #555;">
                            ${combinedDetails ? 
                                `<details style="cursor: pointer;" open>
                                    <summary style="color: #2d7a4f; font-weight: 500;">
                                        <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i> View Details
                                    </summary>
                                    <div style="margin-top: 8px; padding: 10px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #52c785; max-height: 200px; overflow-y: auto;">
                                        ${combinedDetails}
                                    </div>
                                </details>` 
                                : '<em style="color: #999;">No details available</em>'}
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
