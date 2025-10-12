@extends('layouts.dashboard')

@section('title', 'Smart Meal Plans')

@section('page-title', 'Smart Meal Plans')
@section('page-subtitle', 'AI-powered nutrition planning for your little ones')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/parent/meal-plans.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('js/parent/meal-plans.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="meal-plan-page-wrapper">
<!-- Modern Hero Section -->
<div class="meal-plan-hero">
    <div class="hero-background">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-icon">🤖</span>
                <span>AI-Powered Nutrition</span>
            </div>
            <h1 class="hero-title">Smart Meal Planning</h1>
            <p class="hero-description">Create personalized, nutritious meal plans tailored specifically for your child's needs using advanced AI technology</p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container-fluid meal-plan-container">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="ultra-alert success">
                    <div class="alert-decoration">
                        <div class="success-particles"></div>
                    </div>
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Success!</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="ultra-alert error">
                    <div class="alert-decoration">
                        <div class="error-particles"></div>
                    </div>
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Please check the following:</h4>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Meal Plan Generator Card -->
            <div class="ultra-card main-card">
                <div class="card-glow"></div>
                <div class="card-header-ultra">
                    <div class="header-content">
                        <div class="header-icon-wrapper">
                            <div class="rotating-border"></div>
                            <div class="header-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                        </div>
                        <div class="header-text">
                            <h2>AI Meal Generator</h2>
                            <p>Let artificial intelligence create the perfect meal plan</p>
                        </div>
                    </div>
                    <div class="header-stats">
                        <div class="stat">
                            <span class="stat-number">{{ $children->count() }}</span>
                            <span class="stat-label">Children</span>
                        </div>
                    </div>
                </div>

                <div class="card-body-ultra">
                    @if($children->count() > 0)
                        <form method="POST" action="{{ route('parent.meal-plans.generate') }}" class="ultra-form">
                            @csrf
                            
                            <!-- Child Selection Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-child"></i>
                                    </div>
                                    <div class="section-info">
                                        <h3>Select Your Child</h3>
                                        <p>Choose which child you'd like to create a meal plan for</p>
                                    </div>
                                </div>
                                
                                <div class="children-grid">
                                    @foreach($children as $child)
                                        <div class="child-card">
                                            <input type="radio" 
                                                   name="patient_id" 
                                                   value="{{ $child->patient_id }}" 
                                                   id="child_{{ $child->patient_id }}"
                                                   {{ old('patient_id') == $child->patient_id ? 'checked' : '' }}
                                                   required>
                                            <label for="child_{{ $child->patient_id }}" class="child-label">
                                                <div class="child-avatar">
                                                    <i class="fas fa-baby"></i>
                                                </div>
                                                <div class="child-info">
                                                    <h4>{{ $child->first_name }} {{ $child->last_name }}</h4>
                                                    <p>{{ $child->age_months }} months old</p>
                                                </div>
                                                <div class="check-indicator">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('patient_id')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Available Foods Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-shopping-basket"></i>
                                    </div>
                                    <div class="section-info">
                                        <h3>Available Ingredients</h3>
                                        <p>Tell us what ingredients you have at home</p>
                                    </div>
                                </div>
                                
                                <div class="ingredient-input-wrapper">
                                    <div class="input-field-ultra">
                                        <input type="text" 
                                               name="available_foods" 
                                               id="available_foods" 
                                               placeholder="Start typing ingredients..."
                                               value="{{ old('available_foods') }}"
                                               required>
                                        <div class="input-decoration">
                                            <div class="input-glow"></div>
                                        </div>
                                        <div class="input-icon">
                                            <i class="fas fa-apple-alt"></i>
                                        </div>
                                    </div>
                                    <div class="ingredient-suggestions">
                                        <div class="suggestion-category">
                                            <span class="category-label">Popular ingredients:</span>
                                            <div class="suggestion-tags">
                                                <span class="tag" onclick="addIngredient('rice')">Rice</span>
                                                <span class="tag" onclick="addIngredient('chicken')">Chicken</span>
                                                <span class="tag" onclick="addIngredient('vegetables')">Vegetables</span>
                                                <span class="tag" onclick="addIngredient('milk')">Milk</span>
                                                <span class="tag" onclick="addIngredient('eggs')">Eggs</span>
                                                <span class="tag" onclick="addIngredient('fruits')">Fruits</span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('available_foods')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Generate Button -->
                            <div class="form-actions">
                                <button type="submit" class="ultra-button primary">
                                    <div class="button-background">
                                        <div class="button-shine"></div>
                                    </div>
                                    <div class="button-content">
                                        <i class="fas fa-magic button-icon"></i>
                                        <span class="button-text">Generate Smart Meal Plan</span>
                                        <div class="button-particles">
                                            <div class="particle"></div>
                                            <div class="particle"></div>
                                            <div class="particle"></div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="empty-state-ultra">
                            <div class="empty-animation">
                                <div class="empty-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="empty-ripple"></div>
                            </div>
                            <h3>No Children Found</h3>
                            <p>You need to add at least one child to your account before generating meal plans.</p>
                            <a href="{{ route('parent.bind-child') }}" class="ultra-button secondary">
                                <div class="button-content">
                                    <i class="fas fa-plus button-icon"></i>
                                    <span class="button-text">Add Your First Child</span>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Meal Plan Results -->
            @if(session('meal_plan'))
                <div class="ultra-card results-card">
                    <div class="card-glow success"></div>
                    <div class="card-header-ultra">
                        <div class="header-content">
                            <div class="header-icon-wrapper success">
                                <div class="success-pulse"></div>
                                <div class="header-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                            </div>
                            <div class="header-text">
                                <h2>Your Personalized Meal Plan</h2>
                                <p>Generated for {{ session('child_name') }} • {{ now()->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <button onclick="printMealPlan()" class="action-btn" title="Print Meal Plan">
                                <i class="fas fa-print"></i>
                            </button>
                            <button onclick="copyMealPlan()" class="action-btn" title="Copy to Clipboard" id="copyBtn">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button onclick="downloadMealPlan()" class="action-btn" title="Download PDF">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body-ultra">
                        <div class="meal-plan-display">
                            @if(session('meal_plan_html'))
                                <div class="meal-plan-content-ultra">
                                    {!! session('meal_plan_html') !!}
                                </div>
                            @else
                                <div class="meal-plan-content-ultra">
                                    <pre>{{ session('meal_plan') }}</pre>
                                </div>
                            @endif
                        </div>
                        
                        <div class="action-footer">
                            <div class="action-buttons">
                                <button onclick="printMealPlan()" class="ultra-button outline">
                                    <div class="button-content">
                                        <i class="fas fa-print button-icon"></i>
                                        <span class="button-text">Print Plan</span>
                                    </div>
                                </button>
                                <button onclick="copyMealPlan()" class="ultra-button secondary" id="copyMainBtn">
                                    <div class="button-content">
                                        <i class="fas fa-copy button-icon"></i>
                                        <span class="button-text">Copy Text</span>
                                    </div>
                                </button>
                            </div>
                            <div class="plan-stats">
                                <div class="stat-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Generated in 2.3s</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-brain"></i>
                                    <span>AI Optimized</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
@endsection
