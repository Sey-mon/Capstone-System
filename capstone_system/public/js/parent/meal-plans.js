// Enhanced JavaScript for Ultra-Modern UI
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to results
    if (document.querySelector('.results-card')) {
        setTimeout(() => {
            document.querySelector('.results-card').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 500);
    }
    
    // Enhanced form validation
    const form = document.querySelector('.ultra-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('.ultra-button.primary');
            if (submitBtn) {
                submitBtn.innerHTML = `
                    <div class="button-content">
                        <i class="fas fa-spinner fa-spin button-icon"></i>
                        <span class="button-text">Generating...</span>
                    </div>
                `;
                submitBtn.disabled = true;
            }
        });
    }
    
    // Add loading animation to form submission
    const ultraForm = document.querySelector('.ultra-form');
    if (ultraForm) {
        ultraForm.addEventListener('submit', function() {
            const submitBtn = ultraForm.querySelector('.ultra-button.primary');
            if (submitBtn) {
                submitBtn.style.background = 'linear-gradient(135deg, #94a3b8 0%, #64748b 100%)';
                submitBtn.innerHTML = `
                    <div class="button-content">
                        <i class="fas fa-spinner fa-spin button-icon"></i>
                        <span class="button-text">Creating Your Perfect Meal Plan...</span>
                    </div>
                `;
                submitBtn.disabled = true;
            }
        });
    }
    
    // Initialize child search functionality
    initializeChildSearch();
});

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

// Add ingredient to input field
function addIngredient(ingredient) {
    const input = document.getElementById('available_foods');
    const currentValue = input.value.trim();
    
    if (currentValue === '') {
        input.value = ingredient;
    } else {
        const ingredients = currentValue.split(',').map(item => item.trim());
        if (!ingredients.includes(ingredient)) {
            input.value = currentValue + ', ' + ingredient;
        }
    }
    
    // Add visual feedback
    const tag = event.target;
    tag.style.background = 'var(--success-gradient)';
    tag.style.color = 'white';
    tag.style.transform = 'scale(0.95)';
    
    setTimeout(() => {
        tag.style.background = '';
        tag.style.color = '';
        tag.style.transform = '';
    }, 300);
    
    input.focus();
}

// Enhanced print function
function printMealPlan() {
    const mealPlanContent = document.querySelector('.meal-plan-content-ultra');
    if (!mealPlanContent) {
        showToast('No meal plan found to print.', 'error');
        return;
    }
    
    const childName = document.querySelector('.header-text p') ? 
        document.querySelector('.header-text p').textContent.split('•')[0].replace('Generated for ', '').trim() : 
        'Child';
    
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Smart Meal Plan - ${childName}</title>
                <style>
                    body { 
                        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
                        margin: 0;
                        padding: 40px;
                        color: #1e293b;
                        line-height: 1.7;
                        background: #f8fafc;
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
    const mealPlanElement = document.querySelector('.meal-plan-content-ultra pre') || 
                           document.querySelector('.meal-plan-content-ultra');
    
    if (!mealPlanElement) {
        showToast('No meal plan found to copy.', 'error');
        return;
    }
    
    const mealPlanText = mealPlanElement.textContent || mealPlanElement.innerText;
    
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

// Download meal plan as PDF (placeholder function)
function downloadMealPlan() {
    showToast('PDF download feature coming soon!', 'info');
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

// Export functions for global access
window.mealPlanUtils = {
    addIngredient,
    printMealPlan,
    copyMealPlan,
    downloadMealPlan,
    showToast
};