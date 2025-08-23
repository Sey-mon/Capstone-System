// Fallback functions to prevent "undefined" errors
window.modalFallbacks = {
    openModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (typeof bootstrap !== 'undefined') {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } else {
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'modalBackdrop';
                document.body.appendChild(backdrop);
            }
        }
    },
    closeModal: function(modalId) {
        const modal = document.getElementById(modalId);
        const backdrop = document.getElementById('modalBackdrop');
        if (modal) {
            if (typeof bootstrap !== 'undefined') {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            } else {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.style.overflow = '';
                if (backdrop) {
                    backdrop.remove();
                }
            }
        }
    }
};

// Global fallback functions for specific modals
if (typeof window.openQuickAssessmentModal === 'undefined') {
    window.openQuickAssessmentModal = function() {
        console.log('Using fallback for Quick Assessment Modal');
        window.modalFallbacks.openModal('quickAssessmentModal');
    };
}

if (typeof window.openAddPatientModal === 'undefined') {
    window.openAddPatientModal = function() {
        console.log('Using fallback for Add Patient Modal');
        window.modalFallbacks.openModal('patientModal');
    };
}

// Check Bootstrap loading
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        console.log('✅ Bootstrap loaded successfully');
    } else {
        console.error('❌ Bootstrap failed to load');
    }
});
