@extends('layouts.dashboard')

@section('title', 'API Management')

@section('page-title', 'API Management')
@section('page-subtitle', 'Manage Malnutrition Assessment API and Reference Data')

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Error Display -->
    @if ($errors->any())
        <div class="alert alert-danger modern-alert">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-content">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="page-header-modern">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="header-text">
                <h1>API Control Center</h1>
                <p>Monitor and manage your malnutrition assessment API infrastructure</p>
            </div>
        </div>
        <div class="header-actions">
            <button onclick="checkApiStatus()" class="btn-modern btn-primary">
                <i class="fas fa-sync-alt"></i>
                Refresh Status
            </button>
        </div>
    </div>

    <!-- API Status Overview -->
    <div class="stats-grid-modern">
        <div class="stat-card-modern {{ $apiStatus['status'] === 'healthy' ? 'success' : 'danger' }}">
            <div class="stat-gradient-bg"></div>
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon {{ $apiStatus['status'] === 'healthy' ? 'success' : 'danger' }}">
                        <i class="fas {{ $apiStatus['status'] === 'healthy' ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                    </div>
                    <div class="stat-badge {{ $apiStatus['status'] === 'healthy' ? 'success' : 'danger' }}">
                        {{ $apiStatus['status'] === 'healthy' ? 'ONLINE' : 'OFFLINE' }}
                    </div>
                </div>
                <div class="stat-title">API Status</div>
                <div class="stat-value">{{ ucfirst($apiStatus['status']) }}</div>
                <div class="stat-description">
                    {{ $apiStatus['message'] ?? 'API is running normally' }}
                </div>
            </div>
        </div>
        
        <div class="stat-card-modern info">
            <div class="stat-gradient-bg"></div>
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon info">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <div class="stat-badge info">READY</div>
                </div>
                <div class="stat-title">Treatment Protocols</div>
                <div class="stat-value">Available</div>
                <div class="stat-description">
                    Clinical guidelines and protocols loaded
                </div>
            </div>
        </div>

        <div class="stat-card-modern primary">
            <div class="stat-gradient-bg"></div>
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-badge primary">ACTIVE</div>
                </div>
                <div class="stat-title">WHO Standards</div>
                <div class="stat-value">Active</div>
                <div class="stat-description">
                    Reference data synchronized and updated
                </div>
            </div>
        </div>

        <div class="stat-card-modern warning">
            <div class="stat-gradient-bg"></div>
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-code-branch"></i>
                    </div>
                    <div class="stat-badge warning">v{{ $apiStatus['version'] ?? '1.0.0' }}</div>
                </div>
                <div class="stat-title">API Version</div>
                <div class="stat-value">{{ $apiStatus['version'] ?? '1.0.0' }}</div>
                <div class="stat-description">
                    Current stable release version
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="content-section-modern">
        <div class="section-header-modern">
            <div class="section-title">
                <i class="fas fa-bolt"></i>
                <h2>Quick Actions</h2>
            </div>
            <p>Manage API components and reference data with ease</p>
        </div>

        <div class="action-grid-modern">
            <button onclick="loadAjaxContent('who-standards')" class="action-card-modern success clickable">
                <div class="action-decoration"></div>
                <div class="action-icon-modern">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="action-content-modern">
                    <h3>WHO Standards</h3>
                    <p>View and manage WHO growth standards reference data</p>
                    <div class="action-meta">
                        <span class="action-label">Reference Data</span>
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                </div>
                <div class="action-hover-effect"></div>
            </button>

            <button onclick="loadAjaxContent('treatment-protocols')" class="action-card-modern info clickable">
                <div class="action-decoration"></div>
                <div class="action-icon-modern">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div class="action-content-modern">
                    <h3>Treatment Protocols</h3>
                    <p>View available treatment and intervention protocols</p>
                    <div class="action-meta">
                        <span class="action-label">Clinical Guidelines</span>
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                </div>
                <div class="action-hover-effect"></div>
            </button>

            <input type="hidden" id="apiManagementStatusRoute" value="{{ route('admin.api.status') }}">
            <button onclick="checkApiStatus()" class="action-card-modern warning clickable">
                <div class="action-decoration"></div>
                <div class="action-icon-modern">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="action-content-modern">
                    <h3>API Health Check</h3>
                    <p>Check current API connectivity and health status</p>
                    <div class="action-meta">
                        <span class="action-label">System Monitor</span>
                        <i class="fas fa-sync-alt"></i>
                    </div>
                </div>
                <div class="action-hover-effect"></div>
            </button>
        </div>
    </div>

    <!-- AJAX Content Modal -->
    <div id="ajaxContentModal" class="ajax-modal-overlay" style="display: none;">
        <div class="ajax-modal-container">
            <div class="ajax-modal-header">
                <h2 id="ajaxModalTitle">Loading...</h2>
                <button class="ajax-modal-close" onclick="closeAjaxModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ajax-modal-body" id="ajaxModalContent">
                <div class="ajax-loading">
                    <div class="spinner"></div>
                    <p>Loading content...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dataset Viewer Modal -->
    <div id="datasetViewerModal" class="ajax-modal-overlay" style="display: none;">
        <div class="ajax-modal-container">
            <div class="ajax-modal-header">
                <h2 id="datasetViewerTitle">Dataset Viewer</h2>
                <button class="ajax-modal-close" onclick="closeDatasetModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ajax-modal-body" id="datasetViewerContent">
                <div class="ajax-loading">
                    <div class="spinner"></div>
                    <p>Loading dataset...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="@assetv('css/api-management.css')">
<style>
.ajax-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease-out;
}

.ajax-modal-container {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease-out;
}

.ajax-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1001;
}

.ajax-modal-header h2 {
    margin: 0;
    font-size: 1.75rem;
    color: #1f2937;
}

.ajax-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.2s;
}

.ajax-modal-close:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.ajax-modal-body {
    padding: 2rem;
}

.ajax-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 3rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e5e7eb;
    border-top-color: #2e7d32;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@media (max-width: 768px) {
    .ajax-modal-container {
        width: 95%;
        max-height: 95vh;
    }
    
    .ajax-modal-header {
        padding: 1rem;
    }
    
    .ajax-modal-header h2 {
        font-size: 1.25rem;
    }
    
    .ajax-modal-body {
        padding: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script src="@assetv('js/api-management.js')"></script>
<script>
// AJAX Content Loading
function loadAjaxContent(contentType) {
    const modal = document.getElementById('ajaxContentModal');
    const content = document.getElementById('ajaxModalContent');
    const title = document.getElementById('ajaxModalTitle');
    
    // Set title based on content type
    if (contentType === 'who-standards') {
        title.textContent = 'WHO Growth Standards';
    } else if (contentType === 'treatment-protocols') {
        title.textContent = 'Clinical Treatment Protocols';
    }
    
    // Show modal with loading state
    modal.style.display = 'flex';
    
    // Fetch content via AJAX - use admin prefix
    fetch(`/admin/api/${contentType}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        content.innerHTML = html;
        // Scroll to top of modal content
        document.querySelector('.ajax-modal-container').scrollTop = 0;
    })
    .catch(error => {
        console.error('Error loading content:', error);
        content.innerHTML = `
            <div class="ajax-error">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Error Loading Content</h3>
                <p>Unable to load the requested information. Please try again later.</p>
                <button onclick="closeAjaxModal()" class="btn-close-error">Close</button>
            </div>
        `;
    });
    
    // Close modal on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeAjaxModal();
        }
    });
}

function closeAjaxModal() {
    const modal = document.getElementById('ajaxContentModal');
    modal.style.display = 'none';
    document.getElementById('ajaxModalContent').innerHTML = `
        <div class="ajax-loading">
            <div class="spinner"></div>
            <p>Loading content...</p>
        </div>
    `;
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAjaxModal();
    }
});

// View Dataset Function
function viewDataset(datasetType, datasetName) {
    const datasetModal = document.getElementById('datasetViewerModal');
    const datasetTitle = document.getElementById('datasetViewerTitle');
    const datasetContent = document.getElementById('datasetViewerContent');
    
    // Update title
    datasetTitle.textContent = datasetName;
    
    // Show loading state
    datasetContent.innerHTML = `
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; padding: 2rem;">
            <div style="width: 40px; height: 40px; border: 4px solid #e5e7eb; border-top-color: #2e7d32; border-radius: 50%; animation: spin 0.6s linear infinite;"></div>
            <p style="color: #6b7280;">Loading dataset...</p>
        </div>
    `;
    
    // Show dataset modal
    datasetModal.style.display = 'flex';
    
    // Fetch dataset data
    fetch(`/admin/api/dataset/${datasetType}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        datasetContent.innerHTML = html;
    })
    .catch(error => {
        console.error('Error loading dataset:', error);
        datasetContent.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 2rem; text-align: center;">
                <i style="font-size: 2rem; color: #ef4444;">⚠️</i>
                <h3 style="margin: 0; color: #1f2937;">Error Loading Dataset</h3>
                <p style="margin: 0; color: #6b7280;">Unable to load the dataset. Please try again.</p>
            </div>
        `;
    });
}

function closeDatasetModal() {
    document.getElementById('datasetViewerModal').style.display = 'none';
}

// Add styles for error state
const errorStyles = `
.ajax-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 3rem 2rem;
    text-align: center;
}

.ajax-error i {
    font-size: 3rem;
    color: #ef4444;
}

.ajax-error h3 {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
}

.ajax-error p {
    margin: 0;
    color: #6b7280;
    max-width: 400px;
}

.btn-close-error {
    padding: 0.75rem 1.5rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-close-error:hover {
    background: #dc2626;
    transform: translateY(-1px);
}
`;

// Inject error styles
const styleSheet = document.createElement('style');
styleSheet.textContent = errorStyles;
document.head.appendChild(styleSheet);
</script>
@endpush
