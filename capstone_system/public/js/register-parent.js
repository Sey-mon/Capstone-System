document.addEventListener("DOMContentLoaded", function () {
    console.log("Parent registration page loaded successfully!");
    
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        // Form validation
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Passwords do not match!', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password must be at least 6 characters long!', 'error');
                return false;
            }
            
            // Validate child information if provided
            const childFirstName = document.getElementById('child_first_name').value.trim();
            const childLastName = document.getElementById('child_last_name').value.trim();
            const childBirthDate = document.getElementById('child_birth_date').value;
            
            // If any child field is filled, all required child fields must be filled
            if (childFirstName || childLastName || childBirthDate) {
                if (!childFirstName || !childLastName || !childBirthDate) {
                    e.preventDefault();
                    showAlert('If providing child information, please fill in all child fields (first name, last name, and birth date).', 'error');
                    return false;
                }
                
                // Validate birth date is not in the future
                const today = new Date();
                const birthDate = new Date(childBirthDate);
                if (birthDate >= today) {
                    e.preventDefault();
                    showAlert('Child\'s birth date cannot be today or in the future.', 'error');
                    return false;
                }
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Account...';
            
            // Re-enable button after 10 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }, 10000);
        });
        
        // Real-time password validation
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('password_confirmation');
        
        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function() {
                const password = passwordField.value;
                const confirmPassword = this.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    this.style.borderColor = '#dc3545';
                } else {
                    this.style.borderColor = '#dcdcdc';
                }
            });
        }
        
        // Child information helpers
        const childFields = ['child_first_name', 'child_last_name', 'child_birth_date'];
        
        childFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    highlightChildSection();
                });
            }
        });
        
        function highlightChildSection() {
            const childSection = document.querySelector('.child-info-section');
            const anyFieldFilled = childFields.some(fieldId => {
                const field = document.getElementById(fieldId);
                return field && field.value.trim() !== '';
            });
            
            if (anyFieldFilled) {
                childSection.style.borderColor = '#3b82f6';
                childSection.style.backgroundColor = '#eff6ff';
            } else {
                childSection.style.borderColor = '#e5e7eb';
                childSection.style.backgroundColor = '#f9fafb';
            }
        }
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
        const form = document.getElementById('registerForm');
        form.insertBefore(alert, form.firstChild);
        
        // Auto-remove after 7 seconds
        setTimeout(() => {
            alert.remove();
        }, 7000);
    }
});
