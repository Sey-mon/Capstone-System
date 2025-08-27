// JS for toggling old assessments
function toggleOldAssessments(id) {
    var el = document.getElementById('old-assessments-' + id);
    if (el) {
        el.classList.toggle('d-none');
    }
}
