document.addEventListener('DOMContentLoaded', function() {
    // Simple modal handler that works with Bootstrap
    const modalButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
    
    modalButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetModalId = this.getAttribute('data-bs-target');
            const targetModal = document.querySelector(targetModalId);
            
            if (targetModal) {
                // Show modal with proper styling
                targetModal.style.display = 'block';
                targetModal.style.paddingRight = '0px';
                targetModal.classList.add('show');
                targetModal.setAttribute('aria-modal', 'true');
                targetModal.setAttribute('role', 'dialog');
                targetModal.removeAttribute('aria-hidden');
                
                // Add body classes
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
                document.body.style.paddingRight = '0px';
                
                // Close modal function
                function closeModal() {
                    targetModal.style.display = 'none';
                    targetModal.classList.remove('show');
                    targetModal.setAttribute('aria-hidden', 'true');
                    targetModal.removeAttribute('aria-modal');
                    targetModal.removeAttribute('role');
                    
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
                
                // Handle close buttons
                const closeButtons = targetModal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
                closeButtons.forEach(closeBtn => {
                    closeBtn.onclick = closeModal;
                });
                
                // Handle backdrop click (click outside modal)
                targetModal.onclick = function(e) {
                    if (e.target === targetModal) {
                        closeModal();
                    }
                };
                
                // Prevent clicks inside modal content from closing
                const modalContent = targetModal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.onclick = function(e) {
                        e.stopPropagation();
                    };
                }
                
                // Handle ESC key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && targetModal.classList.contains('show')) {
                        closeModal();
                    }
                });
            }
        });
    });
});

/**
 * Validate patient code format
 * Expected format: YYYY-SP-####-CC (e.g., 2025-SP-0001-01)
 */
function validatePatientCode(code) {
    const pattern = /^\d{4}-[A-Z]{2}-\d{4}-\d{2}$/;
    return pattern.test(code);
}

/**
 * Display error messages in a user-friendly way
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#059669',
        confirmButtonText: 'OK'
    });
}

/**
 * Display success messages
 */
function showSuccess(message, callback) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        html: `
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <p>${message}</p>
            </div>
        `,
        confirmButtonColor: '#059669',
        confirmButtonText: 'OK'
    }).then(() => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    });
}

/**
 * Show loading state
 */
function showLoading(message = 'Processing...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Show modal to add/link a child to parent account
 */
function showAddChildModal() {
    Swal.fire({
        title: '<div class="swal-add-child-header"><i class="fas fa-user-plus"></i> Link Child to Account</div>',
        html: `
            <div class="add-child-form-container">
                <p class="add-child-description">
                    <i class="fas fa-shield-alt"></i>
                    Enter your child's Patient ID and Birthdate to verify identity
                </p>
                <form id="addChildForm" class="add-child-form">
                    <div class="form-group-modern">
                        <label for="patient_code" class="form-label-modern">
                            <i class="fas fa-id-card"></i> Unique Patient ID
                        </label>
                        <input 
                            type="text" 
                            id="patient_code" 
                            name="patient_code" 
                            class="form-input-modern" 
                            placeholder="e.g., 2025-SP-0001-01"
                            required
                        >
                    </div>
                    <div class="form-group-modern">
                        <label for="birthdate" class="form-label-modern">
                            <i class="fas fa-calendar-alt"></i> Child's Birthdate
                        </label>
                        <input 
                            type="date" 
                            id="birthdate" 
                            name="birthdate" 
                            class="form-input-modern" 
                            required
                        >
                        <small class="form-help-text">
                            <i class="fas fa-info-circle"></i>
                            Enter the exact birthdate as registered in the system
                        </small>
                    </div>
                </form>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-search"></i> Verify Child',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        width: '600px',
        customClass: {
            popup: 'add-child-popup',
            confirmButton: 'btn-confirm-modern',
            cancelButton: 'btn-cancel-modern'
        },
        preConfirm: () => {
            const patientCode = document.getElementById('patient_code').value;
            const birthdate = document.getElementById('birthdate').value;
            if (!patientCode || !birthdate) {
                Swal.showValidationMessage('Please enter both Patient ID and Birthdate');
                return false;
            }
            return { patient_code: patientCode, birthdate: birthdate };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            previewChildBeforeLinking(result.value.patient_code, result.value.birthdate);
        }
    });
}

/**
 * Preview child information before linking
 */
function previewChildBeforeLinking(patientCode, birthdate) {
    // Show loading
    Swal.fire({
        title: 'Verifying...',
        html: 'Please wait while we verify the information',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const previewUrl = document.querySelector('[data-preview-url]')?.getAttribute('data-preview-url') || '/parent/preview-child';

    // Send AJAX request to preview child
    fetch(previewUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            patient_code: patientCode,
            birthdate: birthdate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMaskedChildConfirmation(data.child);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Verification Failed',
                text: data.message,
                confirmButtonColor: '#059669'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while verifying. Please try again.',
            confirmButtonColor: '#059669'
        });
    });
}

/**
 * Show masked child information for confirmation
 */
function showMaskedChildConfirmation(child) {
    const ageDisplay = child.age_months ? `${child.age_months} months old` : 'Age not recorded';
    const fullNameMasked = `${child.first_name_masked} ${child.middle_name_masked ? child.middle_name_masked + ' ' : ''}${child.last_name_masked}`;
    
    Swal.fire({
        title: '<div class="swal-add-child-header"><i class="fas fa-user-check"></i> Verify Child Identity</div>',
        html: `
            <div class="child-confirmation-container">
                <div class="confirmation-warning">
                    <i class="fas fa-shield-alt"></i>
                    <p><strong>Please confirm this is your child</strong></p>
                </div>
                <div class="child-preview-details">
                    <div class="preview-row">
                        <span class="preview-label"><i class="fas fa-id-card"></i> Patient ID:</span>
                        <span class="preview-value"><strong>${child.custom_patient_id}</strong></span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label"><i class="fas fa-user"></i> Name:</span>
                        <span class="preview-value">${fullNameMasked}</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label"><i class="fas fa-venus-mars"></i> Sex:</span>
                        <span class="preview-value">${child.sex || 'Not specified'}</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label"><i class="fas fa-birthday-cake"></i> Age:</span>
                        <span class="preview-value">${ageDisplay}</span>
                    </div>
                    ${child.barangay_masked ? `
                    <div class="preview-row">
                        <span class="preview-label"><i class="fas fa-map-marker-alt"></i> Barangay:</span>
                        <span class="preview-value">${child.barangay_masked}</span>
                    </div>
                    ` : ''}
                </div>
                <div class="confirmation-question">
                    <i class="fas fa-question-circle"></i>
                    <p>Is this your child?</p>
                </div>
                <div class="privacy-note">
                    <i class="fas fa-lock"></i>
                    <small>Names are masked for privacy protection</small>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Yes, Link This Child',
        cancelButtonText: '<i class="fas fa-times"></i> No, This is Not My Child',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#dc2626',
        width: '700px',
        customClass: {
            popup: 'add-child-popup',
            confirmButton: 'btn-confirm-modern',
            cancelButton: 'btn-cancel-modern'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            linkChildToParent(child.patient_id);
        }
    });
}

/**
 * Link child to parent account
 */
function linkChildToParent(patientId) {
    // Show loading
    Swal.fire({
        title: 'Processing...',
        html: 'Linking child to your account',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const linkUrl = document.querySelector('[data-link-url]')?.getAttribute('data-link-url') || '/parent/link-child';

    // Send AJAX request
    fetch(linkUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            patient_id: patientId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: `
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <p>${data.message}</p>
                    </div>
                `,
                confirmButtonColor: '#059669',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#059669'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while linking the child. Please try again.',
            confirmButtonColor: '#059669'
        });
    });
}

/**
 * Confirm before removing child from account
 */
function confirmRemoveChild(patientId, childName) {
    Swal.fire({
        title: '<div class="swal-warning-header"><i class="fas fa-exclamation-triangle"></i> Remove from Account?</div>',
        html: `
            <div class="unlink-confirmation">
                <p>Are you sure you want to remove <strong>${childName}</strong> from your account?</p>
                <div class="warning-box">
                    <i class="fas fa-info-circle"></i>
                    <p>You can re-link this child later using their Patient ID.</p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Yes, Remove',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        customClass: {
            confirmButton: 'btn-confirm-modern',
            cancelButton: 'btn-cancel-modern'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            removeChild(patientId);
        }
    });
}

/**
 * Remove child from parent account
 */
function removeChild(patientId) {
    Swal.fire({
        title: 'Processing...',
        html: 'Removing child from your account',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const unlinkUrl = document.querySelector('[data-unlink-url]')?.getAttribute('data-unlink-url') || '/parent/unlink-child';

    fetch(unlinkUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            patient_id: patientId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Removed!',
                text: data.message,
                confirmButtonColor: '#059669'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#059669'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while removing the child.',
            confirmButtonColor: '#059669'
        });
    });
}
