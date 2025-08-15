class RegistrationWizard {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.formData = {};
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.showStep(1);
        this.updateProgress();
    }
    
    bindEvents() {
        // Navigation buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('next-step')) {
                e.preventDefault();
                
                // If it's the final step (step 4), submit the form
                if (this.currentStep === 4) {
                    this.submitForm();
                } else {
                    this.nextStep();
                }
            }
            
            if (e.target.classList.contains('prev-step')) {
                e.preventDefault();
                this.prevStep();
            }
            
            if (e.target.classList.contains('skip-child-info')) {
                e.preventDefault();
                this.skipChildInfo();
            }
        });
        
        // Form validation on input
        document.addEventListener('input', (e) => {
            // Auto-format Philippine phone number
            if (e.target.name === 'contact_number') {
                this.formatPhoneNumber(e.target);
            }
            
            this.validateField(e.target);
            this.updateNextButton();
        });
        
        // Handle backspace/delete for phone number
        document.addEventListener('keydown', (e) => {
            if (e.target.name === 'contact_number') {
                // Store current length for delete detection
                e.target.dataset.lastLength = e.target.value.length;
            }
        });
        
        // Terms checkbox
        const termsCheckbox = document.getElementById('terms');
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', () => {
                this.updateNextButton();
            });
        }
    }
    
    showStep(stepNumber) {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.classList.remove('active');
        });
        
        // Show current step
        const currentStepElement = document.getElementById(`step-${stepNumber}`);
        if (currentStepElement) {
            currentStepElement.classList.add('active');
        }
        
        this.currentStep = stepNumber;
        this.updateProgress();
        this.updateNextButton();
        
        // Focus on first input of current step
        setTimeout(() => {
            const firstInput = currentStepElement?.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
    
    updateProgress() {
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNumber = index + 1;
            step.classList.remove('active', 'completed');
            
            if (stepNumber < this.currentStep) {
                step.classList.add('completed');
            } else if (stepNumber === this.currentStep) {
                step.classList.add('active');
            }
        });
    }
    
    nextStep() {
        if (this.validateCurrentStep()) {
            if (this.currentStep < this.totalSteps) {
                this.collectStepData();
                
                // Special handling for step 3 (child info) - populate review
                if (this.currentStep === 3) {
                    this.populateReview();
                }
                
                this.showStep(this.currentStep + 1);
            }
        }
    }
    
    prevStep() {
        if (this.currentStep > 1) {
            this.showStep(this.currentStep - 1);
        }
    }
    
    skipChildInfo() {
        // Clear child info fields
        document.getElementById('child_first_name').value = '';
        document.getElementById('child_last_name').value = '';
        document.getElementById('child_age_months').value = '';
        
        this.collectStepData();
        this.populateReview();
        this.showStep(4);
    }
    
    validateCurrentStep() {
        const currentStepElement = document.getElementById(`step-${this.currentStep}`);
        if (!currentStepElement) return false;
        
        const requiredFields = currentStepElement.querySelectorAll('input[required], select[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        // Special validation for step 4 (terms)
        if (this.currentStep === 4) {
            const termsCheckbox = document.getElementById('terms');
            if (termsCheckbox && !termsCheckbox.checked) {
                this.showError(termsCheckbox, 'You must agree to the terms and conditions');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    formatPhoneNumber(field) {
        let value = field.value.replace(/\D/g, ''); // Remove all non-digits
        
        // Don't format if user is deleting (value is getting shorter)
        const currentLength = field.value.length;
        const newLength = value.length;
        
        // Allow user to delete freely
        if (field.dataset.lastLength && currentLength < field.dataset.lastLength) {
            field.dataset.lastLength = currentLength;
            
            // Just ensure it starts with 09 if there are digits
            if (value.length > 0 && !value.startsWith('09')) {
                if (value.startsWith('9')) {
                    value = '0' + value;
                }
            }
            
            // Format only if we have enough digits
            if (value.length >= 4) {
                value = value.substring(0, 4) + '-' + value.substring(4);
            }
            if (value.length >= 8) {
                value = value.substring(0, 8) + '-' + value.substring(8);
            }
            
            field.value = value;
            field.dataset.lastLength = field.value.length;
            return;
        }
        
        // Ensure it starts with 09
        if (value.length > 0 && !value.startsWith('09')) {
            // If user starts typing with 9, add 0
            if (value.startsWith('9')) {
                value = '0' + value;
            } else if (!value.startsWith('0')) {
                // If doesn't start with 0, prepend 09
                value = '09' + value.substring(0, 9);
            }
        }
        
        // Limit to 11 digits
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        // Format as 09XX-XXX-XXXX
        if (value.length >= 4) {
            value = value.substring(0, 4) + '-' + value.substring(4);
        }
        if (value.length >= 8) {
            value = value.substring(0, 8) + '-' + value.substring(8);
        }
        
        field.value = value;
        field.dataset.lastLength = field.value.length;
    }
    
    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        // Clear previous errors
        this.clearError(field);
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            errorMessage = 'This field is required';
            isValid = false;
        }
        
        // Specific field validations
        if (value && isValid) {
            switch (field.type) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        errorMessage = 'Please enter a valid email address';
                        isValid = false;
                    }
                    break;
                    
                case 'password':
                    // Strong password validation
                    if (value.length < 8) {
                        errorMessage = 'Password must be at least 8 characters long';
                        isValid = false;
                    } else if (!/(?=.*[a-z])/.test(value)) {
                        errorMessage = 'Password must contain at least one lowercase letter';
                        isValid = false;
                    } else if (!/(?=.*[A-Z])/.test(value)) {
                        errorMessage = 'Password must contain at least one uppercase letter';
                        isValid = false;
                    } else if (!/(?=.*\d)/.test(value)) {
                        errorMessage = 'Password must contain at least one number';
                        isValid = false;
                    } else if (!/(?=.*[@$!%*?&#])/.test(value)) {
                        errorMessage = 'Password must contain at least one special character (@$!%*?&#)';
                        isValid = false;
                    }
                    break;
                    
                case 'tel':
                    // Philippine phone number validation (09XX-XXX-XXXX format)
                    const phoneRegex = /^09\d{2}-\d{3}-\d{4}$/;
                    if (!phoneRegex.test(value)) {
                        errorMessage = 'Please enter a valid Philippine phone number (09XX-XXX-XXXX)';
                        isValid = false;
                    }
                    break;
            }
            
            // Name fields validation (letters only)
            if (field.name === 'first_name' || field.name === 'last_name' || 
                field.name === 'middle_name' || field.name === 'child_first_name' || 
                field.name === 'child_last_name') {
                const nameRegex = /^[a-zA-Z\s\-\.]+$/;
                if (!nameRegex.test(value)) {
                    errorMessage = 'Names can only contain letters, spaces, hyphens, and periods';
                    isValid = false;
                }
            }
            
            // Password confirmation
            if (field.id === 'password_confirmation') {
                const password = document.getElementById('password').value;
                if (value !== password) {
                    errorMessage = 'Passwords do not match';
                    isValid = false;
                }
            }
            
            // Date validation
            if (field.type === 'date') {
                const selectedDate = new Date(value);
                const today = new Date();
                
                if (field.id === 'birth_date') {
                    if (selectedDate >= today) {
                        errorMessage = 'Birth date must be in the past';
                        isValid = false;
                    }
                    // Check if age is reasonable (not more than 120 years old)
                    const age = today.getFullYear() - selectedDate.getFullYear();
                    if (age > 120) {
                        errorMessage = 'Please enter a valid birth date';
                        isValid = false;
                    }
                }
            }
            
            // Child age validation (number input)
            if (field.id === 'child_age_months') {
                const ageValue = parseInt(field.value);
                if (field.value.trim() !== '' && (isNaN(ageValue) || ageValue < 0 || ageValue > 60)) {
                    errorMessage = 'Child age must be between 0 and 60 months';
                    isValid = false;
                }
            }
        }
        
        if (!isValid) {
            this.showError(field, errorMessage);
        }
        
        return isValid;
    }
    
    showError(field, message) {
        field.classList.add('error');
        
        // Remove existing error message
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add new error message
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        
        // Insert error message after the input field or password container
        const insertAfter = field.classList.contains('password-input-container') ? 
            field : (field.parentNode.querySelector('.password-input-container') || field);
        
        insertAfter.parentNode.insertBefore(errorElement, insertAfter.nextSibling);
    }
    
    clearError(field) {
        field.classList.remove('error');
        const errorMessage = field.parentNode.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
    
    updateNextButton() {
        const nextButton = document.querySelector('.next-step');
        if (!nextButton) return;
        
        const currentStepElement = document.getElementById(`step-${this.currentStep}`);
        if (!currentStepElement) return;
        
        let canProceed = true;
        
        // Check required fields in current step
        const requiredFields = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
        requiredFields.forEach(field => {
            const value = field.value.trim();
            if (!value) {
                canProceed = false;
            }
            
            // Special validation for specific fields
            if (field.name === 'contact_number' && value) {
                const phoneRegex = /^09\d{2}-\d{3}-\d{4}$/;
                if (!phoneRegex.test(value)) {
                    canProceed = false;
                }
            }
            
            if (field.name === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    canProceed = false;
                }
            }
        });
        
        // Special check for step 4 (terms)
        if (this.currentStep === 4) {
            const termsCheckbox = document.getElementById('terms');
            if (termsCheckbox && !termsCheckbox.checked) {
                canProceed = false;
            }
        }
        
        nextButton.disabled = !canProceed;
        
        // Update button text for final step
        if (this.currentStep === 4) {
            nextButton.textContent = 'Complete Registration';
            nextButton.classList.add('btn-success');
        } else {
            nextButton.textContent = 'Continue';
            nextButton.classList.remove('btn-success');
        }
    }
    
    collectStepData() {
        // Collect data from ALL steps, not just current step
        const allInputs = document.querySelectorAll('#parentRegistrationForm input:not([type="hidden"]), #parentRegistrationForm select, #parentRegistrationForm textarea');
        
        allInputs.forEach(input => {
            if (input.type === 'checkbox') {
                this.formData[input.name] = input.checked;
            } else {
                this.formData[input.name] = input.value;
            }
        });
        
        console.log('Collected form data:', this.formData); // Debug log
    }
    
    populateReview() {
        // Collect all form data
        this.collectStepData();
        
        // Personal Information
        const reviewFirstName = document.getElementById('review-first-name');
        const reviewLastName = document.getElementById('review-last-name');
        const reviewBirthDate = document.getElementById('review-birth-date');
        const reviewSex = document.getElementById('review-sex');
        const reviewPhone = document.getElementById('review-phone');
        const reviewAddress = document.getElementById('review-address');
        const reviewEmail = document.getElementById('review-email');
        const reviewChildName = document.getElementById('review-child-name');
        const reviewChildAge = document.getElementById('review-child-age');
        
        if (reviewFirstName) reviewFirstName.textContent = this.formData.first_name || '';
        if (reviewLastName) reviewLastName.textContent = this.formData.last_name || '';
        if (reviewBirthDate) reviewBirthDate.textContent = this.formData.birth_date || '';
        if (reviewSex) reviewSex.textContent = this.formData.sex || '';
        if (reviewPhone) reviewPhone.textContent = this.formData.contact_number || '';
        if (reviewAddress) reviewAddress.textContent = this.formData.address || '';
        if (reviewEmail) reviewEmail.textContent = this.formData.email || '';
        
        // Child Information
        const childFirstName = this.formData.child_first_name || '';
        const childLastName = this.formData.child_last_name || '';
        const childAgeMonths = this.formData.child_age_months || '';
        
        if (reviewChildName) {
            reviewChildName.textContent = 
                childFirstName && childLastName ? `${childFirstName} ${childLastName}` : 'Not provided';
        }
        if (reviewChildAge) {
            if (childAgeMonths) {
                const years = Math.floor(childAgeMonths / 12);
                const months = childAgeMonths % 12;
                let ageText = '';
                if (years > 0) {
                    ageText += `${years} year${years > 1 ? 's' : ''}`;
                    if (months > 0) {
                        ageText += ` and ${months} month${months > 1 ? 's' : ''}`;
                    }
                } else {
                    ageText = `${months} month${months > 1 ? 's' : ''}`;
                }
                ageText += ` old (${childAgeMonths} months)`;
                reviewChildAge.textContent = ageText;
            } else {
                reviewChildAge.textContent = 'Not provided';
            }
        }
        
        // Show/hide child information section - find the section properly
        const allReviewSections = document.querySelectorAll('.review-section');
        const childSection = allReviewSections[allReviewSections.length - 1]; // Last section should be child info
        
        if (childSection && (childFirstName || childLastName || childAgeMonths)) {
            childSection.style.display = 'block';
        } else if (childSection) {
            childSection.style.display = 'none';
        }
    }
    
    submitForm() {
        console.log('submitForm() called'); // Debug log
        
        if (!this.validateCurrentStep()) {
            console.log('Validation failed'); // Debug log
            return;
        }
        
        console.log('Validation passed, collecting data'); // Debug log
        
        // Collect final data
        this.collectStepData();
        
        console.log('Form data collected:', this.formData); // Debug log
        
        // Populate all hidden fields with collected data
        this.populateHiddenFields();
        
        console.log('About to submit form'); // Debug log
        
        // Submit the actual form
        const form = document.getElementById('parentRegistrationForm');
        if (form) {
            // Disable submit button to prevent double submission
            const submitButton = document.querySelector('.next-step');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';
            }
            
            console.log('Submitting form now'); // Debug log
            console.log('Form action:', form.action); // Debug log
            console.log('Form method:', form.method); // Debug log
            
            // Add a small delay to ensure all data is populated
            setTimeout(() => {
                form.submit();
            }, 100);
        } else {
            console.error('Form not found!'); // Debug log
        }
    }
    
    populateHiddenFields() {
        // Map of visible field names to hidden field IDs
        const fieldMappings = {
            'first_name': 'hidden_first_name',
            'middle_name': 'hidden_middle_name',
            'last_name': 'hidden_last_name',
            'birth_date': 'hidden_birth_date',
            'sex': 'hidden_sex',
            'address': 'hidden_address',
            'contact_number': 'hidden_contact_number',
            'email': 'hidden_email',
            'password': 'hidden_password',
            'password_confirmation': 'hidden_password_confirmation',
            'child_first_name': 'hidden_child_first_name',
            'child_last_name': 'hidden_child_last_name',
            'child_age_months': 'hidden_child_age_months',
            'terms': 'hidden_terms'
        };
        
        // Copy values from visible fields to hidden fields
        Object.keys(fieldMappings).forEach(visibleName => {
            const visibleField = document.querySelector(`[name="${visibleName}"]:not([type="hidden"])`);
            const hiddenField = document.getElementById(fieldMappings[visibleName]);
            
            if (visibleField && hiddenField) {
                if (visibleField.type === 'checkbox') {
                    hiddenField.value = visibleField.checked ? '1' : '0';
                } else {
                    hiddenField.value = visibleField.value || '';
                }
                console.log(`Synced ${visibleName}: ${hiddenField.value}`); // Debug log
            }
        });
    }
}

// Initialize wizard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new RegistrationWizard();
});

// Additional utility functions
function editStep(stepNumber) {
    const wizard = new RegistrationWizard();
    wizard.showStep(stepNumber);
}

// Smooth scrolling for better UX
function smoothScrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Call smooth scroll on step change
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('next-step') || e.target.classList.contains('prev-step')) {
        setTimeout(smoothScrollToTop, 100);
    }
});
