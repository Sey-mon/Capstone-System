@extends('layouts.dashboard')

@section('title', 'Knowledge Base Management')

@section('page-title', 'Knowledge Base Management')
@section('page-subtitle', 'Upload and manage nutrition guideline PDFs for AI-powered recommendations')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/knowledge-base.css') }}?v={{ filemtime(public_path('css/admin/knowledge-base.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Custom SweetAlert2 Styles for Knowledge Base - Green & White Theme */
        .swal-wide {
            border-radius: 16px !important;
            box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.3) !important;
            background: linear-gradient(to bottom, #ffffff 0%, #f0fdf4 100%) !important;
        }
        
        .swal2-popup {
            font-family: 'Inter', 'Segoe UI', sans-serif !important;
            border: 2px solid #10B981 !important;
        }
        
        .swal2-title {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            color: #047857 !important;
        }
        
        .swal2-html-container {
            margin: 1rem 0 !important;
            font-size: 0.95rem !important;
        }
        
        /* Custom scrollbar for summary - Green theme */
        .swal2-html-container > div > div:last-child {
            scrollbar-width: thin;
            scrollbar-color: #10B981 #F0FDF4;
        }
        
        .swal2-html-container > div > div:last-child::-webkit-scrollbar {
            width: 8px;
        }
        
        .swal2-html-container > div > div:last-child::-webkit-scrollbar-track {
            background: #F0FDF4;
            border-radius: 10px;
        }
        
        .swal2-html-container > div > div:last-child::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #10B981 0%, #059669 100%);
            border-radius: 10px;
        }
        
        .swal2-html-container > div > div:last-child::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #059669 0%, #047857 100%);
        }
        
        .swal2-confirm {
            padding: 0.625rem 1.5rem !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
            border: none !important;
        }
        
        .swal2-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(16, 185, 129, 0.6) !important;
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        }
        
        .swal2-cancel {
            background: #ffffff !important;
            color: #047857 !important;
            border: 2px solid #10B981 !important;
        }
        
        .swal2-cancel:hover {
            background: #F0FDF4 !important;
            border-color: #059669 !important;
        }
        
        .swal2-icon.swal2-success {
            border-color: #10B981 !important;
        }
        
        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            background-color: #10B981 !important;
        }
        
        .swal2-icon.swal2-success .swal2-success-ring {
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
    </style>
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- System Health Status Bar -->
    <div class="health-status-bar">
        <div class="status-item" id="llm-status">
            <div class="status-icon">
                <i class="fas fa-circle-notch fa-spin"></i>
            </div>
            <div class="status-content">
                <span class="status-label">LLM Service</span>
                <span class="status-value">Checking...</span>
            </div>
        </div>
        <div class="status-item" id="embedding-status">
            <div class="status-icon">
                <i class="fas fa-circle-notch fa-spin"></i>
            </div>
            <div class="status-content">
                <span class="status-label">Embeddings</span>
                <span class="status-value">Checking...</span>
            </div>
        </div>
        <div class="status-item" id="documents-status">
            <div class="status-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="status-content">
                <span class="status-label">Documents</span>
                <span class="status-value">{{ $knowledgeBase->count() }}</span>
            </div>
        </div>
        <div class="status-actions">
            <button type="button" class="btn btn-sm btn-secondary" id="refresh-status-btn">
                <i class="fas fa-sync-alt"></i> Refresh Status
            </button>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="kb-grid">
        <!-- Upload Section -->
        <div class="kb-card upload-card">
            <div class="kb-card-header">
                <div class="kb-card-title">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Upload PDF Document
                </div>
                <div class="kb-card-badge">
                    <i class="fas fa-info-circle"></i>
                    Max 10MB
                </div>
            </div>
            <div class="kb-card-body">
                <form id="upload-pdf-form" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-area" id="upload-dropzone">
                        <div class="upload-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="upload-text">
                            <p class="upload-title">Drop PDF here or click to browse</p>
                            <p class="upload-subtitle">Nutrition guidelines, pediatric protocols, dietary recommendations</p>
                        </div>
                        <input type="file" id="pdf-file-input" name="pdf_file" accept="application/pdf" hidden>
                        <button type="button" class="btn btn-primary" id="browse-btn">
                            <i class="fas fa-folder-open"></i> Browse Files
                        </button>
                    </div>
                    <div class="file-preview" id="file-preview" style="display: none;">
                        <div class="file-info">
                            <i class="fas fa-file-pdf file-icon"></i>
                            <div class="file-details">
                                <span class="file-name" id="file-name"></span>
                                <span class="file-size" id="file-size"></span>
                            </div>
                            <button type="button" class="btn-remove" id="remove-file-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-success btn-upload" id="upload-btn">
                            <i class="fas fa-upload"></i> Upload PDF
                        </button>
                    </div>
                </form>
                <div class="upload-progress" id="upload-progress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill"></div>
                    </div>
                    <span class="progress-text" id="progress-text">Uploading...</span>
                </div>
            </div>
        </div>

        <!-- Embedding Processing Section -->
        <div class="kb-card processing-card">
            <div class="kb-card-header">
                <div class="kb-card-title">
                    <i class="fas fa-cogs"></i>
                    Process Embeddings
                </div>
                <div class="kb-card-badge processing-badge" id="processing-badge">
                    <i class="fas fa-check-circle"></i>
                    Ready
                </div>
            </div>
            <div class="kb-card-body">
                <div class="processing-info">
                    <div class="info-item">
                        <i class="fas fa-layer-group"></i>
                        <div class="info-content">
                            <span class="info-label">Embedding Status</span>
                            <span class="info-value" id="embedded-count">Loading...</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-database"></i>
                        <div class="info-content">
                            <span class="info-label">Cache Status</span>
                            <span class="info-value" id="cache-status">Loading...</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div class="info-content">
                            <span class="info-label">Last Updated</span>
                            <span class="info-value" id="last-updated">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="processing-actions">
                    <button type="button" class="btn btn-primary btn-block" id="process-all-btn">
                        <i class="fas fa-play-circle"></i> Process All Embeddings
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" id="reembed-missing-btn">
                        <i class="fas fa-redo-alt"></i> Re-embed Missing Only
                    </button>
                </div>
                <div class="processing-progress" id="processing-progress" style="display: none;">
                    <div class="spinner">
                        <i class="fas fa-circle-notch fa-spin"></i>
                    </div>
                    <span class="processing-text" id="processing-text">Processing embeddings...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents List Section -->
    <div class="kb-card documents-card">
        <div class="kb-card-header">
            <div class="kb-card-title">
                <i class="fas fa-list"></i>
                Knowledge Base Documents
            </div>
            <div class="kb-card-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-documents" placeholder="Search documents..." class="search-input">
                </div>
            </div>
        </div>
        <div class="kb-card-body">
            @if($knowledgeBase->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No Documents Yet</h3>
                    <p>Upload your first PDF document to get started with the AI knowledge base.</p>
                </div>
            @else
                <div class="documents-grid" id="documents-grid">
                    @foreach($knowledgeBase as $document)
                        <div class="document-item" data-kb-id="{{ $document->kb_id }}">
                            <div class="document-header">
                                <div class="document-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="document-status">
                                    <span class="badge badge-pending" id="status-{{ $document->kb_id }}">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                </div>
                            </div>
                            <div class="document-body">
                                <h4 class="document-title" title="{{ $document->pdf_name }}">
                                    {{ Str::limit($document->pdf_name, 40) }}
                                </h4>
                                <div class="document-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        {{ $document->added_at->format('M d, Y') }}
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-user"></i>
                                        {{ $document->user->first_name ?? 'System' }}
                                    </span>
                                </div>
                                @if($document->ai_summary)
                                    <div class="document-summary">
                                        <p>{{ Str::limit($document->ai_summary, 120) }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="document-footer">
                                <button type="button" class="btn btn-sm btn-secondary view-summary-btn" data-kb-id="{{ $document->kb_id }}">
                                    <i class="fas fa-eye"></i> View Summary
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-document-btn" data-kb-id="{{ $document->kb_id }}" data-name="{{ $document->pdf_name }}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <div class="toast-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="toast-content">
            <span class="toast-message"></span>
        </div>
        <button class="toast-close">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/knowledge-base.js') }}?v={{ filemtime(public_path('js/admin/knowledge-base.js')) }}"></script>
@endpush
