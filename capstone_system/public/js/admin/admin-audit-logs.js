document.addEventListener('DOMContentLoaded', function() {
    // Get all filter elements
    const filterElements = document.querySelectorAll('.auto-filter');
    const form = document.getElementById('filtersForm');
    // Add event listeners for automatic filtering
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
});
