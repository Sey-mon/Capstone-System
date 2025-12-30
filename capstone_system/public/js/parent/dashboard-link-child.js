// Dashboard Link Child Functionality
// Handles linking children to parent account from dashboard

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

function previewChildBeforeLinking(patientCode, birthdate) {
    Swal.fire({
        title: 'Verifying...',
        html: 'Please wait while we verify the information',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Get CSRF token and routes from meta tags
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const previewUrl = document.querySelector('meta[name="preview-child-url"]').getAttribute('content');

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

function linkChildToParent(patientId) {
    Swal.fire({
        title: 'Processing...',
        html: 'Linking child to your account',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const linkUrl = document.querySelector('meta[name="link-child-url"]').getAttribute('content');

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
