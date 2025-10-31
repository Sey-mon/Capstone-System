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
    
    const childData = assessmentsData[childId];
    
    if (!childData) {
        Swal.fire({
            icon: 'error',
            title: 'Data Not Found',
            text: 'Could not load assessment data for this child. Please refresh the page and try again.',
        });
        return;
    }
    
    if (!childData.assessments || childData.assessments.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Assessments',
            text: 'No assessment history available for this child.',
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

        return `
            <div class="swal-assessment-container">
                <div class="swal-sidebar">
                    <div class="swal-sidebar-header">
                        <h4>
                            <i class="fas fa-history"></i>
                            Assessment History
                        </h4>
                        <p>${assessments.length} Total Assessment${assessments.length !== 1 ? 's' : ''}</p>
                    </div>
                    <div class="swal-assessment-list">
                        ${sidebar}
                    </div>
                </div>
                <div class="swal-content-area">
                    <div class="swal-modal-header">
                        <h3 class="swal-header-title">
                            <i class="fas fa-chart-line"></i>
                            ${childName}
                        </h3>
                        <p class="swal-header-subtitle">Complete assessment timeline and progress tracking</p>
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
    };

    // Calculate modal width based on screen size
    const screenWidth = window.innerWidth;
    let modalWidth = '90%';
    if (screenWidth < 768) {
        modalWidth = '95%';
    } else if (screenWidth < 1200) {
        modalWidth = '92%';
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
                    padding: 20px !important;
                    margin: 0 !important;
                    z-index: 9999 !important;
                    overflow: auto !important;
                `;
            }
            
            // Force popup centering and visibility
            const popup = document.querySelector('.parent-assessment-modal-popup');
            if (popup) {
                popup.style.cssText = `
                    display: block !important;
                    margin: auto !important;
                    position: relative !important;
                    max-width: 1200px !important;
                    width: ${modalWidth} !important;
                    max-height: 90vh !important;
                    overflow: visible !important;
                    transform: none !important;
                `;
            }
            
            // Add smooth scrolling
            const contentArea = document.querySelector('.swal-content-area');
            if (contentArea) {
                contentArea.style.scrollBehavior = 'smooth';
            }
        }
    });
}

// Legacy function for compatibility
function toggleOldAssessments(childId) {
    var el = document.getElementById('old-assessments-' + childId);
    if (el) {
        el.classList.toggle('d-none');
    }
}
