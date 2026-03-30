/**
 * Contact Admin - Simple Form Enhancement
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeContactForm();
});

function initializeContactForm() {
    const form = document.getElementById('contactForm');
    const submitButton = document.getElementById('contactBtn');
    const textarea = document.getElementById('message');

    // Auto-resize textarea
    if (textarea) {
        autoResizeTextarea(textarea);
        textarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
    }

    // Form submission with loading state
    if (form && submitButton) {
        form.addEventListener('submit', function(e) {
            // Add loading state
            setLoadingState(submitButton, true);
        });
    }
}



function setLoadingState(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.textContent = 'Sending...';
    } else {
        button.disabled = false;
        button.textContent = 'Send Message';
    }
}

function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.max(100, textarea.scrollHeight) + 'px';
}