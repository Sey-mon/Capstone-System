/**
 * Nutritionist Registration Wizard
 * Handles multi-step form navigation and file upload functionality
 */

class NutritionistWizard {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 3;
        this.formData = new FormData();
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.updateProgress();
        this.setupFileUpload();
        this.setupFormValidation();
    }
    
    setupEventListeners() {
        // Next/Previous button handlers
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-next')) {
                e.preventDefault();
                this.nextStep();
            }
            
            if (e.target.classList.contains('btn-prev')) {
                e.preventDefault();
                this.prevStep();
            }
        });
        
        // Form submission handler
        const form = document.getElementById('nutritionistWizard');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        // Real-time validation
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('form-control')) {
                this.validateField(e.target);
            }
        });
    }
    
    nextStep() {
        if (this.validateCurrentStep()) {
            this.saveCurrentStepData();
            
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.showStep(this.currentStep);
                this.updateProgress();
            }
        }
    }
    
    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateProgress();
        }
    }
    
    showStep(step) {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Show current step
        const currentContent = document.getElementById(`step-${step}`);
        if (currentContent) {
            currentContent.classList.add('active');
        }
        
        // Update step indicators
        document.querySelectorAll('.step').forEach((stepEl, index) => {
            stepEl.classList.remove('active', 'completed');
            
            if (index + 1 < step) {
                stepEl.classList.add('completed');
            } else if (index + 1 === step) {
                stepEl.classList.add('active');
            }
        });
        
        // Update step progress line
        const progressLine = document.querySelector('.step-progress');
        if (progressLine) {
            const progressPercent = ((step - 1) / (this.totalSteps - 1)) * 100;
            progressLine.style.width = `${progressPercent}%`;
        }
    }
    
    updateProgress() {
        const progressBar = document.querySelector('.wizard-progress-bar');
        if (progressBar) {
            const progressPercent = (this.currentStep / this.totalSteps) * 100;
            progressBar.style.width = `${progressPercent}%`;
        }
        
        // Update button visibility
        const prevBtn = document.querySelector('.btn-prev');
        const nextBtn = document.querySelector('.btn-next');
        const submitBtn = document.querySelector('.btn-submit');
        
        if (prevBtn) {
            prevBtn.style.display = this.currentStep === 1 ? 'none' : 'flex';
        }
        
        if (nextBtn && submitBtn) {
            if (this.currentStep === this.totalSteps) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'flex';
            } else {
                nextBtn.style.display = 'flex';
                submitBtn.style.display = 'none';
            }
        }
    }
    
    validateCurrentStep() {
        const currentStepElement = document.getElementById(`step-${this.currentStep}`);
        if (!currentStepElement) return false;
        
        const requiredFields = currentStepElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        // Additional validation for step 3 (file upload)
        if (this.currentStep === 3) {
            const fileInput = document.getElementById('professional_id');
            if (fileInput && !fileInput.files.length) {
                this.showFieldError(fileInput, 'Professional ID document is required');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        // Clear previous errors
        this.clearFieldError(field);
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            errorMessage = `${this.getFieldLabel(field)} is required`;
            isValid = false;
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                errorMessage = 'Please enter a valid email address';
                isValid = false;
            }
        }
        
        // Phone validation
        if (field.type === 'tel' && value) {
            const phoneRegex = /^[\d\s\-\+\(\)]+$/;
            if (!phoneRegex.test(value)) {
                errorMessage = 'Please enter a valid phone number';
                isValid = false;
            }
        }
        
        // Password validation
        if (field.name === 'password' && value) {
            if (value.length < 6) {
                errorMessage = 'Password must be at least 6 characters long';
                isValid = false;
            }
        }
        
        // Confirm password validation
        if (field.name === 'password_confirmation' && value) {
            const password = document.querySelector('input[name="password"]');
            if (password && value !== password.value) {
                errorMessage = 'Passwords do not match';
                isValid = false;
            }
        }
        
        if (!isValid) {
            this.showFieldError(field, errorMessage);
        }
        
        return isValid;
    }
    
    getFieldLabel(field) {
        const label = field.closest('.form-group')?.querySelector('label');
        return label ? label.textContent.replace('*', '').trim() : field.name;
    }
    
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let errorElement = field.parentNode.querySelector('.error-text');
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = 'error-text';
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorElement = field.parentNode.querySelector('.error-text');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
    
    saveCurrentStepData() {
        const currentStepElement = document.getElementById(`step-${this.currentStep}`);
        if (!currentStepElement) return;
        
        const formElements = currentStepElement.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            if (element.type === 'file') {
                if (element.files.length > 0) {
                    this.formData.set(element.name, element.files[0]);
                }
            } else {
                this.formData.set(element.name, element.value);
            }
        });
    }
    
    setupFileUpload() {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('professional_id');
        const fileInfo = document.getElementById('fileInfo');
        
        if (!uploadArea || !fileInput) return;
        
        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFileSelection(files[0], fileInput, fileInfo);
            }
        });
        
        // Click to upload
        uploadArea.addEventListener('click', (e) => {
            if (!e.target.closest('.btn') && e.target !== fileInput) {
                fileInput.click();
            }
        });
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFileSelection(e.target.files[0], fileInput, fileInfo);
            }
        });
    }
    
    handleFileSelection(file, fileInput, fileInfo) {
        // Validate file
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!allowedTypes.includes(file.type)) {
            this.showFileError('Please select a valid file type (JPEG, PNG, or PDF)');
            return;
        }
        
        if (file.size > maxSize) {
            this.showFileError('File size must be less than 5MB');
            return;
        }
        
        // Update file input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        
        // Show file info
        this.showFileInfo(file, fileInfo);
        this.clearFileError();
    }
    
    showFileInfo(file, fileInfo) {
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        const fileType = file.type.includes('image') ? 'image' : 'pdf';
        const iconClass = fileType === 'image' ? 'fa-image' : 'fa-file-pdf';
        
        fileInfo.innerHTML = `
            <div class="file-details">
                <div class="file-icon">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="file-meta">
                    <h4>${file.name}</h4>
                    <p>${fileSize} MB • ${file.type}</p>
                </div>
            </div>
        `;
        
        fileInfo.classList.add('show');
    }
    
    showFileError(message) {
        const uploadArea = document.getElementById('uploadArea');
        let errorElement = document.querySelector('.upload-error');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-text upload-error';
            uploadArea.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    
    clearFileError() {
        const errorElement = document.querySelector('.upload-error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
    
    setupFormValidation() {
        // Real-time password confirmation
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');
        
        if (confirmPassword && password) {
            confirmPassword.addEventListener('input', () => {
                if (confirmPassword.value && confirmPassword.value !== password.value) {
                    this.showFieldError(confirmPassword, 'Passwords do not match');
                } else {
                    this.clearFieldError(confirmPassword);
                }
            });
        }
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateCurrentStep()) {
            return;
        }
        
        this.saveCurrentStepData();
        
        // Show loading state
        const submitBtn = document.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Prepare form data for submission
        const form = document.getElementById('nutritionistWizard');
        const formData = new FormData(form);
        
        // Submit the form
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            } else {
                const text = await response.text();
                throw new Error('Server returned HTML instead of JSON: ' + text);
            }
        })
        .then(data => {
            if (data.success) {
                this.showSuccess();
            } else {
                // Laravel validation errors are often an object: { field: [msg1, msg2] }
                let errorMessages = [];
                if (data.errors) {
                    if (typeof data.errors === 'object') {
                        for (const key in data.errors) {
                            if (Array.isArray(data.errors[key])) {
                                errorMessages = errorMessages.concat(data.errors[key]);
                            } else {
                                errorMessages.push(data.errors[key]);
                            }
                        }
                    } else if (Array.isArray(data.errors)) {
                        errorMessages = data.errors;
                    } else {
                        errorMessages = [data.errors];
                    }
                } else {
                    errorMessages = ['An error occurred. Please try again.'];
                }
                this.showErrors(errorMessages);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showErrors(['Network error or invalid response. Please check your connection and try again.']);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
    
    showSuccess() {
        // Replace wizard content with success message
        const container = document.querySelector('.wizard-container');
        container.innerHTML = `
            <div class="success-content">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Application Submitted!</h2>
                <p>Thank you for applying to join our team of nutritionists.</p>
                <p>Your application has been submitted for review. Our admin team will verify your credentials and contact you within 2-3 business days.</p>
                <p>You will receive an email notification once your application status is updated.</p>
                <div style="margin-top: 2rem;">
                    <a href="/login" class="btn btn-primary">Go to Login</a>
                </div>
            </div>
        `;
    }
    
    showErrors(errors) {
        // Show errors at the top of current step
        const currentStepElement = document.getElementById(`step-${this.currentStep}`);
        let errorContainer = currentStepElement.querySelector('.alert-error');
        
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'alert alert-error';
            currentStepElement.insertBefore(errorContainer, currentStepElement.firstChild);
        }
        
        errorContainer.innerHTML = `
            <ul>
                ${errors.map(error => `<li>${error}</li>`).join('')}
            </ul>
        `;
        
        errorContainer.scrollIntoView({ behavior: 'smooth' });
    }
}

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function isValidFileType(file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    return allowedTypes.includes(file.type);
}

// Initialize wizard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new NutritionistWizard();
});

// Export for potential external use
window.NutritionistWizard = NutritionistWizard;
