// Advanced Admin Profile JavaScript
// ==========================================

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
    
    // Add hover effects to info rows
    const infoRows = document.querySelectorAll('.info-row');
    infoRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(4px)';
        });
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Add smooth transitions to action buttons
    const actionBtns = document.querySelectorAll('.action-btn');
    actionBtns.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'translateY(-4px)';
            }
        });
        btn.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'translateY(0)';
            }
        });
    });
    
    // Animate timeline items on scroll
    const observerOptions = {
        threshold: 0.3,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateX(0)';
            }
        });
    }, observerOptions);
    
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        item.style.transition = 'all 0.5s ease';
        observer.observe(item);
    });
    
    // Enhanced profile header parallax effect
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                const scrolled = window.pageYOffset;
                const banner = document.querySelector('.profile-banner');
                if (banner && scrolled < 300) {
                    banner.style.transform = `translateY(${scrolled * 0.5}px)`;
                }
                ticking = false;
            });
            ticking = true;
        }
    });
    
    // Add ripple effect to buttons
    function createRipple(event) {
        const button = event.currentTarget;
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        const rippleContainer = button.querySelector('.ripple');
        if (rippleContainer) {
            rippleContainer.remove();
        }
        
        button.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }
    
    document.querySelectorAll('.btn, .action-btn, .btn-icon').forEach(button => {
        button.addEventListener('click', createRipple);
    });
});

// Print functionality
window.addEventListener('beforeprint', function() {
    document.querySelectorAll('.content-card').forEach(card => {
        card.style.pageBreakInside = 'avoid';
    });
});

// Add keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close any open modals or dialogs
        const openModals = document.querySelectorAll('[role="dialog"]');
        openModals.forEach(modal => {
            const closeBtn = modal.querySelector('[data-dismiss="modal"]');
            if (closeBtn) closeBtn.click();
        });
    }
});

// Performance optimization: Lazy load images if any
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// Console message for developers
console.log('%c Admin Profile System ', 'background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold;');
console.log('%c Powered by Modern UI Framework ', 'background: #f3f4f6; color: #374151; padding: 5px 10px; border-radius: 3px; font-size: 12px;');

/* Admin profile modal and update functions moved from inline Blade script.
   These rely on globals set in the Blade view:
   - window.adminData
   - window.adminProfileUpdateUrl
   - window.adminPasswordUpdateUrl
*/

 (function() {
    const adminData = window.adminData || {};

    // Edit Personal Information
    window.editPersonalInfo = function() {
        Swal.fire({
            title: `
                <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-edit" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h3 style="margin: 0; color: #1f2937; font-size: 24px; font-weight: 700;">Edit Personal Information</h3>
                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Update your personal details</p>
                    </div>
                </div>
            `,
            html: `
                <div style="text-align: left; padding: 20px 10px;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>First Name *
                            </label>
                            <input id="swal-first-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${adminData.first_name || ''}" required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>Middle Name
                            </label>
                            <input id="swal-middle-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${adminData.middle_name || ''}">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>Last Name *
                            </label>
                            <input id="swal-last-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${adminData.last_name || ''}" required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-phone" style="color: #10b981; margin-right: 5px;"></i>Contact Number
                            </label>
                            <input id="swal-contact" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${adminData.contact_number || ''}">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-envelope" style="color: #10b981; margin-right: 5px;"></i>Email Address *
                            </label>
                            <input id="swal-email" type="email" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${adminData.email || ''}" required>
                        </div>
                    </div>
                </div>
            `,
            width: '900px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Save Changes',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'admin-swal-popup',
                confirmButton: 'admin-swal-confirm',
                cancelButton: 'admin-swal-cancel'
            },
            didOpen: () => {
                // Focus styling
                document.querySelectorAll('.swal2-input, .swal2-select').forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.borderColor = '#10b981';
                        this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
                    });
                    input.addEventListener('blur', function() {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = 'none';
                    });
                });
            },
            preConfirm: () => {
                const firstName = document.getElementById('swal-first-name').value;
                const lastName = document.getElementById('swal-last-name').value;
                const email = document.getElementById('swal-email').value;
                
                if (!firstName || !lastName || !email) {
                    Swal.showValidationMessage('First Name, Last Name, and Email are required');
                    return false;
                }
                
                return {
                    first_name: firstName,
                    middle_name: document.getElementById('swal-middle-name').value,
                    last_name: lastName,
                    email: email,
                    contact_number: document.getElementById('swal-contact').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updatePersonalInfo(result.value);
            }
        });
    };

    // Change Password
    window.changePassword = function() {
        Swal.fire({
            title: '<div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); margin: -20px -20px 0 -20px; padding: 6px 12px; border-radius: 16px 16px 0 0;"><div style="display: flex; align-items: center; gap: 8px;"><div style="width: 28px; height: 28px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius: 6px; display: flex; align-items: center; justify-content: center; border: 1.5px solid rgba(255, 255, 255, 0.3); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); flex-shrink: 0;"><i class="fas fa-lock" style="color: white; font-size: 13px;"></i></div><div style="text-align: left; flex: 1; line-height: 1;"><div style="color: white; font-size: 14px; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 1px;">Change Password</div><div style="color: rgba(255, 255, 255, 0.85); font-size: 10px; line-height: 1;">Update your security credentials</div></div></div></div>',
            html: `
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
                                    <span style="flex: 1;">Special char (@!$!%*?&#)</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            `,
            width: '900px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Update Password',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'admin-swal-popup',
                confirmButton: 'admin-swal-confirm',
                cancelButton: 'admin-swal-cancel'
            },
            didOpen: () => {
                // Password toggle functionality
                document.querySelectorAll('.password-toggle-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const targetId = this.dataset.target;
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

                // Focus styling
                document.querySelectorAll('.password-input').forEach(input => {
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

                // Real-time password validation
                const newPasswordInput = document.getElementById('swal-new-password');
                
                newPasswordInput.addEventListener('input', function() {
                    const password = this.value;
                    
                    // Check length
                    updateRequirement('length', password.length >= 8);
                    // Check uppercase
                    updateRequirement('uppercase', /[A-Z]/.test(password));
                    // Check lowercase
                    updateRequirement('lowercase', /[a-z]/.test(password));
                    // Check number
                    updateRequirement('number', /[0-9]/.test(password));
                    // Check special character
                    updateRequirement('special', /[@!$!%*?&#]/.test(password));
                });

                function updateRequirement(requirement, isValid) {
                    const li = document.querySelector(`[data-requirement="${requirement}"]`);
                    const indicator = li.querySelector('div');
                    const icon = indicator.querySelector('i');
                    
                    if (isValid) {
                        li.style.color = '#10b981';
                        indicator.style.background = '#10b981';
                        icon.classList.remove('fa-circle');
                        icon.classList.add('fa-check');
                        icon.style.color = 'white';
                        icon.style.fontSize = '10px';
                    } else {
                        li.style.color = '#6b7280';
                        indicator.style.background = '#f3f4f6';
                        icon.classList.remove('fa-check');
                        icon.classList.add('fa-circle');
                        icon.style.color = '#9ca3af';
                        icon.style.fontSize = '6px';
                    }
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
                
                if (newPassword.length < 8) {
                    Swal.showValidationMessage('New password must be at least 8 characters long');
                    return false;
                }
                
                if (!/[A-Z]/.test(newPassword)) {
                    Swal.showValidationMessage('Password must contain at least one uppercase letter');
                    return false;
                }
                
                if (!/[a-z]/.test(newPassword)) {
                    Swal.showValidationMessage('Password must contain at least one lowercase letter');
                    return false;
                }
                
                if (!/[0-9]/.test(newPassword)) {
                    Swal.showValidationMessage('Password must contain at least one number');
                    return false;
                }
                
                if (!/[@!$!%*?&#]/.test(newPassword)) {
                    Swal.showValidationMessage('Password must contain at least one special character (@!$!%*?&#)');
                    return false;
                }
                
                if (newPassword !== confirmPassword) {
                    Swal.showValidationMessage('New passwords do not match');
                    return false;
                }
                
                return {
                    current_password: currentPassword,
                    new_password: newPassword,
                    new_password_confirmation: confirmPassword
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updatePassword(result.value);
            }
        });
    };

    // Update Personal Information
    window.updatePersonalInfo = function(data) {
        fetch(window.adminProfileUpdateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP error! status: ${response.status}`);
                });
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
    };

    // Update Password
    window.updatePassword = function(data) {
        fetch(window.adminPasswordUpdateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw err;
                });
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
    };

 })();
