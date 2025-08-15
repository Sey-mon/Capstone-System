document.addEventListener("DOMContentLoaded", function () {
    console.log("Registration options page loaded successfully!");
    
    // Add hover effects for option cards
    const optionCards = document.querySelectorAll('.option-card');
    
    optionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-2px)';
        });
    });
});
