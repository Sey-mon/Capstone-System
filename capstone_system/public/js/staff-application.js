document.addEventListener("DOMContentLoaded", function () {
    console.log("Staff application wizard loaded successfully!");
    
    // Wizard State Management
    let currentStep = 1;
    const totalSteps = 3; // Staff application has 3 steps (Personal, Professional, Account)
    let isTransitioning = false;
    
    // Initialize wizard
    initializeWizard();
    
    function initializeWizard() {
        try {
            setupNavigationButtons();
            setupFormValidation();
            showStep(1, false);
        } catch (error) {
            console.error('Error initializing wizard:', error);
        }
    }
    
    function setupNavigationButtons() {
        // Next buttons
        const nextButtons = document.querySelectorAll('.next-step');
        nextButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (isTransitioning) return;
                
                if (validateCurrentStep()) {
                    nextStep();
                }
            });
        });
        
        // Previous buttons
        const prevButtons = document.querySelectorAll('.prev-step');
        prevButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (isTransitioning) return;
                
                prevStep();
            });
        });
        
        // Form submission
        const form = document.getElementById('nutritionistWizard');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validate final step before submission
                if (!validateCurrentStep()) {
                    e.preventDefault();
                    return false;
                }
                // All validation passed, allow form to submit
            });
        }
    }
    
    function validateCurrentStep() {
        const currentStepElement = document.querySelector(`.wizard-step[data-step="${currentStep}"]`);
        if (!currentStepElement) return false;
        
        const requiredFields = currentStepElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            // Remove previous error states
            field.classList.remove('error');
            const errorSpan = field.parentElement.querySelector('.error-message');
            if (errorSpan) errorSpan.remove();
            
            let errorMessage = '';
            
            // Check if field is empty
            if (!field.value.trim()) {
                isValid = false;
                errorMessage = 'This field is required';
            }
            // Check pattern validation
            else if (field.hasAttribute('pattern')) {
                const pattern = new RegExp(field.getAttribute('pattern'));
                if (!pattern.test(field.value)) {
                    isValid = false;
                    // Use custom title if available, otherwise generic message
                    errorMessage = field.getAttribute('title') || 'Invalid format';
                }
            }
            // Check minlength validation
            else if (field.hasAttribute('minlength')) {
                const minLength = parseInt(field.getAttribute('minlength'));
                if (field.value.length < minLength) {
                    isValid = false;
                    errorMessage = `Minimum ${minLength} characters required`;
                }
            }
            // Check email validation
            else if (field.type === 'email') {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(field.value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
            }
            
            // Display error if validation failed
            if (errorMessage) {
                field.classList.add('error');
                
                const errorMsg = document.createElement('span');
                errorMsg.className = 'error-message';
                errorMsg.textContent = errorMessage;
                errorMsg.style.color = '#ef4444';
                errorMsg.style.fontSize = '0.875rem';
                errorMsg.style.marginTop = '0.25rem';
                errorMsg.style.display = 'block';
                field.parentElement.appendChild(errorMsg);
            }
        });
        
        if (!isValid) {
            // Scroll to first error
            const firstError = currentStepElement.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
        
        return isValid;
    }
    
    function setupFormValidation() {
        // Real-time validation
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                    const errorMsg = this.parentElement.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error') && this.value.trim()) {
                    this.classList.remove('error');
                    const errorMsg = this.parentElement.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });
        });
    }
    
    function nextStep() {
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    }
    
    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    }
    
    function showStep(stepNumber, animate = true) {
        if (isTransitioning) return;
        
        isTransitioning = true;
        
        const allSteps = document.querySelectorAll('.wizard-step');
        const targetStep = document.querySelector(`.wizard-step[data-step="${stepNumber}"]`);
        
        if (!targetStep) {
            isTransitioning = false;
            return;
        }
        
        // Hide all steps
        allSteps.forEach(step => {
            step.classList.remove('active');
            step.style.display = 'none';
        });
        
        // Show target step with animation
        if (animate) {
            targetStep.style.opacity = '0';
            targetStep.style.display = 'block';
            
            setTimeout(() => {
                targetStep.style.transition = 'opacity 0.3s ease';
                targetStep.style.opacity = '1';
                targetStep.classList.add('active');
                
                setTimeout(() => {
                    targetStep.style.transition = '';
                    isTransitioning = false;
                }, 300);
            }, 50);
        } else {
            targetStep.style.display = 'block';
            targetStep.classList.add('active');
            isTransitioning = false;
        }
        
        // Update progress indicator
        updateProgressIndicator(stepNumber);
        
        // Scroll to top of form
        const wizardContainer = document.querySelector('.wizard-container');
        if (wizardContainer) {
            wizardContainer.scrollTop = 0;
        }
    }
    
    function updateProgressIndicator(stepNumber) {
        const progressSteps = document.querySelectorAll('.wizard-progress .step');
        
        progressSteps.forEach((step, index) => {
            const stepNum = index + 1;
            
            if (stepNum < stepNumber) {
                step.classList.add('completed');
                step.classList.remove('active');
            } else if (stepNum === stepNumber) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
    }
});
