/**
 * Modal Cleanup Utility
 * Handles modal backdrop cleanup and prevents modal backdrop issues
 */

(function() {
    'use strict';

    // Global modal cleanup function
    window.cleanupModalBackdrops = function() {
        // Remove all modal backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
            backdrop.remove();
        });

        // Reset body styles
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        // Clean up any overflow hidden on html
        document.documentElement.style.overflow = '';

        console.log('✅ Modal backdrops cleaned up');
    };

    // Enhanced modal show function
    window.safeShowModal = function(modalId) {
        // First clean up any existing backdrops
        window.cleanupModalBackdrops();

        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error('Modal not found:', modalId);
            return;
        }

        if (typeof bootstrap !== 'undefined') {
            try {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } catch (error) {
                console.error('Error showing modal:', error);
                // Fallback to manual modal display
                showModalManually(modal);
            }
        } else {
            showModalManually(modal);
        }
    };

    // Enhanced modal hide function
    window.safeHideModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error('Modal not found:', modalId);
            return;
        }

        if (typeof bootstrap !== 'undefined') {
            try {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                } else {
                    hideModalManually(modal);
                }
            } catch (error) {
                console.error('Error hiding modal:', error);
                hideModalManually(modal);
            }
        } else {
            hideModalManually(modal);
        }

        // Always cleanup backdrops after hiding
        setTimeout(window.cleanupModalBackdrops, 300);
    };

    // Manual modal show (fallback)
    function showModalManually(modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Create backdrop if it doesn't exist
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.setAttribute('data-manual-backdrop', 'true');
            document.body.appendChild(backdrop);
        }
    }

    // Manual modal hide (fallback)
    function hideModalManually(modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        window.cleanupModalBackdrops();
    }

    // Hide modal when clicking backdrop
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            window.cleanupModalBackdrops();
        }
    });

    // Cleanup on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Clean up any existing backdrops on page load
        window.cleanupModalBackdrops();

        // Add event listeners to all modal close buttons
        const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"], .modal .close, .modal .btn-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                setTimeout(window.cleanupModalBackdrops, 100);
            });
        });

        console.log('✅ Modal cleanup utility initialized');
    });

    // Cleanup when navigating away
    window.addEventListener('beforeunload', function() {
        window.cleanupModalBackdrops();
    });

    // Emergency cleanup function (can be called from browser console)
    window.emergencyModalCleanup = function() {
        // Force remove all modal-related elements and styles
        const backdrops = document.querySelectorAll('.modal-backdrop');
        const modals = document.querySelectorAll('.modal.show');
        
        let cleanedItems = 0;
        
        backdrops.forEach(backdrop => {
            backdrop.remove();
            cleanedItems++;
        });
        
        modals.forEach(modal => {
            modal.style.display = 'none';
            modal.classList.remove('show');
            cleanedItems++;
        });

        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.documentElement.style.overflow = '';

        console.log(`🚨 Emergency modal cleanup completed - ${cleanedItems} items cleaned`);
        
        // Return count for programmatic use
        return cleanedItems;
    };

})();