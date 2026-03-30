// Countdown timer for redirect
let countdown = 3;
const countdownElement = document.getElementById('countdown');

const timer = setInterval(() => {
    countdown--;
    countdownElement.textContent = countdown;
    
    if (countdown <= 0) {
        clearInterval(timer);
        window.location.href = document.getElementById('loginUrl').value;
    }
}, 1000);

// Allow clicking anywhere (except buttons) to go to login immediately
document.addEventListener('click', (e) => {
    if (!e.target.closest('.btn') && !e.target.closest('.success-banner-close')) {
        clearInterval(timer);
        window.location.href = document.getElementById('loginUrl').value;
    }
});

// Auto-dismiss success banner after 6 seconds
setTimeout(function() {
    dismissBanner();
}, 6000);

// Function to dismiss success banner
function dismissBanner() {
    const banner = document.getElementById('successBanner');
    if (banner) {
        banner.classList.add('hide');
        setTimeout(function() {
            banner.remove();
            // Adjust main container padding
            const container = document.querySelector('.success-container');
            if (container) {
                container.style.paddingTop = '2rem';
            }
        }, 500);
    }
}

// Play a subtle success sound (optional - commented out by default)
// const successSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIGGS...'); // Success sound
// successSound.play().catch(e => console.log('Audio play failed:', e));
