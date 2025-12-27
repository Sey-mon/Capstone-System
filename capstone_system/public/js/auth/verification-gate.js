/**
 * Email Verification Gate Enhancements
 * Adds countdown timer, helpful tips, and improved UX for email verification
 */

document.addEventListener('DOMContentLoaded', function() {
    // Resend button cooldown timer
    const resendButton = document.querySelector('.btn-resend');
    let cooldownTime = 60; // 60 seconds cooldown
    let cooldownActive = false;

    // Check if there's a stored cooldown
    const storedCooldown = localStorage.getItem('resendCooldown');
    if (storedCooldown) {
        const remainingTime = parseInt(storedCooldown) - Date.now();
        if (remainingTime > 0) {
            startCooldown(Math.ceil(remainingTime / 1000));
        } else {
            localStorage.removeItem('resendCooldown');
        }
    }

    if (resendButton) {
        const form = resendButton.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (cooldownActive) {
                    e.preventDefault();
                    return false;
                }

                // Start cooldown
                const cooldownEnd = Date.now() + (cooldownTime * 1000);
                localStorage.setItem('resendCooldown', cooldownEnd);
            });
        }
    }

    function startCooldown(seconds) {
        if (!resendButton) return;
        
        cooldownActive = true;
        let remaining = seconds;

        const originalText = resendButton.innerHTML;
        
        const updateButton = () => {
            if (remaining <= 0) {
                cooldownActive = false;
                resendButton.innerHTML = originalText;
                resendButton.disabled = false;
                resendButton.style.opacity = '1';
                resendButton.style.cursor = 'pointer';
                localStorage.removeItem('resendCooldown');
                return;
            }

            resendButton.innerHTML = `<i class="fas fa-clock"></i> Wait ${remaining}s before resending`;
            resendButton.disabled = true;
            resendButton.style.opacity = '0.6';
            resendButton.style.cursor = 'not-allowed';
            remaining--;

            setTimeout(updateButton, 1000);
        };

        updateButton();
    }

    // Add helpful tips
    const verificationCard = document.querySelector('.verification-gate-card');
    if (verificationCard) {
        const helpfulTips = document.createElement('div');
        helpfulTips.className = 'helpful-tips';
        helpfulTips.innerHTML = `
            <div style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">
                <h6 style="margin: 0 0 1rem 0; color: #17a2b8;">
                    <i class="fas fa-lightbulb" style="margin-right: 0.5rem;"></i>
                    Helpful Tips
                </h6>
                <ul style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                    <li>Check your spam or junk folder if you don't see the email</li>
                    <li>Make sure you entered the correct email address during registration</li>
                    <li>The verification link is valid for 60 minutes</li>
                    <li>You can close this page and return later - just use the link in your email</li>
                </ul>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #dee2e6;">
                    <strong>Need help?</strong> 
                    <a href="/contact-admin" style="color: #17a2b8; text-decoration: none; margin-left: 0.5rem;">
                        <i class="fas fa-envelope"></i> Contact Support
                    </a>
                </div>
            </div>
        `;
        
        const actionButtons = verificationCard.querySelector('.action-buttons');
        if (actionButtons) {
            verificationCard.insertBefore(helpfulTips, actionButtons);
        }
    }

    // Add email sent timestamp
    const userInfoBox = document.querySelector('.user-info-box');
    if (userInfoBox) {
        const sentTime = document.createElement('p');
        sentTime.innerHTML = `
            <i class="fas fa-info-circle" style="color: #17a2b8; margin-right: 0.5rem;"></i>
            <small style="color: #6c757d;">
                <strong>Verification email sent:</strong> Just now
                <br>
                <em>Please allow up to 5 minutes for delivery</em>
            </small>
        `;
        sentTime.style.marginTop = '1rem';
        sentTime.style.padding = '0.75rem';
        sentTime.style.background = '#e7f3ff';
        sentTime.style.borderRadius = '6px';
        userInfoBox.appendChild(sentTime);
    }

    // Auto-hide floating alerts after 8 seconds
    const alerts = document.querySelectorAll('.alert-floating');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 8000);
    });
});
