// Enhanced JavaScript for Meal Plans with Cooldown Management
document.addEventListener('DOMContentLoaded', function() {
    // Initialize custom dropdown
    initializeCustomDropdown();
    
    // Add smooth scrolling to results or alerts
    const resultsCard = document.querySelector('.results-card');
    const warningAlert = document.querySelector('.ultra-alert.warning');
    
    if (warningAlert) {
        // If there's a cooldown warning, scroll to it
        setTimeout(() => {
            warningAlert.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 300);
    } else if (resultsCard) {
        setTimeout(() => {
            resultsCard.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 500);
    }
    
    // Enhanced form validation with cooldown check
    const form = document.querySelector('.ultra-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('.ultra-button.primary');
            const selectedChild = document.getElementById('patient_id');
            const availableFoods = form.querySelector('#available_foods');
            
            // Validate inputs
            if (!selectedChild || !selectedChild.value) {
                e.preventDefault();
                showValidationError('Please select a child');
                return;
            }
            
            if (!availableFoods || !availableFoods.value.trim()) {
                e.preventDefault();
                showValidationError('Please enter available ingredients');
                availableFoods?.focus();
                return;
            }
            
            // Show loading state
            if (submitBtn) {
                submitBtn.innerHTML = `
                    <div class="button-content">
                        <i class="fas fa-spinner fa-spin button-icon"></i>
                        <span class="button-text">Generating...</span>
                    </div>
                `;
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
            }
        });
    }
    
    // Auto-dismiss success messages
    const successAlert = document.querySelector('.ultra-alert.success');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            successAlert.style.opacity = '0';
            successAlert.style.transform = 'translateY(-10px)';
            setTimeout(() => successAlert.remove(), 500);
        }, 8000);
    }
});

// Custom Dropdown Functionality
function initializeCustomDropdown() {
    const trigger = document.getElementById('childSelectTrigger');
    const dropdown = document.getElementById('childSelectDropdown');
    const searchInput = document.getElementById('childDropdownSearch');
    const options = document.querySelectorAll('.dropdown-option');
    const hiddenInput = document.getElementById('patient_id');
    const noResults = document.querySelector('.dropdown-no-results');
    
    if (!trigger || !dropdown) return;
    
    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        const isActive = dropdown.classList.contains('active');
        
        if (isActive) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            options.forEach(option => {
                const searchText = option.getAttribute('data-search');
                if (searchText.includes(searchTerm)) {
                    option.style.display = '';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Show/hide no results
            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
        
        // Clear search on Escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
                closeDropdown();
            }
        });
    }
    
    // Option selection
    options.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const value = this.getAttribute('data-value');
            const name = this.getAttribute('data-name');
            const age = this.getAttribute('data-age');
            const ageMonths = parseInt(this.getAttribute('data-age-months') || age);
            
            // Update hidden input
            hiddenInput.value = value;
            
            // Update selected display
            const nameDisplay = document.querySelector('.selected-child-name');
            const ageDisplay = document.querySelector('.selected-child-age');
            
            if (nameDisplay) nameDisplay.textContent = name;
            if (ageDisplay) {
                ageDisplay.textContent = `${age} months old`;
                ageDisplay.style.display = 'block';
            }
            
            // Update selected state
            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            // Check if child is under 6 months
            checkChildAge(ageMonths, name);
            
            // Close dropdown
            closeDropdown();
            
            // Show feedback
            showToast(`Selected: ${name}`, 'success');
        });
    });
    
    // Restore previous selection if exists
    const previousValue = hiddenInput.value;
    if (previousValue) {
        const selectedOption = document.querySelector(`.dropdown-option[data-value="${previousValue}"]`);
        if (selectedOption) {
            selectedOption.click();
        }
    }
    
    function openDropdown() {
        dropdown.classList.add('active');
        trigger.classList.add('active');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 100);
        }
    }
    
    function closeDropdown() {
        dropdown.classList.remove('active');
        trigger.classList.remove('active');
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    }
}

// Check child age for breastfeeding notice
function checkChildAge(ageMonths, childName) {
    const breastfeedingNotice = document.getElementById('breastfeedingNotice');
    const availableFoodsSection = document.querySelector('.form-section:has(#available_foods)');
    const submitButton = document.querySelector('.ultra-button.primary[type="submit"]');
    
    if (!breastfeedingNotice) return;
    
    if (ageMonths < 6) {
        // Show breastfeeding notice
        breastfeedingNotice.style.display = 'flex';
        
        // Scroll to notice
        setTimeout(() => {
            breastfeedingNotice.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }, 300);
        
        // Optional: Disable available foods section and submit button
        if (availableFoodsSection) {
            availableFoodsSection.style.opacity = '0.5';
            availableFoodsSection.style.pointerEvents = 'none';
            const foodsInput = availableFoodsSection.querySelector('#available_foods');
            if (foodsInput) {
                foodsInput.disabled = true;
                foodsInput.placeholder = 'Not applicable for babies under 6 months';
            }
        }
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.style.opacity = '0.5';
            submitButton.style.pointerEvents = 'none';
            const buttonText = submitButton.querySelector('.button-text');
            if (buttonText) {
                buttonText.textContent = 'Meal Plans Not Available for Babies Under 6 Months';
            }
        }
        
        showToast(`${childName} is under 6 months - Exclusive breastfeeding recommended`, 'info');
    } else {
        // Hide breastfeeding notice
        breastfeedingNotice.style.display = 'none';
        
        // Re-enable available foods section and submit button
        if (availableFoodsSection) {
            availableFoodsSection.style.opacity = '1';
            availableFoodsSection.style.pointerEvents = 'auto';
            const foodsInput = availableFoodsSection.querySelector('#available_foods');
            if (foodsInput) {
                foodsInput.disabled = false;
                foodsInput.placeholder = 'e.g., chicken, rice, vegetables, eggs, fish, fruits';
            }
        }
        
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.style.opacity = '1';
            submitButton.style.pointerEvents = 'auto';
            const buttonText = submitButton.querySelector('.button-text');
            if (buttonText) {
                buttonText.textContent = 'Generate Smart Meal Plan';
            }
        }
    }
}

// Show validation error helper
function showValidationError(message) {
    const existingError = document.querySelector('.validation-error-toast');
    if (existingError) existingError.remove();
    
    const toast = document.createElement('div');
    toast.className = 'validation-error-toast';
    toast.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Child search functionality for scalable child selection
function initializeChildSearch() {
    const searchInput = document.getElementById('childSearch');
    if (!searchInput) return;
    
    const childItems = document.querySelectorAll('.child-list-item');
    const noResults = document.querySelector('.no-results');
    const searchCount = document.querySelector('.search-count');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let visibleCount = 0;
        
        childItems.forEach(item => {
            const childName = item.getAttribute('data-child-name');
            
            if (childName.includes(searchTerm)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Update search count
        if (searchCount) {
            searchCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'child' : 'children'}`;
        }
        
        // Show/hide no results message
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        // Add search feedback animation
        const container = document.querySelector('.children-list-container');
        if (container) {
            container.style.opacity = '0.7';
            setTimeout(() => {
                container.style.opacity = '1';
            }, 150);
        }
    });
    
    // Clear search on Escape key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.dispatchEvent(new Event('input'));
        }
    });
}

// Add ingredient to input field with improved UX
function addIngredient(ingredient) {
    const input = document.getElementById('available_foods');
    if (!input) return;
    
    const currentValue = input.value.trim();
    
    if (currentValue === '') {
        input.value = ingredient;
    } else {
        // Split by comma and clean up
        const ingredients = currentValue.split(',').map(item => item.trim().toLowerCase());
        
        // Check if ingredient already exists (case-insensitive)
        if (!ingredients.includes(ingredient.toLowerCase())) {
            input.value = currentValue + ', ' + ingredient;
        } else {
            // Show feedback that ingredient already exists
            showToast(`${ingredient} is already in your list`, 'info');
            return;
        }
    }
    
    // Add visual feedback to the clicked tag
    const tag = event.target;
    tag.style.background = 'var(--success-gradient)';
    tag.style.color = 'white';
    tag.style.transform = 'scale(0.95)';
    
    setTimeout(() => {
        tag.style.background = '';
        tag.style.color = '';
        tag.style.transform = '';
    }, 400);
    
    // Focus input and scroll to show added ingredient
    input.focus();
    input.scrollLeft = input.scrollWidth;
    
    // Show success feedback
    showToast(`Added: ${ingredient}`, 'success');
}

// Simple toast notification
function showToast(message, type = 'info') {
    const existingToast = document.querySelector('.ingredient-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `ingredient-toast ${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-circle' : 
                 'info-circle';
    
    toast.innerHTML = `<i class="fas fa-${icon}"></i><span>${message}</span>`;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Enhanced print function
function printMealPlan() {
    const mealPlanContent = document.getElementById('mealPlanTableContent');
    if (!mealPlanContent) {
        showToast('No meal plan found to print.', 'error');
        return;
    }
    
    const childName = document.querySelector('.modal-title-section p') ? 
        document.querySelector('.modal-title-section p').textContent.split('•')[0].trim() : 
        'Child';
    
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Smart Meal Plan - ${childName}</title>
                <style>
                    @page {
                        margin: 20mm;
                    }
                    
                    body { 
                        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
                        margin: 0;
                        padding: 40px;
                        color: #1e293b;
                        line-height: 1.7;
                        background: #f8fafc;
                    }
                    
                    @media print {
                        body {
                            padding: 20px;
                        }
                    }
                    .print-container {
                        background: white;
                        padding: 40px;
                        border-radius: 20px;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    .print-header {
                        text-align: center;
                        margin-bottom: 40px;
                        padding-bottom: 20px;
                        border-bottom: 3px solid #667eea;
                    }
                    .print-title {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                        font-size: 2.5rem;
                        font-weight: 700;
                        margin: 0 0 10px 0;
                    }
                    .print-subtitle {
                        color: #64748b;
                        font-size: 1.2rem;
                        margin: 0;
                    }
                    .print-meta {
                        background: #f1f5f9;
                        padding: 20px;
                        border-radius: 15px;
                        margin-bottom: 30px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    .meta-item {
                        text-align: center;
                    }
                    .meta-label {
                        font-size: 0.875rem;
                        color: #64748b;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        margin-bottom: 5px;
                    }
                    .meta-value {
                        font-weight: 600;
                        color: #1e293b;
                        font-size: 1.1rem;
                    }
                    h3, h4, h5 {
                        color: #667eea;
                        margin-top: 2rem;
                        margin-bottom: 1rem;
                        font-weight: 600;
                    }
                    ul {
                        padding-left: 1.5rem;
                        margin-bottom: 1.5rem;
                    }
                    li {
                        margin-bottom: 0.75rem;
                    }
                    pre {
                        white-space: pre-wrap;
                        word-wrap: break-word;
                        font-family: inherit;
                        font-size: inherit;
                        color: inherit;
                        background: none;
                        border: none;
                        margin: 0;
                        padding: 0;
                    }
                    .print-footer {
                        margin-top: 40px;
                        padding-top: 20px;
                        border-top: 2px solid #e2e8f0;
                        text-align: center;
                        color: #64748b;
                        font-size: 0.875rem;
                    }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <div class="print-header">
                        <h1 class="print-title">🍽️ Smart Meal Plan</h1>
                        <p class="print-subtitle">For ${childName}</p>
                    </div>
                    
                    <div class="print-meta">
                        <div class="meta-item">
                            <div class="meta-label">Generated Date</div>
                            <div class="meta-value">${new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Generated Time</div>
                            <div class="meta-value">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Powered By</div>
                            <div class="meta-value">AI Nutrition System</div>
                        </div>
                    </div>
                    
                    <div class="print-content">
                        ${mealPlanContent.innerHTML}
                    </div>
                    
                    <div class="print-footer">
                        <p>This meal plan was generated using advanced AI technology specifically tailored for your child's nutritional needs.</p>
                        <p><strong>Always consult with a healthcare professional before making significant dietary changes.</strong></p>
                    </div>
                </div>
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// Enhanced copy function with better feedback
function copyMealPlan() {
    const mealPlanTable = document.querySelector('.meal-schedule-table');
    const childName = document.querySelector('.modal-title-section p')?.textContent || 'Meal Plan';
    
    if (!mealPlanTable) {
        showToast('No meal plan found to copy.', 'error');
        return;
    }
    
    // Extract table data and format as text
    let mealPlanText = `7-DAY PERSONALIZED MEAL PLAN\n${childName}\n${'='.repeat(60)}\n\n`;
    
    const rows = mealPlanTable.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const mealLabel = row.querySelector('.meal-label')?.textContent || '';
        const cells = row.querySelectorAll('.meal-cell .meal-content');
        
        mealPlanText += `\n${mealLabel.toUpperCase()}\n${'-'.repeat(40)}\n`;
        cells.forEach((cell, index) => {
            const mealText = cell.textContent.trim();
            if (mealText && mealText !== 'Loading...') {
                mealPlanText += `Day ${index + 1}: ${mealText}\n`;
            }
        });
    });
    
    mealPlanText += `\n${'='.repeat(60)}\nGenerated by Smart Meal Planning System`;
    
    navigator.clipboard.writeText(mealPlanText).then(function() {
        // Update both copy buttons
        const copyBtns = [document.getElementById('copyBtn'), document.getElementById('copyMainBtn')];
        
        copyBtns.forEach(btn => {
            if (btn) {
                const originalContent = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.style.background = 'var(--success-gradient)';
                btn.style.color = 'white';
                btn.style.transform = 'scale(1.1)';
                
                if (btn.classList.contains('ultra-button')) {
                    btn.innerHTML = `
                        <div class="button-content">
                            <i class="fas fa-check button-icon"></i>
                            <span class="button-text">Copied!</span>
                        </div>
                    `;
                }
                
                setTimeout(function() {
                    btn.innerHTML = originalContent;
                    btn.style.background = '';
                    btn.style.color = '';
                    btn.style.transform = '';
                }, 2000);
            }
        });
        
        // Show toast notification
        showToast('Meal plan copied to clipboard!', 'success');
        
    }).catch(function() {
        showToast('Failed to copy meal plan. Please try again.', 'error');
    });
}

// Download meal plan as text file
function downloadMealPlan() {
    const mealPlanTable = document.querySelector('.meal-schedule-table');
    const childName = document.querySelector('.modal-title-section p')?.textContent.split('•')[0].trim() || 'Meal Plan';
    const date = document.querySelector('.modal-title-section p')?.textContent.split('•')[1]?.trim() || new Date().toLocaleDateString();
    
    if (!mealPlanTable) {
        showToast('No meal plan found to download.', 'error');
        return;
    }
    
    // Extract table data and format as text
    let mealPlanText = `7-DAY PERSONALIZED MEAL PLAN\n`;
    mealPlanText += `${childName}\n`;
    mealPlanText += `Generated: ${date}\n`;
    mealPlanText += `${'='.repeat(80)}\n\n`;
    
    const rows = mealPlanTable.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const mealLabel = row.querySelector('.meal-label')?.textContent || '';
        const cells = row.querySelectorAll('.meal-cell .meal-content');
        
        mealPlanText += `\n${mealLabel.toUpperCase()}\n${'-'.repeat(60)}\n`;
        cells.forEach((cell, index) => {
            const mealText = cell.textContent.trim();
            if (mealText && mealText !== 'Loading...') {
                mealPlanText += `  Day ${index + 1}: ${mealText}\n`;
            }
        });
    });
    
    mealPlanText += `\n${'='.repeat(80)}\n`;
    mealPlanText += `AI Optimized | Nutritionally Balanced | Age-Appropriate\n`;
    mealPlanText += `Generated by Smart Meal Planning System`;
    
    // Create download
    const blob = new Blob([mealPlanText], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Meal-Plan-${childName.replace(/\s+/g, '-')}-${new Date().toISOString().split('T')[0]}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showToast('Meal plan downloaded successfully!', 'success');
}

// Toast notification system
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        </div>
        <div class="toast-message">${message}</div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add toast styles if not already added
    if (!document.querySelector('#toast-styles')) {
        const styles = document.createElement('style');
        styles.id = 'toast-styles';
        styles.textContent = `
            .toast {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-radius: 12px;
                padding: 1rem 1.5rem;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
                gap: 1rem;
                z-index: 1000;
                animation: slideInRight 0.3s ease-out;
                max-width: 400px;
                border-left: 4px solid #667eea;
            }
            .toast-success { border-left-color: #10b981; }
            .toast-error { border-left-color: #ef4444; }
            .toast-info { border-left-color: #3b82f6; }
            .toast-icon { font-size: 1.2rem; }
            .toast-success .toast-icon { color: #10b981; }
            .toast-error .toast-icon { color: #ef4444; }
            .toast-info .toast-icon { color: #3b82f6; }
            .toast-message { flex: 1; color: #1e293b; font-weight: 500; }
            .toast-close { 
                background: none; 
                border: none; 
                color: #94a3b8; 
                cursor: pointer; 
                padding: 0.25rem;
                border-radius: 6px;
                transition: color 0.2s ease;
            }
            .toast-close:hover { color: #475569; }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Enhanced keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + P for print
    if ((e.ctrlKey || e.metaKey) && e.key === 'p' && document.querySelector('.meal-plan-content-ultra')) {
        e.preventDefault();
        printMealPlan();
    }
    
    // Ctrl/Cmd + C for copy (when focused on meal plan)
    if ((e.ctrlKey || e.metaKey) && e.key === 'c' && 
        document.querySelector('.meal-plan-content-ultra:hover')) {
        copyMealPlan();
    }
});

// Utility functions for enhanced user experience
function initializeMealPlanPage() {
    // Initialize tooltips for action buttons
    const actionBtns = document.querySelectorAll('.action-btn');
    actionBtns.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const title = this.getAttribute('title');
            if (title) {
                // Could add custom tooltip here
            }
        });
    });
    
    // Initialize ingredient tag interactions
    const tags = document.querySelectorAll('.tag');
    tags.forEach(tag => {
        tag.addEventListener('click', function() {
            const ingredient = this.textContent.trim();
            addIngredient(ingredient);
        });
    });
    
    // Initialize form field enhancements
    const inputs = document.querySelectorAll('.input-field-ultra input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeMealPlanPage);

// Modal Functions
function openMealPlanModal() {
    const modal = document.getElementById('mealPlanModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Parse meal plan data when opening modal
        parseMealPlanData();
        
        // Auto-scroll to table content
        setTimeout(() => {
            const tableContent = document.getElementById('mealPlanTableContent');
            if (tableContent) {
                tableContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 400);
    }
}

function closeMealPlanModal() {
    const modal = document.getElementById('mealPlanModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Auto-open modal if meal plan exists on page load
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('mealPlanModal');
    const banner = document.querySelector('.success-banner');
    
    if (modal && banner) {
        // Auto-open the modal after a short delay
        setTimeout(() => {
            openMealPlanModal();
            parseMealPlanData();
        }, 500);
    }
    
    // Also parse data if modal exists but no banner (cooldown case)
    if (modal && !banner) {
        parseMealPlanData();
    }
});

// Parse meal plan data and populate table
function parseMealPlanData() {
    const rawData = document.getElementById('rawMealPlanData');
    if (!rawData) {
        console.error('Raw meal plan data not found');
        return;
    }
    
    const mealPlanText = rawData.textContent || rawData.innerText;
    console.log('Raw meal plan text:', mealPlanText);
    
    // Parse the AI-generated meal plan
    const meals = {
        breakfast: [],
        lunch: [],
        snack: [],
        dinner: []
    };
    
    // Parse each day's meals (supports multiple formats)
    // Pattern: **Day X** or 📅 Day X or Day X:
    const dayPattern = /(?:\*\*|📅)?\s*Day\s+(\d+)(?:\*\*)?\s*(?:\([^)]*\))?\s*:?/gi;
    const days = [];
    let match;
    
    while ((match = dayPattern.exec(mealPlanText)) !== null) {
        days.push({
            dayNumber: parseInt(match[1]),
            startIndex: match.index
        });
    }
    
    console.log('Found days:', days);
    
    // Extract meals for each day
    for (let i = 0; i < days.length; i++) {
        const dayInfo = days[i];
        const dayNumber = dayInfo.dayNumber;
        const startIndex = dayInfo.startIndex;
        const endIndex = i < days.length - 1 ? days[i + 1].startIndex : mealPlanText.length;
        const dayContent = mealPlanText.substring(startIndex, endIndex);
        
        console.log(`Day ${dayNumber} content:`, dayContent.substring(0, 200));
        
        // Extract breakfast - improved regex to capture meal name before dash or parenthesis
        const breakfastMatch = dayContent.match(/(?:🍳|🥐)?\s*(?:\*\*)?(?:Breakfast|Almusal)(?:\*\*)?[^\n]*?[:\n]\s*([^\n-]+)/i);
        if (breakfastMatch) {
            meals.breakfast[dayNumber - 1] = cleanMealText(breakfastMatch[1]);
            console.log(`Day ${dayNumber} Breakfast:`, meals.breakfast[dayNumber - 1]);
        }
        
        // Extract lunch
        const lunchMatch = dayContent.match(/(?:🍽️|🍲)?\s*(?:\*\*)?(?:Lunch|Tanghalian)(?:\*\*)?[^\n]*?[:\n]\s*([^\n-]+)/i);
        if (lunchMatch) {
            meals.lunch[dayNumber - 1] = cleanMealText(lunchMatch[1]);
            console.log(`Day ${dayNumber} Lunch:`, meals.lunch[dayNumber - 1]);
        }
        
        // Extract snack - supports PM Snack, Snack, Meryenda
        const snackMatch = dayContent.match(/(?:🍪|🥤)?\s*(?:\*\*)?(?:PM\s+Snack|Snack|Meryenda)(?:\*\*)?[^\n]*?[:\n]\s*([^\n-]+)/i);
        if (snackMatch) {
            meals.snack[dayNumber - 1] = cleanMealText(snackMatch[1]);
            console.log(`Day ${dayNumber} Snack:`, meals.snack[dayNumber - 1]);
        }
        
        // Extract dinner
        const dinnerMatch = dayContent.match(/(?:🌙|🍴)?\s*(?:\*\*)?(?:Dinner|Hapunan)(?:\*\*)?[^\n]*?[:\n]\s*([^\n-]+)/i);
        if (dinnerMatch) {
            meals.dinner[dayNumber - 1] = cleanMealText(dinnerMatch[1]);
            console.log(`Day ${dayNumber} Dinner:`, meals.dinner[dayNumber - 1]);
        }
    }
    
    // Populate table cells with parsed data
    for (let day = 1; day <= 7; day++) {
        const breakfastCell = document.getElementById(`breakfast-day${day}`);
        const lunchCell = document.getElementById(`lunch-day${day}`);
        const snackCell = document.getElementById(`snack-day${day}`);
        const dinnerCell = document.getElementById(`dinner-day${day}`);
        
        if (breakfastCell) breakfastCell.innerHTML = formatMealCell(meals.breakfast[day - 1] || 'N/A');
        if (lunchCell) lunchCell.innerHTML = formatMealCell(meals.lunch[day - 1] || 'N/A');
        if (snackCell) snackCell.innerHTML = formatMealCell(meals.snack[day - 1] || 'N/A');
        if (dinnerCell) dinnerCell.innerHTML = formatMealCell(meals.dinner[day - 1] || 'N/A');
    }
    
    // Create mobile card layout
    createMobileCardLayout(meals);
}

// Clean meal text by removing emojis and extra formatting
function cleanMealText(text) {
    if (!text) return '';
    
    // Remove all emojis and special characters
    let cleaned = text.replace(/[\u{1F000}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/gu, '');
    
    // Remove **bold** markers
    cleaned = cleaned.replace(/\*\*/g, '');
    
    // Remove benefit text that starts with " - "
    cleaned = cleaned.split(' - ')[0];
    
    // Remove text in parentheses at the end if it's a benefit description (not portion)
    if (cleaned.includes('(') && (cleaned.toLowerCase().includes('rich in') || cleaned.toLowerCase().includes('mayaman') || cleaned.toLowerCase().includes('high in'))) {
        cleaned = cleaned.split('(')[0];
    }
    
    // Trim whitespace
    cleaned = cleaned.trim();
    
    return cleaned;
}

// Format meal cell with proper styling
function formatMealCell(mealText) {
    if (!mealText || mealText === 'N/A') {
        return '<span class="no-meal">No meal specified</span>';
    }
    
    // Split into dish name and portion (if exists)
    const parts = mealText.split('(');
    const dishName = parts[0].trim();
    const portion = parts.length > 1 ? '(' + parts.slice(1).join('(').trim() : '';
    
    return `<strong>${dishName}</strong>${portion ? '<br><small class="portion-info">' + portion + '</small>' : ''}`;
}

// Create mobile card layout
function createMobileCardLayout(meals) {
    const tableWrapper = document.querySelector('.table-scroll-wrapper');
    if (!tableWrapper) return;
    
    // Check if mobile cards already exist
    let mobileContainer = document.querySelector('.mobile-meal-cards');
    if (!mobileContainer) {
        mobileContainer = document.createElement('div');
        mobileContainer.className = 'mobile-meal-cards';
        tableWrapper.parentNode.insertBefore(mobileContainer, tableWrapper);
    }
    
    // Clear existing content
    mobileContainer.innerHTML = '';
    
    // Create cards for each day
    for (let day = 1; day <= 7; day++) {
        const dayCard = document.createElement('div');
        dayCard.className = 'day-card';
        dayCard.innerHTML = `
            <div class="day-card-header">
                <h4>Day ${day}</h4>
                <div class="day-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="day-card-body">
                <div class="mobile-meal-item">
                    <div class="mobile-meal-label">
                        <i class="fas fa-sunrise"></i>
                        Breakfast
                    </div>
                    <div class="mobile-meal-content">${formatMealCell(meals.breakfast[day - 1] || 'N/A')}</div>
                </div>
                <div class="mobile-meal-item">
                    <div class="mobile-meal-label">
                        <i class="fas fa-sun"></i>
                        Lunch
                    </div>
                    <div class="mobile-meal-content">${formatMealCell(meals.lunch[day - 1] || 'N/A')}</div>
                </div>
                <div class="mobile-meal-item">
                    <div class="mobile-meal-label">
                        <i class="fas fa-cookie-bite"></i>
                        PM Snack
                    </div>
                    <div class="mobile-meal-content">${formatMealCell(meals.snack[day - 1] || 'N/A')}</div>
                </div>
                <div class="mobile-meal-item">
                    <div class="mobile-meal-label">
                        <i class="fas fa-moon"></i>
                        Dinner
                    </div>
                    <div class="mobile-meal-content">${formatMealCell(meals.dinner[day - 1] || 'N/A')}</div>
                </div>
            </div>
        `;
        mobileContainer.appendChild(dayCard);
    }
}

// Toggle detailed view
function toggleDetailedView() {
    const rawData = document.getElementById('rawMealPlanData');
    if (!rawData) return;
    
    // Create or toggle detailed view modal
    let detailedModal = document.getElementById('detailedViewModal');
    
    if (!detailedModal) {
        detailedModal = document.createElement('div');
        detailedModal.id = 'detailedViewModal';
        detailedModal.className = 'detailed-view-modal';
        detailedModal.innerHTML = `
            <div class="detailed-modal-overlay" onclick="toggleDetailedView()"></div>
            <div class="detailed-modal-content">
                <div class="detailed-modal-header">
                    <h3><i class="fas fa-file-alt"></i> Detailed Meal Plan</h3>
                    <button onclick="toggleDetailedView()" class="detailed-close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="detailed-modal-body">
                    <pre class="raw-meal-plan">${rawData.textContent || rawData.innerText}</pre>
                </div>
            </div>
        `;
        document.body.appendChild(detailedModal);
        
        // Add styles for detailed modal
        const style = document.createElement('style');
        style.textContent = `
            .detailed-view-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }
            .detailed-view-modal.active {
                display: flex;
            }
            .detailed-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                backdrop-filter: blur(4px);
            }
            .detailed-modal-content {
                position: relative;
                width: 100%;
                max-width: 900px;
                max-height: 85vh;
                background: white;
                border-radius: 16px;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            .detailed-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1.5rem;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
            }
            .detailed-modal-header h3 {
                margin: 0;
                font-size: 1.25rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            .detailed-close-btn {
                width: 36px;
                height: 36px;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }
            .detailed-close-btn:hover {
                background: rgba(255, 87, 87, 0.9);
                transform: rotate(90deg);
            }
            .detailed-modal-body {
                flex: 1;
                overflow-y: auto;
                padding: 1.5rem;
            }
        `;
        document.head.appendChild(style);
    }
    
    detailedModal.classList.toggle('active');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMealPlanModal();
    }
});

// Export functions for global access
window.mealPlanUtils = {
    addIngredient,
    printMealPlan,
    copyMealPlan,
    downloadMealPlan,
    showToast,
    openMealPlanModal,
    closeMealPlanModal,
    parseMealPlanData
};

// Also export functions directly to window for onclick handlers
window.addIngredient = addIngredient;
window.printMealPlan = printMealPlan;
window.copyMealPlan = copyMealPlan;
window.downloadMealPlan = downloadMealPlan;
window.openMealPlanModal = openMealPlanModal;
window.closeMealPlanModal = closeMealPlanModal;
window.parseMealPlanData = parseMealPlanData;
window.showToast = showToast;