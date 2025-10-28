// Admin Foods JavaScript

// Search functionality with debounce
let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const search = e.target.value;
    const tag = document.getElementById('tagFilter')?.value || '';
    
    searchTimeout = setTimeout(() => {
        updateUrl(search, tag);
    }, 500);
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

// Modal functions
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add Food Item';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('foodForm').action = '/admin/foods';
    document.getElementById('foodForm').reset();
    document.getElementById('foodModal').style.display = 'block';
}

function closeFoodModal() {
    document.getElementById('foodModal').style.display = 'none';
}

function editFood(id) {
    // Fetch food data with CSRF token
    fetch(`/admin/foods/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(food => {
        document.getElementById('modalTitle').textContent = 'Edit Food Item';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('foodForm').action = `/admin/foods/${id}`;
        document.getElementById('foodId').value = food.food_id;
        document.getElementById('foodName').value = food.food_name_and_description || '';
        document.getElementById('alternateNames').value = food.alternate_common_names || '';
        document.getElementById('energyKcal').value = food.energy_kcal || '';
        document.getElementById('nutritionTags').value = food.nutrition_tags || '';
        document.getElementById('foodModal').style.display = 'block';
    })
    .catch(error => {
        alert('Error loading food data: ' + error.message);
        console.error('Error:', error);
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('foodModal');
    if (event.target == modal) {
        closeFoodModal();
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

