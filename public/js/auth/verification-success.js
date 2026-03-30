// Verification Success Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Add any future JavaScript functionality here
    console.log('Verification Success page loaded successfully');
    
    // Optional: Add a small delay before showing the success animation
    const checkmark = document.querySelector('.checkmark');
    if (checkmark) {
        checkmark.style.opacity = '0';
        setTimeout(() => {
            checkmark.style.opacity = '1';
        }, 200);
    }
});