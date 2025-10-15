@extends('layouts.dashboard')

@section('title', 'Add Knowledge Base Article')

@section('page-title', 'Add New Article')
@section('page-subtitle', 'Create a new knowledge base article for AI training')

@push('styles')
    <style>
        .kb-form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .kb-form-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8ecef;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control.is-invalid {
            border-color: #e53e3e;
        }

        .invalid-feedback {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 6px;
            display: block;
        }

        .form-help {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 6px;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        #pdf_text {
            min-height: 300px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 14px;
        }

        .file-upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            background: #f7fafc;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #667eea;
            background: #edf2f7;
        }

        .file-upload-area.dragover {
            border-color: #667eea;
            background: #e6fffa;
        }

        .file-upload-icon {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 16px;
        }

        .file-upload-text {
            color: #4a5568;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .file-upload-subtext {
            color: #718096;
            font-size: 0.9rem;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            padding-top: 32px;
            border-top: 1px solid #e2e8f0;
            margin-top: 32px;
        }

        .btn {
            padding: 14px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
            color: #2d3748;
            text-decoration: none;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #f0fff4;
            color: #276749;
            border-left-color: #38a169;
        }

        .alert-danger {
            background: #fed7d7;
            color: #742a2a;
            border-left-color: #e53e3e;
        }

        .character-count {
            text-align: right;
            font-size: 0.875rem;
            color: #718096;
            margin-top: 6px;
        }

        .character-count.warning {
            color: #d69e2e;
        }

        .character-count.danger {
            color: #e53e3e;
        }

        /* AI Helper Section */
        .ai-helper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .ai-helper h4 {
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ai-helper p {
            margin: 0;
            opacity: 0.9;
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .kb-form-card {
                padding: 24px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                justify-content: center;
            }
        }
    </style>
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
<div class="kb-form-container">
    <!-- Back Navigation -->
    <div class="mb-4">
        <a href="{{ route('admin.knowledge-base.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Knowledge Base
        </a>
    </div>

    <div class="kb-form-card">
        <!-- AI Helper Section -->
        <div class="ai-helper">
            <h4><i class="fas fa-robot"></i> AI Training Content</h4>
            <p>This article will be used to train and improve the AI assistant. Provide comprehensive, accurate information that will help the AI understand nutrition, malnutrition assessment, and treatment protocols better.</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.knowledge-base.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Article Title -->
            <div class="form-group">
                <label for="pdf_name" class="form-label">
                    <i class="fas fa-heading"></i>
                    Article Title *
                </label>
                <input 
                    type="text" 
                    id="pdf_name" 
                    name="pdf_name" 
                    class="form-control @error('pdf_name') is-invalid @enderror"
                    value="{{ old('pdf_name') }}"
                    placeholder="Enter a descriptive title for this knowledge base article"
                    required
                    maxlength="255"
                >
                @error('pdf_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    Choose a clear, descriptive title that helps identify the content's purpose.
                </div>
            </div>

            <!-- AI Summary -->
            <div class="form-group">
                <label for="ai_summary" class="form-label">
                    <i class="fas fa-brain"></i>
                    AI Summary
                </label>
                <textarea 
                    id="ai_summary" 
                    name="ai_summary" 
                    class="form-control @error('ai_summary') is-invalid @enderror"
                    placeholder="Provide a concise summary of the key points and concepts in this article..."
                    rows="4"
                    maxlength="1000"
                >{{ old('ai_summary') }}</textarea>
                @error('ai_summary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="character-count" id="summary-count">0 / 1000 characters</div>
                <div class="form-help">
                    Optional: Provide a summary highlighting the key concepts for AI training.
                </div>
            </div>

            <!-- Article Content -->
            <div class="form-group">
                <label for="pdf_text" class="form-label">
                    <i class="fas fa-file-text"></i>
                    Article Content *
                </label>
                <textarea 
                    id="pdf_text" 
                    name="pdf_text" 
                    class="form-control @error('pdf_text') is-invalid @enderror"
                    placeholder="Enter the full content of the article. This can include guidelines, protocols, research findings, treatment recommendations, or any educational material..."
                    required
                >{{ old('pdf_text') }}</textarea>
                @error('pdf_text')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="character-count" id="content-count">0 characters</div>
                <div class="form-help">
                    Enter the complete text content. This will be used for AI training and reference.
                </div>
            </div>

            <!-- Optional PDF File Upload -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-file-pdf"></i>
                    PDF File (Optional)
                </label>
                <div class="file-upload-area" id="file-upload-area">
                    <div class="file-upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="file-upload-text">Click to upload or drag and drop</div>
                    <div class="file-upload-subtext">PDF files only, max 10MB</div>
                    <input 
                        type="file" 
                        id="pdf_file" 
                        name="pdf_file" 
                        accept=".pdf"
                        style="display: none;"
                    >
                </div>
                <div id="file-info" style="margin-top: 12px; display: none;">
                    <div class="alert alert-success">
                        <i class="fas fa-file-pdf"></i>
                        <span id="file-name"></span>
                        <button type="button" id="remove-file" style="float: right; background: none; border: none; color: #276749; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                @error('pdf_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    Optional: Upload the source PDF file for reference (content above is still required).
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.knowledge-base.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Article
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Character counting
        const summaryTextarea = document.getElementById('ai_summary');
        const contentTextarea = document.getElementById('pdf_text');
        const summaryCount = document.getElementById('summary-count');
        const contentCount = document.getElementById('content-count');

        function updateCharacterCount(textarea, counter, maxLength = null) {
            const length = textarea.value.length;
            if (maxLength) {
                counter.textContent = `${length} / ${maxLength} characters`;
                if (length > maxLength * 0.9) {
                    counter.classList.add('warning');
                } else {
                    counter.classList.remove('warning');
                }
                if (length >= maxLength) {
                    counter.classList.add('danger');
                } else {
                    counter.classList.remove('danger');
                }
            } else {
                counter.textContent = `${length.toLocaleString()} characters`;
            }
        }

        summaryTextarea.addEventListener('input', () => updateCharacterCount(summaryTextarea, summaryCount, 1000));
        contentTextarea.addEventListener('input', () => updateCharacterCount(contentTextarea, contentCount));

        // Initial count
        updateCharacterCount(summaryTextarea, summaryCount, 1000);
        updateCharacterCount(contentTextarea, contentCount);

        // File upload handling
        const fileUploadArea = document.getElementById('file-upload-area');
        const fileInput = document.getElementById('pdf_file');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const removeFileBtn = document.getElementById('remove-file');

        fileUploadArea.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                fileName.textContent = file.name;
                fileInfo.style.display = 'block';
            }
        });

        removeFileBtn.addEventListener('click', function() {
            fileInput.value = '';
            fileInfo.style.display = 'none';
        });

        // Drag and drop
        fileUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type === 'application/pdf') {
                fileInput.files = files;
                fileName.textContent = files[0].name;
                fileInfo.style.display = 'block';
            }
        });
    });
</script>
@endpush