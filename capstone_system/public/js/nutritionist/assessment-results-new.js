function downloadPDF() {
    window.location.href = window.downloadPDFRoute;
}

function quickAssessment() {
    window.location.href = window.quickAssessmentRoute;
}

// Initialize Bootstrap tabs

document.addEventListener('DOMContentLoaded', function() {
    var triggerTabList = [].slice.call(document.querySelectorAll('#treatmentTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
});
