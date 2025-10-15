@extends('layouts.dashboard')

@section('title', 'Knowledge Base Management')

@section('page-title', 'Knowledge Base Management')
@section('page-subtitle', 'Manage AI training content and documentation')

@push('styles')
    <style>
        /* Modern Knowledge Base Styles */
        .kb-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .kb-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .kb-header-content h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .kb-header-content p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .kb-header-actions {
            display: flex;
            gap: 12px;
        }

        .btn-primary-gradient {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(238, 90, 36, 0.3);
        }

        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 90, 36, 0.4);
            color: white;
            text-decoration: none;
        }

        .kb-search-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8ecef;
        }

        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 14px 20px;
            border: 2px solid #e8ecef;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }

        .kb-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .kb-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e8ecef;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .kb-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .kb-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .kb-card-header {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 16px;
        }

        .kb-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .kb-card-title {
            flex: 1;
        }

        .kb-card-title h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .kb-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 16px;
        }

        .kb-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kb-content {
            margin-bottom: 20px;
        }

        .kb-summary {
            background: #f7fafc;
            padding: 16px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            margin-bottom: 12px;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #4a5568;
        }

        .kb-excerpt {
            font-size: 0.9rem;
            color: #718096;
            line-height: 1.6;
        }

        .kb-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

        .kb-actions-left {
            display: flex;
            gap: 8px;
        }

        .kb-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .kb-btn-primary {
            background: #667eea;
            color: white;
        }

        .kb-btn-primary:hover {
            background: #5a67d8;
            color: white;
            text-decoration: none;
        }

        .kb-btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .kb-btn-secondary:hover {
            background: #cbd5e0;
            color: #2d3748;
            text-decoration: none;
        }

        .kb-btn-danger {
            background: #fed7d7;
            color: #e53e3e;
        }

        .kb-btn-danger:hover {
            background: #feb2b2;
            color: #c53030;
        }

        .kb-stats {
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
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 4px;
        }

        .kb-stat-label {
            color: #718096;
            font-size: 0.9rem;
        }

        .kb-empty {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e2e8f0;
        }

        .kb-empty-icon {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .kb-empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .kb-empty-text {
            color: #718096;
            margin-bottom: 24px;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .kb-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .kb-header-content h1 {
                font-size: 2rem;
            }

            .search-form {
                flex-direction: column;
            }

            .kb-grid {
                grid-template-columns: 1fr;
            }

            .kb-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .kb-stats {
                grid-template-columns: 1fr;
            }

            .kb-actions {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
        }
    </style>
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
<div class="kb-container">
    <!-- Header Section -->
    <div class="kb-header">
        <div class="kb-header-content">
            <h1><i class="fas fa-brain"></i> Knowledge Base</h1>
            <p>Manage AI training content, documentation, and learning materials</p>
        </div>
        <div class="kb-header-actions">
            <a href="{{ route('admin.knowledge-base.create') }}" class="btn-primary-gradient">
                <i class="fas fa-plus"></i>
                Add New Article
            </a>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="kb-stats">
        <div class="kb-stat-card">
            <div class="kb-stat-value">{{ $knowledgeBase->total() }}</div>
            <div class="kb-stat-label">Total Articles</div>
        </div>
        <div class="kb-stat-card">
            <div class="kb-stat-value">{{ \App\Models\KnowledgeBase::whereMonth('added_at', now()->month)->count() }}</div>
            <div class="kb-stat-label">This Month</div>
        </div>
        <div class="kb-stat-card">
            <div class="kb-stat-value">{{ \App\Models\KnowledgeBase::whereDate('added_at', today())->count() }}</div>
            <div class="kb-stat-label">Today</div>
        </div>
        <div class="kb-stat-card">
            <div class="kb-stat-value">{{ number_format(\App\Models\KnowledgeBase::sum(\DB::raw('LENGTH(pdf_text)')) / 1024, 1) }} KB</div>
            <div class="kb-stat-label">Total Content</div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="kb-search-section">
        <form method="GET" action="{{ route('admin.knowledge-base.index') }}" class="search-form">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                placeholder="Search articles by title, content, or AI summary..."
                class="search-input"
            >
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
                Search
            </button>
            @if($search)
                <a href="{{ route('admin.knowledge-base.index') }}" class="kb-btn kb-btn-secondary">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            @endif
        </form>
    </div>

    @if($knowledgeBase->count() > 0)
        <!-- Knowledge Base Grid -->
        <div class="kb-grid">
            @foreach($knowledgeBase as $article)
                <div class="kb-card">
                    <div class="kb-card-header">
                        <div class="kb-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="kb-card-title">
                            <h3>{{ Str::limit($article->pdf_name, 50) }}</h3>
                        </div>
                    </div>

                    <div class="kb-meta">
                        <div class="kb-meta-item">
                            <i class="fas fa-user"></i>
                            <span>{{ $article->user->first_name ?? 'System' }}</span>
                        </div>
                        <div class="kb-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>{{ $article->added_at ? $article->added_at->format('M j, Y') : 'N/A' }}</span>
                        </div>
                        <div class="kb-meta-item">
                            <i class="fas fa-file-text"></i>
                            <span>{{ $article->formatted_size }}</span>
                        </div>
                    </div>

                    <div class="kb-content">
                        @if($article->ai_summary)
                            <div class="kb-summary">
                                <strong>AI Summary:</strong><br>
                                {{ Str::limit($article->ai_summary, 120) }}
                            </div>
                        @endif
                        
                        <div class="kb-excerpt">
                            {{ $article->excerpt }}
                        </div>
                    </div>

                    <div class="kb-actions">
                        <div class="kb-actions-left">
                            <a href="{{ route('admin.knowledge-base.show', $article->kb_id) }}" class="kb-btn kb-btn-primary">
                                <i class="fas fa-eye"></i>
                                View
                            </a>
                            <a href="{{ route('admin.knowledge-base.edit', $article->kb_id) }}" class="kb-btn kb-btn-secondary">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                        </div>
                        <button 
                            onclick="deleteArticle({{ $article->kb_id }})" 
                            class="kb-btn kb-btn-danger"
                            title="Delete Article"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $knowledgeBase->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="kb-empty">
            <div class="kb-empty-icon">
                <i class="fas fa-brain"></i>
            </div>
            <h3 class="kb-empty-title">No Knowledge Base Articles Found</h3>
            <p class="kb-empty-text">
                @if($search)
                    No articles match your search criteria. Try different keywords.
                @else
                    Start building your AI knowledge base by adding your first article.
                @endif
            </p>
            <a href="{{ route('admin.knowledge-base.create') }}" class="btn-primary-gradient">
                <i class="fas fa-plus"></i>
                Add Your First Article
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
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
                    location.reload();
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