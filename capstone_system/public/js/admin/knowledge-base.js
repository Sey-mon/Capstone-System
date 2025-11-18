// Knowledge Base Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize
    initializeKnowledgeBase();

    // Check system health on page load
    checkLlmHealth();
    
    // Check embedding status first, then update document statuses
    checkEmbeddingStatus();
    
    // Wait a bit for embedding status to load, then update document statuses
    setTimeout(() => {
        updateDocumentStatuses();
    }, 1000);

    // Event Listeners
    setupEventListeners();
});

// ============================================================================
// INITIALIZATION
// ============================================================================

function initializeKnowledgeBase() {
    console.log('Knowledge Base Management initialized');
}

// Helper function to get CSRF token
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

// Flag to prevent duplicate event listener attachment
let eventListenersAttached = false;

function setupEventListeners() {
    // File upload handlers
    const browseBtn = document.getElementById('browse-btn');
    const pdfFileInput = document.getElementById('pdf-file-input');
    const uploadDropzone = document.getElementById('upload-dropzone');
    const removeFileBtn = document.getElementById('remove-file-btn');
    const uploadForm = document.getElementById('upload-pdf-form');

    if (browseBtn) {
        browseBtn.addEventListener('click', () => pdfFileInput.click());
    }

    if (pdfFileInput) {
        pdfFileInput.addEventListener('change', handleFileSelect);
    }

    if (uploadDropzone) {
        // Drag and drop
        uploadDropzone.addEventListener('dragover', handleDragOver);
        uploadDropzone.addEventListener('dragleave', handleDragLeave);
        uploadDropzone.addEventListener('drop', handleDrop);
        uploadDropzone.addEventListener('click', (e) => {
            if (e.target.closest('#browse-btn')) return;
            pdfFileInput.click();
        });
    }

    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', clearFileSelection);
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', handleUploadSubmit);
    }

    // Processing buttons
    const processAllBtn = document.getElementById('process-all-btn');
    const reembedMissingBtn = document.getElementById('reembed-missing-btn');
    const refreshStatusBtn = document.getElementById('refresh-status-btn');

    if (processAllBtn) {
        processAllBtn.addEventListener('click', processAllEmbeddings);
    }

    if (reembedMissingBtn) {
        reembedMissingBtn.addEventListener('click', reembedMissing);
    }

    if (refreshStatusBtn) {
        refreshStatusBtn.addEventListener('click', () => {
            checkLlmHealth();
            checkEmbeddingStatus();
            updateDocumentStatuses();
        });
    }

    // Document actions - Use event delegation for dynamically loaded content
    // Only attach once to prevent multiple triggers
    if (!eventListenersAttached) {
        document.addEventListener('click', handleDocumentActions);
        eventListenersAttached = true;
        console.log('✅ Document action listeners attached');
    }

    // Search
    const searchInput = document.getElementById('search-documents');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    // Toast close
    const toastClose = document.querySelector('.toast-close');
    if (toastClose) {
        toastClose.addEventListener('click', hideToast);
    }
}

// ============================================================================
// DOCUMENT ACTION HANDLER
// ============================================================================

function handleDocumentActions(e) {
    // View Summary button
    if (e.target.closest('.view-summary-btn')) {
        e.preventDefault();
        e.stopPropagation();
        const btn = e.target.closest('.view-summary-btn');
        const kbId = btn.dataset.kbId;
        if (kbId) {
            console.log('🔘 View summary button clicked');
            viewDocumentSummary(kbId);
        }
        return;
    }
    
    // Delete button
    if (e.target.closest('.delete-document-btn')) {
        e.preventDefault();
        e.stopPropagation();
        const btn = e.target.closest('.delete-document-btn');
        const kbId = btn.dataset.kbId;
        const name = btn.dataset.name;
        if (kbId && name) {
            deleteDocument(kbId, name);
        }
        return;
    }
}

// ============================================================================
// FILE UPLOAD HANDLING
// ============================================================================

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        displayFilePreview(file);
    }
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        if (file.type === 'application/pdf') {
            document.getElementById('pdf-file-input').files = files;
            displayFilePreview(file);
        } else {
            showToast('Please select a PDF file', 'error');
        }
    }
}

function displayFilePreview(file) {
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const filePreview = document.getElementById('file-preview');
    const uploadDropzone = document.getElementById('upload-dropzone');

    fileName.textContent = file.name;
    fileSize.textContent = formatFileSize(file.size);

    uploadDropzone.style.display = 'none';
    filePreview.style.display = 'flex';
}

function clearFileSelection() {
    const pdfFileInput = document.getElementById('pdf-file-input');
    const filePreview = document.getElementById('file-preview');
    const uploadDropzone = document.getElementById('upload-dropzone');

    pdfFileInput.value = '';
    filePreview.style.display = 'none';
    uploadDropzone.style.display = 'block';
}

function handleUploadSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const uploadProgress = document.getElementById('upload-progress');
    const progressFill = document.getElementById('progress-fill');
    const progressText = document.getElementById('progress-text');
    const uploadBtn = document.getElementById('upload-btn');

    // Show progress
    uploadProgress.style.display = 'block';
    uploadBtn.disabled = true;
    progressFill.style.width = '0%';
    progressText.textContent = 'Uploading...';

    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 10;
        if (progress <= 90) {
            progressFill.style.width = progress + '%';
        }
    }, 200);

    // Upload
    fetch('/admin/knowledge-base/upload', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        progressFill.style.width = '100%';
        progressText.textContent = 'Upload complete!';

        setTimeout(() => {
            if (data.success) {
                showToast(data.message, 'success');
                clearFileSelection();
                uploadProgress.style.display = 'none';
                uploadBtn.disabled = false;
                
                // Reload page to show new document
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'Upload failed', 'error');
                uploadProgress.style.display = 'none';
                uploadBtn.disabled = false;
            }
        }, 500);
    })
    .catch(error => {
        clearInterval(progressInterval);
        console.error('Upload error:', error);
        showToast('Upload failed: ' + error.message, 'error');
        uploadProgress.style.display = 'none';
        uploadBtn.disabled = false;
    });
}

// ============================================================================
// EMBEDDING PROCESSING
// ============================================================================

function processAllEmbeddings() {
    const processAllBtn = document.getElementById('process-all-btn');
    const reembedMissingBtn = document.getElementById('reembed-missing-btn');
    const processingProgress = document.getElementById('processing-progress');
    const processingText = document.getElementById('processing-text');
    const processingBadge = document.getElementById('processing-badge');

    processAllBtn.disabled = true;
    reembedMissingBtn.disabled = true;
    processingProgress.style.display = 'block';
    processingText.textContent = 'Processing embeddings... This may take several minutes.';
    processingBadge.classList.add('processing');
    processingBadge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing';

    fetch('/admin/knowledge-base/process-embeddings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({
            // Optional: specify chunk_size, overlap, batch_size
            // chunk_size: 1000,
            // overlap: 200,
            // batch_size: 128
        })
    })
    .then(response => response.json())
    .then(data => {
        processingProgress.style.display = 'none';
        processAllBtn.disabled = false;
        reembedMissingBtn.disabled = false;
        processingBadge.classList.remove('processing');
        processingBadge.innerHTML = '<i class="fas fa-check-circle"></i> Ready';

        if (data.success) {
            // Show detailed stats from FastAPI response
            let message = data.message;
            
            // Check if embeddings already exist
            if (data.data && data.data.stats && data.data.stats.cached === true) {
                showToast('Embeddings already exist and are up to date', 'info');
                // Force update of statuses since embeddings are ready
                setTimeout(() => {
                    checkEmbeddingStatus();
                    updateDocumentStatuses();
                }, 500);
            } else if (data.data && data.data.stats) {
                const stats = data.data.stats;
                message += ` (${stats.total_chunks || 0} chunks from ${stats.total_documents || 0} documents)`;
                showToast(message, 'success');
                // Update statuses after processing
                setTimeout(() => {
                    checkEmbeddingStatus();
                    updateDocumentStatuses();
                }, 500);
            } else {
                showToast(message, 'success');
                checkEmbeddingStatus();
                updateDocumentStatuses();
            }
        } else {
            showToast(data.message || 'Processing failed', 'error');
        }
    })
    .catch(error => {
        console.error('Processing error:', error);
        processingProgress.style.display = 'none';
        processAllBtn.disabled = false;
        reembedMissingBtn.disabled = false;
        processingBadge.classList.remove('processing');
        processingBadge.innerHTML = '<i class="fas fa-check-circle"></i> Ready';
        showToast('Processing failed: ' + error.message, 'error');
    });
}

function reembedMissing() {
    const processAllBtn = document.getElementById('process-all-btn');
    const reembedMissingBtn = document.getElementById('reembed-missing-btn');
    const processingProgress = document.getElementById('processing-progress');
    const processingText = document.getElementById('processing-text');
    const processingBadge = document.getElementById('processing-badge');

    processAllBtn.disabled = true;
    reembedMissingBtn.disabled = true;
    processingProgress.style.display = 'block';
    processingText.textContent = 'Re-embedding missing documents...';
    processingBadge.classList.add('processing');
    processingBadge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing';

    fetch('/admin/knowledge-base/reembed-missing', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({
            // Optional: specify batch_size
            // batch_size: 128
        })
    })
    .then(response => response.json())
    .then(data => {
        processingProgress.style.display = 'none';
        processAllBtn.disabled = false;
        reembedMissingBtn.disabled = false;
        processingBadge.classList.remove('processing');
        processingBadge.innerHTML = '<i class="fas fa-check-circle"></i> Ready';

        if (data.success) {
            // Handle different status responses from FastAPI
            if (data.data && data.data.status === 'all_embedded') {
                showToast('All documents are already embedded!', 'info');
            } else {
                let message = data.message;
                // Show per-KB details if available
                if (data.data && data.data.per_kb) {
                    const perKb = data.data.per_kb;
                    const processed = Object.keys(perKb).length;
                    message += ` (${processed} document${processed !== 1 ? 's' : ''} processed)`;
                }
                showToast(message, 'success');
            }
            checkEmbeddingStatus();
            updateDocumentStatuses();
        } else {
            showToast(data.message || 'Re-embedding failed', 'error');
        }
    })
    .catch(error => {
        console.error('Re-embedding error:', error);
        processingProgress.style.display = 'none';
        processAllBtn.disabled = false;
        reembedMissingBtn.disabled = false;
        processingBadge.classList.remove('processing');
        processingBadge.innerHTML = '<i class="fas fa-check-circle"></i> Ready';
        showToast('Re-embedding failed: ' + error.message, 'error');
    });
}

// ============================================================================
// STATUS CHECKING
// ============================================================================

function checkLlmHealth() {
    const llmStatus = document.getElementById('llm-status');
    const statusIcon = llmStatus.querySelector('.status-icon');
    const statusValue = llmStatus.querySelector('.status-value');

    fetch('/admin/knowledge-base/llm-health')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.status === 'healthy') {
                statusIcon.innerHTML = '<i class="fas fa-check-circle healthy"></i>';
                statusValue.textContent = 'Healthy';
                statusValue.style.color = '#10B981';
            } else {
                statusIcon.innerHTML = '<i class="fas fa-times-circle unhealthy"></i>';
                statusValue.textContent = 'Unhealthy';
                statusValue.style.color = '#EF4444';
            }
        })
        .catch(error => {
            console.error('LLM health check error:', error);
            statusIcon.innerHTML = '<i class="fas fa-times-circle unhealthy"></i>';
            statusValue.textContent = 'Error';
            statusValue.style.color = '#EF4444';
        });
}

function checkEmbeddingStatus() {
    const embeddingStatus = document.getElementById('embedding-status');
    const statusIcon = embeddingStatus.querySelector('.status-icon');
    const statusValue = embeddingStatus.querySelector('.status-value');
    const embeddedCount = document.getElementById('embedded-count');
    const cacheStatus = document.getElementById('cache-status');
    const lastUpdated = document.getElementById('last-updated');

    fetch('/admin/knowledge-base/embedding-status')
        .then(response => response.json())
        .then(data => {
            console.log('Embedding status response:', data); // Debug: see actual API response
            
            if (data.success) {
                // FastAPI returns: embedding_status, knowledge_base_stats, cache_directory
                const embeddingData = data.data.embedding_status || {};
                const kbStats = data.data.knowledge_base_stats || {};
                
                console.log('Embedding data:', embeddingData); // Debug
                console.log('KB stats:', kbStats); // Debug

                // Update status bar based on API response
                if (embeddingData.status === 'ready') {
                    statusIcon.innerHTML = '<i class="fas fa-check-circle healthy"></i>';
                    statusValue.textContent = 'Ready';
                    statusValue.style.color = '#10B981';
                } else if (embeddingData.status === 'partial') {
                    statusIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                    statusValue.textContent = 'Partial';
                    statusValue.style.color = '#F59E0B';
                } else {
                    statusIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                    statusValue.textContent = 'Pending';
                    statusValue.style.color = '#F59E0B';
                }

                // Update embedding info
                const totalDocs = kbStats.total_documents || 0;
                const embeddedDocs = embeddingData.embedded_count || 0;
                embeddedCount.textContent = `${embeddedDocs} / ${totalDocs} documents`;
                
                // Cache status - check various possible values
                const cacheStatusValue = embeddingData.cache_status || data.data.cache_directory;
                if (cacheStatusValue === 'available' || cacheStatusValue === 'exists' || 
                    (typeof cacheStatusValue === 'string' && cacheStatusValue.length > 0) ||
                    embeddingData.status === 'ready') {
                    cacheStatus.textContent = 'Available';
                } else {
                    cacheStatus.textContent = 'Not Available';
                }
                
                // Last updated - check multiple possible fields
                const lastUpdatedValue = embeddingData.last_updated || 
                                        embeddingData.updated_at || 
                                        embeddingData.timestamp;
                if (lastUpdatedValue) {
                    try {
                        lastUpdated.textContent = new Date(lastUpdatedValue).toLocaleString();
                    } catch (e) {
                        lastUpdated.textContent = lastUpdatedValue;
                    }
                } else if (embeddingData.status === 'ready' || embeddedDocs > 0) {
                    // If embeddings exist but no timestamp, show current time
                    lastUpdated.textContent = new Date().toLocaleString();
                } else {
                    lastUpdated.textContent = 'Never';
                }
            } else {
                statusIcon.innerHTML = '<i class="fas fa-times-circle unhealthy"></i>';
                statusValue.textContent = 'Error';
                statusValue.style.color = '#EF4444';
            }
        })
        .catch(error => {
            console.error('Embedding status check error:', error);
            statusIcon.innerHTML = '<i class="fas fa-times-circle unhealthy"></i>';
            statusValue.textContent = 'Error';
            statusValue.style.color = '#EF4444';
        });
}

function updateDocumentStatuses() {
    // Check which documents are embedded based on the embedding status from API
    fetch('/admin/knowledge-base/embedding-status')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const embeddingData = data.data.embedding_status || {};
                const kbStats = data.data.knowledge_base_stats || {};
                
                // If status is ready, mark all as processed
                if (embeddingData.status === 'ready') {
                    document.querySelectorAll('.document-status .badge').forEach(badge => {
                        badge.className = 'badge badge-processed';
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> Processed';
                    });
                } 
                // If all documents are embedded, mark all as processed
                else if (embeddingData.embedded_count >= kbStats.total_documents && kbStats.total_documents > 0) {
                    document.querySelectorAll('.document-status .badge').forEach(badge => {
                        badge.className = 'badge badge-processed';
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> Processed';
                    });
                } 
                // Partial embedding
                else if (embeddingData.status === 'partial' || embeddingData.embedded_count > 0) {
                    // Some are embedded, some are not - would need per-document status from API
                    // For now, show mixed status
                    document.querySelectorAll('.document-status .badge').forEach((badge, index) => {
                        if (index < embeddingData.embedded_count) {
                            badge.className = 'badge badge-processed';
                            badge.innerHTML = '<i class="fas fa-check-circle"></i> Processed';
                        } else {
                            badge.className = 'badge badge-pending';
                            badge.innerHTML = '<i class="fas fa-clock"></i> Pending';
                        }
                    });
                }
            }
        })
        .catch(error => console.error('Error updating document statuses:', error));
}

// ============================================================================
// DOCUMENT MANAGEMENT
// ============================================================================

function viewDocumentSummary(kbId) {
    console.log('viewDocumentSummary called with kbId:', kbId);
    
    // Find document element
    const documentItem = document.querySelector(`.document-item[data-kb-id="${kbId}"]`);
    
    if (!documentItem) {
        console.error('Document not found with kbId:', kbId);
        Swal.fire({
            icon: 'error',
            title: 'Document Not Found',
            text: 'Could not find the requested document.',
            confirmButtonColor: '#10B981'
        });
        return;
    }

    const title = documentItem.querySelector('.document-title').getAttribute('title');
    const metaItems = documentItem.querySelectorAll('.meta-item');
    let metaText = '';
    metaItems.forEach(item => {
        metaText += item.textContent.trim() + ' ';
    });
    
    console.log('Document found:', title);

    // Show loading state with SweetAlert2
    Swal.fire({
        title: '<i class="fas fa-file-pdf"></i> Loading Summary',
        html: `
            <div style="text-align: left; margin-top: 1rem;">
                <h5 style="color: #1F2937; margin-bottom: 0.5rem; font-size: 1rem;">${title}</h5>
                <p style="color: #6B7280; font-size: 0.875rem; margin-bottom: 1rem;">
                    <i class="fas fa-info-circle"></i> ${metaText}
                </p>
                <div style="text-align: center; padding: 2rem;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p style="margin-top: 1rem; color: #6B7280;">Fetching document summary...</p>
                </div>
            </div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        customClass: {
            popup: 'swal-wide'
        }
    });

    // Fetch full summary from server
    fetch(`/admin/knowledge-base/${kbId}/summary`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const summary = data.summary || 'No summary available for this document.';
                
                Swal.fire({
                    title: '<i class="fas fa-file-pdf" style="color: #10B981;"></i> Document Summary',
                    html: `
                        <div style="text-align: left;">
                            <div style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; color: white; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);">
                                <h5 style="margin: 0 0 0.75rem 0; font-size: 1.1rem; font-weight: 600;">
                                    <i class="fas fa-file-alt"></i> ${title}
                                </h5>
                                <p style="margin: 0; font-size: 0.875rem; opacity: 0.95;">
                                    <i class="fas fa-info-circle"></i> ${metaText}
                                </p>
                            </div>
                            <div style="background: #ffffff; padding: 1.25rem; border-radius: 12px; border: 2px solid #D1FAE5; max-height: 400px; overflow-y: auto; box-shadow: inset 0 2px 4px rgba(16, 185, 129, 0.1);">
                                <div style="color: #047857; font-size: 0.9rem; line-height: 1.7; white-space: pre-wrap; word-wrap: break-word;">${summary}</div>
                            </div>
                        </div>
                    `,
                    width: '700px',
                    confirmButtonText: '<i class="fas fa-check"></i> Close',
                    confirmButtonColor: '#10B981',
                    customClass: {
                        popup: 'swal-wide',
                        confirmButton: 'btn btn-success'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Summary Not Available',
                    html: `
                        <div style="text-align: left;">
                            <h5 style="color: #1F2937; margin-bottom: 0.5rem;">${title}</h5>
                            <p style="color: #6B7280; font-size: 0.875rem;">${metaText}</p>
                        </div>
                        <p style="margin-top: 1rem;">No summary is available for this document yet.</p>
                    `,
                    confirmButtonColor: '#10B981',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error fetching summary:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error Loading Summary',
                html: `
                    <div style="text-align: left;">
                        <h5 style="color: #1F2937; margin-bottom: 0.5rem;">${title}</h5>
                        <p style="color: #6B7280; font-size: 0.875rem;">${metaText}</p>
                    </div>
                    <p style="margin-top: 1rem;">Failed to load the document summary. Please try again.</p>
                `,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'OK'
            });
        });
}

function deleteDocument(kbId, name) {
    Swal.fire({
        title: 'Delete Document?',
        html: `
            <div style="text-align: left; margin: 1rem 0;">
                <p style="margin-bottom: 0.5rem;">Are you sure you want to delete:</p>
                <div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 1rem; border-radius: 8px;">
                    <strong style="color: #991B1B;">${name}</strong>
                </div>
                <p style="margin-top: 1rem; color: #DC2626; font-weight: 600;">
                    <i class="fas fa-exclamation-triangle"></i> This action cannot be undone!
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete It',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        },
        showClass: {
            popup: 'animate__animated animate__shakeX'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                html: 'Please wait while we delete the document.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/admin/knowledge-base/${kbId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove document from DOM
                    const documentItem = document.querySelector(`.document-item[data-kb-id="${kbId}"]`);
                    if (documentItem) {
                        documentItem.remove();
                    }
                    // Update document count
                    const documentsStatus = document.getElementById('documents-status');
                    const currentCount = parseInt(documentsStatus.querySelector('.status-value').textContent);
                    documentsStatus.querySelector('.status-value').textContent = currentCount - 1;

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Document deleted successfully.',
                        confirmButtonColor: '#10B981',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Delete Failed',
                        text: data.message || 'Failed to delete document.',
                        confirmButtonColor: '#EF4444'
                    });
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while deleting the document.',
                    confirmButtonColor: '#EF4444'
                });
            });
        }
    });
}

// ============================================================================
// SEARCH
// ============================================================================

function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const documentItems = document.querySelectorAll('.document-item');

    documentItems.forEach(item => {
        const title = item.querySelector('.document-title').textContent.toLowerCase();
        const summary = item.querySelector('.document-summary p')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchTerm) || summary.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// ============================================================================
// UI HELPERS
// ============================================================================

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastMessage = toast.querySelector('.toast-message');

    toast.className = 'toast show ' + type;
    toastMessage.textContent = message;

    setTimeout(() => {
        hideToast();
    }, 5000);
}

function hideToast() {
    const toast = document.getElementById('toast');
    toast.classList.remove('show');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}
