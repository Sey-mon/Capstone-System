/**
 * Registration Form Enhancements
 * Handles real-time validation, email availability check, and localStorage state management
 */

(function() {
    'use strict';

    // Email availability check with debouncing
    let emailCheckTimeout;
    const emailInput = document.getElementById('email');
    const emailFeedback = document.createElement('div');
    emailFeedback.className = 'email-feedback';
    emailFeedback.style.marginTop = '5px';
    emailFeedback.style.fontSize = '14px';

    if (emailInput) {
        emailInput.parentNode.appendChild(emailFeedback);

        emailInput.addEventListener('input', function() {
            clearTimeout(emailCheckTimeout);
            const email = this.value.trim();

            if (!email || !isValidEmail(email)) {
                emailFeedback.innerHTML = '';
                emailFeedback.className = 'email-feedback';
                return;
            }

            emailFeedback.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking availability...';
            emailFeedback.className = 'email-feedback checking';
            emailFeedback.style.color = '#666';

            emailCheckTimeout = setTimeout(function() {
                checkEmailAvailability(email);
            }, 500);
        });
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function checkEmailAvailability(email) {
        fetch('/api/check-email-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                emailFeedback.innerHTML = '<i class="fas fa-check-circle"></i> Email available!';
                emailFeedback.style.color = '#28a745';
                emailFeedback.className = 'email-feedback available';
            } else {
                emailFeedback.innerHTML = '<i class="fas fa-times-circle"></i> This email is already registered. <a href="' + (loginRoute || '/login') + '">Login here</a>';
                emailFeedback.style.color = '#dc3545';
                emailFeedback.className = 'email-feedback unavailable';
            }
        })
        .catch(error => {
            console.error('Email check failed:', error);
            emailFeedback.innerHTML = '';
        });
    }

    // Save form progress to localStorage
    function saveFormProgress() {
        const formData = {
            first_name: document.getElementById('first_name')?.value || '',
            middle_name: document.getElementById('middle_name')?.value || '',
            last_name: document.getElementById('last_name')?.value || '',
            suffix: document.getElementById('suffix')?.value || '',
            birth_date: document.getElementById('birth_date')?.value || '',
            sex: document.getElementById('sex')?.value || '',
            contact_number: document.getElementById('contact_number')?.value || '',
            house_street: document.getElementById('house_street')?.value || '',
            barangay: document.getElementById('barangay')?.value || '',
            email: document.getElementById('email')?.value || '',
            custom_patient_id: document.getElementById('custom_patient_id')?.value || '',
            currentStep: parseInt(document.querySelector('.wizard-step.active')?.dataset.step || '1'),
            timestamp: new Date().toISOString()
        };

        try {
            localStorage.setItem('registrationFormData', JSON.stringify(formData));
        } catch (e) {
            console.error('Failed to save form progress:', e);
        }
    }

    // Load form progress from localStorage
    function loadFormProgress() {
        try {
            const savedData = localStorage.getItem('registrationFormData');
            if (!savedData) return null;

            const formData = JSON.parse(savedData);
            const savedTime = new Date(formData.timestamp);
            const now = new Date();
            const hoursDiff = (now - savedTime) / (1000 * 60 * 60);

            // Only restore if saved within last 24 hours
            if (hoursDiff > 24) {
                localStorage.removeItem('registrationFormData');
                return null;
            }

            return formData;
        } catch (e) {
            console.error('Failed to load form progress:', e);
            return null;
        }
    }

    // Restore saved form data
    function restoreFormData(formData) {
        if (!formData) return;

        Object.keys(formData).forEach(key => {
            const input = document.getElementById(key);
            if (input && formData[key] && key !== 'currentStep' && key !== 'timestamp') {
                input.value = formData[key];
            }
        });
    }

    // Auto-save form on input changes
    const formInputs = document.querySelectorAll('#parentRegistrationForm input, #parentRegistrationForm select');
    formInputs.forEach(input => {
        input.addEventListener('change', saveFormProgress);
        input.addEventListener('blur', saveFormProgress);
    });

    // Show restore prompt if saved data exists
    window.addEventListener('DOMContentLoaded', function() {
        const savedData = loadFormProgress();
        if (savedData) {
            const shouldRestore = confirm('We found your previous registration attempt. Would you like to continue where you left off?');
            if (shouldRestore) {
                restoreFormData(savedData);
                // TODO: Navigate to saved step if wizard navigation exists
            } else {
                localStorage.removeItem('registrationFormData');
            }
        }
    });

    // Clear saved data on successful submission
    const registrationForm = document.getElementById('parentRegistrationForm');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function() {
            setTimeout(function() {
                localStorage.removeItem('registrationFormData');
            }, 1000);
        });
    }

    // Enhanced Barangay searchable dropdown
    const barangaySelect = document.getElementById('barangay');
    if (barangaySelect) {
        // Store all barangay options
        const allOptions = Array.from(barangaySelect.options).filter(opt => opt.value !== '');
        
        // Create custom searchable dropdown wrapper
        const customDropdown = document.createElement('div');
        customDropdown.className = 'custom-barangay-dropdown';
        customDropdown.style.position = 'relative';
        
        // Create search input
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = '🔍 Type to search barangays...';
        searchInput.className = 'barangay-search-input';
        searchInput.autocomplete = 'off';
        searchInput.style.cssText = `
            width: 100%;
            padding: 12px 40px 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
            cursor: text;
        `;
        
        // Create dropdown icon
        const dropdownIcon = document.createElement('i');
        dropdownIcon.className = 'fas fa-chevron-down';
        dropdownIcon.style.cssText = `
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
            transition: transform 0.3s ease;
        `;
        
        // Create dropdown list
        const dropdownList = document.createElement('div');
        dropdownList.className = 'barangay-dropdown-list';
        dropdownList.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 2px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
            margin-top: -8px;
        `;
        
        // Create result count indicator
        const resultCount = document.createElement('div');
        resultCount.className = 'barangay-result-count';
        resultCount.style.cssText = `
            padding: 8px 16px;
            font-size: 12px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            font-weight: 500;
        `;
        dropdownList.appendChild(resultCount);
        
        // Populate dropdown items
        function renderDropdownItems(options) {
            // Remove existing items (keep result count)
            const existingItems = dropdownList.querySelectorAll('.barangay-item');
            existingItems.forEach(item => item.remove());
            
            if (options.length === 0) {
                resultCount.textContent = 'No barangays found';
                const noResults = document.createElement('div');
                noResults.className = 'barangay-item no-results';
                noResults.style.cssText = `
                    padding: 16px;
                    text-align: center;
                    color: #94a3b8;
                    font-style: italic;
                `;
                noResults.innerHTML = '<i class="fas fa-search"></i> No matching barangays';
                dropdownList.appendChild(noResults);
                return;
            }
            
            resultCount.textContent = `${options.length} barangay${options.length !== 1 ? 's' : ''} available`;
            
            options.forEach(option => {
                const item = document.createElement('div');
                item.className = 'barangay-item';
                item.dataset.value = option.value;
                item.textContent = option.text;
                item.style.cssText = `
                    padding: 12px 16px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    border-left: 3px solid transparent;
                `;
                
                // Hover effect
                item.addEventListener('mouseenter', function() {
                    this.style.background = '#f0f9ff';
                    this.style.borderLeftColor = '#3b82f6';
                    this.style.paddingLeft = '20px';
                });
                
                item.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('selected')) {
                        this.style.background = 'white';
                        this.style.borderLeftColor = 'transparent';
                        this.style.paddingLeft = '16px';
                    }
                });
                
                // Click to select
                item.addEventListener('click', function() {
                    selectBarangay(option.value, option.text);
                });
                
                // Mark if currently selected
                if (option.selected) {
                    item.classList.add('selected');
                    item.style.background = '#eff6ff';
                    item.style.borderLeftColor = '#3b82f6';
                    item.innerHTML = `<i class="fas fa-check" style="color: #3b82f6; margin-right: 8px;"></i>${option.text}`;
                }
                
                dropdownList.appendChild(item);
            });
        }
        
        // Select barangay function
        function selectBarangay(value, text) {
            barangaySelect.value = value;
            searchInput.value = text;
            searchInput.style.borderColor = '#10b981';
            searchInput.style.background = '#f0fdf4';
            closeDropdown();
            
            // Trigger change event
            barangaySelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        // Open dropdown
        function openDropdown() {
            dropdownList.style.display = 'block';
            searchInput.style.borderRadius = '8px 8px 0 0';
            searchInput.style.borderColor = '#3b82f6';
            dropdownIcon.style.transform = 'translateY(-50%) rotate(180deg)';
        }
        
        // Close dropdown
        function closeDropdown() {
            dropdownList.style.display = 'none';
            searchInput.style.borderRadius = '8px';
            dropdownIcon.style.transform = 'translateY(-50%) rotate(0deg)';
        }
        
        // Search input focus - open dropdown
        searchInput.addEventListener('focus', function() {
            openDropdown();
            renderDropdownItems(allOptions);
            this.select(); // Select all text for easy replacement
        });
        
        // Search input - filter results
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            if (searchTerm === '') {
                renderDropdownItems(allOptions);
                this.style.borderColor = '#3b82f6';
                this.style.background = 'white';
                return;
            }
            
            const filteredOptions = allOptions.filter(option => {
                return option.text.toLowerCase().includes(searchTerm);
            });
            
            renderDropdownItems(filteredOptions);
            openDropdown();
        });
        
        // Keyboard navigation
        let focusedIndex = -1;
        searchInput.addEventListener('keydown', function(e) {
            const items = dropdownList.querySelectorAll('.barangay-item:not(.no-results)');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                focusedIndex = Math.min(focusedIndex + 1, items.length - 1);
                updateFocus(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusedIndex = Math.max(focusedIndex - 1, 0);
                updateFocus(items);
            } else if (e.key === 'Enter' && focusedIndex >= 0) {
                e.preventDefault();
                items[focusedIndex].click();
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });
        
        function updateFocus(items) {
            items.forEach((item, index) => {
                if (index === focusedIndex) {
                    item.style.background = '#dbeafe';
                    item.style.borderLeftColor = '#3b82f6';
                    item.scrollIntoView({ block: 'nearest' });
                } else if (!item.classList.contains('selected')) {
                    item.style.background = 'white';
                    item.style.borderLeftColor = 'transparent';
                }
            });
        }
        
        // Click outside to close
        document.addEventListener('click', function(e) {
            if (!customDropdown.contains(e.target)) {
                closeDropdown();
            }
        });
        
        // Assemble custom dropdown
        customDropdown.appendChild(searchInput);
        customDropdown.appendChild(dropdownIcon);
        customDropdown.appendChild(dropdownList);
        
        // Replace original select with custom dropdown
        barangaySelect.style.display = 'none';
        barangaySelect.parentNode.insertBefore(customDropdown, barangaySelect);
        
        // Initialize with selected value if exists
        const selectedOption = allOptions.find(opt => opt.selected);
        if (selectedOption) {
            searchInput.value = selectedOption.text;
            searchInput.style.borderColor = '#10b981';
            searchInput.style.background = '#f0fdf4';
        }
        
        // Initial render
        renderDropdownItems(allOptions);
    }

    // Client-side step validation
    window.validateCurrentStep = function(stepNumber) {
        let isValid = true;
        const step = document.querySelector(`[data-step="${stepNumber}"]`);
        if (!step) return true;

        const requiredInputs = step.querySelectorAll('input[required], select[required]');
        
        requiredInputs.forEach(input => {
            // Remove previous error styling
            input.style.borderColor = '';
            const errorMsg = input.parentNode.querySelector('.validation-error');
            if (errorMsg) errorMsg.remove();

            if (!input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#dc3545';
                
                const error = document.createElement('div');
                error.className = 'validation-error';
                error.style.color = '#dc3545';
                error.style.fontSize = '12px';
                error.style.marginTop = '5px';
                error.textContent = 'This field is required';
                input.parentNode.appendChild(error);
            }
        });

        // Special validation for Step 2 (email and password)
        if (stepNumber === 2) {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');

            if (emailInput && !isValidEmail(emailInput.value)) {
                isValid = false;
                emailInput.style.borderColor = '#dc3545';
            }

            if (passwordInput && confirmInput && passwordInput.value !== confirmInput.value) {
                isValid = false;
                confirmInput.style.borderColor = '#dc3545';
                
                const error = document.createElement('div');
                error.className = 'validation-error';
                error.style.color = '#dc3545';
                error.style.fontSize = '12px';
                error.style.marginTop = '5px';
                error.textContent = 'Passwords do not match';
                confirmInput.parentNode.appendChild(error);
            }

            // Check if email is available
            const emailFeedbackEl = document.querySelector('.email-feedback');
            if (emailFeedbackEl && emailFeedbackEl.classList.contains('unavailable')) {
                isValid = false;
            }
        }

        return isValid;
    };

})();
