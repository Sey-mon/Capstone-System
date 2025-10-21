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

    // Document actions
    const viewSummaryBtns = document.querySelectorAll('.view-summary-btn');
    const deleteDocumentBtns = document.querySelectorAll('.delete-document-btn');

    viewSummaryBtns.forEach(btn => {
        btn.addEventListener('click', () => viewDocumentSummary(btn.dataset.kbId));
    });

    deleteDocumentBtns.forEach(btn => {
        btn.addEventListener('click', () => deleteDocument(btn.dataset.kbId, btn.dataset.name));
    });

    // Modal
    const modalOverlay = document.getElementById('modal-overlay');
    const closeModal = document.getElementById('close-modal');

    if (modalOverlay) {
        modalOverlay.addEventListener('click', hideModal);
    }

    if (closeModal) {
        closeModal.addEventListener('click', hideModal);
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
    // Find document element
    const documentItem = document.querySelector(`.document-item[data-kb-id="${kbId}"]`);
    
    if (documentItem) {
        const title = documentItem.querySelector('.document-title').getAttribute('title');
        const summary = documentItem.querySelector('.document-summary p')?.textContent || 'No summary available';
        const metaItems = documentItem.querySelectorAll('.meta-item');
        let metaText = '';
        metaItems.forEach(item => {
            metaText += item.textContent.trim() + ' | ';
        });
        metaText = metaText.slice(0, -3); // Remove last ' | '

        const summaryContent = document.getElementById('summary-content');
        summaryContent.innerHTML = `
            <div style="margin-bottom: 1rem;">
                <h4 style="color: var(--kb-text-primary); margin-bottom: 0.5rem;">${title}</h4>
                <p style="color: var(--kb-text-secondary); font-size: 0.875rem;">
                    ${metaText}
                </p>
            </div>
            <pre style="background: var(--kb-bg); padding: 1rem; border-radius: 8px; white-space: pre-wrap; word-wrap: break-word; font-size: 0.875rem; line-height: 1.6;">${summary}</pre>
        `;
        showModal();
    }
}

function deleteDocument(kbId, name) {
    if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        return;
    }

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
            showToast(data.message, 'success');
            // Remove document from DOM
            const documentItem = document.querySelector(`.document-item[data-kb-id="${kbId}"]`);
            if (documentItem) {
                documentItem.remove();
            }
            // Update document count
            const documentsStatus = document.getElementById('documents-status');
            const currentCount = parseInt(documentsStatus.querySelector('.status-value').textContent);
            documentsStatus.querySelector('.status-value').textContent = currentCount - 1;
        } else {
            showToast(data.message || 'Delete failed', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Delete failed: ' + error.message, 'error');
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

function showModal() {
    const modal = document.getElementById('summary-modal');
    modal.classList.add('active');
}

function hideModal() {
    const modal = document.getElementById('summary-modal');
    modal.classList.remove('active');
}

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
