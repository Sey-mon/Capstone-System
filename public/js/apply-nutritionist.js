document.addEventListener("DOMContentLoaded", function () {
    console.log("Nutritionist application page loaded successfully!");
    
    const applicationForm = document.getElementById('applicationForm');
    
    if (applicationForm) {
        // Form validation
        applicationForm.addEventListener('submit', function(e) {
            const qualifications = document.getElementById('qualifications').value.trim();
            const experience = document.getElementById('experience').value.trim();
            
            if (qualifications.length < 50) {
                e.preventDefault();
                showAlert('Please provide more detailed qualifications (minimum 50 characters).', 'error');
                return false;
            }
            
            if (experience.length < 50) {
                e.preventDefault();
                showAlert('Please provide more detailed experience (minimum 50 characters).', 'error');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting Application...';
        });
        
        // Add character counter for textareas
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            const maxLength = 1000;
            const counter = document.createElement('div');
            counter.className = 'character-counter';
            
            function updateCounter() {
                const remaining = maxLength - textarea.value.length;
                counter.textContent = `${textarea.value.length}/${maxLength} characters`;
                
                if (remaining < 100) {
                    counter.classList.add('warning');
                } else {
                    counter.classList.remove('warning');
                }
            }
            
            textarea.addEventListener('input', updateCounter);
            textarea.parentNode.appendChild(counter);
            updateCounter();
        });
    }
    
    // Show alert function
    function showAlert(message, type = 'error') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<p>${message}</p>`;
        
        // Insert at top of form
        const form = document.getElementById('applicationForm');
        form.insertBefore(alert, form.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
});
