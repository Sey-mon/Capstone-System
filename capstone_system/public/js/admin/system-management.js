// System Management - Categories & Barangays Management
let allCategories = [];
let allBarangays = [];
let currentTab = 'system-health';

// TOGGLE FUNCTIONS - Must be defined early for inline handlers
function toggleCategoryClear() {
    const input = document.getElementById('categorySearch');
    const clearBtn = document.getElementById('clearCategoryBtn');
    
    if (!input || !clearBtn) return;
    
    if (input.value.trim() === '') {
        clearBtn.style.display = 'none';
    } else {
        clearBtn.style.display = 'block';
    }
    
    filterCategories(input.value);
}

function toggleBarangayClear() {
    const input = document.getElementById('barangaySearch');
    const clearBtn = document.getElementById('clearBarangayBtn');
    
    if (!input || !clearBtn) return;
    
    if (input.value.trim() === '') {
        clearBtn.style.display = 'none';
    } else {
        clearBtn.style.display = 'block';
    }
    
    filterBarangays(input.value);
}

// CLEAR FUNCTIONS - Must be defined early for onclick handlers
function clearCategorySearch() {
    const input = document.getElementById('categorySearch');
    const clearBtn = document.getElementById('clearCategoryBtn');
    if (input) {
        input.value = '';                   // Clears the text
        input.focus();                      // Puts cursor back
        
        if (clearBtn) {
            clearBtn.style.display = 'none';
        }
        
        filterCategories('');               // Reset the list
    }
}

function clearBarangaySearch() {
    const input = document.getElementById('barangaySearch');
    const clearBtn = document.getElementById('clearBarangayBtn');
    if (input) {
        input.value = '';                   // Clears the text
        input.focus();                      // Puts cursor back
        
        if (clearBtn) {
            clearBtn.style.display = 'none';
        }
        
        filterBarangays('');                // Reset the list
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Hide clear buttons initially
    const clearCategoryBtn = document.getElementById('clearCategoryBtn');
    const clearBarangayBtn = document.getElementById('clearBarangayBtn');
    if (clearCategoryBtn) clearCategoryBtn.style.display = 'none';
    if (clearBarangayBtn) clearBarangayBtn.style.display = 'none';
    
    initializeEventListeners();
    loadAllData();
});

// Initialize all event listeners
function initializeEventListeners() {
    // Tab switching
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            switchTab(this.dataset.tab);
        });
    });
}

// Load all categories and barangays for searching
function loadAllData() {
    // Load categories
    fetch('/admin/categories/data/all')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allCategories = data.data;
            }
        })
        .catch(error => console.error('Error loading categories:', error));

    // Load barangays
    fetch('/admin/barangays/data/all')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allBarangays = data.data;
            }
        })
        .catch(error => console.error('Error loading barangays:', error));
}

// Switch between tabs
function switchTab(tabName) {
    currentTab = tabName;
    
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });

    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    const selectedTab = document.getElementById(tabName + '-content');
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Add active class to clicked button
    const activeBtn = document.getElementById(tabName + '-tab');
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
}

// Filter categories based on search term
function filterCategories(searchTerm) {
    const filtered = allCategories.filter(cat => 
        cat.category_name.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Render filtered results
    renderCategoryTable(filtered);
}

// Filter barangays based on search term
function filterBarangays(searchTerm) {
    const filtered = allBarangays.filter(bar => 
        bar.barangay_name.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Render filtered results
    renderBarangayTable(filtered);
}

// Render category table with given data
function renderCategoryTable(categories) {
    const tbody = document.querySelector('#categories-content table tbody');
    if (!tbody) return;

    if (categories.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3>No categories found</h3>
                        <p>No results match your search</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = categories.map(cat => `
        <tr>
            <td style="font-weight: 500;">${escapeHtml(cat.category_name)}</td>
            <td>
                <span class="status-badge primary">
                    <i class="fas fa-box"></i>
                    ${cat.items_count} items
                </span>
            </td>
            <td>
                <div class="action-buttons-modern">
                    <button class="action-btn edit" 
                            onclick="openEditCategoryModal(${cat.category_id})"
                            title="Edit Category">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" 
                            onclick="confirmDeleteCategory(${cat.category_id}, '${escapeHtml(cat.category_name)}')"
                            title="${cat.items_count > 0 ? `Cannot delete - ${cat.items_count} items associated` : 'Delete Category'}"
                            ${cat.items_count > 0 ? 'disabled' : ''}>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Render barangay table with given data
function renderBarangayTable(barangays) {
    const tbody = document.querySelector('#barangays-content table tbody');
    if (!tbody) return;

    if (barangays.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>No barangays found</h3>
                        <p>No results match your search</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = barangays.map(bar => `
        <tr>
            <td style="font-weight: 500;">${escapeHtml(bar.barangay_name)}</td>
            <td>
                <span class="status-badge success">
                    <i class="fas fa-user-injured"></i>
                    ${bar.patients_count} patients
                </span>
            </td>
            <td>
                <div class="action-buttons-modern">
                    <button class="action-btn edit" 
                            onclick="openEditBarangayModal(${bar.barangay_id})"
                            title="Edit Barangay">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" 
                            onclick="confirmDeleteBarangay(${bar.barangay_id}, '${escapeHtml(bar.barangay_name)}')"
                            title="${bar.patients_count > 0 ? `Cannot delete - ${bar.patients_count} patients associated` : 'Delete Barangay'}"
                            ${bar.patients_count > 0 ? 'disabled' : ''}>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Modal functions for categories
function openAddCategoryModal() {
    // You can enhance this with a proper modal implementation
    const name = prompt('Enter category name:');
    if (name && name.trim()) {
        storeCategory(name);
    }
}

function openEditCategoryModal(categoryId) {
    const category = allCategories.find(c => c.category_id === categoryId);
    if (category) {
        const newName = prompt('Edit category name:', category.category_name);
        if (newName && newName.trim()) {
            updateCategory(categoryId, newName);
        }
    }
}

function confirmDeleteCategory(categoryId, categoryName) {
    if (confirm(`Are you sure you want to delete "${categoryName}"?`)) {
        deleteCategory(categoryId);
    }
}

// Modal functions for barangays
function openAddBarangayModal() {
    const name = prompt('Enter barangay name:');
    if (name && name.trim()) {
        storeBarangay(name);
    }
}

function openEditBarangayModal(barangayId) {
    const barangay = allBarangays.find(b => b.barangay_id === barangayId);
    if (barangay) {
        const newName = prompt('Edit barangay name:', barangay.barangay_name);
        if (newName && newName.trim()) {
            updateBarangay(barangayId, newName);
        }
    }
}

function confirmDeleteBarangay(barangayId, barangayName) {
    if (confirm(`Are you sure you want to delete "${barangayName}"?`)) {
        deleteBarangay(barangayId);
    }
}

// CRUD operations for categories
function storeCategory(name) {
    fetch('/admin/categories', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ category_name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category added successfully!');
            loadAllData();
            filterCategories('');
        } else {
            alert('Error: ' + (data.message || 'Failed to add category'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding category');
    });
}

function updateCategory(categoryId, name) {
    fetch(`/admin/categories/${categoryId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ category_name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category updated successfully!');
            loadAllData();
            filterCategories('');
        } else {
            alert('Error: ' + (data.message || 'Failed to update category'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating category');
    });
}

function deleteCategory(categoryId) {
    fetch(`/admin/categories/${categoryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category deleted successfully!');
            loadAllData();
            filterCategories('');
        } else {
            alert('Error: ' + (data.message || 'Failed to delete category'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting category');
    });
}

// CRUD operations for barangays
function storeBarangay(name) {
    fetch('/admin/barangays', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ barangay_name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Barangay added successfully!');
            loadAllData();
            filterBarangays('');
        } else {
            alert('Error: ' + (data.message || 'Failed to add barangay'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding barangay');
    });
}

function updateBarangay(barangayId, name) {
    fetch(`/admin/barangays/${barangayId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ barangay_name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Barangay updated successfully!');
            loadAllData();
            filterBarangays('');
        } else {
            alert('Error: ' + (data.message || 'Failed to update barangay'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating barangay');
    });
}

function deleteBarangay(barangayId) {
    fetch(`/admin/barangays/${barangayId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Barangay deleted successfully!');
            loadAllData();
            filterBarangays('');
        } else {
            alert('Error: ' + (data.message || 'Failed to delete barangay'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting barangay');
    });
}

// System health refresh
function refreshSystemHealth() {
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i>Refreshing...';
    }

    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Search table function (for backward compatibility)
function searchTable(table) {
    const searchId = table === 'categories' ? 'categorySearch' : 'barangaySearch';
    const searchInput = document.getElementById(searchId);
    
    if (searchInput) {
        if (table === 'categories') {
            filterCategories(searchInput.value);
        } else if (table === 'barangays') {
            filterBarangays(searchInput.value);
        }
    }
}


