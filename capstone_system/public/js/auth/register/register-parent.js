document.addEventListener("DOMContentLoaded", function () {
    // Wizard State Management
    let currentStep = 1;
    const totalSteps = 4;
    let isTransitioning = false;
    
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
            setupPasswordToggle();
            
                        // Initialize first step
            setTimeout(() => {
                initializeDefaultValues();
                showStep(1, false);
                hideLoadingState();
            }, 500);
            
        } catch (error) {
            showAlert('Failed to initialize registration form. Please refresh the page.', 'error');
        }
    }
    
    function setupNavigationButtons() {
        // Next buttons
        const nextButtons = document.querySelectorAll('.next-step');
        
        nextButtons.forEach((button, index) => {
            button.addEventListener('click', debounce(function(e) {
                e.preventDefault();
                
                if (isTransitioning) {
                    return;
                }
                
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
                // Clear patient ID field
                document.getElementById('custom_patient_id').value = '';
                
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
        const patientId = document.getElementById('custom_patient_id').value;
        
        document.getElementById('review-patient-id').textContent = 
            patientId || 'Not provided - You can link your child later';
    }
    
    function validateCurrentStep() {
        const currentStepElement = document.querySelector(`.wizard-step[data-step="${currentStep}"]`);
        if (!currentStepElement) {
            return false;
        }
        
        const requiredFields = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        let firstInvalidField = null;
        
        requiredFields.forEach(field => {
            const fieldValue = field.value;
            const isEmpty = !fieldValue || (typeof fieldValue === 'string' && fieldValue.trim() === '');
            
            if (isEmpty) {
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
                    // Could not focus on invalid field
                }
            }
            if (currentStep !== 2) { // Don't show generic message if we already showed password-specific message
                showAlert('Please fill in all required fields.', 'error');
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
                    // Error removing existing alert
                }
            });
        } catch (e) {
            // Error in showAlert cleanup
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
            const container = document.querySelector('.wizard-container');
            
            if (!container) {
                // Try to find the login-card as fallback
                const fallbackContainer = document.querySelector('.login-card');
                if (fallbackContainer) {
                    fallbackContainer.insertAdjacentElement('afterbegin', alert);
                    return;
                }
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
            // Error showing alert
        }
    }
    
    // Loading state functions
    function showLoadingState() {
        const container = document.querySelector('.wizard-container') || document.querySelector('.login-card');
        if (!container) {
            return;
        }
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
    
    // Password visibility toggle
    function setupPasswordToggle() {
        const toggleButtons = document.querySelectorAll('.password-visibility-toggle');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const targetId = this.getAttribute('data-target');
                const passwordField = document.getElementById(targetId);
                
                if (!passwordField) return;
                
                const showIcon = this.querySelector('.show-icon');
                const hideIcon = this.querySelector('.hide-icon');
                
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    showIcon.style.display = 'none';
                    hideIcon.style.display = 'inline';
                } else {
                    passwordField.type = 'password';
                    showIcon.style.display = 'inline';
                    hideIcon.style.display = 'none';
                }
            });
        });
    }
    
    // Terms and Privacy link tracking
    function setupTermsAndPrivacy() {
        let termsViewed = false;
        let privacyViewed = false;
        
        const termsCheckbox = document.getElementById('terms');
        const termsNotice = document.getElementById('termsNotice');
        const submitBtn = document.getElementById('submitBtn');
        
        // Check if elements exist
        if (!termsCheckbox || !submitBtn) {
            return;
        }
        
        // Get all Terms and Privacy links (including ones in checkbox label)
        const allTermsLinks = document.querySelectorAll('a[href*="terms"]');
        const allPrivacyLinks = document.querySelectorAll('a[href*="privacy"]');
        
        // Track when any Terms link is clicked
        allTermsLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't prevent default - let link open
                termsViewed = true;
                setTimeout(() => {
                    checkAndEnableCheckbox();
                }, 300);
            });
        });
        
        // Track when any Privacy link is clicked
        allPrivacyLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't prevent default - let link open
                privacyViewed = true;
                setTimeout(() => {
                    checkAndEnableCheckbox();
                }, 300);
            });
        });
        
        // Enable checkbox only after both links are clicked
        function checkAndEnableCheckbox() {
            if (termsViewed && privacyViewed && termsCheckbox) {
                termsCheckbox.disabled = false;
                if (termsNotice) {
                    termsNotice.style.display = 'none';
                }
            }
        }
        
        // Enable submit button only when checkbox is checked
        function checkSubmitButton() {
            if (submitBtn && termsCheckbox) {
                if (termsCheckbox.checked) {
                    submitBtn.disabled = false;
                    submitBtn.style.cursor = 'pointer';
                } else {
                    submitBtn.disabled = true;
                    submitBtn.style.cursor = 'not-allowed';
                }
            }
        }
        
        // Ensure checkbox value syncs to hidden field and enables submit button
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', function() {
                const hiddenTerms = document.getElementById('hidden_terms');
                if (hiddenTerms) {
                    hiddenTerms.value = this.checked ? '1' : '0';
                }
                checkSubmitButton();
            });
            
            // Initial check on page load
            checkSubmitButton();
        }
        
        // Prevent form submission if checkbox is not checked
        const form = document.getElementById('parentRegistrationForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!termsCheckbox.checked) {
                    e.preventDefault();
                    showAlert('Please check the Terms and Conditions checkbox to continue.', 'error');
                    return false;
                }
                if (!termsViewed || !privacyViewed) {
                    e.preventDefault();
                    showAlert('Please read both the Terms and Conditions and Privacy Policy before continuing.', 'error');
                    return false;
                }
            });
        }
    }
    
    // Initialize terms and privacy with a small delay to ensure DOM is ready
    setTimeout(() => {
        setupTermsAndPrivacy();
    }, 500);
});
