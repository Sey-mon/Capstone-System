// Advanced Parent Profile JavaScript
// ==========================================

// Parent data for forms - Will be populated from blade template
let parentData = {};

// Initialize parent data from template
function initializeParentData(data) {
    parentData = data;
}

// Edit Personal Information
function editPersonalInfo() {
    Swal.fire({
        title: '<div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); margin: -20px -20px 0 -20px; padding: 6px 12px; border-radius: 16px 16px 0 0;"><div style="display: flex; align-items: center; gap: 8px;"><div style="width: 28px; height: 28px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius: 6px; display: flex; align-items: center; justify-content: center; border: 1.5px solid rgba(255, 255, 255, 0.3); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); flex-shrink: 0;"><i class="fas fa-user-edit" style="color: white; font-size: 13px;"></i></div><div style="text-align: left; flex: 1; line-height: 1;"><div style="color: white; font-size: 14px; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 1px;">Edit Personal Information</div><div style="color: rgba(255, 255, 255, 0.85); font-size: 10px; line-height: 1;">Update your personal details</div></div></div></div>',
        html: `
            <div style="text-align: left; padding: 20px 10px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>First Name *
                        </label>
                        <input id="swal-first-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.first_name}" required>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>Middle Name
                        </label>
                        <input id="swal-middle-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.middle_name}">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>Last Name *
                        </label>
                        <input id="swal-last-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.last_name}" required>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-phone" style="color: #10b981; margin-right: 5px;"></i>Contact Number
                        </label>
                        <input id="swal-contact" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.contact_number || ''}">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-calendar" style="color: #10b981; margin-right: 5px;"></i>Date of Birth
                        </label>
                        <input id="swal-birth-date" type="date" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.birth_date || ''}">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-venus-mars" style="color: #10b981; margin-right: 5px;"></i>Sex
                        </label>
                        <select id="swal-gender" class="swal2-select" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                            <option value="">Select Sex</option>
                            <option value="male" ${(parentData.sex || '') === 'male' ? 'selected' : ''}>Male</option>
                            <option value="female" ${(parentData.sex || '') === 'female' ? 'selected' : ''}>Female</option>
                            <option value="other" ${(parentData.sex || '') === 'other' ? 'selected' : ''}>Other</option>
                        </select>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-map-marker-alt" style="color: #10b981; margin-right: 5px;"></i>Address
                        </label>
                        <textarea id="swal-address" class="swal2-textarea" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; min-height: 80px;" rows="3">${parentData.address || ''}</textarea>
                    </div>
                </div>
            </div>
        `,
        width: '900px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Save Changes',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        customClass: {
            popup: 'parent-swal-popup',
            confirmButton: 'parent-swal-confirm',
            cancelButton: 'parent-swal-cancel'
        },
        confirmButtonText: '<i class="fas fa-save"></i> Save Changes',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'parent-swal-popup',
            confirmButton: 'parent-swal-confirm',
            cancelButton: 'parent-swal-cancel'
        },
        didOpen: () => {
            // Debug: Log the sex value
            console.log('Parent sex value:', parentData.sex);
            
            // Focus styling
            document.querySelectorAll('.swal2-input, .swal2-select, .swal2-textarea').forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#10b981';
                    this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
                });
                input.addEventListener('blur', function() {
                    this.style.borderColor = '#e5e7eb';
                    this.style.boxShadow = 'none';
                });
            });
            
            // Manually set the sex select value to ensure it's selected
            const sexSelect = document.getElementById('swal-gender');
            if (sexSelect && parentData.sex) {
                sexSelect.value = parentData.sex.toLowerCase();
                console.log('Set sex select to:', parentData.sex.toLowerCase());
            }
        },
        preConfirm: () => {
            const firstName = document.getElementById('swal-first-name').value;
            const lastName = document.getElementById('swal-last-name').value;
            
            if (!firstName || !lastName) {
                Swal.showValidationMessage('First Name and Last Name are required');
                return false;
            }
            
            return {
                first_name: firstName,
                middle_name: document.getElementById('swal-middle-name').value,
                last_name: lastName,
                contact_number: document.getElementById('swal-contact').value,
                birth_date: document.getElementById('swal-birth-date').value,
                sex: document.getElementById('swal-gender').value,
                address: document.getElementById('swal-address').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            updatePersonalInfo(result.value);
        }
    });
}

// Change Password
function changePassword() {
    Swal.fire({
        title: `<div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); margin: -20px -20px 0 -20px; padding: 6px 12px; border-radius: 16px 16px 0 0;"><div style="display: flex; align-items: center; gap: 8px;"><div style="width: 28px; height: 28px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius: 6px; display: flex; align-items: center; justify-content: center; border: 1.5px solid rgba(255, 255, 255, 0.3); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); flex-shrink: 0;"><i class="fas fa-lock" style="color: white; font-size: 13px;"></i></div><div style="text-align: left; flex: 1; line-height: 1;"><div style="color: white; font-size: 14px; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 1px;">Change Password</div><div style="color: rgba(255, 255, 255, 0.85); font-size: 10px; line-height: 1;">Update your security credentials</div></div></div></div>`,
        html: `
            <div style="padding: 0;">
                <div style="display: grid; grid-template-columns: 1fr 380px; gap: 0; background: #f9fafb; border-radius: 12px; overflow: hidden; box-shadow: inset 0 0 0 1px #e5e7eb;">
                    <!-- Left Column - Password Inputs -->
                    <div style="padding: 20px 25px; background: white;">
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <div>
                                <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #1f2937; margin-bottom: 8px; font-size: 13px;">
                                    <i class="fas fa-lock" style="color: #10b981; font-size: 12px;"></i>
                                    <span>Current Password</span>
                                    <span style="color: #ef4444;">*</span>
                                </label>
                                <div style="position: relative;">
                                    <input id="swal-current-password" type="password" class="swal2-input password-input" autocomplete="current-password" style="width: 100%; margin: 0; padding: 11px 40px 11px 12px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.2s; background: #fafafa;" placeholder="••••••••" required>
                                    <button type="button" class="password-toggle-btn" data-target="swal-current-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #9ca3af; padding: 5px; transition: color 0.2s;">
                                        <i class="fas fa-eye" style="font-size: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #1f2937; margin-bottom: 8px; font-size: 13px;">
                                    <i class="fas fa-key" style="color: #10b981; font-size: 12px;"></i>
                                    <span>New Password</span>
                                    <span style="color: #ef4444;">*</span>
                                </label>
                                <div style="position: relative;">
                                    <input id="swal-new-password" type="password" class="swal2-input password-input" autocomplete="new-password" style="width: 100%; margin: 0; padding: 11px 40px 11px 12px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.2s; background: #fafafa;" placeholder="••••••••" required>
                                    <button type="button" class="password-toggle-btn" data-target="swal-new-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #9ca3af; padding: 5px; transition: color 0.2s;">
                                        <i class="fas fa-eye" style="font-size: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #1f2937; margin-bottom: 8px; font-size: 13px;">
                                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 12px;"></i>
                                    <span>Confirm New Password</span>
                                    <span style="color: #ef4444;">*</span>
                                </label>
                                <div style="position: relative;">
                                    <input id="swal-confirm-password" type="password" class="swal2-input password-input" autocomplete="new-password" style="width: 100%; margin: 0; padding: 11px 40px 11px 12px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.2s; background: #fafafa;" placeholder="••••••••" required>
                                    <button type="button" class="password-toggle-btn" data-target="swal-confirm-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #9ca3af; padding: 5px; transition: color 0.2s;">
                                        <i class="fas fa-eye" style="font-size: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column - Password Requirements -->
                    <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); padding: 20px; display: flex; flex-direction: column; justify-content: center; border-left: 1px solid #a7f3d0;">
                        <div style="text-align: center; margin-bottom: 12px;">
                            <div style="width: 52px; height: 52px; margin: 0 auto 10px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); position: relative;">
                                <i class="fas fa-shield-alt" style="color: white; font-size: 24px;"></i>
                                <div style="position: absolute; inset: -4px; border: 2px solid rgba(16, 185, 129, 0.2); border-radius: 50%;"></div>
                            </div>
                            <h4 style="margin: 0; color: #065f46; font-size: 14px; font-weight: 700; letter-spacing: -0.01em;">Security Requirements</h4>
                            <p style="margin: 3px 0 0 0; color: #059669; font-size: 11px; font-weight: 500;">Your password must include</p>
                        </div>
                        <div class="password-strength-info" style="background: white; border-radius: 10px; padding: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid rgba(16, 185, 129, 0.1);">
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 12px;">
                                <li class="requirement" data-requirement="length" style="color: #6b7280; padding: 7px 0; display: flex; align-items: center; gap: 10px; transition: all 0.2s;">
                                    <div style="width: 18px; height: 18px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;">
                                        <i class="fas fa-circle" style="font-size: 6px; color: #9ca3af;"></i>
                                    </div>
                                    <span style="flex: 1;">At least 8 characters</span>
                                </li>
                                <li class="requirement" data-requirement="uppercase" style="color: #6b7280; padding: 7px 0; display: flex; align-items: center; gap: 10px; transition: all 0.2s;">
                                    <div style="width: 18px; height: 18px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;">
                                        <i class="fas fa-circle" style="font-size: 6px; color: #9ca3af;"></i>
                                    </div>
                                    <span style="flex: 1;">One uppercase (A-Z)</span>
                                </li>
                                <li class="requirement" data-requirement="lowercase" style="color: #6b7280; padding: 7px 0; display: flex; align-items: center; gap: 10px; transition: all 0.2s;">
                                    <div style="width: 18px; height: 18px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;">
                                        <i class="fas fa-circle" style="font-size: 6px; color: #9ca3af;"></i>
                                    </div>
                                    <span style="flex: 1;">One lowercase (a-z)</span>
                                </li>
                                <li class="requirement" data-requirement="number" style="color: #6b7280; padding: 7px 0; display: flex; align-items: center; gap: 10px; transition: all 0.2s;">
                                    <div style="width: 18px; height: 18px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;">
                                        <i class="fas fa-circle" style="font-size: 6px; color: #9ca3af;"></i>
                                    </div>
                                    <span style="flex: 1;">One number (0-9)</span>
                                </li>
                                <li class="requirement" data-requirement="special" style="color: #6b7280; padding: 7px 0; display: flex; align-items: center; gap: 10px; transition: all 0.2s;">
                                    <div style="width: 18px; height: 18px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;">
                                        <i class="fas fa-circle" style="font-size: 6px; color: #9ca3af;"></i>
                                    </div>
                                    <span style="flex: 1;">Special char (@$!%*?&#)</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `,
        width: '950px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Update Password',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'parent-swal-popup',
            confirmButton: 'parent-swal-confirm',
            cancelButton: 'parent-swal-cancel'
        },
        didOpen: () => {
            // Enhanced focus styling for inputs
            document.querySelectorAll('.swal2-input').forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#10b981';
                    this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
                    this.style.background = 'white';
                });
                input.addEventListener('blur', function() {
                    this.style.borderColor = '#e5e7eb';
                    this.style.boxShadow = 'none';
                    this.style.background = '#fafafa';
                });
            });

            // Enhanced password toggle with hover effect
            document.querySelectorAll('.password-toggle-btn').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.color = '#10b981';
                });
                btn.addEventListener('mouseleave', function() {
                    this.style.color = '#9ca3af';
                });
                btn.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                        this.style.color = '#10b981';
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                        this.style.color = '#9ca3af';
                    }
                });
            });

            // Password strength validation
            const newPasswordInput = document.getElementById('swal-new-password');
            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', function() {
                    const password = this.value;
                    const requirements = {
                        length: password.length >= 8,
                        uppercase: /[A-Z]/.test(password),
                        lowercase: /[a-z]/.test(password),
                        number: /[0-9]/.test(password),
                        special: /[@$!%*?&#]/.test(password)
                    };

                    Object.keys(requirements).forEach(req => {
                        const element = document.querySelector(`[data-requirement="${req}"]`);
                        if (element) {
                            const iconContainer = element.querySelector('div');
                            const icon = iconContainer.querySelector('i');
                            if (requirements[req]) {
                                element.style.color = '#059669';
                                element.style.fontWeight = '600';
                                iconContainer.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                                iconContainer.style.boxShadow = '0 2px 6px rgba(16, 185, 129, 0.3)';
                                icon.classList.remove('fa-circle');
                                icon.classList.add('fa-check');
                                icon.style.color = 'white';
                                icon.style.fontSize = '10px';
                            } else {
                                element.style.color = '#6b7280';
                                element.style.fontWeight = '400';
                                iconContainer.style.background = '#f3f4f6';
                                iconContainer.style.boxShadow = 'none';
                                icon.classList.remove('fa-check');
                                icon.classList.add('fa-circle');
                                icon.style.color = '#9ca3af';
                                icon.style.fontSize = '6px';
                            }
                        }
                    });
                });
            }
        },
        preConfirm: () => {
            const currentPassword = document.getElementById('swal-current-password').value;
            const newPassword = document.getElementById('swal-new-password').value;
            const confirmPassword = document.getElementById('swal-confirm-password').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                Swal.showValidationMessage('All fields are required');
                return false;
            }
            
            // Password strength validation
            const requirements = {
                length: newPassword.length >= 8,
                uppercase: /[A-Z]/.test(newPassword),
                lowercase: /[a-z]/.test(newPassword),
                number: /[0-9]/.test(newPassword),
                special: /[@$!%*?&#]/.test(newPassword)
            };

            const unmetRequirements = Object.keys(requirements).filter(req => !requirements[req]);
            
            if (unmetRequirements.length > 0) {
                Swal.showValidationMessage('Password does not meet all requirements');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                Swal.showValidationMessage('New passwords do not match');
                return false;
            }
            
            return {
                current_password: currentPassword,
                password: newPassword,
                password_confirmation: confirmPassword
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            updatePassword(result.value);
        }
    });
}

// Update Personal Information - needs updateRoute to be set
function updatePersonalInfo(data) {
    if (!window.updateProfileRoute) {
        console.error('Update profile route not defined');
        return;
    }

    fetch(window.updateProfileRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            ...data,
            _method: 'PUT'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server did not return JSON response');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Personal information updated successfully',
                confirmButtonColor: '#10b981',
                timer: 2000
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update personal information',
                confirmButtonColor: '#ef4444'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'An error occurred while updating personal information',
            confirmButtonColor: '#ef4444'
        });
    });
}

// Update Password - needs passwordUpdateRoute to be set
function updatePassword(data) {
    if (!window.updatePasswordRoute) {
        console.error('Update password route not defined');
        return;
    }

    fetch(window.updatePasswordRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            ...data,
            _method: 'PUT'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server did not return JSON response');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Password Updated!',
                text: data.message || 'Your password has been updated successfully',
                confirmButtonColor: '#10b981',
                timer: 2000
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update password',
                confirmButtonColor: '#ef4444'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'An error occurred while updating password',
            confirmButtonColor: '#ef4444'
        });
    });
}

// Enhanced Notification Function
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds with fade out
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Smooth Scroll for Quick Actions
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth reveal animations on page load
    const cards = document.querySelectorAll('.content-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });
    
    // Animate quick stats on load
    const quickStats = document.querySelectorAll('.quick-stat');
    quickStats.forEach((stat, index) => {
        stat.style.opacity = '0';
        stat.style.transform = 'scale(0.9)';
        setTimeout(() => {
            stat.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            stat.style.opacity = '1';
            stat.style.transform = 'scale(1)';
        }, 50 * index);
    });
    
    // Animate stat numbers
    animateNumbers();
});

// Animate counting numbers
function animateNumbers() {
    const statValues = document.querySelectorAll('.quick-stat-value');
    statValues.forEach(stat => {
        const text = stat.textContent.trim();
        const target = parseInt(text);
        
        // Skip if not a number or is a string like "Active"
        if (isNaN(target)) return;
        
        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                stat.textContent = target;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current);
            }
        }, 30);
    });
}

// Add ripple effect to buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn') || 
        e.target.classList.contains('action-btn') ||
        e.target.closest('.btn') ||
        e.target.closest('.action-btn')) {
        
        const button = e.target.classList.contains('btn') || e.target.classList.contains('action-btn') 
            ? e.target 
            : e.target.closest('.btn') || e.target.closest('.action-btn');
        
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
});

// Add CSS for ripple effect dynamically
const style = document.createElement('style');
style.textContent = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Delete Account Function
function deleteAccount() {
    Swal.fire({
        title: '<div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); margin: -20px -20px 0 -20px; padding: 6px 12px; border-radius: 16px 16px 0 0;"><div style="display: flex; align-items: center; gap: 8px;"><div style="width: 28px; height: 28px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius: 6px; display: flex; align-items: center; justify-content: center; border: 1.5px solid rgba(255, 255, 255, 0.3); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); flex-shrink: 0;"><i class="fas fa-exclamation-triangle" style="color: white; font-size: 13px;"></i></div><div style="text-align: left; flex: 1; line-height: 1;"><div style="color: white; font-size: 14px; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 1px;">Schedule Account Deletion</div><div style="color: rgba(255, 255, 255, 0.85); font-size: 10px; line-height: 1;">30-day grace period before permanent deletion</div></div></div></div>',
        html: `
            <div style="text-align: left; padding: 20px 10px;">
                <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #92400e; font-weight: 600; font-size: 14px;">
                        <i class="fas fa-clock" style="margin-right: 8px;"></i>
                        30-Day Grace Period
                    </p>
                    <p style="margin: 8px 0 0 0; color: #78350f; font-size: 13px; line-height: 1.5;">
                        Your account will be scheduled for deletion, but you can cancel anytime within 30 days by simply logging back in.
                    </p>
                </div>
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #991b1b; font-weight: 600; font-size: 14px;">
                        <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                        What happens when you schedule deletion:
                    </p>
                    <ul style="margin: 8px 0 0 0; color: #7f1d1d; font-size: 13px; padding-left: 24px; line-height: 1.8;">
                        <li style="margin-bottom: 6px;"><strong>Your account becomes inactive</strong> - You won't be able to access the system</li>
                        <li style="margin-bottom: 6px;"><strong>Your children's records are preserved</strong> - They will be unlinked from your account but remain in the system</li>
                        <li style="margin-bottom: 6px;"><strong>You have 30 days to change your mind</strong> - Simply log in again to cancel the deletion</li>
                        <li><strong>After 30 days, deletion is final</strong> - Your account and personal data will be permanently removed</li>
                    </ul>
                </div>
                <p style="color: #374151; margin: 0; font-size: 14px;">
                    Do you want to schedule your account for deletion?
                </p>
            </div>
        `,
        width: '650px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-clock"></i> Schedule Deletion (30 Days)',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'parent-swal-popup',
            confirmButton: 'parent-swal-confirm',
            cancelButton: 'parent-swal-cancel'
        },
        focusCancel: true,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Scheduling Deletion...',
                html: 'Please wait while we process your request.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send delete request
            fetch(window.deleteAccountRoute, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deletion Scheduled',
                        html: `
                            <div style="text-align: left;">
                                <p style="margin: 0 0 12px 0;">${data.message || 'Your account is scheduled for deletion in 30 days.'}</p>
                                <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 12px; margin-top: 12px;">
                                    <p style="margin: 0; color: #065f46; font-size: 13px; font-weight: 600;">
                                        <i class="fas fa-undo" style="margin-right: 8px;"></i>
                                        Changed your mind?
                                    </p>
                                    <p style="margin: 6px 0 0 0; color: #047857; font-size: 12px;">
                                        Simply log in again within 30 days to cancel the deletion and restore your account.
                                    </p>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#10b981',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = data.redirect || '/login';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to schedule account deletion. Please try again.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while scheduling account deletion. Please try again.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
                console.error('Delete account error:', error);
            });
        }
    });
}

