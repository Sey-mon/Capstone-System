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
                document.querySelectorAll('.swal2-input').forEach(input => {
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
            title: `
                <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #06b6d4, #0891b2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-key" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h3 style="margin: 0; color: #1f2937; font-size: 24px; font-weight: 700;">Change Password</h3>
                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Update your account password</p>
                    </div>
                </div>
            `,
            html: `
                <div style="text-align: left; padding: 20px 10px;">
                    <div style="display: grid; gap: 20px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>Current Password *
                            </label>
                            <input id="swal-current-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Enter current password" required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>New Password *
                            </label>
                            <input id="swal-new-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Enter new password (min. 8 characters)" required>
                            <small style="color: #6b7280; font-size: 12px; margin-top: 5px; display: block;">Password must be at least 8 characters long</small>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>Confirm New Password *
                            </label>
                            <input id="swal-confirm-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Re-enter new password" required>
                        </div>
                    </div>
                </div>
            `,
            width: '600px',
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
                // Focus styling
                document.querySelectorAll('.swal2-input').forEach(input => {
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => response.json())
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
                text: 'An error occurred while updating personal information',
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => response.json())
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
                text: 'An error occurred while updating password',
                confirmButtonColor: '#ef4444'
            });
        });
    };

 })();
