// Nutritionist Foods JavaScript

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        if (successAlert) successAlert.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';
    }, 5000);
});

// Debounced search functionality
let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const search = e.target.value;
    const tag = document.getElementById('tagFilter')?.value || '';
    
    searchTimeout = setTimeout(() => {
        updateUrl(search, tag);
    }, 500); // Wait 500ms after user stops typing
});

document.getElementById('tagFilter')?.addEventListener('change', function(e) {
    const tag = e.target.value;
    const search = document.getElementById('searchInput')?.value || '';
    updateUrl(search, tag);
});

function updateUrl(search, tag) {
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (tag) params.set('tag', tag);
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

// View food details function
function viewFoodDetails(foodId) {
    const modal = document.getElementById('viewFoodModal');
    const content = document.getElementById('foodDetailsContent');
    
    modal.style.display = 'block';
    content.innerHTML = '<p style="text-align:center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
    
    fetch(`/admin/foods/${foodId}`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = `
                <div class="detail-row">
                    <strong>Food ID:</strong>
                    <span>${data.food_id}</span>
                </div>
                <div class="detail-row">
                    <strong>Food Name & Description:</strong>
                    <span>${data.food_name_and_description || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Alternate Names:</strong>
                    <span>${data.alternate_common_names || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Energy (kcal):</strong>
                    <span>${data.energy_kcal ? parseFloat(data.energy_kcal).toFixed(1) : '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Nutrition Tags:</strong>
                    <span>${data.nutrition_tags || '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Created:</strong>
                    <span>${data.created_at ? new Date(data.created_at).toLocaleDateString() : '-'}</span>
                </div>
                <div class="detail-row">
                    <strong>Last Updated:</strong>
                    <span>${data.updated_at ? new Date(data.updated_at).toLocaleDateString() : '-'}</span>
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = '<p style="text-align:center; padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Error loading food details</p>';
            console.error('Error:', error);
        });
}

function closeViewFoodModal() {
    document.getElementById('viewFoodModal').style.display = 'none';
}

// Quick request modal functions
function openQuickRequestModal() {
    document.getElementById('quickRequestModal').style.display = 'block';
}

function closeQuickRequestModal() {
    document.getElementById('quickRequestModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const quickModal = document.getElementById('quickRequestModal');
    const viewModal = document.getElementById('viewFoodModal');
    
    if (event.target == quickModal) {
        closeQuickRequestModal();
    }
    if (event.target == viewModal) {
        closeViewFoodModal();
    }
}
