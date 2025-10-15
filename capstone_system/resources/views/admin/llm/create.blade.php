@extends('layouts.dashboard')

@section('title', 'Add LLM Training Data')

@section('page-title', 'Add Training Data')
@section('page-subtitle', 'Create new content for LLM training and fine-tuning')

@push('styles')
    <style>
        .llm-form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .llm-form-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8ecef;
        }

        .llm-info-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .llm-info-panel::before {
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
            0%, 100% { transform: scale(0.8); opacity: 0.3; }
            50% { transform: scale(1.2); opacity: 0.1; }
        }

        .llm-info-content {
            position: relative;
            z-index: 2;
        }

        .llm-info-panel h4 {
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.3rem;
        }

        .llm-info-panel p {
            margin: 0 0 16px 0;
            opacity: 0.9;
            line-height: 1.6;
        }

        .llm-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .llm-feature {
            background: rgba(255, 255, 255, 0.1);
            padding: 12px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .llm-feature i {
            margin-right: 8px;
        }

        .form-group {
            margin-bottom: 28px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label .required {
            color: #e53e3e;
        }

        .form-control {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #fff;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .form-control.is-invalid {
            border-color: #e53e3e;
        }

        .invalid-feedback {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 8px;
            display: block;
        }

        .form-help {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 8px;
            line-height: 1.4;
        }

        .form-help.llm-tip {
            background: #f7fafc;
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid #667eea;
            margin-top: 12px;
        }

        textarea.form-control {
            min-height: 140px;
            resize: vertical;
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
        }

        #content {
            min-height: 320px;
            font-family: 'Monaco', 'Consolas', 'SF Mono', monospace;
            font-size: 14px;
            line-height: 1.6;
        }

        .character-count {
            text-align: right;
            font-size: 0.875rem;
            color: #718096;
            margin-top: 8px;
            font-weight: 500;
        }

        .character-count.warning {
            color: #d69e2e;
        }

        .character-count.danger {
            color: #e53e3e;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }

        .tags-input {
            position: relative;
        }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .tag-item {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tag-remove {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .tag-remove:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            padding-top: 32px;
            border-top: 2px solid #e2e8f0;
            margin-top: 40px;
        }

        .btn {
            padding: 16px 28px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
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
            border-radius: 12px;
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

        .progress-indicator {
            background: #f7fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 32px;
            border: 1px solid #e2e8f0;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #718096;
        }

        .progress-step.active {
            color: #667eea;
            font-weight: 600;
        }

        .progress-step .step-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .progress-step.active .step-icon {
            background: #667eea;
            color: white;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .llm-form-card {
                padding: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                justify-content: center;
            }

            .llm-features {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
<div class="llm-form-container">
    <!-- Back Navigation -->
    <div class="mb-4">
        <a href="{{ route('admin.llm.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to LLM Data
        </a>
    </div>

    <div class="llm-form-card">
        <!-- LLM Information Panel -->
        <div class="llm-info-panel">
            <div class="llm-info-content">
                <h4><i class="fas fa-robot"></i> LLM Training Data Creation</h4>
                <p>Create high-quality training content for your Large Language Model. This data will be used to improve the AI's understanding of nutrition, malnutrition assessment, and treatment protocols.</p>
                
                <div class="llm-features">
                    <div class="llm-feature">
                        <i class="fas fa-brain"></i>
                        AI Training Ready
                    </div>
                    <div class="llm-feature">
                        <i class="fas fa-api"></i>
                        API Accessible
                    </div>
                    <div class="llm-feature">
                        <i class="fas fa-search"></i>
                        Searchable Content
                    </div>
                    <div class="llm-feature">
                        <i class="fas fa-download"></i>
                        Export Formats
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-steps">
                <div class="progress-step active">
                    <div class="step-icon"><i class="fas fa-edit"></i></div>
                    <span>Content Creation</span>
                </div>
                <div class="progress-step">
                    <div class="step-icon">2</div>
                    <span>AI Processing</span>
                </div>
                <div class="progress-step">
                    <div class="step-icon">3</div>
                    <span>Training Ready</span>
                </div>
            </div>
            <div style="font-size: 0.85rem; color: #718096;">
                Step 1: Provide comprehensive content for LLM training
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

        <form method="POST" action="{{ route('admin.llm.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Title -->
            <div class="form-group">
                <label for="title" class="form-label">
                    <i class="fas fa-heading"></i>
                    Training Data Title <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}"
                    placeholder="Enter a descriptive title for this training content"
                    required
                    maxlength="255"
                >
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-help llm-tip">
                    <strong>LLM Tip:</strong> Use clear, descriptive titles that help categorize the content type (e.g., "WHO Malnutrition Guidelines", "MUAC Measurement Protocol").
                </div>
            </div>

            <!-- Content Type and Priority -->
            <div class="form-row">
                <div class="form-group">
                    <label for="content_type" class="form-label">
                        <i class="fas fa-tag"></i>
                        Content Type
                    </label>
                    <select 
                        id="content_type" 
                        name="content_type" 
                        class="form-control form-select @error('content_type') is-invalid @enderror"
                    >
                        <option value="">Select content type</option>
                        <option value="guideline" {{ old('content_type') == 'guideline' ? 'selected' : '' }}>Clinical Guideline</option>
                        <option value="protocol" {{ old('content_type') == 'protocol' ? 'selected' : '' }}>Treatment Protocol</option>
                        <option value="research" {{ old('content_type') == 'research' ? 'selected' : '' }}>Research Finding</option>
                        <option value="faq" {{ old('content_type') == 'faq' ? 'selected' : '' }}>FAQ/Q&A</option>
                        <option value="case_study" {{ old('content_type') == 'case_study' ? 'selected' : '' }}>Case Study</option>
                    </select>
                    @error('content_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="priority" class="form-label">
                        <i class="fas fa-flag"></i>
                        Training Priority
                    </label>
                    <select 
                        id="priority" 
                        name="priority" 
                        class="form-control form-select @error('priority') is-invalid @enderror"
                    >
                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- AI Summary -->
            <div class="form-group">
                <label for="summary" class="form-label">
                    <i class="fas fa-brain"></i>
                    AI Training Summary
                </label>
                <textarea 
                    id="summary" 
                    name="summary" 
                    class="form-control @error('summary') is-invalid @enderror"
                    placeholder="Provide a concise summary highlighting key concepts, procedures, or knowledge points that the AI should learn from this content..."
                    rows="4"
                    maxlength="1000"
                >{{ old('summary') }}</textarea>
                @error('summary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="character-count" id="summary-count">0 / 1000 characters</div>
                <div class="form-help llm-tip">
                    <strong>LLM Tip:</strong> This summary helps the AI understand the core concepts. Focus on key learning objectives and actionable insights.
                </div>
            </div>

            <!-- Main Content -->
            <div class="form-group">
                <label for="content" class="form-label">
                    <i class="fas fa-file-text"></i>
                    Training Content <span class="required">*</span>
                </label>
                <textarea 
                    id="content" 
                    name="content" 
                    class="form-control @error('content') is-invalid @enderror"
                    placeholder="Enter the complete training content. Include detailed procedures, explanations, examples, and any relevant clinical information..."
                    required
                    minlength="50"
                >{{ old('content') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="character-count" id="content-count">0 characters (min. 50)</div>
                <div class="form-help llm-tip">
                    <strong>LLM Training Guidelines:</strong>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <li>Be comprehensive and detailed</li>
                        <li>Include step-by-step procedures when applicable</li>
                        <li>Provide context and reasoning</li>
                        <li>Use clear, professional medical terminology</li>
                        <li>Include examples and edge cases</li>
                    </ul>
                </div>
            </div>

            <!-- Tags -->
            <div class="form-group">
                <label for="tags" class="form-label">
                    <i class="fas fa-tags"></i>
                    Content Tags
                </label>
                <div class="tags-input">
                    <input 
                        type="text" 
                        id="tags" 
                        name="tags" 
                        class="form-control @error('tags') is-invalid @enderror"
                        value="{{ old('tags') }}"
                        placeholder="nutrition, malnutrition, MUAC, WHO, assessment, treatment"
                    >
                    <div class="tags-container" id="tags-container"></div>
                </div>
                @error('tags')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    Separate tags with commas. Tags help organize and retrieve training data efficiently.
                </div>
            </div>

            <!-- Optional PDF Upload -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-file-pdf"></i>
                    Source Document (Optional)
                </label>
                <input 
                    type="file" 
                    id="pdf_file" 
                    name="pdf_file" 
                    class="form-control @error('pdf_file') is-invalid @enderror"
                    accept=".pdf"
                >
                @error('pdf_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    Upload the source PDF document for reference (max 10MB). The text content above is still required for LLM training.
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.llm.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-robot"></i>
                    Create Training Data
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
        const summaryTextarea = document.getElementById('summary');
        const contentTextarea = document.getElementById('content');
        const summaryCount = document.getElementById('summary-count');
        const contentCount = document.getElementById('content-count');

        function updateCharacterCount(textarea, counter, maxLength = null, minLength = null) {
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
                let text = `${length.toLocaleString()} characters`;
                if (minLength) {
                    text += ` (min. ${minLength})`;
                    if (length < minLength) {
                        counter.classList.add('warning');
                    } else {
                        counter.classList.remove('warning');
                    }
                }
                counter.textContent = text;
            }
        }

        summaryTextarea.addEventListener('input', () => updateCharacterCount(summaryTextarea, summaryCount, 1000));
        contentTextarea.addEventListener('input', () => updateCharacterCount(contentTextarea, contentCount, null, 50));

        // Initial count
        updateCharacterCount(summaryTextarea, summaryCount, 1000);
        updateCharacterCount(contentTextarea, contentCount, null, 50);

        // Tags handling
        const tagsInput = document.getElementById('tags');
        const tagsContainer = document.getElementById('tags-container');

        function updateTags() {
            const tags = tagsInput.value.split(',').map(tag => tag.trim()).filter(tag => tag);
            tagsContainer.innerHTML = '';
            
            tags.forEach(tag => {
                const tagElement = document.createElement('div');
                tagElement.className = 'tag-item';
                tagElement.innerHTML = `
                    ${tag}
                    <button type="button" class="tag-remove" onclick="removeTag('${tag}')">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                tagsContainer.appendChild(tagElement);
            });
        }

        tagsInput.addEventListener('input', updateTags);
        
        // Initial tags display
        if (tagsInput.value) {
            updateTags();
        }

        window.removeTag = function(tagToRemove) {
            const currentTags = tagsInput.value.split(',').map(tag => tag.trim());
            const newTags = currentTags.filter(tag => tag !== tagToRemove);
            tagsInput.value = newTags.join(', ');
            updateTags();
        };
    });
</script>
@endpush