// Auto-hide floating alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-floating');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.animation = 'slideOutRight 0.3s ease-in forwards';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});