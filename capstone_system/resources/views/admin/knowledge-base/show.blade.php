@extends('layouts.dashboard')

@section('title', 'View Knowledge Base Article')

@section('page-title', $knowledgeBase->pdf_name)
@section('page-subtitle', 'Knowledge Base Article Details')

@push('styles')
    <style>
        .kb-view-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .kb-article-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .kb-article-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0 0 12px 0;
            line-height: 1.2;
        }

        .kb-article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            opacity: 0.9;
            font-size: 1rem;
        }

        .kb-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kb-content-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8ecef;
            margin-bottom: 30px;
        }

        .kb-summary-section {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #667eea;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .kb-summary-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .kb-summary-content {
            color: #4a5568;
            line-height: 1.6;
            font-size: 1rem;
        }

        .kb-content-section {
            margin-bottom: 32px;
        }

        .kb-content-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .kb-content-text {
            color: #4a5568;
            line-height: 1.7;
            font-size: 1rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .kb-actions {
            display: flex;
            gap: 16px;
            justify-content: space-between;
            align-items: center;
            padding: 24px 0;
            border-top: 1px solid #e2e8f0;
            margin-top: 32px;
        }

        .kb-actions-left {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 24px;
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
            text-decoration: none;
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

        .kb-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .kb-stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e8ecef;
            text-align: center;
        }

        .kb-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 4px;
        }

        .kb-stat-label {
            color: #718096;
            font-size: 0.9rem;
        }

        .back-navigation {
            margin-bottom: 24px;
        }

        /* Copy to clipboard functionality */
        .copy-section {
            position: relative;
        }

        .copy-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            opacity: 1;
            transform: translateY(-1px);
        }

        .copy-btn.copied {
            background: #38a169;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .kb-article-title {
                font-size: 1.8rem;
            }

            .kb-article-meta {
                flex-direction: column;
                gap: 12px;
            }

            .kb-content-card {
                padding: 24px;
            }

            .kb-actions {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }

            .kb-actions-left {
                justify-content: center;
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
<div class="kb-view-container">
    <!-- Back Navigation -->
    <div class="back-navigation">
        <a href="{{ route('admin.knowledge-base.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Knowledge Base
        </a>
    </div>

    <!-- Article Header -->
    <div class="kb-article-header">
        <h1 class="kb-article-title">{{ $knowledgeBase->pdf_name }}</h1>
        <div class="kb-article-meta">
            <div class="kb-meta-item">
                <i class="fas fa-user"></i>
                <span>Added by {{ $knowledgeBase->user->first_name ?? 'System' }} {{ $knowledgeBase->user->last_name ?? '' }}</span>
            </div>
            <div class="kb-meta-item">
                <i class="fas fa-calendar"></i>
                <span>{{ $knowledgeBase->added_at ? $knowledgeBase->added_at->format('F j, Y \a\t g:i A') : 'Date not available' }}</span>
            </div>
            <div class="kb-meta-item">
                <i class="fas fa-file-text"></i>
                <span>{{ $knowledgeBase->formatted_size }}</span>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="kb-stats-grid">
        <div class="kb-stat-card">
            <div class="kb-stat-value">{{ number_format(str_word_count($knowledgeBase->pdf_text ?? '')) }}</div>
            <div class="kb-stat-label">Words</div>
        </div>
        <div class="kb-stat-card">
            <div class="kb-stat-value">{{ number_format(strlen($knowledgeBase->pdf_text ?? '')) }}</div>
            <div class="kb-stat-label">Characters</div>
        </div>
        <div class="kb-stat-card">
            <div class="kb-stat-value">{{ $knowledgeBase->added_at ? $knowledgeBase->added_at->diffForHumans() : 'Unknown' }}</div>
            <div class="kb-stat-label">Last Updated</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="kb-content-card">
        @if($knowledgeBase->ai_summary)
            <!-- AI Summary Section -->
            <div class="kb-summary-section copy-section">
                <button class="copy-btn" onclick="copyToClipboard('summary-content', this)">
                    <i class="fas fa-copy"></i> Copy
                </button>
                <h3 class="kb-summary-title">
                    <i class="fas fa-brain"></i>
                    AI Summary
                </h3>
                <div class="kb-summary-content" id="summary-content">{{ $knowledgeBase->ai_summary }}</div>
            </div>
        @endif

        <!-- Full Content Section -->
        <div class="kb-content-section copy-section">
            <button class="copy-btn" onclick="copyToClipboard('full-content', this)">
                <i class="fas fa-copy"></i> Copy
            </button>
            <h3 class="kb-content-title">
                <i class="fas fa-file-alt"></i>
                Full Content
            </h3>
            <div class="kb-content-text" id="full-content">{{ $knowledgeBase->pdf_text }}</div>
        </div>

        <!-- Actions -->
        <div class="kb-actions">
            <div class="kb-actions-left">
                <a href="{{ route('admin.knowledge-base.edit', $knowledgeBase->kb_id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    Edit Article
                </a>
                <a href="{{ route('admin.knowledge-base.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    View All Articles
                </a>
            </div>
            <button onclick="deleteArticle({{ $knowledgeBase->kb_id }})" class="btn btn-danger">
                <i class="fas fa-trash"></i>
                Delete Article
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(elementId, button) {
        const element = document.getElementById(elementId);
        const text = element.innerText || element.textContent;
        
        navigator.clipboard.writeText(text).then(function() {
            // Change button appearance temporarily
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Copied';
            button.classList.add('copied');
            
            setTimeout(function() {
                button.innerHTML = originalHTML;
                button.classList.remove('copied');
            }, 2000);
        }, function(err) {
            console.error('Could not copy text: ', err);
            alert('Failed to copy text to clipboard');
        });
    }

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

    // Add some interactivity for better user experience
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth scrolling for any anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    });
</script>
@endpush