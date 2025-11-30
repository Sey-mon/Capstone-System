// Premium View Meal Plans JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeViewToggle();
    initializeSortFilter();
    initializeAnimations();
});

// Initialize filter functionality
function initializeFilters() {
    const childFilter = document.getElementById('childFilter');
    if (childFilter) {
        childFilter.addEventListener('change', function() {
            filterMealPlans(this.value);
        });
    }
}

// Initialize sort functionality
function initializeSortFilter() {
    const sortFilter = document.getElementById('sortFilter');
    if (sortFilter) {
        sortFilter.addEventListener('change', function() {
            sortMealPlans(this.value);
        });
    }
}

// Format meal plan details with the same formatting as ApiController.php
function formatMealPlanDetails(text) {
    if (!text) return '';
    
    // Section headings with big bold black styling (h4)
    // Ensure the child profile table is opened once and closed before other sections
    text = text.replace(/CHILD PROFILE:/gi, '<h4 class="meal-plan-heading">Child Profile</h4><div class="child-profile-table">');
        
    // Filipino specific profile items - compact inline format
    text = text.replace(/\*\*Edad\*\*:\s*([^\n\*]+)/gi, '<span class="profile-item"><strong>👶 Edad:</strong> $1</span>');
    text = text.replace(/\*\*Timbang\*\*:\s*([^\n\*]+)/gi, '<span class="profile-item"><strong>⚖️ Timbang:</strong> $1</span>');
    text = text.replace(/\*\*Taas\*\*:\s*([^\n\*]+)/gi, '<span class="profile-item"><strong>📏 Taas:</strong> $1</span>');
    text = text.replace(/\*\*BMI\*\*:\s*([^\n\*]+)/gi, '<span class="profile-item"><strong>📊 BMI:</strong> $1</span>');
    
    // Profile items: keep as inline blocks inside the child-profile-table
    text = text.replace(/\*\*Allergy\*\*:\s*([^\n\*]+)/gi, '<span class="profile-item"><strong>🚫 Allergy:</strong> $1</span>');
    text = text.replace(/\*\*Allergies\*\*:\s*([^\n\*]+)/gi, '<span class="profile-item"><strong>🚫 Allergies:</strong> $1</span>');
    text = text.replace(/\*\*Karamdaman\*\*:\s*([^\n\*]+)/gi, '<span class="profile-item"><strong>⚕️ Karamdaman:</strong> $1</span>');
    text = text.replace(/\*\*Relihiyon\*\*:\s*([^\n\*]+)/gi, '<span class="profile-item"><strong>🕌 Relihiyon:</strong> $1</span>');

    // Additional patterns for compliance items that might not have ** formatting
    text = text.replace(/(^|\n)\s*Allerg(?:y|ies):\s*([^\n]+)/gi, '<span class="profile-item"><strong>🚫 Allergy:</strong> $2</span>');
    text = text.replace(/(^|\n)\s*Relihiyon:\s*([^\n]+)/gi, '<span class="profile-item"><strong>🕌 Relihiyon:</strong> $2</span>');
    text = text.replace(/(^|\n)\s*Karamdaman:\s*([^\n]+)/gi, '<span class="profile-item"><strong>⚕️ Karamdaman:</strong> $2</span>');
    text = text.replace(/\*\*Available Ingredients\*\*:/gi, '</div><h4 class="meal-plan-heading">🥘 Available Ingredients:</h4>');
    
    // Fix the 7-DAY MEAL PLAN pattern to ensure it's properly detected as a section header
    text = text.replace(/\b7-DAY MEAL PLAN\b:?\s*/gi, '</div><h4 class="meal-plan-heading">📅 7-Day Meal Plan</h4>');
    text = text.replace(/###\s*7-DAY MEAL PLAN\s*/gi, '</div><h4 class="meal-plan-heading">📅 7-Day Meal Plan</h4>');
    text = text.replace(/\*\*Kasalukuyang Edad \([0-9]+ buwan\)\*\*:/gi, '<h5 class="meal-plan-heading">👶 Kasalukuyang Edad:</h5>');
    
    // Green h4 patterns
    text = text.replace(/AGE-SPECIFIC GUIDELINES:/gi, '<h4 class="green-heading">🍼 AGE-SPECIFIC GUIDELINES:</h4>');
    
    // Day headers - handle both quoted and unquoted formats with big bold black styling (h3)
    text = text.replace(/\*\*Day ([0-9]+)\*\*:/gi, '<h3 class="day-heading">📅 Day $1</h3>');
    text = text.replace(/"?\*\*Day ([0-9]+)\*\*"?:?/gi, '<h3 class="day-heading">📅 Day $1</h3>');
    text = text.replace(/DAY ([0-9]+):/gi, '<h3 class="day-heading">📅 Day $1</h3>');
    
    // Meal type formatting - big bold black with emojis (h4)
    text = text.replace(/- \*\*Breakfast \(Almusal\)\*\*:/gi, '<h4 class="meal-type-heading">🍳 Breakfast (Almusal)</h4>');
    text = text.replace(/- \*\*Lunch \(Tanghalian\)\*\*:/gi, '<h4 class="meal-type-heading">🍽️ Lunch (Tanghalian)</h4>');
    text = text.replace(/- \*\*Snack \(Meryenda\)\*\*:/gi, '<h4 class="meal-type-heading">🍪 Snack (Meryenda)</h4>');
    text = text.replace(/- \*\*Dinner \(Hapunan\)\*\*:/gi, '<h4 class="meal-type-heading">🌙 Dinner (Hapunan)</h4>');
    
    // Alternative patterns without parentheses (fallback) - big bold black (h4)
    text = text.replace(/- \*\*Breakfast\*\*:/gi, '<h4 class="meal-type-heading">🍳 Breakfast (Almusal)</h4>');
    text = text.replace(/- \*\*Lunch\*\*:/gi, '<h4 class="meal-type-heading">🍽️ Lunch (Tanghalian)</h4>');
    text = text.replace(/- \*\*Snack\*\*:/gi, '<h4 class="meal-type-heading">🍪 Snack (Meryenda)</h4>');
    text = text.replace(/- \*\*Dinner\*\*:/gi, '<h4 class="meal-type-heading">🌙 Dinner (Hapunan)</h4>');
    
    // Filipino observation sections - h4 headings with spacing
    text = text.replace(/REGULAR NA OBSERBAHAN:/gi, '<br><br><h4 class="observation-heading">👀 Regular na Obserbahan</h4><div class="observation">');
    
    // Observation frequency headings - simplified patterns
    text = text.replace(/\*\*Araw Araw\*\*\s*(\(MUST include this exact subheader\))?:/gi, '<h4 class="observation-subheading">📅 Araw Araw</h4>');
    text = text.replace(/\*\*Araw-Araw\*\*\s*(\(MUST include this exact subheader\))?:/gi, '<h4 class="observation-subheading">📅 Araw Araw</h4>');
    text = text.replace(/\*\*Bawat Linggo\*\*\s*(\(MUST include this exact subheader\))?:/gi, '<h4 class="observation-subheading">📊 Bawat Linggo</h4>');
    text = text.replace(/\*\*Bawat Buwan\*\*:/gi, '<h4 class="observation-subheading">📅 Bawat Buwan</h4>');
    
    // BALANSENG PAGKAIN section - h3 heading with spacing
    text = text.replace(/BALANSENG PAGKAIN PARA SA BATA:/gi, '<br><br><h3 class="balanced-food-heading">🍽️ BALANSENG PAGKAIN PARA SA BATA</h3>');
    text = text.replace(/###\\s*BALANSENG PAGKAIN PARA SA BATA/gi, '<br><br><h3 class="balanced-food-heading">🍽️ BALANSENG PAGKAIN PARA SA BATA</h3>');
    
    // Make "Bawat pagkain dapat may:" an h4 heading
    text = text.replace(/Bawat pagkain dapat may:/gi, '<h4 class="balanced-food-subheading">Bawat pagkain dapat may:</h4>');
    
    // Warning sub-sections - h4 headings 
    text = text.replace(/KAILANGAN NG AGARANG ATENSYON:/gi, '<h4 class="urgent-heading">🚨 Kailangan ng Agarang Atensyon</h4>');
    text = text.replace(/MGA DAPAT PANSININ:/gi, '<h4 class="notice-heading">👁️ Mga Dapat Pansinin</h4>');
    
    // Convert newlines to <br>
    text = text.replace(/\n/g, '<br>');
    
    return text;
}

// Filter meal plans by child with smooth animation
function filterMealPlans(childId) {
    const cards = document.querySelectorAll('.meal-plan-card-premium');
    let visibleCount = 0;
    
    // Convert childId to string for consistent comparison
    const filterValue = String(childId).trim();
    
    cards.forEach((card, index) => {
        // Get the child ID from the card and convert to string
        const cardChildId = String(card.getAttribute('data-child-id')).trim();
        
        // Show card if no filter selected or if child ID matches
        const shouldShow = filterValue === '' || cardChildId === filterValue;
        
        if (shouldShow) {
            card.style.display = 'block';
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
            card.style.animation = `fadeIn 0.5s ease-out ${index * 0.05}s both`;
            visibleCount++;
        } else {
            card.style.display = 'none';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.8)';
        }
    });
    
    // Show empty message if no cards visible
    checkEmptyState(visibleCount);
    
    // Reapply current sort after filtering
    const sortFilter = document.getElementById('sortFilter');
    if (sortFilter && sortFilter.value) {
        setTimeout(() => sortMealPlans(sortFilter.value), 100);
    }
}

// Sort meal plans
function sortMealPlans(sortBy) {
    const grid = document.getElementById('mealPlansGrid');
    if (!grid) return;
    
    const cards = Array.from(document.querySelectorAll('.meal-plan-card-premium'));
    
    // Filter out hidden cards to avoid sorting issues
    const visibleCards = cards.filter(card => card.style.display !== 'none');
    
    visibleCards.sort((a, b) => {
        switch(sortBy) {
            case 'newest':
                // Get the first meta-item which contains the date string
                const dateStrA = a.querySelector('.meta-item:first-child')?.textContent.trim() || '';
                const dateStrB = b.querySelector('.meta-item:first-child')?.textContent.trim() || '';
                
                // Parse the date strings to actual Date objects for proper comparison
                // Icon is included, so we need to extract just the date part
                const dateTextA = dateStrA.replace(/^\s*[\uD83C-\uDBFF\uDC00-\uDFFF\u2600-\u27FF]+\s*/, '').trim();
                const dateTextB = dateStrB.replace(/^\s*[\uD83C-\uDBFF\uDC00-\uDFFF\u2600-\u27FF]+\s*/, '').trim();
                
                const parsedDateA = new Date(dateTextA);
                const parsedDateB = new Date(dateTextB);
                
                // Compare dates in reverse order (newest first) - bigger timestamp = newer
                return parsedDateB.getTime() - parsedDateA.getTime();
                
            case 'oldest':
                // Get the first meta-item which contains the date string
                const dateOldStrA = a.querySelector('.meta-item:first-child')?.textContent.trim() || '';
                const dateOldStrB = b.querySelector('.meta-item:first-child')?.textContent.trim() || '';
                
                // Parse the date strings to actual Date objects for proper comparison
                const dateOldTextA = dateOldStrA.replace(/^\s*[\uD83C-\uDBFF\uDC00-\uDFFF\u2600-\u27FF]+\s*/, '').trim();
                const dateOldTextB = dateOldStrB.replace(/^\s*[\uD83C-\uDBFF\uDC00-\uDFFF\u2600-\u27FF]+\s*/, '').trim();
                
                const parsedOldDateA = new Date(dateOldTextA);
                const parsedOldDateB = new Date(dateOldTextB);
                
                // Compare dates in normal order (oldest first) - smaller timestamp = older
                return parsedOldDateA.getTime() - parsedOldDateB.getTime();
                
            case 'child':
                const nameA = a.querySelector('.child-name')?.textContent.trim() || '';
                const nameB = b.querySelector('.child-name')?.textContent.trim() || '';
                return nameA.localeCompare(nameB);
                
            default:
                return 0;
        }
    });
    
    // Clear the grid and re-append sorted cards with animation
    visibleCards.forEach((card, index) => {
        card.style.animation = 'none';
        setTimeout(() => {
            grid.appendChild(card);
            card.style.animation = `fadeIn 0.5s ease-out ${index * 0.05}s both`;
        }, 10);
    });
}

// Check and display empty state
function checkEmptyState(visibleCount) {
    let emptyMessage = document.querySelector('.no-results-message');
    
    if (visibleCount === 0 && !emptyMessage) {
        const grid = document.getElementById('mealPlansGrid');
        emptyMessage = document.createElement('div');
        emptyMessage.className = 'no-results-message';
        emptyMessage.innerHTML = `
            <div class="empty-state-premium" style="grid-column: 1/-1;">
                <div class="empty-icon-wrapper">
                    <div class="icon-circle">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                <h2 class="empty-title">No Meal Plans Found</h2>
                <p class="empty-description">
                    No meal plans match your current filter. Try selecting a different child or clear the filter.
                </p>
            </div>
        `;
        grid.appendChild(emptyMessage);
    } else if (visibleCount > 0 && emptyMessage) {
        emptyMessage.remove();
    }
}

// Initialize view toggle (grid/list)
function initializeViewToggle() {
    const viewButtons = document.querySelectorAll('[data-view]');
    const grid = document.getElementById('mealPlansGrid');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Update active button with animation
            viewButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update grid layout with transition
            if (view === 'list') {
                grid.style.gridTemplateColumns = '1fr';
                grid.querySelectorAll('.meal-plan-card-premium').forEach(card => {
                    card.style.maxWidth = '100%';
                });
            } else {
                grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(380px, 1fr))';
                grid.querySelectorAll('.meal-plan-card-premium').forEach(card => {
                    card.style.maxWidth = 'none';
                });
            }
        });
    });
}

// Initialize entrance animations
function initializeAnimations() {
    const cards = document.querySelectorAll('.meal-plan-card-premium');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
}

// Generate weekly meal table
function generateWeeklyMealTable(weeklyFormat) {
    if (!weeklyFormat || !weeklyFormat.days || !weeklyFormat.meals) {
        return '<p class="text-muted">Weekly format not available</p>';
    }
    
    const days = weeklyFormat.days;
    const meals = weeklyFormat.meals;
    
    let tableHtml = '<table class="weekly-meal-table">';
    
    // Header row with days
    tableHtml += '<thead><tr><th class="meal-type-header">MEAL</th>';
    days.forEach(day => {
        tableHtml += `<th class="day-header">${day.toUpperCase()}</th>`;
    });
    tableHtml += '</tr></thead>';
    
    // Body rows for each meal
    tableHtml += '<tbody>';
    Object.keys(meals).forEach((mealType, index) => {
        const rowClass = index % 2 === 0 ? 'even-row' : 'odd-row';
        tableHtml += `<tr class="${rowClass}">`;
        tableHtml += `<td class="meal-type-cell">${mealType}</td>`;
        
        days.forEach(day => {
            const mealContent = meals[mealType][day] || 'Not specified';
            tableHtml += `<td class="meal-content-cell">${mealContent}</td>`;
        });
        
        tableHtml += '</tr>';
    });
    tableHtml += '</tbody>';
    
    tableHtml += '</table>';
    return tableHtml;
}

// View meal plan details in SweetAlert2 modal
function viewMealPlan(planId) {
    // Show loading state with SweetAlert2
    Swal.fire({
        title: '<i class="fas fa-utensils"></i> Meal Plan Details',
        html: `
            <div class="swal-loading-state">
                <div class="swal-spinner-wrapper">
                    <div class="swal-premium-spinner"></div>
                </div>
                <p style="margin-top: 20px; color: #718096;">Loading meal plan details...</p>
            </div>
        `,
        width: '90%',
        showConfirmButton: false,
        showCloseButton: true,
        allowOutsideClick: false,
        customClass: {
            popup: 'swal-premium-popup',
            title: 'swal-premium-title',
            htmlContainer: 'swal-premium-content',
            closeButton: 'swal-premium-close'
        },
        didOpen: () => {
            // Fetch meal plan details
            fetch(`/parent/meal-plans/${planId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Generate weekly table HTML
                        const weeklyTableHtml = generateWeeklyMealTable(data.plan.weekly_format);
                        
                        Swal.update({
                            title: `<i class="fas fa-utensils"></i> Weekly Meal Plan - ${data.plan.patient_name}`,
                            html: `
                                <div class="swal-meal-plan-content">
                                    <div class="swal-patient-info">
                                        <div class="info-badge">
                                            <i class="fas fa-child"></i>
                                            <span>${data.plan.patient_name}</span>
                                        </div>
                                        <div class="info-badge">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>${data.plan.generated_at}</span>
                                        </div>
                                    </div>
                                    
                                    ${data.plan.notes ? `
                                        <div class="swal-notes-section">
                                            <div class="notes-header">
                                                <i class="fas fa-sticky-note"></i>
                                                <strong>Important Notes</strong>
                                            </div>
                                            <p>${data.plan.notes}</p>
                                        </div>
                                    ` : ''}
                                    
                                    <div class="swal-plan-details">
                                        <div class="plan-details-header">
                                            <i class="fas fa-calendar-week"></i>
                                            <h3>Weekly Meal Schedule</h3>
                                        </div>
                                        <div class="weekly-meal-table-wrapper">
                                            ${weeklyTableHtml}
                                        </div>
                                    </div>
                                </div>
                            `,
                            showConfirmButton: true,
                            confirmButtonText: '<i class="fas fa-print"></i> Print Plan',
                            showCancelButton: true,
                            cancelButtonText: '<i class="fas fa-times"></i> Close',
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#718096',
                            customClass: {
                                popup: 'swal-premium-popup',
                                title: 'swal-premium-title',
                                htmlContainer: 'swal-premium-content',
                                confirmButton: 'swal-premium-confirm',
                                cancelButton: 'swal-premium-cancel',
                                closeButton: 'swal-premium-close'
                            },
                            preConfirm: () => {
                                // Call print function and keep modal open
                                printMealPlanFromSwal(planId, data);
                                return false; // Prevent modal from closing
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to load meal plan',
                            confirmButtonColor: '#10b981'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while loading the meal plan.',
                        confirmButtonColor: '#10b981'
                    });
                });
        }
    });
}

// Print meal plan from SweetAlert modal
function printMealPlanFromSwal(planId, data) {
    // Generate the weekly table HTML for printing
    const weeklyTableHtml = generateWeeklyMealTable(data.plan.weekly_format);
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Weekly Meal Plan - ${data.plan.patient_name}</title>
            <style>
                @page { 
                    margin: 15mm;
                    size: landscape;
                }
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 20px;
                    color: #2d3748;
                }
                .header { 
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: white;
                    padding: 25px;
                    border-radius: 12px;
                    margin-bottom: 25px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0 0 10px 0;
                    font-size: 28px;
                    color: white;
                }
                .header p {
                    margin: 5px 0;
                    opacity: 0.95;
                }
                .info-section {
                    background: #f0fdf4;
                    padding: 15px 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    border-left: 5px solid #10b981;
                    display: flex;
                    justify-content: space-between;
                }
                .notes { 
                    background: #fffbeb; 
                    padding: 15px 20px; 
                    border-left: 5px solid #f59e0b;
                    margin: 20px 0;
                    border-radius: 8px;
                }
                .notes strong {
                    color: #d97706;
                }
                
                /* Weekly Meal Table Styles for Print */
                .weekly-meal-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                    page-break-inside: avoid;
                }
                .weekly-meal-table thead {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: white;
                }
                .weekly-meal-table th {
                    padding: 12px 10px;
                    text-align: center;
                    font-weight: 700;
                    font-size: 11px;
                    letter-spacing: 0.5px;
                    border: 1px solid #10b981;
                }
                .meal-type-header {
                    background: #047857 !important;
                    text-align: left !important;
                    padding-left: 15px !important;
                }
                .meal-type-cell {
                    padding: 12px 15px;
                    font-weight: 700;
                    font-size: 12px;
                    color: #047857;
                    background: #ecfdf5;
                    border: 1px solid #d1fae5;
                    text-align: left;
                    vertical-align: top;
                }
                .meal-content-cell {
                    padding: 12px 10px;
                    color: #374151;
                    border: 1px solid #e5e7eb;
                    vertical-align: top;
                    text-align: left;
                    font-size: 11px;
                    line-height: 1.5;
                }
                .weekly-meal-table tbody tr:nth-child(even) {
                    background-color: #fafafa;
                }
                
                @media print {
                    body { padding: 10px; }
                    @page { margin: 10mm; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🍎 Weekly Meal Plan</h1>
                <p><strong>${data.plan.patient_name}</strong></p>
                <p>Generated: ${data.plan.generated_at}</p>
            </div>
            
            ${data.plan.notes ? `
                <div class="notes">
                    <strong>📝 Important Notes:</strong><br>
                    <p style="margin: 10px 0 0;">${data.plan.notes}</p>
                </div>
            ` : ''}
            
            <div style="margin: 20px 0;">
                ${weeklyTableHtml}
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; text-align: center; color: #718096; font-size: 11px;">
                <p><strong>Capstone Nutrition System</strong> - Evidence-Based Nutritional Guidance</p>
                <p>Printed on ${new Date().toLocaleDateString()}</p>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
    }, 300);
}

// Print meal plan
function printMealPlan(planId) {
    showLoadingToast('Preparing meal plan for printing...');
    
    fetch(`/parent/meal-plans/${planId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                printMealPlanFromSwal(planId, data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error loading meal plan for printing',
                confirmButtonColor: '#10b981'
            });
        });
}

// Download meal plan as PDF
function downloadMealPlan(planId) {
    showLoadingToast('Generating PDF...');
    window.location.href = `/parent/meal-plans/${planId}/download`;
    
    setTimeout(() => {
        showAlert('success', 'PDF download started!');
    }, 1000);
}

// Delete meal plan with SweetAlert2 confirmation
function deleteMealPlan(planId) {
    Swal.fire({
        title: 'Delete Meal Plan?',
        html: '<p style="font-size: 15px; color: #718096; margin: 10px 0;">Are you sure you want to delete this meal plan? This action cannot be undone.</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#718096',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        customClass: {
            popup: 'swal-delete-popup',
            confirmButton: 'swal-delete-confirm',
            cancelButton: 'swal-delete-cancel'
        },
        showClass: {
            popup: 'swal2-show',
            backdrop: 'swal2-backdrop-show'
        },
        hideClass: {
            popup: 'swal2-hide',
            backdrop: 'swal2-backdrop-hide'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show deleting progress
            Swal.fire({
                title: 'Deleting...',
                html: '<div class="swal-premium-spinner"></div>',
                showConfirmButton: false,
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal-loading-popup'
                }
            });
            
            fetch(`/parent/meal-plans/${planId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Meal plan has been deleted successfully.',
                        confirmButtonColor: '#10b981',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Remove the card from the DOM with animation
                    const card = document.querySelector(`[data-child-id]`);
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            card.remove();
                            
                            // Check if there are no more cards
                            const remainingCards = document.querySelectorAll('.meal-plan-card-premium');
                            if (remainingCards.length === 0) {
                                setTimeout(() => location.reload(), 1000);
                            }
                        }, 400);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete meal plan',
                        confirmButtonColor: '#10b981'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while deleting the meal plan.',
                    confirmButtonColor: '#10b981'
                });
            });
        }
    });
}

// Remove old confirm delete function
function confirmDelete(planId) {
    // This is now handled by deleteMealPlan directly
    deleteMealPlan(planId);
}

// Print modal content
function printModalContent() {
    const modalBody = document.getElementById('mealPlanModalBody');
    const printWindow = window.open('', '_blank');
    const content = modalBody.innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Meal Plan</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 40px;
                    line-height: 1.7;
                    color: #2d3748;
                }
                h1, h2, h3, h4, h5 { 
                    color: #10b981; 
                    margin-top: 20px;
                }
                .alert { 
                    padding: 15px; 
                    margin: 15px 0; 
                    border-left: 4px solid #10b981;
                    background: #ecfdf5;
                }
                @media print {
                    body { padding: 20px; }
                }
            </style>
        </head>
        <body>
            ${content}
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
    }, 300);
}

// Show alert message with animation
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `premium-alert ${type}-alert`;
    alertDiv.innerHTML = `
        <div class="alert-icon-wrapper">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        </div>
        <div class="alert-content">
            <h4>${type === 'success' ? 'Success!' : 'Error'}</h4>
            <p>${message}</p>
        </div>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    const container = document.querySelector('.meal-plans-container .row .col-12');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateY(-20px)';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
}

// Show loading toast
function showLoadingToast(message) {
    // Remove existing toast
    const existingToast = document.querySelector('.loading-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = 'loading-toast';
    toast.innerHTML = `
        <div class="premium-spinner" style="width: 20px; height: 20px; border-width: 2px;"></div>
        <span>${message}</span>
    `;
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 9999;
        animation: slideUp 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add custom styles for SweetAlert2 modals
const style = document.createElement('style');
style.textContent = `
    /* SweetAlert2 Premium Styling */
    .swal-premium-popup {
        border-radius: 24px !important;
        padding: 0 !important;
    }
    
    .swal-premium-title {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white !important;
        padding: 30px !important;
        margin: 0 !important;
        border-radius: 24px 24px 0 0 !important;
        font-size: 24px !important;
        font-weight: 700 !important;
    }
    
    .swal-premium-title i {
        margin-right: 10px;
    }
    
    .swal-premium-content {
        padding: 40px !important;
        max-height: 70vh;
        overflow-y: auto;
        text-align: left !important;
    }
    
    .swal-premium-close {
        color: white !important;
        font-size: 28px !important;
        background: rgba(255, 255, 255, 0.2) !important;
        border-radius: 12px !important;
        width: 40px !important;
        height: 40px !important;
        transition: all 0.3s ease !important;
    }
    
    .swal-premium-close:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        transform: rotate(90deg) !important;
    }
    
    .swal-premium-confirm,
    .swal-premium-cancel {
        padding: 14px 28px !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        transition: all 0.3s ease !important;
    }
    
    .swal-premium-confirm:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4) !important;
    }
    
    /* Loading State */
    .swal-loading-state {
        padding: 20px;
        text-align: center;
    }
    
    .swal-spinner-wrapper {
        margin: 0 auto 20px;
        display: flex;
        justify-content: center;
    }
    
    .swal-premium-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #d1fae5;
        border-top-color: #10b981;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Meal Plan Content */
    .swal-meal-plan-content {
        font-size: 15px;
        line-height: 1.8;
        color: #2d3748;
    }
    
    .swal-patient-info {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 25px;
        padding: 20px;
        background: #f7fafc;
        border-radius: 12px;
    }
    
    .info-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: white;
        border-radius: 20px;
        border: 2px solid #e2e8f0;
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
    }
    
    .info-badge i {
        color: #667eea;
        font-size: 16px;
    }
    
    .swal-notes-section {
        background: linear-gradient(135deg, #fff8e1 0%, #ffe082 100%);
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        border-left: 4px solid #ffa726;
    }
    
    .notes-header {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 16px;
        color: #e65100;
        margin-bottom: 12px;
    }
    
    .notes-header i {
        font-size: 18px;
    }
    
    .swal-notes-section p {
        margin: 0;
        color: #f57c00;
        line-height: 1.7;
    }
    
    .swal-plan-details {
        margin-top: 20px;
    }
    
    .plan-details-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 15px;
        border-bottom: 3px solid #e2e8f0;
        margin-bottom: 20px;
    }
    
    .plan-details-header i {
        color: #667eea;
        font-size: 24px;
    }
    
    .plan-details-header h3 {
        margin: 0;
        color: #2d3748;
        font-size: 22px;
        font-weight: 700;
    }
    
    .plan-content-wrapper {
        background: white;
        padding: 25px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
    }
    
    .plan-content-wrapper h2 {
        color: #764ba2;
        font-size: 20px;
        margin-top: 25px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .plan-content-wrapper h3 {
        color: #667eea;
        font-size: 18px;
        margin-top: 20px;
        margin-bottom: 12px;
    }
    
    .plan-content-wrapper h4 {
        color: #055b25;
        font-size: 16px;
        margin-top: 15px;
        margin-bottom: 10px;
    }
    
    .plan-content-wrapper h5 {
        color: #000000;
        font-size: 14px;
        margin-top: 12px;
        margin-bottom: 8px;
    }
    
    .plan-content-wrapper ul,
    .plan-content-wrapper ol {
        padding-left: 30px;
        margin: 15px 0;
    }
    
    .plan-content-wrapper li {
        margin-bottom: 10px;
        line-height: 1.7;
    }
    
    .plan-content-wrapper p {
        margin-bottom: 15px;
        line-height: 1.8;
    }
    
    .plan-content-wrapper strong {
        color: #2d3748;
        font-weight: 700;
    }
    
    /* Delete Modal Styling */
    .swal-delete-popup {
        border-radius: 20px !important;
    }
    
    .swal-delete-confirm {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    }
    
    .swal-delete-confirm:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4) !important;
    }
    
    .swal-delete-cancel {
        background: #e2e8f0 !important;
        color: #4a5568 !important;
    }
    
    .swal-delete-cancel:hover {
        background: #cbd5e0 !important;
    }
    
    /* Loading Toast Styles */
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Scrollbar Styling for Modal Content */
    .swal-premium-content::-webkit-scrollbar {
        width: 8px;
    }
    
    .swal-premium-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .swal-premium-content::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }
    
    .swal-premium-content::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #5568d3 0%, #653a8b 100%);
    }
    
    /* Meal Plan Formatting Styles - matching ApiController.php formatting */
    .meal-plan-heading {
        color: #055b25;
        font-size: 18px;
        font-weight: 700;
        margin: 20px 0 10px 0;
    }
    
    .child-profile-table {
        background: #f8fffe;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .profile-item {
        display: inline-block;
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #d1fae5;
        margin: 2px;
        font-size: 14px;
    }
    
    .day-heading {
        color: #2563eb;
        font-size: 20px;
        font-weight: 700;
        margin: 25px 0 15px 0;
        padding: 10px 0;
        border-bottom: 2px solid #dbeafe;
    }
    
    .meal-type-heading {
        color: #7c3aed;
        font-size: 16px;
        font-weight: 600;
        margin: 15px 0 8px 0;
    }
    
    .green-heading {
        color: #059669;
        font-size: 16px;
        font-weight: 600;
        margin: 15px 0 10px 0;
    }
    
    .observation-heading {
        color: #dc2626;
        font-size: 18px;
        font-weight: 700;
        margin: 25px 0 15px 0;
    }
    
    .observation-subheading {
        color: #ea580c;
        font-size: 16px;
        font-weight: 600;
        margin: 15px 0 8px 0;
    }
    
    .balanced-food-heading {
        color: #7c2d12;
        font-size: 20px;
        font-weight: 700;
        margin: 25px 0 15px 0;
        text-align: center;
    }
    
    .balanced-food-subheading {
        color: #92400e;
        font-size: 16px;
        font-weight: 600;
        margin: 15px 0 10px 0;
    }
    
    .urgent-heading {
        color: #dc2626;
        font-size: 16px;
        font-weight: 700;
        margin: 15px 0 10px 0;
        background: #fef2f2;
        padding: 8px 12px;
        border-radius: 6px;
        border-left: 4px solid #dc2626;
    }
    
    .notice-heading {
        color: #d97706;
        font-size: 16px;
        font-weight: 600;
        margin: 15px 0 10px 0;
        background: #fffbeb;
        padding: 8px 12px;
        border-radius: 6px;
        border-left: 4px solid #d97706;
    }
`;
document.head.appendChild(style);

// Initialize view toggle (grid/list)
function initializeViewToggle() {
    const viewButtons = document.querySelectorAll('[data-view]');
    const grid = document.getElementById('mealPlansGrid');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update grid layout
            if (view === 'list') {
                grid.style.gridTemplateColumns = '1fr';
            } else {
                grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(350px, 1fr))';
            }
        });
    });
}
