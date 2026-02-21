// Store assessments data
let assessmentsData = {};

// Load assessments data from script tag
document.addEventListener('DOMContentLoaded', function() {
    const dataScript = document.getElementById('assessments-data');
    if (dataScript) {
        try {
            const jsonData = dataScript.textContent.trim();
            if (jsonData) {
                assessmentsData = JSON.parse(jsonData);
            }
        } catch (error) {
            console.error('Error parsing assessments data:', error);
            assessmentsData = {};
        }
    }
});

function showAssessmentHistory(childId, childName) {
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('Error: Modal library not loaded. Please refresh the page.');
        return;
    }
    
    /* ============================================
       SCALABLE ASSESSMENT HISTORY MODAL
       Features for handling 1-100+ assessments:
       - Responsive layout (mobile to 4K)
       - Sticky header with count badge
       - Scrollable list with shadow indicators
       - Auto-search filter (10+ assessments)
       - Scroll-to-top button (200px+ scroll)
       - Keyboard navigation (↑↓ arrows)
       - Smooth animations & transitions
       - Performance optimizations
       ============================================ */
    
    const childData = assessmentsData[childId];
    
    if (!childData) {
        Swal.fire({
            icon: 'error',
            title: 'Data Not Found',
            text: 'Could not load screening data for this child. Please refresh the page and try again.',
        });
        return;
    }
    
    if (!childData.assessments || childData.assessments.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Screenings',
            text: 'No screening history available for this child.',
        });
        return;
    }

    const assessments = childData.assessments;
    let currentIndex = 0;

    function renderAssessment(index) {
        const assessment = assessments[index];
        const isLatest = index === 0;
        
        let diagnosisBadge = '';
        let diagnosisClass = 'unknown';
        let diagnosisIcon = 'fa-question-circle';
        let diagnosisText = assessment.diagnosis || 'Status Unknown';
        
        if (assessment.diagnosis === 'Severe Acute Malnutrition (SAM)') {
            diagnosisClass = 'critical';
            diagnosisIcon = 'fa-exclamation-triangle';
            diagnosisText = 'Severe Acute Malnutrition';
        } else if (assessment.diagnosis === 'Moderate Acute Malnutrition (MAM)') {
            diagnosisClass = 'warning';
            diagnosisIcon = 'fa-exclamation-circle';
            diagnosisText = 'Moderate Acute Malnutrition';
        } else if (assessment.diagnosis === 'Normal') {
            diagnosisClass = 'normal';
            diagnosisIcon = 'fa-check-circle';
            diagnosisText = 'Normal Nutritional Status';
        }

        diagnosisBadge = `
            <div class="swal-diagnosis-badge ${diagnosisClass}">
                <i class="fas ${diagnosisIcon}"></i>
                <span>${diagnosisText}</span>
            </div>
        `;

        let remarksSection = '';
        if (assessment.remarks) {
            try {
                const clinicalData = JSON.parse(assessment.remarks);
                
                if (clinicalData && clinicalData.clinical_symptoms) {
                    // Structured clinical data
                    const symptoms = clinicalData.clinical_symptoms;
                    const capitalize = (str) => str ? str.charAt(0).toUpperCase() + str.slice(1) : 'N/A';
                    
                    let visibleSignsHtml = '';
                    if (symptoms.visible_signs && Array.isArray(symptoms.visible_signs) && symptoms.visible_signs.length > 0) {
                        visibleSignsHtml = `
                            <div class="clinical-item full-width">
                                <span class="clinical-label">Visible Signs:</span>
                                <span class="clinical-value">${symptoms.visible_signs.join(', ')}</span>
                            </div>
                        `;
                    }
                    
                    let additionalNotesHtml = '';
                    if (clinicalData.additional_notes) {
                        additionalNotesHtml = `
                            <div class="additional-notes-section">
                                <div class="remarks-header">
                                    <i class="fas fa-clipboard"></i>
                                    <span>Additional Notes</span>
                                </div>
                                <div class="additional-notes-content">
                                    <p>${clinicalData.additional_notes}</p>
                                </div>
                            </div>
                        `;
                    }
                    
                    remarksSection = `
                        <div class="remarks-section structured">
                            <div class="remarks-header">
                                <i class="fas fa-stethoscope"></i>
                                <span>Clinical Symptoms & Physical Signs</span>
                            </div>
                            <div class="clinical-grid">
                                <div class="clinical-item">
                                    <span class="clinical-label">Appetite:</span>
                                    <span class="clinical-value">${capitalize(symptoms.appetite)}</span>
                                </div>
                                <div class="clinical-item">
                                    <span class="clinical-label">Edema:</span>
                                    <span class="clinical-value">${capitalize(symptoms.edema)}</span>
                                </div>
                                <div class="clinical-item">
                                    <span class="clinical-label">MUAC:</span>
                                    <span class="clinical-value">${symptoms.muac || 'N/A'} cm</span>
                                </div>
                                <div class="clinical-item">
                                    <span class="clinical-label">Diarrhea:</span>
                                    <span class="clinical-value">${symptoms.diarrhea || 'N/A'} day(s)</span>
                                </div>
                                <div class="clinical-item">
                                    <span class="clinical-label">Vomiting:</span>
                                    <span class="clinical-value">${symptoms.vomiting || 'N/A'} times/day</span>
                                </div>
                                <div class="clinical-item">
                                    <span class="clinical-label">Fever:</span>
                                    <span class="clinical-value">${symptoms.fever || 'N/A'} day(s)</span>
                                </div>
                                <div class="clinical-item full-width">
                                    <span class="clinical-label">Breastfeeding Status:</span>
                                    <span class="clinical-value">${capitalize(clinicalData.breastfeeding_status)}</span>
                                </div>
                                ${visibleSignsHtml}
                            </div>
                            ${additionalNotesHtml}
                        </div>
                    `;
                } else {
                    // Plain text fallback
                    remarksSection = `
                        <div class="swal-remarks-section">
                            <div class="swal-remarks-header">
                                <i class="fas fa-comment-medical"></i>
                                Professional Remarks
                            </div>
                            <p class="swal-remarks-content">${assessment.remarks}</p>
                        </div>
                    `;
                }
            } catch (e) {
                // If JSON parsing fails, display as plain text
                remarksSection = `
                    <div class="swal-remarks-section">
                        <div class="swal-remarks-header">
                            <i class="fas fa-comment-medical"></i>
                            Professional Remarks
                        </div>
                        <p class="swal-remarks-content">${assessment.remarks}</p>
                    </div>
                `;
            }
        }

        const sidebar = assessments.map((item, idx) => `
            <div class="swal-assessment-item ${idx === index ? 'active' : ''}" onclick="selectAssessment(${idx})">
                <div class="swal-assessment-item-date">
                    <i class="fas fa-calendar-alt"></i>
                    ${item.date}
                </div>
                <div class="swal-assessment-item-status">
                    ${item.diagnosis || 'Status Unknown'}
                </div>
                ${idx === 0 ? '<span class="swal-latest-badge">Latest</span>' : ''}
            </div>
        `).join('');

        const assessmentCount = assessments.length;
        const countDisplay = assessmentCount !== 1 ? 's' : '';
        const showSearch = assessmentCount > 10; // Show search if more than 10 assessments

        return `
            <div class="swal-assessment-container">
                <div class="swal-sidebar" id="assessment-sidebar">
                    <div class="swal-sidebar-header">
                        <h4>
                            <i class="fas fa-history"></i>
                            Screening History
                            <span class="swal-assessment-counter">${assessmentCount}</span>
                        </h4>
                        <p>${assessmentCount} Total Screening${countDisplay}</p>
                        ${showSearch ? `
                        <div class="swal-assessment-search has-many">
                            <input 
                                type="text" 
                                id="assessment-search-input" 
                                placeholder="Search by date or status..."
                                onkeyup="filterAssessments()"
                            >
                            <i class="fas fa-search"></i>
                        </div>
                        ` : ''}
                    </div>
                    <div class="swal-assessment-list" id="assessment-list">
                        ${sidebar}
                    </div>
                    <button class="swal-scroll-top-btn" id="scroll-top-btn" onclick="scrollToTopOfList()">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
                <div class="swal-content-area">
                    <div class="swal-modal-header">
                        <h3 class="swal-header-title">
                            <i class="fas fa-chart-line"></i>
                            ${childName}
                        </h3>
                        <p class="swal-header-subtitle">Complete screening timeline and progress tracking</p>
                        <div class="swal-header-buttons">
                            <button class="btn btn-primary btn-sm swal-treatment-button" onclick="showTreatmentPlan(${assessment.id}, '${childName.replace(/'/g, "\\'")}', ${index})">
                                <i class="fas fa-prescription me-1"></i>
                                View Treatment Plan
                            </button>
                        </div>
                    </div>
                    <div class="swal-assessment-detail">
                        <div class="swal-detail-header">
                            <div class="swal-detail-date">
                                <i class="fas fa-calendar-check"></i>
                                ${assessment.date}
                                ${isLatest ? '<span class="swal-latest-badge" style="margin-left: 10px;">Latest</span>' : ''}
                            </div>
                            ${diagnosisBadge}
                        </div>
                        <div class="swal-metrics-grid">
                            <div class="swal-metric-card">
                                <div class="swal-metric-icon weight">
                                    <i class="fas fa-weight"></i>
                                </div>
                                <div class="swal-metric-info">
                                    <span class="swal-metric-label">Weight</span>
                                    <span class="swal-metric-value">${assessment.weight} kg</span>
                                </div>
                            </div>
                            <div class="swal-metric-card">
                                <div class="swal-metric-icon height">
                                    <i class="fas fa-ruler-vertical"></i>
                                </div>
                                <div class="swal-metric-info">
                                    <span class="swal-metric-label">Height</span>
                                    <span class="swal-metric-value">${assessment.height} cm</span>
                                </div>
                            </div>
                            <div class="swal-metric-card">
                                <div class="swal-metric-icon nutritionist">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div class="swal-metric-info">
                                    <span class="swal-metric-label">Assessed By</span>
                                    <span class="swal-metric-value">${assessment.nutritionist}</span>
                                </div>
                            </div>
                        </div>
                        <div class="swal-indicators-section">
                            <h4 class="swal-section-title">
                                <i class="fas fa-chart-bar"></i>
                                Nutritional Indicators
                            </h4>
                            <div class="swal-indicators-grid">
                                <div class="swal-indicator-item">
                                    <span class="swal-indicator-label">Weight for Age:</span>
                                    <span class="swal-indicator-badge">${assessment.weight_for_age || 'Not assessed'}</span>
                                </div>
                                <div class="swal-indicator-item">
                                    <span class="swal-indicator-label">Height for Age:</span>
                                    <span class="swal-indicator-badge">${assessment.height_for_age || 'Not assessed'}</span>
                                </div>
                                <div class="swal-indicator-item">
                                    <span class="swal-indicator-label">BMI for Age:</span>
                                    <span class="swal-indicator-badge">${assessment.bmi_for_age || 'Not assessed'}</span>
                                </div>
                            </div>
                        </div>
                        ${remarksSection}
                    </div>
                </div>
            </div>
        `;
    }

    window.selectAssessment = function(index) {
        currentIndex = index;
        Swal.update({
            html: renderAssessment(index)
        });
        
        // Re-initialize scroll listeners after update
        setTimeout(() => {
            initScrollListeners();
        }, 100);
    };
    
    // Scroll to top of assessment list
    window.scrollToTopOfList = function() {
        const listElement = document.getElementById('assessment-list');
        if (listElement) {
            listElement.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    };
    
    // Filter assessments based on search input
    window.filterAssessments = function() {
        const searchInput = document.getElementById('assessment-search-input');
        const listElement = document.getElementById('assessment-list');
        
        if (!searchInput || !listElement) return;
        
        const searchTerm = searchInput.value.toLowerCase();
        const items = listElement.querySelectorAll('.swal-assessment-item');
        let visibleCount = 0;
        
        items.forEach(item => {
            const date = item.querySelector('.swal-assessment-item-date').textContent.toLowerCase();
            const status = item.querySelector('.swal-assessment-item-status').textContent.toLowerCase();
            
            const matches = date.includes(searchTerm) || status.includes(searchTerm);
            
            if (matches || searchTerm === '') {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Update header count
        const headerP = document.querySelector('.swal-sidebar-header p');
        if (headerP && searchTerm !== '') {
            headerP.textContent = `${visibleCount} of ${items.length} Screening${items.length !== 1 ? 's' : ''}`;
        } else if (headerP) {
            headerP.textContent = `${items.length} Total Screening${items.length !== 1 ? 's' : ''}`;
        }
    };
    
    // Initialize scroll shadow detection
    function initScrollListeners() {
        const listElement = document.getElementById('assessment-list');
        const sidebarElement = document.getElementById('assessment-sidebar');
        const scrollTopBtn = document.getElementById('scroll-top-btn');
        
        if (!listElement || !sidebarElement) return;
        
        function updateScrollShadows() {
            const scrollTop = listElement.scrollTop;
            const scrollHeight = listElement.scrollHeight;
            const clientHeight = listElement.clientHeight;
            const scrollBottom = scrollHeight - scrollTop - clientHeight;
            
            // Show top shadow if scrolled down more than 10px
            if (scrollTop > 10) {
                sidebarElement.classList.add('has-scroll-top');
            } else {
                sidebarElement.classList.remove('has-scroll-top');
            }
            
            // Show bottom shadow if not at bottom
            if (scrollBottom > 10) {
                sidebarElement.classList.add('has-scroll-bottom');
            } else {
                sidebarElement.classList.remove('has-scroll-bottom');
            }
            
            // Show scroll to top button if scrolled down
            if (scrollTopBtn) {
                if (scrollTop > 200) {
                    scrollTopBtn.classList.add('show');
                } else {
                    scrollTopBtn.classList.remove('show');
                }
            }
        }
        
        // Initial check
        updateScrollShadows();
        
        // Listen to scroll events
        listElement.addEventListener('scroll', updateScrollShadows);
        
        // Check after a short delay (for dynamic content)
        setTimeout(updateScrollShadows, 100);
    }

    // Calculate modal width based on screen size - more granular scaling
    const screenWidth = window.innerWidth;
    let modalWidth = '85%';
    let maxWidth = '1400px';
    
    if (screenWidth < 640) {
        modalWidth = '98%';
        maxWidth = 'none';
    } else if (screenWidth < 768) {
        modalWidth = '95%';
        maxWidth = 'none';
    } else if (screenWidth < 1024) {
        modalWidth = '92%';
        maxWidth = '900px';
    } else if (screenWidth < 1280) {
        modalWidth = '88%';
        maxWidth = '1100px';
    } else if (screenWidth < 1536) {
        modalWidth = '85%';
        maxWidth = '1300px';
    }

    Swal.fire({
        html: renderAssessment(0),
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'parent-assessment-modal-container',
            popup: 'parent-assessment-modal-popup'
        },
        width: modalWidth,
        heightAuto: false,
        backdrop: true,
        allowOutsideClick: true,
        didOpen: () => {
            // Force container to full viewport
            const container = document.querySelector('.parent-assessment-modal-container');
            if (container) {
                container.style.cssText = `
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    bottom: 0 !important;
                    width: 100vw !important;
                    height: 100vh !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    padding: clamp(10px, 2vw, 20px) !important;
                    margin: 0 !important;
                    z-index: 9999 !important;
                    overflow: auto !important;
                `;
            }
            
            // Force popup centering and visibility with dynamic sizing
            const popup = document.querySelector('.parent-assessment-modal-popup');
            if (popup) {
                popup.style.cssText = `
                    display: block !important;
                    margin: auto !important;
                    position: relative !important;
                    max-width: ${maxWidth} !important;
                    width: ${modalWidth} !important;
                    max-height: 92vh !important;
                    overflow: visible !important;
                    transform: none !important;
                `;
            }
            
            // Add smooth scrolling
            const contentArea = document.querySelector('.swal-content-area');
            if (contentArea) {
                contentArea.style.scrollBehavior = 'smooth';
            }
            
            // Initialize scroll shadow listeners
            initScrollListeners();
            
            // Add keyboard navigation
            document.addEventListener('keydown', handleKeyboardNavigation);
            
            // Handle window resize
            const handleResize = () => {
                const newWidth = window.innerWidth;
                let newModalWidth = '85%';
                let newMaxWidth = '1400px';
                
                if (newWidth < 640) {
                    newModalWidth = '98%';
                    newMaxWidth = 'none';
                } else if (newWidth < 768) {
                    newModalWidth = '95%';
                    newMaxWidth = 'none';
                } else if (newWidth < 1024) {
                    newModalWidth = '92%';
                    newMaxWidth = '900px';
                } else if (newWidth < 1280) {
                    newModalWidth = '88%';
                    newMaxWidth = '1100px';
                } else if (newWidth < 1536) {
                    newModalWidth = '85%';
                    newMaxWidth = '1300px';
                }
                
                if (popup) {
                    popup.style.width = newModalWidth;
                    popup.style.maxWidth = newMaxWidth;
                }
            };
            
            window.addEventListener('resize', handleResize);
            
            // Cleanup on close
            const observer = new MutationObserver((mutations) => {
                if (!document.body.contains(popup)) {
                    window.removeEventListener('resize', handleResize);
                    document.removeEventListener('keydown', handleKeyboardNavigation);
                    observer.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    });
    
    // Keyboard navigation handler
    function handleKeyboardNavigation(e) {
        const listElement = document.getElementById('assessment-list');
        if (!listElement) return;
        
        // Don't interfere if user is typing in search
        if (document.activeElement.id === 'assessment-search-input') return;
        
        const items = Array.from(listElement.querySelectorAll('.swal-assessment-item'));
        const activeIndex = items.findIndex(item => item.classList.contains('active'));
        
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
            
            let newIndex = activeIndex;
            if (e.key === 'ArrowUp' && activeIndex > 0) {
                newIndex = activeIndex - 1;
            } else if (e.key === 'ArrowDown' && activeIndex < items.length - 1) {
                newIndex = activeIndex + 1;
            }
            
            if (newIndex !== activeIndex) {
                selectAssessment(newIndex);
                
                // Scroll the item into view
                setTimeout(() => {
                    const newActiveItem = items[newIndex];
                    if (newActiveItem && listElement) {
                        const itemTop = newActiveItem.offsetTop;
                        const itemHeight = newActiveItem.offsetHeight;
                        const listScrollTop = listElement.scrollTop;
                        const listHeight = listElement.clientHeight;
                        
                        if (itemTop < listScrollTop) {
                            listElement.scrollTop = itemTop - 10;
                        } else if (itemTop + itemHeight > listScrollTop + listHeight) {
                            listElement.scrollTop = itemTop + itemHeight - listHeight + 10;
                        }
                    }
                }, 50);
            }
        }
    }
}

// Legacy function for compatibility
function toggleOldAssessments(childId) {
    var el = document.getElementById('old-assessments-' + childId);
    if (el) {
        el.classList.toggle('d-none');
    }
}

// Show treatment plan view
function showTreatmentPlan(assessmentId, childName, currentIndex) {
    // Find the assessment data from the embedded JSON
    let assessment = null;
    let childId = null;
    
    // Search through all children's assessments to find the matching one
    for (const [patientId, childData] of Object.entries(assessmentsData)) {
        const foundAssessment = childData.assessments.find(a => a.id == assessmentId);
        if (foundAssessment) {
            assessment = foundAssessment;
            childId = patientId;
            break;
        }
    }
    
    if (!assessment) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Assessment data not found. Please refresh the page and try again.'
        });
        return;
    }
    
    renderTreatmentPlanView(assessment, childName, childId, currentIndex);
}

function renderTreatmentPlanView(assessment, childName, childId, currentIndex) {
    // Parse treatment plan from the diagnosis field or other available data
    // The diagnosis field contains the AI-generated diagnosis
    // We'll need to check if there's a treatment field in the assessment data
    
    const diagnosis = assessment.diagnosis || 'Status Unknown';
    
    // Determine diagnosis styling
    let diagnosisClass = 'unknown';
    let diagnosisIcon = 'fa-question-circle';
    if (diagnosis.includes('Severe')) {
        diagnosisClass = 'critical';
        diagnosisIcon = 'fa-exclamation-triangle';
    } else if (diagnosis.includes('Moderate')) {
        diagnosisClass = 'warning';
        diagnosisIcon = 'fa-exclamation-circle';
    } else if (diagnosis.includes('Normal')) {
        diagnosisClass = 'normal';
        diagnosisIcon = 'fa-check-circle';
    }

    // For parents, we'll show general treatment guidelines based on diagnosis
    let treatmentContent = '';
    
    if (diagnosis.includes('Severe Acute Malnutrition')) {
        treatmentContent = `
            <div class="swal-treatment-section">
                <h4 class="swal-section-title">
                    <i class="fas fa-bolt"></i>
                    Immediate Actions Required
                </h4>
                <ul class="swal-treatment-list">
                    <li>Immediate medical consultation with nutritionist</li>
                    <li>Follow prescribed therapeutic feeding program</li>
                    <li>Monitor child's appetite and food intake daily</li>
                    <li>Ensure all scheduled check-ups are attended</li>
                </ul>
            </div>
            <div class="swal-treatment-section">
                <h4 class="swal-section-title">
                    <i class="fas fa-heartbeat"></i>
                    Monitoring & Follow-up
                </h4>
                <ul class="swal-treatment-list">
                    <li>Weekly weight monitoring</li>
                    <li>Watch for signs of improvement or deterioration</li>
                    <li>Keep a daily food diary</li>
                    <li>Regular follow-up appointments as scheduled</li>
                </ul>
            </div>
            <div class="swal-treatment-section swal-emergency">
                <h4 class="swal-section-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Emergency Warning Signs
                </h4>
                <ul class="swal-treatment-list">
                    <li>Refusal to eat or drink for 24 hours</li>
                    <li>Severe diarrhea or vomiting</li>
                    <li>High fever or difficulty breathing</li>
                    <li>Extreme lethargy or unresponsiveness</li>
                    <li>Seek immediate medical attention if any of these occur</li>
                </ul>
            </div>
        `;
    } else if (diagnosis.includes('Moderate Acute Malnutrition')) {
        treatmentContent = `
            <div class="swal-treatment-section">
                <h4 class="swal-section-title">
                    <i class="fas fa-bolt"></i>
                    Recommended Actions
                </h4>
                <ul class="swal-treatment-list">
                    <li>Increase energy-dense, nutritious foods</li>
                    <li>Provide frequent, small meals (5-6 times daily)</li>
                    <li>Follow nutritionist's meal plan recommendations</li>
                    <li>Ensure adequate hydration</li>
                </ul>
            </div>
            <div class="swal-treatment-section">
                <h4 class="swal-section-title">
                    <i class="fas fa-heartbeat"></i>
                    Monitoring & Follow-up
                </h4>
                <ul class="swal-treatment-list">
                    <li>Bi-weekly weight monitoring</li>
                    <li>Track food intake and appetite</li>
                    <li>Regular nutritionist consultations</li>
                    <li>Monitor for any signs of worsening condition</li>
                </ul>
            </div>
            <div class="swal-treatment-section">
                <h4 class="swal-section-title">
                    <i class="fas fa-users"></i>
                    Family Support
                </h4>
                <ul class="swal-treatment-list">
                    <li>Attend nutrition education sessions</li>
                    <li>Learn about locally available nutritious foods</li>
                    <li>Create a positive mealtime environment</li>
                    <li>Involve family in meal planning and preparation</li>
                </ul>
            </div>
        `;
    } else if (diagnosis.includes('Normal')) {
        treatmentContent = `
            <div class="swal-treatment-section">
                <h4 class="swal-section-title">
                    <i class="fas fa-check-circle"></i>
                    Maintain Healthy Status
                </h4>
                <ul class="swal-treatment-list">
                    <li>Continue with balanced, nutritious diet</li>
                    <li>Maintain regular meal times</li>
                    <li>Encourage physical activity appropriate for age</li>
                    <li>Ensure adequate sleep and rest</li>
                </ul>
            </div>
            <div class="swal-treatment-section">
                <h4 class="swal-section-title">
                    <i class="fas fa-heartbeat"></i>
                    Monitoring & Follow-up
                </h4>
                <ul class="swal-treatment-list">
                    <li>Regular growth monitoring as scheduled</li>
                    <li>Attend routine check-ups</li>
                    <li>Stay updated with vaccination schedule</li>
                    <li>Monitor for any changes in appetite or behavior</li>
                </ul>
            </div>
        `;
    } else {
        treatmentContent = `
            <div class="swal-treatment-section">
                <h4 class="swal-section-title">
                    <i class="fas fa-info-circle"></i>
                    General Guidance
                </h4>
                <ul class="swal-treatment-list">
                    <li>Consult with your nutritionist for specific recommendations</li>
                    <li>Follow the personalized meal plan provided</li>
                    <li>Attend all scheduled appointments</li>
                    <li>Keep track of your child's progress</li>
                </ul>
            </div>
        `;
    }

    const htmlContent = `
        <div class="swal-treatment-container">
            <div class="swal-treatment-header">
                <button class="btn btn-secondary btn-sm swal-back-button" onclick="backToScreeningDetails(${childId}, ${assessment.id})">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Screening
                </button>
                <h3 class="swal-header-title">
                    <i class="fas fa-prescription"></i>
                    Treatment & Care Guidelines
                </h3>
                <p class="swal-header-subtitle">${childName} - ${assessment.date}</p>
            </div>
            
            <div class="swal-treatment-content">
                <div class="swal-diagnosis-header">
                    <div class="swal-diagnosis-badge ${diagnosisClass}">
                        <i class="fas ${diagnosisIcon}"></i>
                        <span>${diagnosis}</span>
                    </div>
                </div>

                ${treatmentContent}
                
                <div class="swal-treatment-section" style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 1rem; margin-top: 1.5rem;">
                    <h4 class="swal-section-title" style="color: #1e40af;">
                        <i class="fas fa-info-circle"></i>
                        Important Note
                    </h4>
                    <p style="margin: 0; color: #1e3a8a;">
                        These are general guidelines based on the nutritional status assessment. 
                        For personalized treatment plans and specific medical advice, please consult 
                        with your assigned nutritionist during scheduled appointments.
                    </p>
                </div>
            </div>
        </div>
    `;

    Swal.fire({
        html: htmlContent,
        width: '90%',
        showCancelButton: false,
        showConfirmButton: false,
        showCloseButton: true,
        scrollbarPadding: false,
        heightAuto: false,
        customClass: {
            container: 'parent-assessment-modal-container',
            popup: 'parent-assessment-modal-popup treatment-popup',
            htmlContainer: 'p-0',
            closeButton: 'swal-close-button'
        },
        buttonsStyling: false
    });
}

function backToScreeningDetails(childId, selectedAssessmentId) {
    Swal.close();
    const childData = assessmentsData[childId];
    if (childData) {
        const assessmentIndex = childData.assessments.findIndex(a => a.id == selectedAssessmentId);
        if (assessmentIndex !== -1) {
            showAssessmentHistory(childId, childData.name);
        }
    }
}

// Download assessment PDF
function downloadAssessmentPDF(assessmentId) {
    // Open PDF download in new window
    // The route should be accessible to parents to download their children's assessment PDFs
    const pdfUrl = `/nutritionist/assessment/${assessmentId}/pdf`;
    window.open(pdfUrl, '_blank');
}
