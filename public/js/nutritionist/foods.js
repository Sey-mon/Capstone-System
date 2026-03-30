// Nutritionist Foods JavaScript

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        if (successAlert) successAlert.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';
    }, 5000);
});

// Debounced search functionality
let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const search = e.target.value;
    const tag = document.getElementById('tagFilter')?.value || '';
    
    searchTimeout = setTimeout(() => {
        updateUrl(search, tag);
    }, 500); // Wait 500ms after user stops typing
});

document.getElementById('tagFilter')?.addEventListener('change', function(e) {
    const tag = e.target.value;
    const search = document.getElementById('searchInput')?.value || '';
    updateUrl(search, tag);
});

function updateUrl(search, tag) {
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (tag) params.set('tag', tag);
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

// View food details function - using SweetAlert2
function viewFoodDetails(foodId) {
    // Show loading modal first
    Swal.fire({
        title: '<i class="fas fa-spinner fa-spin"></i> Loading...',
        html: 'Fetching food details...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/nutritionist/foods/${foodId}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch food details');
            }
            return response.json();
        })
        .then(data => {
            Swal.fire({
                title: '<span style="color: #2e7d32;"><i class="fas fa-utensils"></i> Food Details</span>',
                html: `
                    <div style="text-align: left; padding: 10px;">
                        <!-- Basic Information -->
                        <div style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f4 100%); padding: 15px; border-radius: 12px; margin-bottom: 15px; border-left: 4px solid #4caf50;">
                            <h4 style="color: #2e7d32; margin: 0 0 12px 0; font-size: 16px;">
                                <i class="fas fa-info-circle"></i> Basic Information
                            </h4>
                            <div style="display: grid; gap: 10px;">
                                <div>
                                    <label style="font-weight: 600; color: #555; font-size: 13px; display: block; margin-bottom: 4px;">
                                        <i class="fas fa-hashtag"></i> Food ID
                                    </label>
                                    <div style="background: white; padding: 8px 12px; border-radius: 6px; font-weight: 600; color: #2e7d32;">
                                        #${data.food_id}
                                    </div>
                                </div>
                                <div>
                                    <label style="font-weight: 600; color: #555; font-size: 13px; display: block; margin-bottom: 4px;">
                                        <i class="fas fa-apple-alt"></i> Food Name & Description
                                    </label>
                                    <div style="background: white; padding: 10px 12px; border-radius: 6px; line-height: 1.5;">
                                        ${data.food_name_and_description || '-'}
                                    </div>
                                </div>
                                <div>
                                    <label style="font-weight: 600; color: #555; font-size: 13px; display: block; margin-bottom: 4px;">
                                        <i class="fas fa-tag"></i> Alternate Names
                                    </label>
                                    <div style="background: white; padding: 8px 12px; border-radius: 6px; color: #666;">
                                        ${data.alternate_common_names || 'None'}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nutritional Information -->
                        <div style="background: linear-gradient(135deg, #fff3e0 0%, #ffe8cc 100%); padding: 15px; border-radius: 12px; margin-bottom: 15px; border-left: 4px solid #ff9800;">
                            <h4 style="color: #e65100; margin: 0 0 12px 0; font-size: 16px;">
                                <i class="fas fa-fire"></i> Nutritional Information
                            </h4>
                            <div style="display: grid; gap: 10px;">
                                <div>
                                    <label style="font-weight: 600; color: #555; font-size: 13px; display: block; margin-bottom: 4px;">
                                        <i class="fas fa-bolt"></i> Energy (per 100g)
                                    </label>
                                    <div style="background: white; padding: 10px 12px; border-radius: 6px; font-weight: 700; font-size: 18px; color: #ff9800;">
                                        ${data.energy_kcal ? parseFloat(data.energy_kcal).toFixed(1) + ' kcal' : 'N/A'}
                                    </div>
                                </div>
                                <div>
                                    <label style="font-weight: 600; color: #555; font-size: 13px; display: block; margin-bottom: 4px;">
                                        <i class="fas fa-tags"></i> Nutrition Tags
                                    </label>
                                    <div style="background: white; padding: 10px 12px; border-radius: 6px;">
                                        ${formatTagsForSwal(data.nutrition_tags)}
                                    </div>
                                </div>
                            </div>
                        </div>

                        ${data.created_at || data.updated_at ? `
                        <!-- Record Information -->
                        <div style="background: #f9fafb; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px; color: #666;">
                                ${data.created_at ? `
                                <div>
                                    <i class="fas fa-calendar-plus"></i> Created: 
                                    <strong>${new Date(data.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</strong>
                                </div>
                                ` : ''}
                                ${data.updated_at ? `
                                <div>
                                    <i class="fas fa-calendar-check"></i> Updated: 
                                    <strong>${new Date(data.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</strong>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `,
                width: '700px',
                confirmButtonText: '<i class="fas fa-check"></i> Close',
                confirmButtonColor: '#4caf50',
                customClass: {
                    popup: 'food-details-modal'
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `
                    <p>Unable to load food details.</p>
                    <p style="font-size: 13px; color: #666;">Please try again or contact support if the problem persists.</p>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        });
}

// Helper function to format tags for SweetAlert
function formatTagsForSwal(tags) {
    if (!tags) return '<span style="color: #999; font-style: italic;">No tags</span>';
    const tagArray = tags.split(',').map(tag => tag.trim()).filter(tag => tag);
    if (tagArray.length === 0) return '<span style="color: #999; font-style: italic;">No tags</span>';
    return tagArray.map(tag => 
        `<span style="display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 12px; margin: 2px; font-size: 12px; font-weight: 600;">${tag}</span>`
    ).join('');
}

// Request Food using SweetAlert2 with enhanced validation and UI
function openRequestFoodModal() {
    Swal.fire({
        title: '<div style="display: flex; align-items: center; gap: 10px; justify-content: center;"><i class="fas fa-plus-circle" style="color: #4caf50;"></i><span style="color: #2e7d32; font-weight: 700;">Request New Food</span></div>',
        html: `
            <form id="requestFoodForm" style="text-align: left;">
                <!-- Food Name & Description -->
                <div style="margin-bottom: 18px;">
                    <label style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 600; color: #2e7d32; font-size: 14px;">
                        <span>
                            <i class="fas fa-utensils" style="margin-right: 6px; color: #4caf50;"></i>
                            Food Name & Description <span style="color: #dc3545;">*</span>
                        </span>
                        <span id="food_name_counter" style="color: #999; font-size: 12px; font-weight: 400;">0/500</span>
                    </label>
                    <textarea 
                        id="food_name_and_description" 
                        rows="3" 
                        maxlength="500"
                        placeholder="Enter a detailed name and description (e.g., 'Brown Rice - Whole grain rice rich in fiber')"
                        style="width: 100%; padding: 12px; border: 2px solid #e8f5e9; border-radius: 10px; font-size: 14px; font-family: inherit; transition: all 0.3s; resize: vertical;"
                        required
                    ></textarea>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
                        <small id="food_name_validation" style="color: #dc3545; font-size: 12px; display: none;">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Similar food exists!</strong> Check the database first.
                        </small>
                        <small id="food_name_success" style="color: #4caf50; font-size: 12px; display: none;">
                            <i class="fas fa-check-circle"></i> Looks good!
                        </small>
                    </div>
                </div>

                <!-- Alternate Names -->
                <div style="margin-bottom: 18px;">
                    <label style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 600; color: #2e7d32; font-size: 14px;">
                        <span>
                            <i class="fas fa-tag" style="margin-right: 6px; color: #4caf50;"></i>
                            Alternate Names
                        </span>
                        <span id="alternate_counter" style="color: #999; font-size: 12px; font-weight: 400;">0/300</span>
                    </label>
                    <input 
                        type="text" 
                        id="alternate_common_names" 
                        maxlength="300"
                        placeholder="e.g., Kanin, Steamed Rice (separate with commas)"
                        style="width: 100%; padding: 12px; border: 2px solid #e8f5e9; border-radius: 10px; font-size: 14px; transition: all 0.3s;"
                    />
                    <small style="color: #666; font-size: 11px; margin-top: 4px; display: block;">
                        <i class="fas fa-lightbulb" style="color: #ffc107;"></i> Helps users find this food using different names
                    </small>
                </div>

                <!-- Energy Value -->
                <div style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2e7d32; font-size: 14px;">
                        <i class="fas fa-fire" style="margin-right: 6px; color: #ff5722;"></i>
                        Energy (kcal per 100g) <span style="color: #dc3545;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input 
                            type="number" 
                            id="energy_kcal" 
                            step="0.01" 
                            min="0"
                            max="9999.99"
                            placeholder="Enter caloric value (e.g., 130)"
                            style="width: 100%; padding: 12px 45px 12px 12px; border: 2px solid #e8f5e9; border-radius: 10px; font-size: 14px; transition: all 0.3s;"
                            required
                        />
                        <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #999; font-size: 13px; font-weight: 600;">kcal</span>
                    </div>
                    <small id="energy_validation" style="color: #666; font-size: 11px; margin-top: 4px; display: block;">
                        <i class="fas fa-info-circle" style="color: #2196f3;"></i> Typical range: 20-900 kcal per 100g
                    </small>
                </div>

                <!-- Nutrition Tags -->
                <div style="margin-bottom: 18px;">
                    <label style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 600; color: #2e7d32; font-size: 14px;">
                        <span>
                            <i class="fas fa-tags" style="margin-right: 6px; color: #4caf50;"></i>
                            Nutrition Tags
                        </span>
                        <span id="tags_counter" style="color: #999; font-size: 12px; font-weight: 400;">0 tags</span>
                    </label>
                    <div style="position: relative;">
                        <input 
                            type="text" 
                            id="nutrition_tags_input" 
                            autocomplete="off"
                            placeholder="Type or select tags from dropdown..."
                            style="width: 100%; padding: 12px 40px 12px 12px; border: 2px solid #e8f5e9; border-radius: 10px; font-size: 14px; transition: all 0.3s;"
                        />
                        <button 
                            type="button"
                            id="tags_dropdown_btn"
                            style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #4caf50; color: white; border: none; border-radius: 6px; padding: 6px 10px; cursor: pointer; transition: all 0.2s;"
                            title="Show common tags"
                        >
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div id="tags_dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #4caf50; border-radius: 10px; margin-top: 4px; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000;"></div>
                    </div>
                    <input type="hidden" id="nutrition_tags" />
                    <div id="selected_tags" style="margin-top: 10px; min-height: 30px; display: flex; flex-wrap: wrap; gap: 6px;"></div>
                    <small style="color: #666; font-size: 11px; margin-top: 4px; display: block;">
                        <i class="fas fa-lightbulb" style="color: #ffc107;"></i> Type custom tags or select from dropdown. Press Enter or comma to add.
                    </small>
                </div>
                
                <!-- Info Banner -->
                <div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 14px; border-radius: 10px; margin-top: 15px; border-left: 4px solid #4caf50;">
                    <small style="color: #1b5e20; display: flex; align-items: flex-start; gap: 10px; line-height: 1.6; font-size: 12px;">
                        <i class="fas fa-check-double" style="margin-top: 2px; font-size: 16px; color: #4caf50;"></i>
                        <span>Your request will be <strong>reviewed by an admin</strong> within 24-48 hours. You'll be notified once it's processed.</span>
                    </small>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane"></i> Submit Request',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        confirmButtonColor: '#4caf50',
        cancelButtonColor: '#95a5a6',
        width: '700px',
        focusConfirm: false,
        didOpen: () => {
            // Get all form elements
            const foodNameInput = document.getElementById('food_name_and_description');
            const alternateInput = document.getElementById('alternate_common_names');
            const energyInput = document.getElementById('energy_kcal');
            const tagsInputField = document.getElementById('nutrition_tags_input');
            const tagsHiddenField = document.getElementById('nutrition_tags');
            const tagsDropdownBtn = document.getElementById('tags_dropdown_btn');
            const tagsDropdown = document.getElementById('tags_dropdown');
            const selectedTagsContainer = document.getElementById('selected_tags');
            
            const foodNameCounter = document.getElementById('food_name_counter');
            const alternateCounter = document.getElementById('alternate_counter');
            const tagsCounter = document.getElementById('tags_counter');
            
            const validationMsg = document.getElementById('food_name_validation');
            const successMsg = document.getElementById('food_name_success');
            const energyValidation = document.getElementById('energy_validation');
            
            let checkTimeout;
            let selectedTags = [];
            
            // Common nutrition tags
            const commonTags = [
                'high-protein', 'low-fat', 'low-carb', 'high-fiber',
                'gluten-free', 'dairy-free', 'vegan', 'vegetarian',
                'keto-friendly', 'paleo', 'whole-grain', 'organic',
                'low-sodium', 'low-sugar', 'sugar-free', 'fat-free',
                'high-calcium', 'iron-rich', 'vitamin-rich', 'antioxidant',
                'omega-3', 'probiotic', 'low-calorie', 'heart-healthy',
                'diabetic-friendly', 'weight-loss', 'energy-boosting', 'anti-inflammatory'
            ];

            // Character counter for food name
            foodNameInput.addEventListener('input', function() {
                const count = this.value.length;
                foodNameCounter.textContent = `${count}/500`;
                foodNameCounter.style.color = count > 450 ? '#ff5722' : count > 400 ? '#ffc107' : '#999';
                
                // Real-time duplicate checking
                clearTimeout(checkTimeout);
                const value = this.value.trim();
                
                if (value.length < 3) {
                    validationMsg.style.display = 'none';
                    successMsg.style.display = 'none';
                    this.style.borderColor = '#e8f5e9';
                    return;
                }

                checkTimeout = setTimeout(() => {
                    checkDuplicateFood(value, validationMsg, successMsg, this);
                }, 600);
            });

            // Character counter for alternate names
            alternateInput.addEventListener('input', function() {
                const count = this.value.length;
                alternateCounter.textContent = `${count}/300`;
                alternateCounter.style.color = count > 270 ? '#ff5722' : count > 240 ? '#ffc107' : '#999';
            });

            // Energy validation with smart feedback
            energyInput.addEventListener('input', function() {
                const value = parseFloat(this.value);
                
                if (!value || isNaN(value)) {
                    energyValidation.innerHTML = '<i class="fas fa-info-circle" style="color: #2196f3;"></i> Typical range: 20-900 kcal per 100g';
                    energyValidation.style.color = '#666';
                    this.style.borderColor = '#e8f5e9';
                } else if (value < 0) {
                    energyValidation.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545;"></i> Energy cannot be negative';
                    energyValidation.style.color = '#dc3545';
                    this.style.borderColor = '#dc3545';
                } else if (value === 0) {
                    energyValidation.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> Are you sure energy is 0 kcal?';
                    energyValidation.style.color = '#ffc107';
                    this.style.borderColor = '#ffc107';
                } else if (value > 900) {
                    energyValidation.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #ff5722;"></i> This value seems very high. Please verify.';
                    energyValidation.style.color = '#ff5722';
                    this.style.borderColor = '#ff9800';
                } else if (value < 20) {
                    energyValidation.innerHTML = '<i class="fas fa-info-circle" style="color: #2196f3;"></i> Low calorie food - looks good!';
                    energyValidation.style.color = '#2196f3';
                    this.style.borderColor = '#4caf50';
                } else {
                    energyValidation.innerHTML = '<i class="fas fa-check-circle" style="color: #4caf50;"></i> Value looks good!';
                    energyValidation.style.color = '#4caf50';
                    this.style.borderColor = '#4caf50';
                }
            });

            // Function to update selected tags display
            function updateTagsDisplay() {
                tagsCounter.textContent = `${selectedTags.length} tag${selectedTags.length !== 1 ? 's' : ''}`;
                tagsCounter.style.color = selectedTags.length > 20 ? '#ff5722' : selectedTags.length > 15 ? '#ffc107' : '#999';
                
                if (selectedTags.length > 0) {
                    selectedTagsContainer.innerHTML = selectedTags.map(tag => 
                        `<span style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #4caf50 0%, #45a049 100%); color: white; padding: 6px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="fas fa-tag" style="font-size: 10px;"></i>
                            <span>${tag}</span>
                            <button type="button" onclick="removeTag('${tag}')" style="background: rgba(255,255,255,0.3); border: none; color: white; border-radius: 50%; width: 18px; height: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; padding: 0; transition: all 0.2s;" title="Remove tag">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>`
                    ).join('');
                } else {
                    selectedTagsContainer.innerHTML = '<small style="color: #999; font-style: italic;"><i class="fas fa-info-circle"></i> No tags selected yet</small>';
                }
                
                // Update hidden field
                tagsHiddenField.value = selectedTags.join(', ');
            }
            
            // Function to add tag
            window.addTag = function(tag) {
                tag = tag.trim().toLowerCase();
                if (tag && !selectedTags.includes(tag) && selectedTags.length < 20) {
                    selectedTags.push(tag);
                    updateTagsDisplay();
                    tagsInputField.value = '';
                    filterDropdownTags('');
                }
            };
            
            // Function to remove tag
            window.removeTag = function(tag) {
                selectedTags = selectedTags.filter(t => t !== tag);
                updateTagsDisplay();
            };
            
            // Function to filter and show dropdown
            function filterDropdownTags(searchTerm) {
                const filtered = commonTags.filter(tag => 
                    !selectedTags.includes(tag) && 
                    tag.toLowerCase().includes(searchTerm.toLowerCase())
                );
                
                if (filtered.length > 0) {
                    tagsDropdown.innerHTML = filtered.map(tag => 
                        `<div style="padding: 10px 12px; cursor: pointer; transition: all 0.2s; border-bottom: 1px solid #f0f0f0;" 
                             onmouseover="this.style.background='#e8f5e9'" 
                             onmouseout="this.style.background='white'" 
                             onclick="addTag('${tag}')">
                            <i class="fas fa-tag" style="color: #4caf50; margin-right: 8px; font-size: 11px;"></i>
                            <span style="color: #333; font-size: 13px;">${tag}</span>
                        </div>`
                    ).join('');
                    tagsDropdown.style.display = 'block';
                } else {
                    tagsDropdown.innerHTML = '<div style="padding: 12px; color: #999; text-align: center; font-size: 12px;"><i class="fas fa-search"></i> No matching tags found</div>';
                    tagsDropdown.style.display = searchTerm ? 'block' : 'none';
                }
            }
            
            // Tags input event listeners
            tagsInputField.addEventListener('input', function() {
                filterDropdownTags(this.value);
            });
            
            tagsInputField.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const value = this.value.trim().replace(/,$/g, '');
                    if (value) {
                        addTag(value);
                    }
                }
            });
            
            tagsInputField.addEventListener('focus', function() {
                this.style.borderColor = '#4caf50';
                this.style.boxShadow = '0 0 0 4px rgba(76, 175, 80, 0.15)';
                filterDropdownTags(this.value);
            });
            
            tagsInputField.addEventListener('blur', function() {
                setTimeout(() => {
                    tagsDropdown.style.display = 'none';
                    this.style.borderColor = '#e8f5e9';
                    this.style.boxShadow = 'none';
                }, 200);
            });
            
            // Dropdown button
            tagsDropdownBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                tagsInputField.focus();
                filterDropdownTags('');
            });
            
            tagsDropdownBtn.addEventListener('mouseenter', function() {
                this.style.background = '#45a049';
            });
            
            tagsDropdownBtn.addEventListener('mouseleave', function() {
                this.style.background = '#4caf50';
            });
            
            // Initialize tags display
            updateTagsDisplay();

            // Enhanced focus effects with animations
            const inputs = document.querySelectorAll('#requestFoodForm input, #requestFoodForm textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#4caf50';
                    this.style.boxShadow = '0 0 0 4px rgba(76, 175, 80, 0.15)';
                    this.style.transform = 'translateY(-1px)';
                });
                input.addEventListener('blur', function() {
                    if (!this.value || this.id === 'energy_kcal') {
                        this.style.borderColor = '#e8f5e9';
                    }
                    this.style.boxShadow = 'none';
                    this.style.transform = 'translateY(0)';
                });
            });

            // Auto-focus on food name field
            foodNameInput.focus();
        },
        preConfirm: () => {
            const foodName = document.getElementById('food_name_and_description').value.trim();
            const alternateName = document.getElementById('alternate_common_names').value.trim();
            const energy = document.getElementById('energy_kcal').value;
            const tags = document.getElementById('nutrition_tags').value.trim(); // Hidden field with selected tags

            // Enhanced validation
            if (!foodName) {
                Swal.showValidationMessage('<i class=\"fas fa-exclamation-circle\"></i> Please enter food name and description');
                return false;
            }

            if (foodName.length < 3) {
                Swal.showValidationMessage('<i class=\"fas fa-exclamation-circle\"></i> Food name must be at least 3 characters');
                return false;
            }

            if (!energy || energy <= 0) {
                Swal.showValidationMessage('<i class=\"fas fa-exclamation-circle\"></i> Please enter a valid energy value (kcal)');
                return false;
            }

            if (energy > 10000) {
                Swal.showValidationMessage('<i class=\"fas fa-exclamation-circle\"></i> Energy value seems too high. Please verify.');
                return false;
            }

            return {
                food_name_and_description: foodName,
                alternate_common_names: alternateName,
                energy_kcal: energy,
                nutrition_tags: tags
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitFoodRequest(result.value);
        }
    });
}

// Check for duplicate food in database with enhanced feedback
function checkDuplicateFood(foodName, validationMsg, successMsg, inputElement) {
    fetch(`/api/foods/check-duplicate?name=${encodeURIComponent(foodName)}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                validationMsg.style.display = 'block';
                successMsg.style.display = 'none';
                inputElement.style.borderColor = '#ff9800';
                inputElement.style.boxShadow = '0 0 0 4px rgba(255, 152, 0, 0.15)';
            } else {
                validationMsg.style.display = 'none';
                successMsg.style.display = 'block';
                inputElement.style.borderColor = '#4caf50';
                inputElement.style.boxShadow = '0 0 0 4px rgba(76, 175, 80, 0.15)';
            }
        })
        .catch(error => {
            console.error('Error checking duplicate:', error);
            validationMsg.style.display = 'none';
            successMsg.style.display = 'none';
            inputElement.style.borderColor = '#e8f5e9';
            inputElement.style.boxShadow = 'none';
        });
}

// Submit food request with enhanced feedback
function submitFoodRequest(data) {
    Swal.fire({
        title: '<i class="fas fa-spinner fa-spin" style="color: #4caf50;"></i> Submitting Request',
        html: '<p style="margin: 15px 0; color: #666;">Please wait while we process your submission...</p>',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData();
    formData.append('food_name_and_description', data.food_name_and_description);
    formData.append('alternate_common_names', data.alternate_common_names);
    formData.append('energy_kcal', data.energy_kcal);
    formData.append('nutrition_tags', data.nutrition_tags);

    fetch('/nutritionist/food-requests', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to submit request');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '<span style="color: #2e7d32; font-weight: 700;">Request Submitted Successfully!</span>',
                html: `
                    <div style="text-align: left; padding: 0 20px;">
                        <p style="color: #555; margin-bottom: 15px; line-height: 1.6;">
                            <i class="fas fa-check-circle" style="color: #4caf50; margin-right: 8px;"></i>
                            ${data.message || 'Your food request has been submitted successfully.'}
                        </p>
                        <div style="background: #e8f5e9; padding: 12px; border-radius: 8px; border-left: 4px solid #4caf50; margin-top: 10px;">
                            <p style="margin: 0; color: #2e7d32; font-size: 13px; line-height: 1.5;">
                                <i class="fas fa-clock" style="margin-right: 6px;"></i>
                                <strong>What's next?</strong> An admin will review your request within 24-48 hours. 
                                You can track its status in the "My Requests" section.
                            </p>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#4caf50',
                confirmButtonText: '<i class="fas fa-check"></i> Got it!',
                width: '550px'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: '<span style="color: #d32f2f; font-weight: 700;">Submission Failed</span>',
                html: `
                    <p style="color: #555; margin: 15px 0;">
                        <i class="fas fa-exclamation-circle" style="color: #dc3545; margin-right: 8px;"></i>
                        ${data.message || 'Failed to submit request. Please try again.'}
                    </p>
                `,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Try Again'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: '<span style="color: #d32f2f; font-weight: 700;">Network Error</span>',
            html: `
                <p style="color: #555; margin: 15px 0;">
                    <i class="fas fa-wifi" style="color: #dc3545; margin-right: 8px;"></i>
                    Unable to connect to the server. Please check your connection and try again.
                </p>
            `,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Close'
        });
    });
}

// View request details using SweetAlert2
function viewRequestDetails(requestId) {
    // Show loading state
    Swal.fire({
        title: '<i class="fas fa-spinner fa-spin" style="color: #4caf50;"></i> Loading...',
        html: '<p style="margin: 10px 0; color: #666;">Fetching request details...</p>',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/nutritionist/food-requests/${requestId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch request details');
            }
            return response.json();
        })
        .then(data => {
            const statusConfig = {
                pending: { color: '#ffc107', icon: 'fa-clock', label: 'Pending Review', bgGradient: 'linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%)' },
                approved: { color: '#4caf50', icon: 'fa-check-circle', label: 'Approved', bgGradient: 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' },
                rejected: { color: '#dc3545', icon: 'fa-times-circle', label: 'Rejected', bgGradient: 'linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%)' }
            };
            
            const status = statusConfig[data.status] || statusConfig.pending;
            const formattedDate = new Date(data.created_at).toLocaleDateString('en-US', { 
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' 
            });
            
            // Format reviewer info if exists
            let reviewerInfo = '';
            if (data.status !== 'pending' && data.reviewer) {
                const reviewDate = data.reviewed_at ? new Date(data.reviewed_at).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' 
                }) : 'N/A';
                reviewerInfo = `
                    <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin-top: 12px; border-left: 3px solid ${status.color};">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: #666; font-size: 13px;">
                                <i class="fas fa-user-check" style="color: ${status.color}; margin-right: 6px;"></i>
                                Reviewed by: <strong>${data.reviewer.name || 'Admin'}</strong>
                            </span>
                        </div>
                        <span style="color: #666; font-size: 12px;">
                            <i class="fas fa-clock" style="margin-right: 6px;"></i>
                            ${reviewDate}
                        </span>
                        ${data.review_notes ? `<p style="margin: 8px 0 0 0; padding: 8px; background: white; border-radius: 4px; color: #444; font-size: 13px; font-style: italic;">"${data.review_notes}"</p>` : ''}
                    </div>
                `;
            }
            
            Swal.fire({
                title: `<div style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                            <i class="fas ${status.icon}" style="color: ${status.color};"></i>
                            <span style="color: ${status.color}; font-weight: 700;">${status.label}</span>
                        </div>`,
                html: `
                    <div style="text-align: left;">
                        <!-- Status Banner -->
                        <div style="background: ${status.bgGradient}; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid ${status.color};">
                            <small style="color: #666; font-size: 12px;">Request ID: <strong>#${data.id}</strong></small>
                        </div>

                        <!-- Food Information -->
                        <div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                            <h4 style="margin: 0 0 12px 0; color: #2e7d32; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-utensils"></i> Food Details
                            </h4>
                            
                            <div style="margin-bottom: 10px;">
                                <label style="display: block; color: #555; font-weight: 600; font-size: 12px; margin-bottom: 4px;">
                                    <i class="fas fa-file-alt" style="color: #4caf50; width: 16px;"></i> Description
                                </label>
                                <div style="background: white; padding: 10px; border-radius: 6px; color: #333; font-size: 13px; line-height: 1.5;">
                                    ${data.food_name_and_description}
                                </div>
                            </div>
                            
                            <div style="margin-bottom: 10px;">
                                <label style="display: block; color: #555; font-weight: 600; font-size: 12px; margin-bottom: 4px;">
                                    <i class="fas fa-tag" style="color: #4caf50; width: 16px;"></i> Alternate Names
                                </label>
                                <div style="background: white; padding: 10px; border-radius: 6px; color: #333; font-size: 13px;">
                                    ${data.alternate_common_names || '<span style="color: #999; font-style: italic;">None provided</span>'}
                                </div>
                            </div>
                        </div>

                        <!-- Nutritional Information -->
                        <div style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                            <h4 style="margin: 0 0 12px 0; color: #e65100; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-fire"></i> Nutritional Data
                            </h4>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label style="display: block; color: #555; font-weight: 600; font-size: 12px; margin-bottom: 4px;">
                                        Energy (kcal)
                                    </label>
                                    <div style="background: white; padding: 10px; border-radius: 6px; color: #ff5722; font-size: 16px; font-weight: 700;">
                                        ${data.energy_kcal ? parseFloat(data.energy_kcal).toFixed(1) : '-'} kcal
                                    </div>
                                </div>
                                
                                <div>
                                    <label style="display: block; color: #555; font-weight: 600; font-size: 12px; margin-bottom: 4px;">
                                        <i class="fas fa-tags"></i> Tags
                                    </label>
                                    <div style="background: white; padding: 10px; border-radius: 6px; font-size: 13px;">
                                        ${formatTagsForRequestSwal(data.nutrition_tags)}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; border-left: 3px solid #2196f3;">
                            <div style="color: #666; font-size: 13px; margin-bottom: 4px;">
                                <i class="fas fa-calendar-plus" style="color: #2196f3; margin-right: 6px;"></i>
                                <strong>Submitted:</strong> ${formattedDate}
                            </div>
                            <div style="color: #666; font-size: 12px;">
                                <i class="fas fa-user" style="color: #2196f3; margin-right: 6px;"></i>
                                By: ${data.requester ? data.requester.name : 'You'}
                            </div>
                        </div>

                        ${reviewerInfo}
                    </div>
                `,
                confirmButtonText: '<i class="fas fa-check"></i> Close',
                confirmButtonColor: status.color,
                width: '650px',
                customClass: {
                    popup: 'request-details-modal'
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: '<span style="color: #d32f2f; font-weight: 700;">Failed to Load</span>',
                html: `
                    <p style="color: #555; margin: 15px 0;">
                        <i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-right: 8px;"></i>
                        Unable to fetch request details. Please try again.
                    </p>
                `,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Close'
            });
        });
}

// Helper function to format tags for request modal
function formatTagsForRequestSwal(tags) {
    if (!tags) return '<span style="color: #999; font-style: italic;">No tags</span>';
    const tagArray = tags.split(',').map(tag => tag.trim()).filter(tag => tag);
    if (tagArray.length === 0) return '<span style="color: #999; font-style: italic;">No tags</span>';
    return tagArray.map(tag => 
        `<span style="display: inline-block; background: #4caf50; color: white; padding: 3px 8px; border-radius: 10px; margin: 2px; font-size: 11px; font-weight: 600;">${tag}</span>`
    ).join('');
}

// Cancel request
function cancelRequest(requestId) {
    Swal.fire({
        title: '<span style="color: #ff9800; font-weight: 700;">Cancel Request?</span>',
        html: '<p style="color: #555; margin: 10px 0;">Are you sure you want to cancel this food request? This action cannot be undone.</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, cancel it',
        cancelButtonText: '<i class="fas fa-times"></i> No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: '<i class="fas fa-spinner fa-spin" style="color: #dc3545;"></i> Cancelling...',
                html: '<p style="margin: 10px 0; color: #666;">Please wait...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/nutritionist/food-requests/${requestId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to cancel request');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="color: #2e7d32; font-weight: 700;">Request Cancelled!</span>',
                        html: '<p style="color: #555; margin: 10px 0;">Your food request has been successfully cancelled.</p>',
                        confirmButtonColor: '#4caf50',
                        confirmButtonText: '<i class="fas fa-check"></i> OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span style="color: #d32f2f; font-weight: 700;">Failed to Cancel</span>',
                        html: `<p style="color: #555; margin: 10px 0;">${data.message || 'Failed to cancel request. Please try again.'}</p>`,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Close'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: '<span style="color: #d32f2f; font-weight: 700;">Error</span>',
                    html: '<p style="color: #555; margin: 10px 0;">An error occurred while cancelling the request.</p>',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Close'
                });
            });
        }
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const viewFoodModal = document.getElementById('viewFoodModal');
    const viewRequestModal = document.getElementById('viewRequestModal');
    
    if (event.target == viewFoodModal) {
        closeViewFoodModal();
    }
    if (event.target == viewRequestModal) {
        closeViewRequestModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const viewFoodModal = document.getElementById('viewFoodModal');
        const viewRequestModal = document.getElementById('viewRequestModal');
        
        if (viewFoodModal && viewFoodModal.style.display === 'flex') {
            closeViewFoodModal();
        }
        if (viewRequestModal && viewRequestModal.style.display === 'flex') {
            closeViewRequestModal();
        }
    }
});
