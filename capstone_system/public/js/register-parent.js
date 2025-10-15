document.addEventListener("DOMContentLoaded", function () {
    console.log("Parent registration wizard loaded successfully!");
    
    // Wizard State Management
    let currentStep = 1;
    const totalSteps = 4;
    let isTransitioning = false;
    let formData = {};
    
    // Debounce function for performance optimization
    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };
    
    // Initialize wizard with loading animation
    initializeWizard();
    
    function initializeWizard() {
        try {
            // Show loading state
            showLoadingState();
            
            // Set up all functionality
            setupNavigationButtons();
            setupFormDataSync();
            setupSkipFunctionality();
            setupFormValidation();
            setupProgressAnimation();
            setupAccessibility();
            
                        // Initialize first step
            setTimeout(() => {
                initializeDefaultValues();
                showStep(1, false);
                hideLoadingState();
            }, 500);
            
        } catch (error) {
            console.error('Error initializing wizard:', error);
            showAlert('Failed to initialize registration form. Please refresh the page.', 'error');
        }
    }
    
    function setupNavigationButtons() {
        // Next buttons
        const nextButtons = document.querySelectorAll('.next-step');
        nextButtons.forEach(button => {
            button.addEventListener('click', debounce(function(e) {
                e.preventDefault();
                
                if (isTransitioning) return;
                
                if (button.type === 'submit') {
                    return; // Let the form submit naturally
                }
                
                // Add loading state to button
                const originalText = button.textContent;
                button.textContent = 'Validating...';
                button.disabled = true;
                
                // Validate with delay for better UX
                setTimeout(() => {
                    try {
                        if (validateCurrentStep()) {
                            syncCurrentStepData();
                            nextStep();
                        }
                    } catch (error) {
                        console.error('Error during validation:', error);
                        showAlert('An error occurred during validation. Please try again.', 'error');
                    } finally {
                        // Reset button state
                        button.textContent = originalText;
                        button.disabled = false;
                    }
                }, 300);
                
            }, 300));
        });
        
        // Previous buttons
        const prevButtons = document.querySelectorAll('.prev-step');
        prevButtons.forEach(button => {
            button.addEventListener('click', debounce(function(e) {
                e.preventDefault();
                
                if (isTransitioning) return;
                
                syncCurrentStepData();
                prevStep();
            }, 150));
        });
        
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey && !e.metaKey) {
                const activeElement = document.activeElement;
                if (activeElement.tagName !== 'TEXTAREA') {
                    const nextButton = document.querySelector(`.wizard-step[data-step="${currentStep}"] .next-step`);
                    if (nextButton && !nextButton.disabled) {
                        nextButton.click();
                    }
                }
            }
        });
    }
    
    function setupSkipFunctionality() {
        const skipButton = document.querySelector('.skip-child-info');
        if (skipButton) {
            skipButton.addEventListener('click', function(e) {
                e.preventDefault();
                // Clear child info fields
                document.getElementById('child_first_name').value = '';
                document.getElementById('child_last_name').value = '';
                document.getElementById('child_age_months').value = '';
                
                // Sync and move to next step
                syncCurrentStepData();
                nextStep();
            });
        }
    }
    
    function setupFormDataSync() {
        // Sync data when fields change
        const formFields = document.querySelectorAll('#parentRegistrationForm input, #parentRegistrationForm select, #parentRegistrationForm textarea');
        formFields.forEach(field => {
            if (!field.id.startsWith('hidden_') && field.type !== 'hidden') {
                field.addEventListener('input', function() {
                    syncFieldToHidden(field);
                });
                field.addEventListener('change', function() {
                    syncFieldToHidden(field);
                });
            }
        });
    }
    
    function syncFieldToHidden(field) {
        const hiddenField = document.getElementById('hidden_' + field.name);
        if (hiddenField) {
            if (field.type === 'checkbox') {
                hiddenField.value = field.checked ? '1' : '0';
            } else {
                hiddenField.value = field.value;
            }
        }
        
        // Update complete address when any address field changes
        if (['house_street', 'barangay', 'city', 'province'].includes(field.name)) {
            updateCompleteAddress();
        }
    }
    
    function updateCompleteAddress() {
        const houseStreet = document.getElementById('house_street').value || '';
        const barangay = document.getElementById('barangay').value || '';
        const city = document.getElementById('city').value || '';
        const province = document.getElementById('province').value || '';
        
        let completeAddress = '';
        if (houseStreet || barangay || city || province) {
            const addressParts = [houseStreet, barangay, city, province].filter(part => part.trim());
            completeAddress = addressParts.join(', ');
        }
        
        const hiddenAddressField = document.getElementById('hidden_address');
        if (hiddenAddressField) {
            hiddenAddressField.value = completeAddress;
        }
    }
    
    function initializeDefaultValues() {
        // Set default values for city and province
        const cityField = document.getElementById('city');
        const provinceField = document.getElementById('province');
        
        if (cityField && !cityField.value) {
            cityField.value = 'San Pedro';
        }
        
        if (provinceField && !provinceField.value) {
            provinceField.value = 'Laguna';
        }
        
        // Initialize complete address
        updateCompleteAddress();
        
        // Sync all current values to hidden fields
        const formFields = document.querySelectorAll('#parentRegistrationForm input, #parentRegistrationForm select');
        formFields.forEach(field => {
            if (!field.id.startsWith('hidden_') && field.type !== 'hidden') {
                syncFieldToHidden(field);
            }
        });
    }
    
    function syncCurrentStepData() {
        const currentStepElement = document.querySelector(`.wizard-step[data-step="${currentStep}"]`);
        if (currentStepElement) {
            const stepFields = currentStepElement.querySelectorAll('input, select, textarea');
            stepFields.forEach(field => {
                syncFieldToHidden(field);
            });
        }
        
        // Update review section if we're moving from step 3
        if (currentStep >= 3) {
            updateReviewSection();
        }
    }
    
    function updateReviewSection() {
        // Personal Information - Combine name with suffix
        const firstName = document.getElementById('first_name').value || '';
        const middleName = document.getElementById('middle_name').value || '';
        const lastName = document.getElementById('last_name').value || '';
        const suffix = document.getElementById('suffix').value || '';
        
        let fullName = [firstName, middleName, lastName].filter(name => name.trim()).join(' ');
        if (suffix) {
            fullName += `, ${suffix}`;
        }
        
        document.getElementById('review-full-name').textContent = fullName || 'Not provided';
        document.getElementById('review-birth-date').textContent = 
            document.getElementById('birth_date').value || 'Not provided';
        document.getElementById('review-sex').textContent = 
            document.getElementById('sex').value || 'Not provided';
        document.getElementById('review-phone').textContent = 
            document.getElementById('contact_number').value || 'Not provided';
        // Format complete address
        const houseStreet = document.getElementById('house_street').value || '';
        const barangay = document.getElementById('barangay').value || '';
        const city = document.getElementById('city').value || '';
        const province = document.getElementById('province').value || '';
        
        let completeAddress = '';
        if (houseStreet || barangay || city || province) {
            const addressParts = [houseStreet, barangay, city, province].filter(part => part.trim());
            completeAddress = addressParts.join(', ');
        }
        
        document.getElementById('review-address').textContent = completeAddress || 'Not provided';
            
        // Account Information
        document.getElementById('review-email').textContent = 
            document.getElementById('email').value || 'Not provided';
            
        // Child Information
        const childFirstName = document.getElementById('child_first_name').value;
        const childLastName = document.getElementById('child_last_name').value;
        const childAge = document.getElementById('child_age_months').value;
        
        document.getElementById('review-child-name').textContent = 
            (childFirstName && childLastName) ? `${childFirstName} ${childLastName}` : 'Not provided';
        document.getElementById('review-child-age').textContent = 
            childAge ? `${childAge} months` : 'Not provided';
    }
    
    function validateCurrentStep() {
        const currentStepElement = document.querySelector(`.wizard-step[data-step="${currentStep}"]`);
        if (!currentStepElement) return false;
        
        const requiredFields = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        let firstInvalidField = null;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#dc3545';
                isValid = false;
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
            } else {
                field.style.borderColor = '#dcdcdc';
            }
        });
        
        // Step-specific validation
        if (currentStep === 2) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            if (password && confirmPassword && password !== confirmPassword) {
                showAlert('Passwords do not match!', 'error');
                document.getElementById('password_confirmation').style.borderColor = '#dc3545';
                isValid = false;
            }
            
            if (password && password.length < 8) {
                showAlert('Password must be at least 8 characters long!', 'error');
                document.getElementById('password').style.borderColor = '#dc3545';
                isValid = false;
            }
        }
        
        if (!isValid) {
            if (firstInvalidField) {
                try {
                    firstInvalidField.focus();
                } catch (e) {
                    console.warn('Could not focus on invalid field:', e);
                }
            }
            if (currentStep !== 2) { // Don't show generic message if we already showed password-specific message
                try {
                    showAlert('Please fill in all required fields.', 'error');
                } catch (e) {
                    console.error('Error showing alert:', e);
                }
            }
        }
        
        return isValid;
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
        currentStep = stepNumber;
        
        const allSteps = document.querySelectorAll('.wizard-step');
        const targetStep = document.querySelector(`.wizard-step[data-step="${stepNumber}"]`);
        
        if (!targetStep) {
            console.error(`Step ${stepNumber} not found`);
            isTransitioning = false;
            return;
        }
        
        if (animate) {
            // Fade out current step
            const currentActive = document.querySelector('.wizard-step.active');
            if (currentActive) {
                currentActive.style.opacity = '0';
                currentActive.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    allSteps.forEach(step => step.classList.remove('active'));
                    targetStep.classList.add('active');
                    
                    // Animate in new step
                    targetStep.style.opacity = '0';
                    targetStep.style.transform = 'translateX(20px)';
                    
                    requestAnimationFrame(() => {
                        targetStep.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                        targetStep.style.opacity = '1';
                        targetStep.style.transform = 'translateX(0)';
                    });
                    
                }, 200);
            } else {
                targetStep.classList.add('active');
            }
        } else {
            allSteps.forEach(step => step.classList.remove('active'));
            targetStep.classList.add('active');
        }
        
        // Update progress indicator with animation
        updateProgressIndicator(stepNumber);
        
        // Smooth scroll to top
        targetStep.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        
        // Focus management with delay
        setTimeout(() => {
            const firstInput = targetStep.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput && !firstInput.hasAttribute('readonly')) {
                firstInput.focus();
                firstInput.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
            
            isTransitioning = false;
        }, animate ? 500 : 100);
        
        // Announce to screen readers
        announceStepChange(stepNumber);
        
        console.log(`Showing step ${stepNumber}`);
    }
    
    function updateProgressIndicator(stepNumber) {
        const progressSteps = document.querySelectorAll('.wizard-progress .step');
        const progressBar = document.querySelector('.wizard-progress::after');
        
        progressSteps.forEach((step, index) => {
            const stepNum = index + 1;
            const stepNumber_elem = step.querySelector('.step-number');
            
            if (stepNum < stepNumber) {
                step.classList.add('completed');
                step.classList.remove('active');
                stepNumber_elem.textContent = '✓';
            } else if (stepNum === stepNumber) {
                step.classList.add('active');
                step.classList.remove('completed');
                stepNumber_elem.textContent = stepNum;
            } else {
                step.classList.remove('active', 'completed');
                stepNumber_elem.textContent = stepNum;
            }
        });
        
        // Update progress bar width with animation
        const progressContainer = document.querySelector('.wizard-progress');
        if (progressContainer) {
            const progressWidth = ((stepNumber - 1) / (totalSteps - 1)) * 70; // 70% max width
            progressContainer.style.setProperty('--progress-width', `${progressWidth}%`);
        }
    }
    
    function setupProgressAnimation() {
        // Add CSS custom property for progress bar
        const style = document.createElement('style');
        style.textContent = `
            .wizard-progress {
                --progress-width: 0%;
            }
            .wizard-progress::after {
                width: var(--progress-width) !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    function setupFormValidation() {
        const form = document.getElementById('parentRegistrationForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Sync all data before final validation
                syncCurrentStepData();
                
                // Clean phone number before validation and submission
                const phoneField = document.getElementById('contact_number');
                if (phoneField) {
                    const cleanPhone = phoneField.value.replace(/\D/g, '');
                    // Temporarily store clean phone for submission
                    phoneField.setAttribute('data-original-value', phoneField.value);
                    phoneField.value = cleanPhone;
                }
                
                // Final validation
                if (!validateFinalSubmission()) {
                    // Restore original phone format if validation fails
                    if (phoneField && phoneField.hasAttribute('data-original-value')) {
                        phoneField.value = phoneField.getAttribute('data-original-value');
                        phoneField.removeAttribute('data-original-value');
                    }
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating Account...';
                
                console.log('Form submitted successfully');
                
                // Re-enable button after 10 seconds as fallback
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }, 10000);
            });
        }
    }
    
    function validateFinalSubmission() {
        // First, sanitize phone number for validation
        const phoneField = document.getElementById('contact_number');
        if (phoneField) {
            const cleanPhone = phoneField.value.replace(/\D/g, '');
            // Set a clean phone number without spaces for form submission validation
            phoneField.setAttribute('data-clean-value', cleanPhone);
        }
        
        // Check all required fields across all steps
        const requiredFields = ['first_name', 'last_name', 'birth_date', 'sex', 'contact_number', 'house_street', 'barangay', 'city', 'province', 'email', 'password'];
        const missingFields = [];
        
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field || !field.value.trim()) {
                missingFields.push(fieldName);
            }
        });
        
        if (missingFields.length > 0) {
            showAlert(`Missing required fields: ${missingFields.join(', ')}`, 'error');
            return false;
        }
        
        // Special validation for phone number
        if (phoneField) {
            const cleanPhone = phoneField.value.replace(/\D/g, '');
            if (cleanPhone.length !== 11 || !cleanPhone.startsWith('09')) {
                showAlert('Please enter a valid 11-digit Philippine mobile number starting with 09', 'error');
                return false;
            }
        }
        
        // Password validation
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        
        if (password !== confirmPassword) {
            showAlert('Passwords do not match!', 'error');
            return false;
        }
        
        // Terms validation
        const termsCheckbox = document.getElementById('terms');
        if (!termsCheckbox.checked) {
            showAlert('Please agree to the Terms and Conditions to continue.', 'error');
            return false;
        }
        
        return true;
    }
    
    // Enhanced alert function with animations
    function showAlert(message, type = 'error') {
        try {
            // Remove existing alerts with animation
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => {
                try {
                    alert.style.transform = 'translateY(-20px)';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert && alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                } catch (e) {
                    console.warn('Error removing existing alert:', e);
                }
            });
        } catch (e) {
            console.error('Error in showAlert cleanup:', e);
        }
        
        try {
            // Create new alert with enhanced styling
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.setAttribute('role', 'alert');
            alert.setAttribute('aria-live', 'polite');
            
            const icon = type === 'error' ? '⚠️' : type === 'success' ? '✅' : 'ℹ️';
            alert.innerHTML = `
                <div class="alert-content">
                    <span class="alert-icon">${icon}</span>
                    <p>${message}</p>
                    <button class="alert-close" aria-label="Close alert">&times;</button>
                </div>
            `;
            
            // Insert at top of container
            const container = document.querySelector('.register-container');
            
            if (!container) {
                console.error('Container not found, falling back to console message:', message);
                return;
            }
            
            const wizardHeader = container.querySelector('.wizard-header');
            
            if (wizardHeader) {
                // Insert after wizard header
                wizardHeader.insertAdjacentElement('afterend', alert);
            } else {
                // Fallback: insert at the beginning of container
                container.insertAdjacentElement('afterbegin', alert);
            }
            
            // Animate in
            alert.style.transform = 'translateY(-20px)';
            alert.style.opacity = '0';
            requestAnimationFrame(() => {
                alert.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                alert.style.transform = 'translateY(0)';
                alert.style.opacity = '1';
            });
            
            // Close button functionality
            const closeBtn = alert.querySelector('.alert-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    alert.style.transform = 'translateY(-20px)';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert && alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                });
            }
            
            // Scroll to alert
            setTimeout(() => {
                if (alert && alert.scrollIntoView) {
                    alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100);
        
            // Auto-remove after 8 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.transform = 'translateY(-20px)';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 8000);
            
        } catch (e) {
            console.error('Error showing alert:', e);
            // Fallback to console if alert fails
            console.log(`Alert (${type}): ${message}`);
        }
    }
    
    // Loading state functions
    function showLoadingState() {
        const container = document.querySelector('.register-container');
        const loader = document.createElement('div');
        loader.className = 'wizard-loader';
        loader.innerHTML = `
            <div class="loader-spinner"></div>
            <p>Initializing registration form...</p>
        `;
        container.appendChild(loader);
    }
    
    function hideLoadingState() {
        const loader = document.querySelector('.wizard-loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => loader.remove(), 300);
        }
    }
    
    // Accessibility functions
    function setupAccessibility() {
        // Add ARIA labels to progress steps
        const progressSteps = document.querySelectorAll('.wizard-progress .step');
        progressSteps.forEach((step, index) => {
            const stepNum = index + 1;
            const label = step.querySelector('.step-label').textContent;
            step.setAttribute('aria-label', `Step ${stepNum}: ${label}`);
            step.setAttribute('role', 'tab');
        });
        
        // Add form section roles
        const wizardSteps = document.querySelectorAll('.wizard-step');
        wizardSteps.forEach((step, index) => {
            step.setAttribute('role', 'tabpanel');
            step.setAttribute('aria-labelledby', `step-${index + 1}-label`);
        });
    }
    
    function announceStepChange(stepNumber) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = `Now on step ${stepNumber} of ${totalSteps}`;
        
        document.body.appendChild(announcement);
        setTimeout(() => announcement.remove(), 1000);
    }
    
    // Real-time field validation with debouncing
    function setupRealTimeValidation() {
        const validateField = debounce((field) => {
            const value = field.value.trim();
            const fieldName = field.name;
            let isValid = true;
            let message = '';
            
            // Field-specific validation
            switch (fieldName) {
                case 'email':
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    isValid = emailPattern.test(value);
                    message = isValid ? '' : 'Please enter a valid email address';
                    break;
                case 'contact_number':
                    // Remove all non-digits for validation
                    const cleanPhone = value.replace(/\D/g, '');
                    const phonePattern = /^09\d{9}$/;
                    isValid = phonePattern.test(cleanPhone) && cleanPhone.length === 11;
                    message = isValid ? '' : 'Please enter a valid 11-digit Philippine mobile number (09XXXXXXXXX)';
                    break;
                case 'password':
                    isValid = value.length >= 8;
                    message = isValid ? '' : 'Password must be at least 8 characters long';
                    break;
            }
            
            // Update field styling and message
            updateFieldValidation(field, isValid, message);
        }, 500);
        
        // Attach to all form fields
        const formFields = document.querySelectorAll('#parentRegistrationForm input, #parentRegistrationForm select, #parentRegistrationForm textarea');
        formFields.forEach(field => {
            if (field.type !== 'hidden' && !field.id.startsWith('hidden_')) {
                field.addEventListener('input', () => validateField(field));
                field.addEventListener('blur', () => validateField(field));
            }
        });
    }
    
    function updateFieldValidation(field, isValid, message) {
        // Remove existing validation styling
        field.classList.remove('field-valid', 'field-invalid');
        
        // Add appropriate styling
        if (field.value.trim()) {
            field.classList.add(isValid ? 'field-valid' : 'field-invalid');
        }
        
        // Update or create validation message
        let messageElement = field.parentElement.querySelector('.validation-message');
        if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.className = 'validation-message';
            field.parentElement.appendChild(messageElement);
        }
        
        messageElement.textContent = message;
        messageElement.className = `validation-message ${isValid ? 'valid' : 'invalid'}`;
    }
    
    // Input formatting functions
    function setupInputFormatting() {
        // Phone number validation (no auto-formatting, just validation)
        const phoneInput = document.getElementById('contact_number');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                // Only allow numbers
                let value = e.target.value.replace(/\D/g, '');
                
                // Limit to 11 digits maximum
                if (value.length > 11) {
                    value = value.slice(0, 11);
                }
                
                e.target.value = value;
                
                // Simple validation feedback
                const isValid = value.length === 11 && value.startsWith('09');
                if (value.length > 0) {
                    if (isValid) {
                        e.target.classList.add('field-valid');
                        e.target.classList.remove('field-invalid');
                    } else {
                        e.target.classList.add('field-invalid');
                        e.target.classList.remove('field-valid');
                    }
                } else {
                    e.target.classList.remove('field-valid', 'field-invalid');
                }
            });
            
            phoneInput.addEventListener('blur', function(e) {
                const value = e.target.value;
                if (value.length > 0 && (value.length !== 11 || !value.startsWith('09'))) {
                    // Show validation message
                    let messageElement = e.target.parentElement.querySelector('.validation-message');
                    if (!messageElement) {
                        messageElement = document.createElement('div');
                        messageElement.className = 'validation-message invalid';
                        e.target.parentElement.appendChild(messageElement);
                    }
                    messageElement.textContent = 'Please enter a valid 11-digit Philippine mobile number starting with 09';
                } else {
                    // Remove validation message
                    const messageElement = e.target.parentElement.querySelector('.validation-message');
                    if (messageElement) {
                        messageElement.remove();
                    }
                }
            });
        }
        
        // Name fields - capitalize first letter
        const nameFields = ['first_name', 'last_name', 'child_first_name', 'child_last_name'];
        nameFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', function(e) {
                    let value = e.target.value;
                    // Remove numbers and special characters
                    value = value.replace(/[^a-zA-Z\s]/g, '');
                    // Capitalize first letter of each word
                    value = value.replace(/\b\w/g, letter => letter.toUpperCase());
                    e.target.value = value;
                });
            }
        });
    }
    
    // Enhanced password strength checking
    function enhancedPasswordValidation() {
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('password_confirmation');
        
        if (!passwordField) return;
        
        passwordField.addEventListener('input', debounce(function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrengthUI(strength);
            
            // Check confirmation match if confirm field has value
            if (confirmField && confirmField.value) {
                checkPasswordMatch();
            }
        }, 300));
        
        if (confirmField) {
            confirmField.addEventListener('input', debounce(checkPasswordMatch, 300));
        }
    }
    
    function calculatePasswordStrength(password) {
        // Handle undefined or null password
        if (!password || typeof password !== 'string') {
            return {
                requirements: {
                    length: false,
                    uppercase: false,
                    lowercase: false,
                    number: false,
                    special: false
                },
                score: 0,
                level: 'weak'
            };
        }
        
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[@$!%*?&#]/.test(password)
        };
        
        const score = Object.values(requirements).filter(Boolean).length;
        const strength = {
            requirements,
            score,
            level: score <= 2 ? 'weak' : score <= 3 ? 'medium' : score <= 4 ? 'strong' : 'excellent'
        };
        
        return strength;
    }
    
    function updatePasswordStrengthUI(strength) {
        // Update requirement indicators
        Object.keys(strength.requirements).forEach(req => {
            const element = document.querySelector(`[data-requirement="${req}"]`);
            if (element) {
                element.classList.toggle('met', strength.requirements[req]);
            }
        });
        
        // Update strength indicator
        let strengthIndicator = document.querySelector('.password-strength-indicator');
        if (!strengthIndicator) {
            strengthIndicator = document.createElement('div');
            strengthIndicator.className = 'password-strength-indicator';
            const passwordField = document.getElementById('password');
            passwordField.parentElement.appendChild(strengthIndicator);
        }
        
        strengthIndicator.className = `password-strength-indicator strength-${strength.level}`;
        strengthIndicator.textContent = `Password strength: ${strength.level.charAt(0).toUpperCase() + strength.level.slice(1)}`;
    }
    
    function checkPasswordMatch() {
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('password_confirmation');
        
        if (!passwordField || !confirmField) return;
        
        const password = passwordField.value;
        const confirm = confirmField.value;
        
        if (!confirm) return;
        
        const matches = password === confirm;
        
        // Update confirm field styling
        confirmField.classList.toggle('field-valid', matches);
        confirmField.classList.toggle('field-invalid', !matches);
        
        // Update or create match indicator
        let matchIndicator = confirmField.parentElement.querySelector('.password-match-indicator');
        if (!matchIndicator) {
            matchIndicator = document.createElement('div');
            matchIndicator.className = 'password-match-indicator';
            confirmField.parentElement.appendChild(matchIndicator);
        }
        
        matchIndicator.className = `password-match-indicator ${matches ? 'match' : 'no-match'}`;
        matchIndicator.textContent = matches ? '✓ Passwords match' : '✗ Passwords do not match';
    }
    
    // Additional functionality from Blade template
    function setupCSRFTokenRefresh() {
        window.refreshCSRFToken = function() {
            fetch('/csrf-token', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.csrf_token) {
                    // Update the CSRF token in the form
                    const csrfInput = document.querySelector('input[name="_token"]');
                    if (csrfInput) {
                        csrfInput.value = data.csrf_token;
                    }
                    // Update meta tag
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    if (csrfMeta) {
                        csrfMeta.content = data.csrf_token;
                    }
                    console.log('CSRF token refreshed');
                }
            })
            .catch(error => {
                console.error('Failed to refresh CSRF token:', error);
            });
        };
    }

    function setupFormDebugLogging() {
        const form = document.getElementById('parentRegistrationForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submission intercepted');
                console.log('Form action:', this.action);
                console.log('Form method:', this.method);
                
                // Verify CSRF token is present
                const csrfToken = document.querySelector('input[name="_token"]');
                console.log('CSRF token validation:', csrfToken ? 'OK' : 'Missing');
                
                // Check required fields
                const requiredFields = ['first_name', 'last_name', 'email', 'password'];
                const missingFields = [];
                
                requiredFields.forEach(field => {
                    const input = document.querySelector(`input[name="${field}"]`);
                    if (!input || !input.value.trim()) {
                        missingFields.push(field);
                    }
                });
                
                if (missingFields.length > 0) {
                    console.error('Missing required fields:', missingFields);
                }
                
                // Form data validation completed successfully
                console.log('Form submission proceeding...');
            });
        }
    }

    function setupPasswordVisibilityToggle() {
        const passwordToggles = document.querySelectorAll('.password-visibility-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const targetId = this.getAttribute('data-target');
                const targetField = document.getElementById(targetId);
                const showText = this.querySelector('.show-text');
                const hideText = this.querySelector('.hide-text');
                
                if (targetField && showText && hideText) {
                    if (targetField.type === 'password') {
                        targetField.type = 'text';
                        showText.style.display = 'none';
                        hideText.style.display = 'block';
                    } else {
                        targetField.type = 'password';
                        showText.style.display = 'block';
                        hideText.style.display = 'none';
                    }
                }
            });
        });
    }

    function setupCustomContactNumberValidation() {
        const contactNumberField = document.getElementById('contact_number');
        if (contactNumberField) {
            // Simple validation without auto-formatting
            function validateContactNumber(value) {
                const pattern = /^09[0-9]{9}$/;
                return pattern.test(value);
            }
            
            contactNumberField.addEventListener('input', function(e) {
                // Only allow digits, no auto-formatting
                let value = e.target.value.replace(/\D/g, '');
                
                // Limit to 11 digits
                if (value.length > 11) {
                    value = value.substring(0, 11);
                }
                
                e.target.value = value;
                
                // Visual feedback only
                if (value.length === 11 && validateContactNumber(value)) {
                    e.target.classList.add('field-valid');
                    e.target.classList.remove('field-invalid');
                    e.target.setCustomValidity('');
                } else if (value.length > 0) {
                    e.target.classList.add('field-invalid');
                    e.target.classList.remove('field-valid');
                    if (value.length < 11) {
                        e.target.setCustomValidity('Contact number must be 11 digits long.');
                    } else if (!value.startsWith('09')) {
                        e.target.setCustomValidity('Mobile number must start with 09.');
                    }
                } else {
                    e.target.classList.remove('field-valid', 'field-invalid');
                    e.target.setCustomValidity('');
                }
            });

            contactNumberField.addEventListener('blur', function() {
                const value = this.value;
                if (value.length === 11 && validateContactNumber(value)) {
                    this.classList.add('field-valid');
                    this.classList.remove('field-invalid');
                    this.setCustomValidity('');
                } else if (value.length > 0) {
                    this.classList.add('field-invalid');
                    this.classList.remove('field-valid');
                    if (value.length < 11) {
                        this.setCustomValidity('Contact number must be 11 digits long.');
                    } else {
                        this.setCustomValidity('Please enter a valid Philippine mobile number starting with 09.');
                    }
                }
            });
        }
    }

    function setupEnhancedDateInputStyling() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = '#66bb6a';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.style.borderColor = '#e2e8f0';
                }
            });
        });
    }

    // Initialize all enhancements including new functions
    setTimeout(() => {
        setupRealTimeValidation();
        setupInputFormatting();
        enhancedPasswordValidation();
        setupCSRFTokenRefresh();
        setupFormDebugLogging();
        setupPasswordVisibilityToggle();
        setupCustomContactNumberValidation();
        setupEnhancedDateInputStyling();
    }, 1000);
});
