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

        // Close results
        $(document).on('click', '#close-results-btn', () => this.hideResults());

        // Feeding Program - Open modal
        $(document).on('click', '#open-feeding-program-btn, #open-feeding-program-btn-empty', () => this.showFeedingProgramModal());
        
        // Show all plans
        $(document).on('click', '#show-all-plans', () => {
            $('.plan-card').show();
        });
        
        // Download PDF button
        $(document).on('click', '#download-feeding-pdf, .download-pdf-btn', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.downloadPDF();
        });
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
                <div style="text-align: left;">
                    <div class="form-row-2col">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-child"></i> Target Age Group
                            </label>
                            <select id="swal-age-group" class="swal2-select">
                                <option value="all">All Ages (6mo-5y) - Mixed</option>
                                <option value="6-12months">Infants (6-12mo)</option>
                                <option value="12-24months">Toddlers (12-24mo)</option>
                                <option value="24-60months">Preschoolers (24-60mo)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <i class="fas fa-users"></i> Number of Children
                            </label>
                            <input id="swal-total-children" type="number" class="swal2-input" placeholder="e.g., 50" min="1">
                            <small>For shopping list quantities</small>
                        </div>
                    </div>
                    
                    <div class="form-row-2col">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-alt"></i> Program Duration
                            </label>
                            <select id="swal-duration" class="swal2-select">
                                <option value="1">1 Day</option>
                                <option value="2">2 Days</option>
                                <option value="3">3 Days</option>
                                <option value="4">4 Days</option>
                                <option value="5" selected>5 Days (Max)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <i class="fas fa-wallet"></i> Budget Level
                            </label>
                            <select id="swal-budget" class="swal2-select">
                                <option value="low">Low Budget (₱50-100/child/day)</option>
                                <option value="moderate" selected>Moderate (₱100-150/child/day)</option>
                                <option value="high">High Budget (₱150-200/child/day)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 0.5rem;">
                        <label>
                            <i class="fas fa-map-marker-alt"></i> Barangay (Optional)
                        </label>
                        <select id="swal-barangay" class="swal2-select">
                            ${barangayOptions}
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-top: 1rem;">
                        <label>
                            <i class="fas fa-shopping-basket"></i> Available Ingredients (Optional)
                        </label>
                        <textarea id="swal-ingredients" class="swal2-textarea" placeholder="List available ingredients from suppliers or donations (e.g., manok, bangus, monggo, kangkong, saging, kalabasa)"></textarea>
                        <small>Comma-separated list helps customize meal plans based on what you have</small>
                    </div>
                </div>
            `,
            width: '1000px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Generate Plan',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            focusConfirm: false,
            customClass: {
                popup: 'feeding-program-modal',
                htmlContainer: 'feeding-program-form'
            },
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

    async generateFeedingProgram(formData) {
        const days = parseInt(formData.duration);

        Swal.fire({
            title: 'Generating Meal Plan...',
            html: `
                <div style="margin: 20px 0;">
                    <p>Creating ${days}-day feeding program for Filipino children...</p>
                    <p style="font-size: 0.9em; color: #6c757d; margin-top: 10px;">
                        <i class="fas fa-info-circle"></i> This may take 30-60 seconds
                    </p>
                </div>
            `,
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
                timeout: 120000, // 2 minute timeout
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
                // Validate the response data
                if (!response.data || !response.data.meal_plan) {
                    throw new Error('Invalid response from server: missing meal plan data');
                }
                
                // Save to database
                const savedPlan = await this.saveFeedingProgramToDatabase(formData, response.data);
                
                // Add the new plan to the list immediately (before showing success modal)
                this.addPlanToList(savedPlan);
                
                const programHtml = this.formatFeedingProgram(response.data);
                
                // Check if parsing was successful
                if (programHtml.includes('Parsing Error')) {
                    // Show warning but still display the result
                    Swal.fire({
                        icon: 'warning',
                        title: 'Meal Plan Generated with Issues',
                        html: programHtml,
                        width: '90%',
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#f59e0b',
                        timer: 5000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'meal-plan-result'
                        },
                        footer: '<p style="color: #856404;">The meal plan was created but had formatting issues. Try generating again for better results.</p>'
                    }).then(() => {
                        // Reload the page when modal is closed (either by timer or manual close)
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: '<i class="fas fa-check-circle" style="color: #10b981;"></i> Success!',
                        html: programHtml,
                        width: '90%',
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#10b981',
                        timer: 5000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'meal-plan-result'
                        }
                    }).then(() => {
                        // Reload the page when modal is closed (either by timer or manual close)
                        location.reload();
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Generation Failed',
                    text: response.message || 'Failed to generate feeding program meal plan',
                    confirmButtonColor: '#2d7a4f'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            
            let errorMessage = 'Failed to connect to AI service.';
            
            if (error.status === 504 || error.statusText === 'timeout') {
                errorMessage = 'Request timed out. The AI service is taking too long to respond. Please try again.';
            } else if (error.responseJSON?.detail) {
                errorMessage = error.responseJSON.detail;
            } else if (error.responseText) {
                errorMessage = error.responseText;
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `
                    <p>${errorMessage}</p>
                    <p style="font-size: 0.9em; color: #6c757d; margin-top: 10px;">
                        Check if FastAPI server is running on port 8002.
                    </p>
                `,
                confirmButtonColor: '#2d7a4f',
                confirmButtonText: 'OK'
            });
        }
    }

    async saveFeedingProgramToDatabase(formData, apiResponse) {
        try {
            const response = await $.ajax({
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
            return response.plan; // Return the saved plan data
        } catch (error) {
            console.error('Failed to save to database:', error);
            throw error;
        }
    }

    formatFeedingProgram(response) {
        // Store response data for PDF generation
        this.currentProgramData = response;
        
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
            <div class="action-buttons" style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn btn-primary download-pdf-btn" id="download-feeding-pdf" style="background: linear-gradient(135deg, #10b981, #34d399); border: none; padding: 10px 20px; border-radius: 8px; color: white; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-pdf"></i> Save as PDF
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
            // First, try to extract JSON from markdown code blocks or clean the string
            let jsonString = mealPlanText.trim();
            
            // Remove markdown code blocks if present (```json ... ``` or ``` ... ```)
            const codeBlockMatch = jsonString.match(/```(?:json)?\s*\n?([\s\S]*?)\n?```/);
            if (codeBlockMatch) {
                jsonString = codeBlockMatch[1].trim();
            }
            
            // Try to find JSON object boundaries if there's extra text
            const jsonMatch = jsonString.match(/\{[\s\S]*\}/);
            if (jsonMatch && !codeBlockMatch) {
                jsonString = jsonMatch[0];
            }
            
            // Clean up common LLM output issues
            jsonString = jsonString
                .replace(/^[^{]*/, '')  // Remove text before first {
                .replace(/[^}]*$/, '')  // Remove text after last }
                .trim();
            
            // Now try to parse the JSON
            try {
                parsedData = JSON.parse(jsonString);
            } catch (e) {
                console.warn('JSON parsing failed, falling back to markdown parsing:', e.message);
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
                                
                                // Try multiple possible field names for meal type
                                const mealTypeValue = meal.meal_name_tagalog || meal.meal_name || meal.meal_type || meal.mealType || meal.type || meal.meal || '';
                                
                                mealEntries.push({
                                    day: `Day ${dayData.day}`,
                                    mealType: this.normalizeMealType(mealTypeValue),
                                    dishName: meal.dish_name || meal.dishName || meal.name || 'N/A',
                                    ingredients: ingredientsStr
                                });
                            });
                        }
                    });
                    
                }
            } catch (e) {
                console.error('Error processing parsed JSON data:', e);
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
            console.error('Failed to extract meal entries from response');
            html += `
                <tr>
                    <td colspan="4" style="padding: 20px; text-align: center;">
                        <div style="background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <p style="color: #856404; margin: 0 0 10px 0; font-weight: bold;">
                                <i class="fas fa-exclamation-triangle"></i> Parsing Error
                            </p>
                            <p style="color: #856404; margin: 0; font-size: 0.9em;">
                                The meal plan was generated but couldn't be displayed properly. 
                                This is a known issue we're working to fix. Please try generating again.
                            </p>
                            <button onclick="location.reload()" class="btn btn-primary" style="margin-top: 15px; background-color: #2d7a4f; border: none; padding: 8px 16px; border-radius: 4px; color: white; cursor: pointer;">
                                <i class="fas fa-redo"></i> Try Again
                            </button>
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
                    
                    // Format ingredients as comma-separated text with proper wrapping
                    const ingredientsDisplay = ingredientsList.length > 0 
                        ? ingredientsList.join(', ')
                        : '<em style="color: #999;">—</em>';
                    
                    html += `
                        <td style="font-weight: 600; color: #2c3e50; padding: 12px; border: 1px solid #ddd; text-align: center; font-size: 0.95em;">${meal.mealType}</td>
                        <td style="color: #34495e; padding: 12px; border: 1px solid #ddd; font-size: 0.95em; word-break: break-word;">
                            <strong>${meal.dishName}</strong>
                        </td>
                        <td style="color: #555; padding: 12px; border: 1px solid #ddd; font-size: 0.875em; line-height: 1.7; word-wrap: break-word; overflow-wrap: break-word;">
                            ${ingredientsDisplay}
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

    addPlanToList(plan) {
        // Hide empty state and show grid
        $('.empty-state').hide();
        $('#program-plans-list').show();

        // Format age group label
        let ageGroupLabel = '';
        let ageGroupShort = '';
        if (plan.target_age_group === 'all') {
            ageGroupLabel = 'All Ages (6mo-5y)';
            ageGroupShort = 'All Ages';
        } else if (plan.target_age_group === '6-12months') {
            ageGroupLabel = 'Infants (6-12mo)';
            ageGroupShort = '6-12mo';
        } else if (plan.target_age_group === '12-24months') {
            ageGroupLabel = 'Toddlers (12-24mo)';
            ageGroupShort = '12-24mo';
        } else {
            ageGroupLabel = 'Preschoolers (24-60mo)';
            ageGroupShort = '24-60mo';
        }

        // Format the generated_at timestamp
        const generatedDate = new Date(plan.generated_at);
        const formattedDate = generatedDate.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric'
        });

        // Build the HTML for the new plan card
        const planHtml = `
            <div class="plan-card" 
                 data-plan-id="${plan.program_plan_id}"
                 data-budget="${plan.budget_level}"
                 data-age-group="${plan.target_age_group}"
                 data-barangay="${(plan.barangay || '').toLowerCase()}"
                 data-duration="${plan.program_duration_days}"
                 data-timestamp="${Math.floor(generatedDate.getTime() / 1000)}">
                <div class="plan-card-header">
                    <div class="plan-avatar">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="plan-info">
                        <h3 class="plan-title">
                            ${plan.budget_level.charAt(0).toUpperCase() + plan.budget_level.slice(1)} Budget Plan
                        </h3>
                        <div class="plan-meta">
                            <span class="meta-item">
                                <i class="fas fa-baby"></i>
                                ${ageGroupShort}
                            </span>
                            ${plan.total_children ? `
                            <span class="meta-item">
                                <i class="fas fa-users"></i>
                                ${plan.total_children} children
                            </span>
                            ` : ''}
                        </div>
                    </div>
                    <div class="plan-status">
                        <span class="status-badge status-completed">
                            <i class="fas fa-check-circle"></i>
                            COMPLETED
                        </span>
                    </div>
                </div>

                <div class="plan-card-body">
                    <div class="plan-detail-row">
                        <div class="detail-item">
                            <label>
                                <i class="fas fa-calendar"></i>
                                GENERATED DATE
                            </label>
                            <div class="detail-value">
                                ${formattedDate}
                            </div>
                        </div>
                        <div class="detail-item">
                            <label>
                                <i class="fas fa-map-marker-alt"></i>
                                LOCATION
                            </label>
                            <div class="detail-value budget-badge budget-${plan.budget_level}">
                                ${plan.barangay || 'Not Specified'}
                            </div>
                        </div>
                    </div>

                    <div class="plan-detail-row">
                        <div class="detail-item full-width">
                            <label>
                                <i class="fas fa-hourglass-half"></i>
                                PROGRAM DURATION
                            </label>
                            <div class="detail-value">${plan.program_duration_days} Days Program</div>
                        </div>
                    </div>

                    ${plan.available_ingredients ? `
                    <div class="plan-detail-row">
                        <div class="detail-item full-width">
                            <label>
                                <i class="fas fa-carrot"></i>
                                AVAILABLE INGREDIENTS
                            </label>
                            <div class="detail-value ingredients-text">
                                ${plan.available_ingredients.length > 100 ? plan.available_ingredients.substring(0, 100) + '...' : plan.available_ingredients}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>

                <div class="plan-card-footer">
                    <button type="button" class="btn-action btn-view view-program-plan-btn" 
                            data-plan-id="${plan.program_plan_id}">
                        <i class="fas fa-eye"></i>
                        View Details
                    </button>
                    <button type="button" class="btn-action btn-delete delete-program-plan-btn" 
                            data-plan-id="${plan.program_plan_id}">
                        <i class="fas fa-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
        `;

        // Add the new plan to the top of the grid with animation
        const $planElement = $(planHtml).hide();
        $('#program-plans-list').prepend($planElement);
        $planElement.fadeIn(400);
        
        // Update the plan count in the header
        this.updatePlanCount();
    }
    
    updatePlanCount() {
        const totalPlans = $('#program-plans-list .plan-card').length;
        const planText = totalPlans === 1 ? 'plan' : 'plans';
        $('.btn-count').html(`<i class="fas fa-file-alt"></i> ${totalPlans} ${planText}`);
    }

    downloadPDF() {
        if (!this.currentProgramData) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No meal plan data available. Please generate a meal plan first.',
            });
            return;
        }

        const data = this.currentProgramData;
        
        // Show loading
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while we create your PDF file',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Wait a bit for modal to show
        setTimeout(() => {
            try {
                // Generate filename with date
                const today = new Date();
                const dateStr = today.getFullYear() + '-' + 
                              String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                              String(today.getDate()).padStart(2, '0');
                const filename = `Feeding-Program-Plan-${dateStr}.pdf`;
                
                // Age group labels
                const ageGroupLabels = {
                    'all': 'All Ages (6 months - 5 years)',
                    '6-12months': 'Infants (6-12 months)',
                    '12-24months': 'Toddlers (12-24 months)',
                    '24-60months': 'Preschoolers (24-60 months)'
                };
                
                // Get the entire meal plan results div
                const resultsDiv = document.querySelector('.feeding-program-results');
                
                if (!resultsDiv) {
                    throw new Error('Cannot find meal plan content');
                }
                
                // Clone it completely
                const clonedContent = resultsDiv.cloneNode(true);
                
                // Remove action buttons
                const actionButtons = clonedContent.querySelector('.action-buttons');
                if (actionButtons) {
                    actionButtons.remove();
                }
                
                // Create wrapper for PDF
                const pdfWrapper = document.createElement('div');
                pdfWrapper.style.cssText = 'font-family: Arial, sans-serif; padding: 15px; background: white; color: black;';
                
                // Add title
                const titleDiv = document.createElement('div');
                titleDiv.style.cssText = 'text-align: center; margin-bottom: 15px; border-bottom: 2px solid #10b981; padding-bottom: 10px;';
                titleDiv.innerHTML = '<h1 style="color: #2d7a4f; font-size: 20px; margin: 0;">Feeding Program Meal Plan</h1>';
                pdfWrapper.appendChild(titleDiv);
                
                // Add the cloned content
                pdfWrapper.appendChild(clonedContent);
                
                // Configure PDF options
                const opt = {
                    margin: 10,
                    filename: filename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { 
                        scale: 2,
                        logging: true,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#ffffff'
                    },
                    jsPDF: { 
                        unit: 'mm', 
                        format: 'a4', 
                        orientation: 'portrait'
                    }
                };
                
                // Generate PDF
                html2pdf().set(opt).from(pdfWrapper).save()
                    .then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'PDF Downloaded!',
                            text: `File saved as ${filename}`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    })
                    .catch((error) => {
                        console.error('PDF generation error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'PDF Generation Failed',
                            text: error.message || 'There was an error creating the PDF. Check console for details.'
                        });
                    });
                    
            } catch (error) {
                console.error('Error in PDF generation:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to prepare PDF content'
                });
            }
        }, 100);
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
    // View saved plan - use event delegation for dynamically added plans
    $(document).on('click', '.view-program-plan-btn', function() {
        const planId = $(this).data('plan-id');
        viewFeedingProgramPlan(planId);
    });

    // Delete saved plan
    $(document).on('click', '.delete-program-plan-btn', function() {
        const planId = $(this).data('plan-id');
        const $planCard = $(this).closest('.plan-card');
        
        // Extract plan details
        const planTitle = $planCard.find('.plan-title').text().trim();
        const duration = $planCard.data('duration');
        const budget = $planCard.data('budget');
        const ageGroup = $planCard.data('age-group');
        const barangay = $planCard.data('barangay');
        
        deleteFeedingProgramPlan(planId, $planCard, planTitle, duration, budget, ageGroup, barangay);
    });

    // Program plans search functionality
    $('#program-search').on('input', function() {
        filterProgramPlans();
    });

    // Program plans filter functionality
    $('#program-budget-filter, #program-age-filter, #date-from-filter, #date-to-filter').on('change', function() {
        filterProgramPlans();
    });

    // Reset filters button
    $('#reset-program-filters').on('click', function() {
        // Clear all filters
        $('#program-search').val('');
        $('#program-budget-filter').val('');
        $('#program-age-filter').val('');
        $('#date-from-filter').val('');
        $('#date-to-filter').val('');
        $('#per-page-filter').val('12');
        
        // Show all plans
        $('.plan-card').show();
        $('#program-no-results').remove();
    });
});

/**
 * Filter program plans based on search and filters
 */
function filterProgramPlans() {
    const searchTerm = $('#program-search').val().toLowerCase();
    const budgetFilter = $('#program-budget-filter').val().toLowerCase();
    const ageFilter = $('#program-age-filter').val().toLowerCase();
    const dateFrom = $('#date-from-filter').val();
    const dateTo = $('#date-to-filter').val();

    // Convert date strings to timestamps (start of day / end of day)
    const dateFromTs = dateFrom ? new Date(dateFrom + 'T00:00:00').getTime() / 1000 : null;
    const dateToTs = dateTo ? new Date(dateTo + 'T23:59:59').getTime() / 1000 : null;

    $('.plan-card').each(function() {
        const $item = $(this);
        const budget = $item.data('budget').toString().toLowerCase();
        const ageGroup = $item.data('age-group').toString().toLowerCase();
        const barangay = $item.data('barangay').toString().toLowerCase();
        const timestamp = parseInt($item.data('timestamp')) || 0;
        
        // Check search term
        const matchesSearch = !searchTerm || 
            budget.includes(searchTerm) ||
            ageGroup.includes(searchTerm) ||
            barangay.includes(searchTerm);
        
        // Check budget filter
        const matchesBudget = !budgetFilter || budget === budgetFilter;
        
        // Check age filter
        const matchesAge = !ageFilter || ageGroup === ageFilter;

        // Check date range filter
        const matchesDateFrom = !dateFromTs || timestamp >= dateFromTs;
        const matchesDateTo = !dateToTs || timestamp <= dateToTs;
        
        // Show/hide based on all filters
        if (matchesSearch && matchesBudget && matchesAge && matchesDateFrom && matchesDateTo) {
            $item.show();
        } else {
            $item.hide();
        }
    });
    
    // Show "no results" message if needed
    const visibleCount = $('.plan-card:visible').length;
    if (visibleCount === 0) {
        if (!$('#program-no-results').length) {
            $('#program-plans-list').append(`
                <div id="program-no-results" class="empty-state" style="grid-column: 1 / -1; padding: 2rem; text-align: center;">
                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; color: #9E9E9E;"></i>
                    <h3>No Plans Found</h3>
                    <p>No feeding program plans match your search criteria.</p>
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
                    html: `
                        <div id="pdf-content" class="modal-scrollable-content">
                            ${metadata}
                            ${mealPlanHtml}
                        </div>
                        <div class="action-buttons" style="margin-top: 1rem; display: flex; gap: 1rem; justify-content: center; padding-top: 1rem; border-top: 2px solid #E0E0E0;">
                            <button type="button" class="btn-primary" id="download-pdf-btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                                <i class="fas fa-file-pdf"></i> Save as PDF
                            </button>
                        </div>
                    `,
                    width: '900px',
                    customClass: {
                        container: 'feeding-program-view-modal',
                        popup: 'feeding-program-view-popup',
                        htmlContainer: 'feeding-program-view-container'
                    },
                    showCloseButton: true,
                    showConfirmButton: false,
                    didOpen: () => {
                        // Add click handler for PDF download
                        document.getElementById('download-pdf-btn')?.addEventListener('click', function() {
                            
                            try {
                                const { jsPDF } = window.jspdf;
                                const doc = new jsPDF();
                                
                                // Add title
                                doc.setFontSize(18);
                                doc.setTextColor(45, 122, 79);
                                doc.text('Feeding Program Meal Plan', 105, 20, { align: 'center' });
                                
                                // Add metadata
                                doc.setFontSize(10);
                                doc.setTextColor(0, 0, 0);
                                let yPos = 35;
                                
                                const ageLabel = getAgeGroupLabel(plan.target_age_group);
                                doc.text(`Age Group: ${ageLabel}`, 20, yPos);
                                yPos += 7;
                                doc.text(`Duration: ${plan.program_duration_days} Days`, 20, yPos);
                                yPos += 7;
                                doc.text(`Budget: ${plan.budget_level}`, 20, yPos);
                                yPos += 7;
                                if (plan.barangay) {
                                    doc.text(`Barangay: ${plan.barangay}`, 20, yPos);
                                    yPos += 7;
                                }
                                if (plan.total_children) {
                                    doc.text(`Children: ${plan.total_children}`, 20, yPos);
                                    yPos += 7;
                                }
                                
                                yPos += 5;
                                
                                // Build table data directly from plan data to avoid rowspan issues
                                const tableData = [];
                                let mealPlanSource = plan.plan_details;

                                // Parse JSON if string
                                let parsedSource = mealPlanSource;
                                if (typeof mealPlanSource === 'string') {
                                    let jsonString = mealPlanSource.trim();
                                    const codeBlockMatch = jsonString.match(/```(?:json)?\s*\n?([\s\S]*?)\n?```/);
                                    if (codeBlockMatch) jsonString = codeBlockMatch[1].trim();
                                    const jsonMatch = jsonString.match(/\{[\s\S]*\}/);
                                    if (jsonMatch && !codeBlockMatch) jsonString = jsonMatch[0];
                                    jsonString = jsonString.replace(/^[^{]*/, '').replace(/[^}]*$/, '').trim();
                                    try { parsedSource = JSON.parse(jsonString); } catch(e) { parsedSource = mealPlanSource; }
                                }

                                if (typeof parsedSource === 'object' && parsedSource !== null) {
                                    const mealPlanArray = (parsedSource.meal_plan && Array.isArray(parsedSource.meal_plan))
                                        ? parsedSource.meal_plan
                                        : Array.isArray(parsedSource) ? parsedSource : null;

                                    if (mealPlanArray) {
                                        mealPlanArray.forEach(dayData => {
                                            if (dayData.meals && Array.isArray(dayData.meals)) {
                                                dayData.meals.forEach(meal => {
                                                    let ingredientsStr = '';
                                                    if (Array.isArray(meal.ingredients)) {
                                                        ingredientsStr = meal.ingredients.join(', ');
                                                    } else if (typeof meal.ingredients === 'object' && meal.ingredients !== null) {
                                                        ingredientsStr = Object.values(meal.ingredients).join(', ');
                                                    } else if (typeof meal.ingredients === 'string') {
                                                        ingredientsStr = meal.ingredients;
                                                    }
                                                    const mealTypeRaw = meal.meal_name_tagalog || meal.meal_name || meal.meal_type || meal.mealType || meal.type || meal.meal || '';
                                                    const mealType = (window.mealPlansManager && window.mealPlansManager.normalizeMealType)
                                                        ? window.mealPlansManager.normalizeMealType(mealTypeRaw)
                                                        : mealTypeRaw;
                                                    tableData.push([
                                                        `Day ${dayData.day}`,
                                                        mealType,
                                                        meal.dish_name || meal.dishName || meal.name || 'N/A',
                                                        ingredientsStr
                                                    ]);
                                                });
                                            }
                                        });
                                    }
                                }

                                // Fallback: read from DOM while handling rowspan
                                if (tableData.length === 0) {
                                    const visibleTable = document.querySelector('.meal-plan-table tbody');
                                    if (visibleTable) {
                                        let currentDay = '';
                                        visibleTable.querySelectorAll('tr').forEach(row => {
                                            const cells = row.querySelectorAll('td');
                                            if (cells.length === 4) {
                                                currentDay = cells[0].textContent.trim();
                                                tableData.push([
                                                    currentDay,
                                                    cells[1].textContent.trim(),
                                                    cells[2].textContent.trim(),
                                                    cells[3].textContent.trim()
                                                ]);
                                            } else if (cells.length === 3) {
                                                // Day cell is covered by rowspan — inject tracked day
                                                tableData.push([
                                                    currentDay,
                                                    cells[0].textContent.trim(),
                                                    cells[1].textContent.trim(),
                                                    cells[2].textContent.trim()
                                                ]);
                                            }
                                        });
                                    }
                                }
                                
                                // Add table
                                doc.autoTable({
                                    head: [['Day', 'Meal Type', 'Dish Name', 'Ingredients']],
                                    body: tableData,
                                    startY: yPos,
                                    theme: 'grid',
                                    headStyles: {
                                        fillColor: [45, 122, 79],
                                        textColor: 255,
                                        fontSize: 9,
                                        fontStyle: 'bold'
                                    },
                                    bodyStyles: {
                                        fontSize: 8,
                                        cellPadding: 3
                                    },
                                    columnStyles: {
                                        0: { cellWidth: 20 },
                                        1: { cellWidth: 25 },
                                        2: { cellWidth: 45 },
                                        3: { cellWidth: 'auto' }
                                    }
                                });
                                
                                // Save PDF
                                const today = new Date();
                                const dateStr = today.getFullYear() + '-' + 
                                              String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                                              String(today.getDate()).padStart(2, '0');
                                const filename = `Feeding-Program-Plan-${dateStr}.pdf`;
                                
                                doc.save(filename);
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'PDF Downloaded!',
                                    text: `File saved as ${filename}`,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                
                            } catch (error) {
                                console.error('PDF error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to generate PDF: ' + error.message
                                });
                            }
                        });
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
function deleteFeedingProgramPlan(planId, $planCard, planTitle, duration, budget, ageGroup, barangay) {
    // Format age group display
    let ageGroupDisplay = '';
    switch(ageGroup) {
        case 'all':
            ageGroupDisplay = 'All Ages (6mo-5y)';
            break;
        case '6-12months':
            ageGroupDisplay = 'Infants (6-12mo)';
            break;
        case '12-24months':
            ageGroupDisplay = 'Toddlers (12-24mo)';
            break;
        case '24-60months':
            ageGroupDisplay = 'Preschoolers (24-60mo)';
            break;
        default:
            ageGroupDisplay = ageGroup;
    }

    const barangayText = barangay ? `<div style="margin-bottom: 0.5rem;"><i class="fas fa-map-marker-alt" style="color: #10b981; margin-right: 0.5rem;"></i><strong>Location:</strong> ${barangay}</div>` : '';

    Swal.fire({
        title: '<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Delete Meal Plan?',
        html: `
            <div style="text-align: left; padding: 1rem 0.5rem;">
                <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 1.25rem; border-radius: 12px; border-left: 4px solid #ef4444; margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 1rem 0; color: #991b1b; font-size: 1rem; font-weight: 700;">
                        <i class="fas fa-info-circle"></i> Plan Details
                    </h4>
                    <div style="color: #7f1d1d; font-size: 0.9375rem; line-height: 1.8;">
                        <div style="margin-bottom: 0.5rem;"><i class="fas fa-utensils" style="color: #10b981; margin-right: 0.5rem;"></i><strong>Plan:</strong> ${planTitle}</div>
                        <div style="margin-bottom: 0.5rem;"><i class="fas fa-calendar-alt" style="color: #10b981; margin-right: 0.5rem;"></i><strong>Duration:</strong> ${duration} ${duration > 1 ? 'Days' : 'Day'}</div>
                        <div style="margin-bottom: 0.5rem;"><i class="fas fa-wallet" style="color: #10b981; margin-right: 0.5rem;"></i><strong>Budget:</strong> ${budget ? (budget.charAt(0).toUpperCase() + budget.slice(1)) : 'N/A'}</div>
                        <div style="margin-bottom: 0.5rem;"><i class="fas fa-child" style="color: #10b981; margin-right: 0.5rem;"></i><strong>Age Group:</strong> ${ageGroupDisplay}</div>
                        ${barangayText}
                    </div>
                </div>
                
                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 1rem; border-radius: 12px; border-left: 4px solid #f59e0b;">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <i class="fas fa-exclamation-triangle" style="color: #d97706; font-size: 1.25rem; margin-top: 0.125rem;"></i>
                        <div>
                            <strong style="color: #92400e; display: block; margin-bottom: 0.375rem; font-size: 0.9375rem;">Warning:</strong>
                            <p style="margin: 0; color: #78350f; font-size: 0.875rem; line-height: 1.6;">
                                This action cannot be undone. All meal plan data, nutritional information, and shopping lists associated with this program will be permanently deleted.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `,
        width: '650px',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete Plan',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        customClass: {
            popup: 'delete-confirmation-modal',
            confirmButton: 'swal2-confirm-delete',
            cancelButton: 'swal2-cancel-delete'
        },
        buttonsStyling: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: '<i class="fas fa-spinner fa-spin"></i> Deleting...',
                text: 'Please wait while we delete the meal plan',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/nutritionist/feeding-program/${planId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '<i class="fas fa-check-circle" style="color: #10b981;"></i> Deleted Successfully!',
                            html: `
                                <div style="text-align: center; padding: 1rem;">
                                    <p style="color: #374151; font-size: 1rem; margin: 0;">
                                        The meal plan has been permanently deleted.
                                    </p>
                                </div>
                            `,
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            confirmButtonText: '<i class="fas fa-check"></i> OK',
                            timer: 2500,
                            timerProgressBar: true
                        }).then(() => {
                            // Remove the card from DOM with animation
                            $planCard.fadeOut(400, function() {
                                $(this).remove();
                                
                                // Update plan count using the global instance
                                if (window.mealPlansManager && window.mealPlansManager.updatePlanCount) {
                                    window.mealPlansManager.updatePlanCount();
                                }
                                
                                // Check if there are no more plans, show empty state
                                if ($('#program-plans-list .plan-card').length === 0) {
                                    $('#program-plans-list').hide();
                                    $('.empty-state').show();
                                }
                            });
                        });
                    } else {
                        Swal.fire({
                            title: '<i class="fas fa-times-circle" style="color: #ef4444;"></i> Deletion Failed',
                            text: response.message || 'Failed to delete meal plan. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: '<i class="fas fa-times"></i> Close'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error deleting plan:', xhr);
                    let errorMessage = 'An unexpected error occurred while deleting the meal plan.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'Meal plan not found. It may have already been deleted.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to delete this meal plan.';
                    }
                    
                    Swal.fire({
                        title: '<i class="fas fa-times-circle" style="color: #ef4444;"></i> Error',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: '<i class="fas fa-times"></i> Close'
                    });
                }
            });
        }
    });
}
