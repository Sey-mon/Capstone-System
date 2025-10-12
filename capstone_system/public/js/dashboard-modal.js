// Enhanced modal functions that use the cleanup utility
window.modalFallbacks = {
    openModal: function(modalId) {
        if (typeof window.safeShowModal === 'function') {
            window.safeShowModal(modalId);
        } else {
            // Fallback if cleanup utility isn't loaded
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
        }
    },
    closeModal: function(modalId) {
        if (typeof window.safeHideModal === 'function') {
            window.safeHideModal(modalId);
        } else {
            // Fallback if cleanup utility isn't loaded
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
            // Always try to cleanup backdrops
            if (typeof window.cleanupModalBackdrops === 'function') {
                setTimeout(window.cleanupModalBackdrops, 300);
            }
        }
    }
};

// Global fallback functions for specific modals
if (typeof window.openAddPatientModal === 'undefined') {
    window.openAddPatientModal = function() {
        console.log('Using fallback for Add Patient Modal');
        window.modalFallbacks.openModal('patientModal');
    };
}

// Global function to close any modal and cleanup backdrops
window.closeAnyModal = function() {
    if (typeof window.cleanupModalBackdrops === 'function') {
        window.cleanupModalBackdrops();
    }
};

// Check Bootstrap loading
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        console.log('✅ Bootstrap loaded successfully');
    } else {
        console.error('❌ Bootstrap failed to load');
    }
});
