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

        const remarksSection = assessment.remarks ? `
            <div class="swal-remarks-section">
                <div class="swal-remarks-header">
                    <i class="fas fa-comment-medical"></i>
                    Professional Remarks
                </div>
                <p class="swal-remarks-content">${assessment.remarks}</p>
            </div>
        ` : '';

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
