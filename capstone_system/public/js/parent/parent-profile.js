/**
 * Profile Page JavaScript Functions
 * Handles tab switching and edit mode toggling
 */

/**
 * Switch between profile tabs (Personal Information and Security)
 * @param {string} tabName - The name of the tab to show
 */
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    const selectedTab = document.getElementById(tabName + '-tab');
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Add active class to clicked tab button
    if (event && event.target) {
        event.target.classList.add('active');
    }
}

/**
 * Toggle between view mode and edit mode for personal information
 */
function toggleEdit() {
    const viewMode = document.getElementById('view-mode');
    const editMode = document.getElementById('edit-mode');
    
    if (!viewMode || !editMode) {
        console.error('View mode or edit mode elements not found');
        return;
    }
    
    if (viewMode.style.display === 'none') {
        viewMode.style.display = 'block';
        editMode.style.display = 'none';
    } else {
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
    }
}

/**
 * Initialize profile page functionality when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners to tab buttons
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.textContent.toLowerCase().includes('personal') ? 'personal' : 'security';
            showTab(tabName);
        });
    });
    
    // Add form validation if needed
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add any form validation logic here if needed
            console.log('Form submitted');
        });
    });
});