// Enhanced Filter Functions
document.addEventListener('DOMContentLoaded', function() {
    // Get all filter elements
    const filterElements = document.querySelectorAll('.auto-filter');
    const form = document.getElementById('filtersForm');
    
    // Auto-submit form when filters change
    filterElements.forEach(element => {
        if (element.type === 'select-one') {
            // For select elements, trigger on change
            element.addEventListener('change', function() {
                submitForm();
            });
        } else if (element.type === 'date') {
            // For date inputs, trigger on change with slight delay
            element.addEventListener('change', function() {
                setTimeout(() => {
                    submitForm();
                }, 300);
            });
        } else if (element.type === 'text') {
            // For search input, add debounce with longer delay
            let timeout;
            element.addEventListener('input', function() {
                clearTimeout(timeout);
                // Show visual feedback that search will trigger
                const searchIcon = element.parentElement.querySelector('.search-icon');
                if (searchIcon) {
                    searchIcon.style.opacity = '0.5';
                }
                
                timeout = setTimeout(() => {
                    // Reset icon opacity before submit
                    if (searchIcon) {
                        searchIcon.style.opacity = '1';
                    }
                    submitForm();
                }, 1200); // 1200ms (1.2 seconds) debounce - gives more time to finish typing
            });
        }
    });
    
    function submitForm() {
        // Add loading state
        const tableContainer = document.querySelector('.table-responsive');
        if (tableContainer) {
            tableContainer.style.opacity = '0.6';
            tableContainer.style.pointerEvents = 'none';
        }
        // Submit the form
        form.submit();
    }
    
    // Update filter count on page load
    updateFilterCount();
});

// Clear all filters function
function clearAllFilters() {
    // Reset all form inputs
    const form = document.getElementById('filtersForm');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.type === 'text' || input.type === 'date') {
            input.value = '';
        } else if (input.type === 'select-one') {
            input.selectedIndex = 0;
        }
    });
    
    // Submit the cleared form
    form.submit();
}

// Show active filter count
function updateFilterCount() {
    const form = document.getElementById('filtersForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, select');
    let activeCount = 0;
    
    inputs.forEach(input => {
        if (input.value && input.value !== '') {
            activeCount++;
        }
    });
    
    const clearBtn = document.getElementById('clearAllBtn');
    if (clearBtn) {
        if (activeCount > 0) {
            clearBtn.innerHTML = `<i class="fas fa-times"></i> Clear All (${activeCount})`;
            clearBtn.style.background = 'rgba(255, 255, 255, 0.3)';
        } else {
            clearBtn.innerHTML = '<i class="fas fa-times"></i> Clear All';
            clearBtn.style.background = 'rgba(255, 255, 255, 0.2)';
        }
    }
}
