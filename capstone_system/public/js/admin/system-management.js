/**
 * System Management with SweetAlert2 and AJAX
 */

// Global variables for full-page search
let allCategories = [];
let filteredCategories = [];
let currentCategoryPage = 1;
let itemsPerPage = 10;

let allBarangays = [];
let filteredBarangays = [];
let currentBarangayPage = 1;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    setupTabSwitching();
    restoreActiveTab();
    setupSearchListeners();
});

// Tab Switching
function setupTabSwitching() {
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tab = this.getAttribute('data-tab');
            switchTab(tab);
        });
    });
}

function switchTab(tab) {
    localStorage.setItem('systemManagementActiveTab', tab);
    
    // Update buttons
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
    
    // Update content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
        content.classList.remove('active');
    });
    
    const targetContent = document.getElementById(tab + '-content');
    if (targetContent) {
        targetContent.style.display = 'block';
        targetContent.classList.add('active');
        
        // Load data for this tab if needed
        if (tab === 'categories') {
            loadAllCategories();
        } else if (tab === 'barangays') {
            loadAllBarangays();
        }
    }
}

function restoreActiveTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasBarangayPagination = urlParams.has('barangays_page');
    const hasCategoryPagination = urlParams.has('categories_page');
    
    let activeTab = 'system-health';
    
    if (hasBarangayPagination) {
        activeTab = 'barangays';
    } else if (hasCategoryPagination) {
        activeTab = 'categories';
    } else {
        const savedTab = localStorage.getItem('systemManagementActiveTab');
        if (savedTab) {
            activeTab = savedTab;
        }
    }
    
    if (activeTab !== 'system-health') {
        switchTab(activeTab);
    }
}

// HTML Escape Function
function escapeHtml(text, escapeQuotes = false) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    let result = text.replace(/[&<>"']/g, m => map[m]);
    if (escapeQuotes) {
        result = result.replace(/"/g, '&quot;').replace(/'/g, "\\'");
    }
    return result;
}

// ============================================
// CATEGORY MANAGEMENT
// ============================================

function openAddCategoryModal() {
    Swal.fire({
        title: 'Add New Category',
        html: `
            <div style="text-align: left;">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">
                        Category Name <span style="color: #dc3545;">*</span>
                    </label>
                    <input type="text" 
                           id="swal-category-name" 
                           class="swal2-input" 
                           placeholder="Enter category name"
                           style="width: 100%; margin: 0; box-sizing: border-box;">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Category',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#5cb85c',
        cancelButtonColor: '#6c757d',
        width: '500px',
        didOpen: () => {
            document.getElementById('swal-category-name').focus();
        },
        preConfirm: () => {
            const categoryName = document.getElementById('swal-category-name').value.trim();
            
            if (!categoryName) {
                Swal.showValidationMessage('Please enter a category name');
                return false;
            }
            
            return { category_name: categoryName };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            saveCategory(result.value, false);
        }
    });
}

function openEditCategoryModal(categoryId) {
    fetch(`/admin/categories/${categoryId}`)
        .then(response => response.json())
        .then(data => {
            const category = data.itemcategory || data.category;
            
            if (!category) {
                throw new Error('Category data not found');
            }
            
            Swal.fire({
                title: 'Edit Category',
                html: `
                    <div style="text-align: left;">
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">
                                Category Name <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" 
                                   id="swal-edit-category-name" 
                                   class="swal2-input" 
                                   value="${escapeHtml(category.category_name)}"
                                   style="width: 100%; margin: 0; box-sizing: border-box;">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Category',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#5cb85c',
                cancelButtonColor: '#6c757d',
                width: '500px',
                didOpen: () => {
                    document.getElementById('swal-edit-category-name').focus();
                },
                preConfirm: () => {
                    const categoryName = document.getElementById('swal-edit-category-name').value.trim();
                    
                    if (!categoryName) {
                        Swal.showValidationMessage('Please enter a category name');
                        return false;
                    }
                    
                    return { category_name: categoryName };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    saveCategory(result.value, true, categoryId);
                }
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load category data',
                confirmButtonColor: '#5cb85c'
            });
        });
}

function saveCategory(data, isEdit, categoryId = null) {
    const url = isEdit ? `/admin/categories/${categoryId}` : '/admin/categories';
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#5cb85c',
                timer: 2000
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to save category',
                confirmButtonColor: '#5cb85c'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while saving',
            confirmButtonColor: '#5cb85c'
        });
    });
}

function confirmDeleteCategory(categoryId, categoryName) {
    Swal.fire({
        title: 'Delete Category?',
        html: `Are you sure you want to delete <strong>${escapeHtml(categoryName)}</strong>?<br><br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            deleteCategory(categoryId);
        }
    });
}

function deleteCategory(categoryId) {
    fetch(`/admin/categories/${categoryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message,
                confirmButtonColor: '#5cb85c',
                timer: 2000
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to delete category',
                confirmButtonColor: '#5cb85c'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while deleting',
            confirmButtonColor: '#5cb85c'
        });
    });
}

// ============================================
// BARANGAY MANAGEMENT
// ============================================

function openAddBarangayModal() {
    Swal.fire({
        title: 'Add New Barangay',
        html: `
            <div style="text-align: left;">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">
                        Barangay Name <span style="color: #dc3545;">*</span>
                    </label>
                    <input type="text" 
                           id="swal-barangay-name" 
                           class="swal2-input" 
                           placeholder="Enter barangay name"
                           style="width: 100%; margin: 0; box-sizing: border-box;">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Barangay',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#5cb85c',
        cancelButtonColor: '#6c757d',
        width: '500px',
        didOpen: () => {
            document.getElementById('swal-barangay-name').focus();
        },
        preConfirm: () => {
            const barangayName = document.getElementById('swal-barangay-name').value.trim();
            
            if (!barangayName) {
                Swal.showValidationMessage('Please enter a barangay name');
                return false;
            }
            
            return { barangay_name: barangayName };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            saveBarangay(result.value, false);
        }
    });
}

function openEditBarangayModal(barangayId) {
    fetch(`/admin/barangays/${barangayId}`)
        .then(response => response.json())
        .then(data => {
            const barangay = data.barangay;
            
            if (!barangay) {
                throw new Error('Barangay data not found');
            }
            
            Swal.fire({
                title: 'Edit Barangay',
                html: `
                    <div style="text-align: left;">
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">
                                Barangay Name <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" 
                                   id="swal-edit-barangay-name" 
                                   class="swal2-input" 
                                   value="${escapeHtml(barangay.barangay_name)}"
                                   style="width: 100%; margin: 0; box-sizing: border-box;">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Barangay',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#5cb85c',
                cancelButtonColor: '#6c757d',
                width: '500px',
                didOpen: () => {
                    document.getElementById('swal-edit-barangay-name').focus();
                },
                preConfirm: () => {
                    const barangayName = document.getElementById('swal-edit-barangay-name').value.trim();
                    
                    if (!barangayName) {
                        Swal.showValidationMessage('Please enter a barangay name');
                        return false;
                    }
                    
                    return { barangay_name: barangayName };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    saveBarangay(result.value, true, barangayId);
                }
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load barangay data',
                confirmButtonColor: '#5cb85c'
            });
        });
}

function saveBarangay(data, isEdit, barangayId = null) {
    const url = isEdit ? `/admin/barangays/${barangayId}` : '/admin/barangays';
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#5cb85c',
                timer: 2000
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to save barangay',
                confirmButtonColor: '#5cb85c'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while saving',
            confirmButtonColor: '#5cb85c'
        });
    });
}

function confirmDeleteBarangay(barangayId, barangayName) {
    Swal.fire({
        title: 'Delete Barangay?',
        html: `Are you sure you want to delete <strong>${escapeHtml(barangayName)}</strong>?<br><br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            deleteBarangay(barangayId);
        }
    });
}

function deleteBarangay(barangayId) {
    fetch(`/admin/barangays/${barangayId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message,
                confirmButtonColor: '#5cb85c',
                timer: 2000
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to delete barangay',
                confirmButtonColor: '#5cb85c'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while deleting',
            confirmButtonColor: '#5cb85c'
        });
    });
}

// ============================================
// FILTER FUNCTIONALITY
// ============================================

function filterTable() {
    const searchInput = document.getElementById('searchFilter');
    const categoryFilter = document.getElementById('categoryFilterSelect');
    
    if (!searchInput) return;
    
    const searchValue = searchInput.value.toLowerCase();
    const activeTab = document.querySelector('.tab-content.active');
    
    if (!activeTab) return;
    
    const rows = activeTab.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.querySelector('.empty-state')) {
            return;
        }
        
        const text = row.textContent.toLowerCase();
        const matchesSearch = text.includes(searchValue);
        
        if (matchesSearch) {
            row.style.display = '';
            visibleCount++;
            
            // Highlight matching text
            if (searchValue) {
                row.querySelectorAll('td').forEach(cell => {
                    if (!cell.querySelector('.action-buttons')) {
                        const originalText = cell.textContent;
                        const regex = new RegExp(`(${searchValue})`, 'gi');
                        if (regex.test(originalText)) {
                            cell.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
                        }
                    }
                });
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update results count if exists
    const resultsCount = activeTab.querySelector('.results-count');
    if (resultsCount) {
        resultsCount.textContent = `Showing ${visibleCount} results`;
    }
}

function clearFilters() {
    const searchInput = document.getElementById('searchFilter');
    
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Remove all highlights
    document.querySelectorAll('mark').forEach(mark => {
        const parent = mark.parentNode;
        parent.textContent = parent.textContent;
    });
    
    // Show all rows
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = '';
    });
}

// ============================================
// TABLE SEARCH FUNCTIONALITY
// ============================================

function setupSearchListeners() {
    const categorySearchInput = document.getElementById('categorySearch');
    const barangaySearchInput = document.getElementById('barangaySearch');
    
    if (categorySearchInput) {
        categorySearchInput.addEventListener('keyup', function() {
            searchCategories();
        });
        categorySearchInput.addEventListener('input', function() {
            searchCategories();
        });
    }
    
    if (barangaySearchInput) {
        barangaySearchInput.addEventListener('keyup', function() {
            searchBarangays();
        });
        barangaySearchInput.addEventListener('input', function() {
            searchBarangays();
        });
    }
}

async function loadAllCategories() {
    if (allCategories.length > 0) return; // Already loaded
    
    try {
        const response = await fetch('/admin/categories/data/all');
        const data = await response.json();
        
        if (data.success) {
            allCategories = data.data;
            displayCategoriesPage(1);
        }
    } catch (error) {
        console.error('Failed to load categories:', error);
    }
}

async function loadAllBarangays() {
    if (allBarangays.length > 0) return; // Already loaded
    
    try {
        const response = await fetch('/admin/barangays/data/all');
        const data = await response.json();
        
        if (data.success) {
            allBarangays = data.data;
            displayBarangaysPage(1);
        }
    } catch (error) {
        console.error('Failed to load barangays:', error);
    }
}

function searchCategories() {
    const searchInput = document.getElementById('categorySearch');
    const searchValue = searchInput.value.toLowerCase().trim();
    
    if (searchValue === '') {
        filteredCategories = [...allCategories];
    } else {
        filteredCategories = allCategories.filter(category => {
            const name = category.category_name.toLowerCase();
            return name.includes(searchValue);
        });
    }
    
    currentCategoryPage = 1;
    displayCategoriesPage(1);
}

function searchBarangays() {
    const searchInput = document.getElementById('barangaySearch');
    const searchValue = searchInput.value.toLowerCase().trim();
    
    if (searchValue === '') {
        filteredBarangays = [...allBarangays];
    } else {
        filteredBarangays = allBarangays.filter(barangay => {
            const name = barangay.barangay_name.toLowerCase();
            return name.includes(searchValue);
        });
    }
    
    currentBarangayPage = 1;
    displayBarangaysPage(1);
}

function displayCategoriesPage(pageNum) {
    const tabContent = document.getElementById('categories-content');
    if (!tabContent) return;
    
    const tbody = tabContent.querySelector('tbody');
    if (!tbody) return;
    
    // Use filteredCategories if search is active, otherwise use allCategories
    const items = filteredCategories.length > 0 || document.getElementById('categorySearch').value ? filteredCategories : allCategories;
    
    if (items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3>No categories found</h3>
                        <p>Get started by creating your first category</p>
                        <button class="btn btn-primary" onclick="openAddCategoryModal()">
                            <i class="fas fa-plus"></i>
                            Add Category
                        </button>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    const startIndex = (pageNum - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedItems = items.slice(startIndex, endIndex);
    
    tbody.innerHTML = paginatedItems.map(category => `
        <tr>
            <td style="font-weight: 500;">${escapeHtml(category.category_name)}</td>
            <td>
                <span class="status-badge primary">
                    <i class="fas fa-box"></i>
                    ${category.items_count} items
                </span>
            </td>
            <td>
                <div class="action-buttons-modern">
                    <button class="action-btn edit" 
                            onclick="openEditCategoryModal(${category.category_id})"
                            title="Edit Category">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" 
                            onclick="confirmDeleteCategory(${category.category_id}, '${escapeHtml(category.category_name, true)}')"
                            title="${category.items_count > 0 ? `Cannot delete - ${category.items_count} items associated` : 'Delete Category'}"
                            ${category.items_count > 0 ? 'disabled' : ''}>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    updateCategoryPagination(items.length, pageNum);
}

function displayBarangaysPage(pageNum) {
    const tabContent = document.getElementById('barangays-content');
    if (!tabContent) return;
    
    const tbody = tabContent.querySelector('tbody');
    if (!tbody) return;
    
    // Use filteredBarangays if search is active, otherwise use allBarangays
    const items = filteredBarangays.length > 0 || document.getElementById('barangaySearch').value ? filteredBarangays : allBarangays;
    
    if (items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>No barangays found</h3>
                        <p>Get started by adding your first barangay</p>
                        <button class="btn btn-primary" onclick="openAddBarangayModal()">
                            <i class="fas fa-plus"></i>
                            Add Barangay
                        </button>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    const startIndex = (pageNum - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedItems = items.slice(startIndex, endIndex);
    
    tbody.innerHTML = paginatedItems.map(barangay => `
        <tr>
            <td style="font-weight: 500;">${escapeHtml(barangay.barangay_name)}</td>
            <td>
                <span class="status-badge success">
                    <i class="fas fa-user-injured"></i>
                    ${barangay.patients_count} patients
                </span>
            </td>
            <td>
                <div class="action-buttons-modern">
                    <button class="action-btn edit" 
                            onclick="openEditBarangayModal(${barangay.barangay_id})"
                            title="Edit Barangay">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" 
                            onclick="confirmDeleteBarangay(${barangay.barangay_id}, '${escapeHtml(barangay.barangay_name, true)}')"
                            title="${barangay.patients_count > 0 ? `Cannot delete - ${barangay.patients_count} patients associated` : 'Delete Barangay'}"
                            ${barangay.patients_count > 0 ? 'disabled' : ''}>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    updateBarangayPagination(items.length, pageNum);
}

function updateCategoryPagination(totalItems, currentPage) {
    const tabContent = document.getElementById('categories-content');
    if (!tabContent) return;
    
    let paginationFooter = tabContent.querySelector('.pagination-footer-modern');
    if (!paginationFooter) {
        paginationFooter = document.createElement('div');
        paginationFooter.className = 'pagination-footer-modern';
        tabContent.appendChild(paginationFooter);
    }
    
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, totalItems);
    
    let html = `
        <div class="pagination-info-modern">
            Showing ${startItem} to ${endItem} of ${totalItems} categories
        </div>
        <div class="pagination-controls-modern">
    `;
    
    if (currentPage === 1) {
        html += '<button class="pagination-btn" disabled><i class="fas fa-chevron-left"></i></button>';
    } else {
        html += `<button class="pagination-btn" onclick="currentCategoryPage=${currentPage - 1}; displayCategoriesPage(${currentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;
    }
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            html += `<button class="pagination-btn active">${i}</button>`;
        } else {
            html += `<button class="pagination-btn" onclick="currentCategoryPage=${i}; displayCategoriesPage(${i})">${i}</button>`;
        }
    }
    
    if (currentPage === totalPages) {
        html += '<button class="pagination-btn" disabled><i class="fas fa-chevron-right"></i></button>';
    } else {
        html += `<button class="pagination-btn" onclick="currentCategoryPage=${currentPage + 1}; displayCategoriesPage(${currentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;
    }
    
    html += '</div>';
    paginationFooter.innerHTML = html;
}

function updateBarangayPagination(totalItems, currentPage) {
    const tabContent = document.getElementById('barangays-content');
    if (!tabContent) return;
    
    let paginationFooter = tabContent.querySelector('.pagination-footer-modern');
    if (!paginationFooter) {
        paginationFooter = document.createElement('div');
        paginationFooter.className = 'pagination-footer-modern';
        tabContent.appendChild(paginationFooter);
    }
    
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, totalItems);
    
    let html = `
        <div class="pagination-info-modern">
            Showing ${startItem} to ${endItem} of ${totalItems} barangays
        </div>
        <div class="pagination-controls-modern">
    `;
    
    if (currentPage === 1) {
        html += '<button class="pagination-btn" disabled><i class="fas fa-chevron-left"></i></button>';
    } else {
        html += `<button class="pagination-btn" onclick="currentBarangayPage=${currentPage - 1}; displayBarangaysPage(${currentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;
    }
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            html += `<button class="pagination-btn active">${i}</button>`;
        } else {
            html += `<button class="pagination-btn" onclick="currentBarangayPage=${i}; displayBarangaysPage(${i})">${i}</button>`;
        }
    }
    
    if (currentPage === totalPages) {
        html += '<button class="pagination-btn" disabled><i class="fas fa-chevron-right"></i></button>';
    } else {
        html += `<button class="pagination-btn" onclick="currentBarangayPage=${currentPage + 1}; displayBarangaysPage(${currentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;
    }
    
    html += '</div>';
    paginationFooter.innerHTML = html;
}

function searchTable(tableType) {
    // Legacy function - redirect to new search methods
    if (tableType === 'categories') {
        searchCategories();
    } else if (tableType === 'barangays') {
        searchBarangays();
    }
}

// ============================================
// REFRESH SYSTEM HEALTH
// ============================================

function refreshSystemHealth() {
    const refreshBtn = document.getElementById('refreshBtn');
    const icon = refreshBtn.querySelector('i');
    
    // Add spinning animation
    icon.classList.add('fa-spin');
    refreshBtn.disabled = true;
    
    // Reload the page
    setTimeout(() => {
        window.location.reload();
    }, 500);
}
