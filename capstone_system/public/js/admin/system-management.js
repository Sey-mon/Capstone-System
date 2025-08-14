/**
 * Modern System Management JavaScript
 * Handles category and barangay CRUD operations with modern UI interactions
 */

class SystemManagement {
    constructor() {
        this.baseUrl = window.location.origin;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupModalBehavior();
        this.setupFormValidation();
    }

    setupEventListeners() {
        // Tab switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const tab = button.getAttribute('data-tab') || button.id.replace('-tab', '');
                this.switchTab(tab);
            });
        });

        // Form submissions
        this.setupFormSubmissions();

        // Close modal handlers
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target.id);
            }
            if (e.target.classList.contains('close-modal')) {
                const modal = e.target.closest('.modal');
                if (modal) this.closeModal(modal.id);
            }
        });

        // Keyboard handlers
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal[style*="flex"]');
                if (openModal) this.closeModal(openModal.id);
            }
        });
    }

    setupModalBehavior() {
        // Add smooth animation classes
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.add('modal-enter');
        });
    }

    setupFormValidation() {
        // Real-time validation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', (e) => {
                this.validateField(e.target);
            });

            input.addEventListener('blur', (e) => {
                this.validateField(e.target);
            });
        });
    }

    setupFormSubmissions() {
        // Category forms
        const addCategoryForm = document.getElementById('addCategoryForm');
        if (addCategoryForm) {
            addCategoryForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCategorySubmission(addCategoryForm, 'POST', '/admin/categories');
            });
        }

        const editCategoryForm = document.getElementById('editCategoryForm');
        if (editCategoryForm) {
            editCategoryForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const categoryId = document.getElementById('edit_category_id').value;
                this.handleCategorySubmission(editCategoryForm, 'PUT', `/admin/categories/${categoryId}`);
            });
        }

        // Barangay forms
        const addBarangayForm = document.getElementById('addBarangayForm');
        if (addBarangayForm) {
            addBarangayForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleBarangaySubmission(addBarangayForm, 'POST', '/admin/barangays');
            });
        }

        const editBarangayForm = document.getElementById('editBarangayForm');
        if (editBarangayForm) {
            editBarangayForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const barangayId = document.getElementById('edit_barangay_id').value;
                this.handleBarangaySubmission(editBarangayForm, 'PUT', `/admin/barangays/${barangayId}`);
            });
        }
    }

    // Tab Management
    switchTab(tab) {
        // Hide all tab contents with animation
        document.querySelectorAll('.tab-content').forEach(content => {
            content.style.opacity = '0';
            setTimeout(() => {
                content.style.display = 'none';
                content.classList.remove('active');
            }, 150);
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Show selected tab content with animation
        setTimeout(() => {
            const targetContent = document.getElementById(tab + '-content');
            const targetButton = document.getElementById(tab + '-tab');
            
            if (targetContent && targetButton) {
                targetContent.style.display = 'block';
                targetContent.classList.add('active');
                targetButton.classList.add('active');
                
                setTimeout(() => {
                    targetContent.style.opacity = '1';
                }, 50);
            }
        }, 150);
    }

    // Modal Management
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show');
            
            // Focus first input
            const firstInput = modal.querySelector('.form-control');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            
            setTimeout(() => {
                modal.style.display = 'none';
                this.clearForm(modal);
                document.body.style.overflow = '';
            }, 300);
        }
    }

    clearForm(modal) {
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            
            // Clear validation states
            form.querySelectorAll('.form-control').forEach(field => {
                field.classList.remove('is-invalid', 'is-valid');
            });
            
            form.querySelectorAll('.invalid-feedback').forEach(error => {
                error.textContent = '';
                error.style.opacity = '0';
            });
            
            // Remove any success/error messages
            form.querySelectorAll('.form-success, .form-error').forEach(msg => {
                msg.remove();
            });
        }
    }

    // Field Validation
    validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        const errorElement = field.parentNode.querySelector('.invalid-feedback');
        
        if (isRequired && !value) {
            this.setFieldError(field, 'This field is required');
            return false;
        }
        
        if (value.length > 0 && value.length < 2) {
            this.setFieldError(field, 'Must be at least 2 characters long');
            return false;
        }
        
        this.setFieldValid(field);
        return true;
    }

    setFieldError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        const errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.opacity = '1';
        }
    }

    setFieldValid(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        const errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.opacity = '0';
        }
    }

    // Category Management
    openAddCategoryModal() {
        this.openModal('addCategoryModal');
    }

    async openEditCategoryModal(categoryId) {
        try {
            this.showLoading('Loading category data...');
            
            const response = await fetch(`${this.baseUrl}/admin/categories/${categoryId}`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('edit_category_id').value = data.category.category_id;
                document.getElementById('edit_category_name').value = data.category.category_name;
                this.openModal('editCategoryModal');
            } else {
                this.showNotification('Failed to load category data', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Failed to load category data', 'error');
        } finally {
            this.hideLoading();
        }
    }

    confirmDeleteCategory(categoryId, categoryName) {
        if (confirm(`Are you sure you want to delete "${categoryName}"? This action cannot be undone.`)) {
            this.deleteCategory(categoryId);
        }
    }

    async deleteCategory(categoryId) {
        try {
            this.showLoading('Deleting category...');
            
            const response = await fetch(`${this.baseUrl}/admin/categories/${categoryId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred while deleting the category', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async handleCategorySubmission(form, method, url) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        try {
            // Validate form
            let isValid = true;
            form.querySelectorAll('.form-control[required]').forEach(field => {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });
            
            if (!isValid) return;
            
            // Show loading state
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            
            const formData = new FormData(form);
            if (method === 'PUT') {
                formData.append('_method', 'PUT');
            }
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message, 'success');
                this.closeModal(form.closest('.modal').id);
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred while processing the request', 'error');
        } finally {
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    }

    // Barangay Management
    openAddBarangayModal() {
        this.openModal('addBarangayModal');
    }

    async openEditBarangayModal(barangayId) {
        try {
            this.showLoading('Loading barangay data...');
            
            const response = await fetch(`${this.baseUrl}/admin/barangays/${barangayId}`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('edit_barangay_id').value = data.barangay.barangay_id;
                document.getElementById('edit_barangay_name').value = data.barangay.barangay_name;
                this.openModal('editBarangayModal');
            } else {
                this.showNotification('Failed to load barangay data', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Failed to load barangay data', 'error');
        } finally {
            this.hideLoading();
        }
    }

    confirmDeleteBarangay(barangayId, barangayName) {
        if (confirm(`Are you sure you want to delete "${barangayName}"? This action cannot be undone.`)) {
            this.deleteBarangay(barangayId);
        }
    }

    async deleteBarangay(barangayId) {
        try {
            this.showLoading('Deleting barangay...');
            
            const response = await fetch(`${this.baseUrl}/admin/barangays/${barangayId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred while deleting the barangay', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async handleBarangaySubmission(form, method, url) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        try {
            // Validate form
            let isValid = true;
            form.querySelectorAll('.form-control[required]').forEach(field => {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });
            
            if (!isValid) return;
            
            // Show loading state
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            
            const formData = new FormData(form);
            if (method === 'PUT') {
                formData.append('_method', 'PUT');
            }
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message, 'success');
                this.closeModal(form.closest('.modal').id);
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred while processing the request', 'error');
        } finally {
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    }

    // Utility Methods
    showLoading(message = 'Loading...') {
        // Create or show loading overlay
        let loadingOverlay = document.getElementById('loadingOverlay');
        if (!loadingOverlay) {
            loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'loadingOverlay';
            loadingOverlay.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                ">
                    <div style="
                        background: white;
                        padding: 2rem;
                        border-radius: 1rem;
                        display: flex;
                        align-items: center;
                        gap: 1rem;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                    ">
                        <div style="
                            width: 20px;
                            height: 20px;
                            border: 2px solid #e5e7eb;
                            border-top: 2px solid var(--primary-color);
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                        "></div>
                        <span>${message}</span>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
        }
        loadingOverlay.style.display = 'flex';
    }

    hideLoading() {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }

    showNotification(message, type = 'info') {
        // Create modern notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 0.75rem;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                max-width: 400px;
                animation: slideInRight 0.3s ease;
            ">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="
                    background: none;
                    border: none;
                    color: white;
                    font-size: 1.2rem;
                    cursor: pointer;
                    opacity: 0.7;
                    margin-left: auto;
                ">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.systemManagement = new SystemManagement();
});

// Global functions for backward compatibility
function switchTab(tab) {
    window.systemManagement?.switchTab(tab);
}

function openAddCategoryModal() {
    window.systemManagement?.openAddCategoryModal();
}

function openEditCategoryModal(categoryId) {
    window.systemManagement?.openEditCategoryModal(categoryId);
}

function confirmDeleteCategory(categoryId, categoryName) {
    window.systemManagement?.confirmDeleteCategory(categoryId, categoryName);
}

function openAddBarangayModal() {
    window.systemManagement?.openAddBarangayModal();
}

function openEditBarangayModal(barangayId) {
    window.systemManagement?.openEditBarangayModal(barangayId);
}

function confirmDeleteBarangay(barangayId, barangayName) {
    window.systemManagement?.confirmDeleteBarangay(barangayId, barangayName);
}

function closeModal(modalId) {
    window.systemManagement?.closeModal(modalId);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
