document.addEventListener('DOMContentLoaded', function() {
    // Simple modal handler that works with Bootstrap
    const modalButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
    
    modalButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetModalId = this.getAttribute('data-bs-target');
            const targetModal = document.querySelector(targetModalId);
            
            if (targetModal) {
                // Show modal with proper styling
                targetModal.style.display = 'block';
                targetModal.style.paddingRight = '0px';
                targetModal.classList.add('show');
                targetModal.setAttribute('aria-modal', 'true');
                targetModal.setAttribute('role', 'dialog');
                targetModal.removeAttribute('aria-hidden');
                
                // Add body classes
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
                document.body.style.paddingRight = '0px';
                
                // Close modal function
                function closeModal() {
                    targetModal.style.display = 'none';
                    targetModal.classList.remove('show');
                    targetModal.setAttribute('aria-hidden', 'true');
                    targetModal.removeAttribute('aria-modal');
                    targetModal.removeAttribute('role');
                    
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
                
                // Handle close buttons
                const closeButtons = targetModal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
                closeButtons.forEach(closeBtn => {
                    closeBtn.onclick = closeModal;
                });
                
                // Handle backdrop click (click outside modal)
                targetModal.onclick = function(e) {
                    if (e.target === targetModal) {
                        closeModal();
                    }
                };
                
                // Prevent clicks inside modal content from closing
                const modalContent = targetModal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.onclick = function(e) {
                        e.stopPropagation();
                    };
                }
                
                // Handle ESC key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && targetModal.classList.contains('show')) {
                        closeModal();
                    }
                });
            }
        });
    });
});