@extends('layouts.dashboard')

@section('title', 'Edit Knowledge Base Article')

@section('page-title', 'Edit Article')
@section('page-subtitle', 'Update knowledge base article')

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

        .btn-danger {
            background: #fed7d7;
            color: #e53e3e;
        }

        .btn-danger:hover {
            background: #feb2b2;
            color: #c53030;
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

        .article-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .article-info h4 {
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
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

            .article-meta {
                flex-direction: column;
                gap: 8px;
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
        <a href="{{ route('admin.knowledge-base.show', $knowledgeBase->kb_id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Article
        </a>
    </div>

    <div class="kb-form-card">
        <!-- Article Information -->
        <div class="article-info">
            <h4><i class="fas fa-edit"></i> Editing Article</h4>
            <div class="article-meta">
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span>Created by: {{ $knowledgeBase->user->first_name ?? 'System' }} {{ $knowledgeBase->user->last_name ?? '' }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>Created: {{ $knowledgeBase->added_at ? $knowledgeBase->added_at->format('M j, Y') : 'Unknown' }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-file-text"></i>
                    <span>Size: {{ $knowledgeBase->formatted_size }}</span>
                </div>
            </div>
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

        <form method="POST" action="{{ route('admin.knowledge-base.update', $knowledgeBase->kb_id) }}">
            @csrf
            @method('PUT')

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
                    value="{{ old('pdf_name', $knowledgeBase->pdf_name) }}"
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
                >{{ old('ai_summary', $knowledgeBase->ai_summary) }}</textarea>
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
                    placeholder="Enter the full content of the article..."
                    required
                >{{ old('pdf_text', $knowledgeBase->pdf_text) }}</textarea>
                @error('pdf_text')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="character-count" id="content-count">0 characters</div>
                <div class="form-help">
                    Enter the complete text content. This will be used for AI training and reference.
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.knowledge-base.show', $knowledgeBase->kb_id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="button" onclick="deleteArticle({{ $knowledgeBase->kb_id }})" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Delete Article
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Article
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
    });

    function deleteArticle(id) {
        if (confirm('Are you sure you want to delete this knowledge base article? This action cannot be undone.')) {
            fetch(`/admin/knowledge-base/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to index page
                    window.location.href = '{{ route("admin.knowledge-base.index") }}';
                } else {
                    alert('Error deleting article: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the article.');
            });
        }
    }
</script>
@endpush