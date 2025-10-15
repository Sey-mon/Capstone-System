@extends('layouts.dashboard')

@section('title', 'LLM Training Data Management')

@section('page-title', 'LLM Knowledge Base')
@section('page-subtitle', 'Manage training data for Large Language Model development')

@push('styles')
    <style>
        /* LLM-focused styles */
        .llm-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .llm-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .llm-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(0.8); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.2; }
        }

        .llm-header-content {
            position: relative;
            z-index: 2;
        }

        .llm-header-content h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .llm-header-content p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .llm-actions {
            display: flex;
            gap: 12px;
            position: relative;
            z-index: 2;
        }

        .btn-ai-primary {
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

        .btn-ai-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 90, 36, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-ai-secondary {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-ai-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        /* Training Data Statistics */
        .training-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card-llm {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #e8ecef;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .stat-card-llm::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-card-llm .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .stat-card-llm .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .stat-card-llm .stat-label {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .stat-card-llm .stat-subtext {
            font-size: 0.8rem;
            color: #a0aec0;
        }

        /* LLM Data Grid */
        .llm-data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .data-entry-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e8ecef;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .data-entry-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .data-entry-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .entry-header {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 16px;
        }

        .entry-icon {
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

        .entry-title {
            flex: 1;
        }

        .entry-title h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .entry-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 16px;
        }

        .entry-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .training-summary {
            background: #f7fafc;
            padding: 16px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            margin-bottom: 12px;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #4a5568;
        }

        .training-summary .summary-label {
            font-weight: 600;
            color: #2d3748;
            display: block;
            margin-bottom: 8px;
        }

        .content-preview {
            font-size: 0.9rem;
            color: #718096;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .training-metrics {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #edf2f7;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .metric-item {
            text-align: center;
        }

        .metric-value {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.9rem;
        }

        .metric-label {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 2px;
        }

        .entry-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

        .entry-actions-left {
            display: flex;
            gap: 8px;
        }

        .llm-btn {
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

        .llm-btn-primary {
            background: #667eea;
            color: white;
        }

        .llm-btn-primary:hover {
            background: #5a67d8;
            color: white;
            text-decoration: none;
        }

        .llm-btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .llm-btn-secondary:hover {
            background: #cbd5e0;
            color: #2d3748;
            text-decoration: none;
        }

        .llm-btn-api {
            background: #48bb78;
            color: white;
        }

        .llm-btn-api:hover {
            background: #38a169;
            color: white;
        }

        /* Search and filters */
        .llm-controls {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8ecef;
        }

        .controls-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-group {
            flex: 1;
            min-width: 300px;
        }

        .search-input-llm {
            width: 100%;
            padding: 14px 20px;
            border: 2px solid #e8ecef;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-input-llm:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .api-info-panel {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .api-info-panel h4 {
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .api-endpoints {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .api-endpoint {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.85rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .llm-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .llm-header-content h1 {
                font-size: 2rem;
            }

            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }

            .llm-data-grid {
                grid-template-columns: 1fr;
            }

            .training-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
<div class="llm-container">
    <!-- LLM Header -->
    <div class="llm-header">
        <div class="llm-header-content">
            <h1>
                <i class="fas fa-robot"></i>
                LLM Training Data
            </h1>
            <p>Manage and organize content for Large Language Model training and fine-tuning</p>
        </div>
        <div class="llm-actions">
            <a href="{{ route('admin.llm.create') }}" class="btn-ai-primary">
                <i class="fas fa-plus"></i>
                Add Training Data
            </a>
            <a href="{{ route('admin.llm.export') }}?format=json" class="btn-ai-secondary">
                <i class="fas fa-download"></i>
                Export Data
            </a>
        </div>
    </div>

    <!-- API Information Panel -->
    <div class="api-info-panel">
        <h4><i class="fas fa-code"></i> LLM API Endpoints Ready</h4>
        <p>Your training data is accessible through REST API endpoints for seamless LLM integration</p>
        <div class="api-endpoints">
            <div class="api-endpoint">GET /admin/llm/api/training-data</div>
            <div class="api-endpoint">GET /admin/llm/api/search</div>
            <div class="api-endpoint">GET /admin/llm/api/stats</div>
            <div class="api-endpoint">GET /admin/llm/api/export</div>
        </div>
    </div>

    <!-- Training Statistics -->
    <div class="training-stats">
        <div class="stat-card-llm">
            <div class="stat-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-value">{{ $knowledgeBase->total() }}</div>
            <div class="stat-label">Training Entries</div>
            <div class="stat-subtext">Ready for LLM consumption</div>
        </div>
        <div class="stat-card-llm">
            <div class="stat-icon">
                <i class="fas fa-file-word"></i>
            </div>
            <div class="stat-value">{{ number_format(\App\Models\KnowledgeBase::sum(\DB::raw('(LENGTH(pdf_text) - LENGTH(REPLACE(pdf_text, " ", "")) + 1)'))) }}</div>
            <div class="stat-label">Total Words</div>
            <div class="stat-subtext">Training vocabulary size</div>
        </div>
        <div class="stat-card-llm">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value">{{ \App\Models\KnowledgeBase::whereMonth('added_at', now()->month)->count() }}</div>
            <div class="stat-label">This Month</div>
            <div class="stat-subtext">New training data added</div>
        </div>
        <div class="stat-card-llm">
            <div class="stat-icon">
                <i class="fas fa-memory"></i>
            </div>
            <div class="stat-value">{{ number_format(\App\Models\KnowledgeBase::sum(\DB::raw('LENGTH(pdf_text)')) / 1024, 1) }} KB</div>
            <div class="stat-label">Data Size</div>
            <div class="stat-subtext">Total content volume</div>
        </div>
    </div>

    <!-- Search and Controls -->
    <div class="llm-controls">
        <form method="GET" action="{{ route('admin.llm.index') }}" class="controls-row">
            <div class="search-group">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search training data by title, content, or AI summary..."
                    class="search-input-llm"
                >
            </div>
            <button type="submit" class="llm-btn llm-btn-primary">
                <i class="fas fa-search"></i>
                Search
            </button>
            @if($search)
                <a href="{{ route('admin.llm.index') }}" class="llm-btn llm-btn-secondary">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            @endif
            <a href="{{ route('admin.llm.training-data') }}" class="llm-btn llm-btn-api">
                <i class="fas fa-api"></i>
                API Data
            </a>
        </form>
    </div>

    @if($knowledgeBase->count() > 0)
        <!-- Training Data Grid -->
        <div class="llm-data-grid">
            @foreach($knowledgeBase as $entry)
                <div class="data-entry-card">
                    <div class="entry-header">
                        <div class="entry-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="entry-title">
                            <h3>{{ Str::limit($entry->pdf_name, 50) }}</h3>
                        </div>
                    </div>

                    <div class="entry-meta">
                        <div class="entry-meta-item">
                            <i class="fas fa-user"></i>
                            <span>{{ $entry->user->first_name ?? 'System' }}</span>
                        </div>
                        <div class="entry-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>{{ $entry->added_at ? $entry->added_at->format('M j, Y') : 'N/A' }}</span>
                        </div>
                        <div class="entry-meta-item">
                            <i class="fas fa-clock"></i>
                            <span>{{ $entry->added_at ? $entry->added_at->diffForHumans() : 'Unknown' }}</span>
                        </div>
                    </div>

                    @if($entry->ai_summary)
                        <div class="training-summary">
                            <span class="summary-label">Training Summary:</span>
                            {{ Str::limit($entry->ai_summary, 120) }}
                        </div>
                    @endif
                    
                    <div class="content-preview">
                        {{ $entry->excerpt }}
                    </div>

                    <!-- Training Metrics -->
                    <div class="training-metrics">
                        <div class="metric-item">
                            <div class="metric-value">{{ number_format(str_word_count($entry->pdf_text ?? '')) }}</div>
                            <div class="metric-label">Words</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-value">{{ number_format(strlen($entry->pdf_text ?? '')) }}</div>
                            <div class="metric-label">Characters</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-value">{{ $entry->formatted_size }}</div>
                            <div class="metric-label">Size</div>
                        </div>
                    </div>

                    <div class="entry-actions">
                        <div class="entry-actions-left">
                            <a href="{{ route('admin.llm.show', $entry->kb_id) }}" class="llm-btn llm-btn-primary">
                                <i class="fas fa-eye"></i>
                                View
                            </a>
                            <a href="{{ route('admin.llm.edit', $entry->kb_id) }}" class="llm-btn llm-btn-secondary">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                        </div>
                        <button 
                            onclick="deleteEntry({{ $entry->kb_id }})" 
                            class="llm-btn llm-btn-secondary"
                            title="Delete Training Data"
                            style="color: #e53e3e;"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="text-center">
            {{ $knowledgeBase->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <div style="font-size: 4rem; color: #cbd5e0; margin-bottom: 20px;">
                <i class="fas fa-robot"></i>
            </div>
            <h3 style="font-size: 1.5rem; font-weight: 600; color: #4a5568; margin-bottom: 8px;">
                No LLM Training Data Found
            </h3>
            <p style="color: #718096; margin-bottom: 24px;">
                @if($search)
                    No training data matches your search criteria. Try different keywords.
                @else
                    Start building your LLM training dataset by adding your first entry.
                @endif
            </p>
            <a href="{{ route('admin.llm.create') }}" class="btn-ai-primary">
                <i class="fas fa-plus"></i>
                Add Your First Training Data
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function deleteEntry(id) {
        if (confirm('Are you sure you want to delete this training data entry? This will affect LLM training datasets.')) {
            fetch(`/admin/llm/${id}`, {
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
                    alert('Error deleting training data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the training data.');
            });
        }
    }

    // API demonstration
    function showApiData() {
        fetch('/admin/llm/api/training-data?limit=5&format=standard')
            .then(response => response.json())
            .then(data => {
                console.log('LLM Training Data API Response:', data);
                alert('Check console for API response format. This data is ready for your LLM system.');
            })
            .catch(error => {
                console.error('API Error:', error);
            });
    }
</script>
@endpush