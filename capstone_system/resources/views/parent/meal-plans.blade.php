@extends('layouts.dashboard')

@section('title', 'Smart Meal Plans')

@section('page-title', 'Smart Meal Plans')
@section('page-subtitle', 'AI-powered nutrition planning for your little ones')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/parent/meal-plans.css') }}?v={{ time() }}{{ rand(1000, 9999) }}">
@endpush

@push('scripts')
<script src="{{ asset('js/parent/meal-plans.js') }}?v={{ time() }}{{ rand(1000, 9999) }}"></script>
@endpush

@section('content')
<div class="meal-plan-page-wrapper">
    <!-- Compact Header Section -->
    <div class="desktop-header-section">
        <div class="header-left">
            <div class="page-icon">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="page-info">
                <h1 class="page-main-title">Smart Meal Planning</h1>
                <p class="page-description">AI-powered 7-day meal plans for your children</p>
            </div>
        </div>
        <div class="header-right">
            <div class="header-stat-badge">
                <i class="fas fa-child"></i>
                <span>{{ count($children ?? []) }} {{ count($children ?? []) === 1 ? 'Child' : 'Children' }}</span>
            </div>
        </div>
    </div>

<!-- Main Content -->
<div class="container-fluid meal-plan-container">
    <div class="row justify-content-center">
        <div class="col-12">
            
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

            @if($errors->has('cooldown'))
                <div class="ultra-alert warning">
                    <div class="alert-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Meal Plan Cooldown Active</h4>
                        <p>{{ $errors->first('cooldown') }}</p>
                        <small>7-day meal plans should be followed for the full week for best results.</small>
                    </div>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @elseif($errors->any())
                <div class="ultra-alert error">
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
                <div class="card-header-ultra">
                    <div class="header-content">
                        <div class="header-icon-wrapper">
                            <div class="header-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                        </div>
                        <div class="header-text">
                            <h2>AI Meal Generator</h2>
                            <p>Generate personalized 7-day meal plans</p>
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
                                        <h3>Select Child</h3>
                                        <p>Choose a child for meal planning</p>
                                    </div>
                                </div>
                                
                                <div class="custom-select-wrapper">
                                    <div class="custom-select-trigger" id="childSelectTrigger">
                                        <div class="selected-child-display">
                                            <div class="selected-child-avatar">
                                                <i class="fas fa-baby"></i>
                                            </div>
                                            <div class="selected-child-info">
                                                <span class="selected-child-name">Select a child...</span>
                                                <span class="selected-child-age"></span>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                                    </div>
                                    
                                    <div class="custom-select-dropdown" id="childSelectDropdown">
                                        <div class="dropdown-search">
                                            <i class="fas fa-search"></i>
                                            <input type="text" 
                                                   id="childDropdownSearch" 
                                                   placeholder="Search children..."
                                                   autocomplete="off">
                                        </div>
                                        <div class="dropdown-options">
                                            @foreach($children as $child)
                                                <div class="dropdown-option" 
                                                     data-value="{{ $child->patient_id }}"
                                                     data-name="{{ $child->first_name }} {{ $child->last_name }}"
                                                     data-age="{{ $child->age_months }}"
                                                     data-age-months="{{ $child->age_months }}"
                                                     data-search="{{ strtolower($child->first_name . ' ' . $child->last_name) }}">
                                                    <div class="option-avatar">
                                                        <i class="fas fa-baby"></i>
                                                    </div>
                                                    <div class="option-info">
                                                        <span class="option-name">{{ $child->first_name }} {{ $child->last_name }}</span>
                                                        <span class="option-age">{{ $child->age_months }} months old</span>
                                                    </div>
                                                    <i class="fas fa-check option-check"></i>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="dropdown-no-results" style="display: none;">
                                            <i class="fas fa-search"></i>
                                            <p>No children found</p>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}" required>
                                </div>
                                
                                @error('patient_id')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Breastfeeding Notice for Babies Under 6 Months -->
                            <div class="ultra-alert info" id="breastfeedingNotice" style="display: none;">
                                <div class="alert-icon">
                                    <i class="fas fa-baby"></i>
                                </div>
                                <div class="alert-content">
                                    <h4>Exclusive Breastfeeding Recommended</h4>
                                    <p>For babies under 6 months old, the World Health Organization (WHO) recommends exclusive breastfeeding. Breast milk provides all the nutrition your baby needs during this period.</p>
                                    <small><strong>Note:</strong> This meal plan generator is designed for children 6 months and older who are ready for complementary foods. Please consult with your pediatrician or health worker for specific feeding guidance for babies under 6 months.</small>
                                </div>
                            </div>

                            <!-- Available Foods Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-shopping-basket"></i>
                                    </div>
                                    <div class="section-info">
                                        <h3>Available Ingredients</h3>
                                        <p>What ingredients do you have?</p>
                                    </div>
                                </div>
                                
                                <div class="ingredient-input-wrapper">
                                    <div class="input-field-ultra">
                                        <input type="text" 
                                               name="available_foods" 
                                               id="available_foods" 
                                               placeholder="e.g., chicken, rice, vegetables, eggs, fish, fruits"
                                               value="{{ old('available_foods') }}"
                                               required>
                                        <div class="input-decoration">
                                            <div class="input-glow"></div>
                                        </div>
                                        <div class="input-icon">
                                            <i class="fas fa-apple-alt"></i>
                                        </div>
                                    </div>
                                    <div class="ingredient-help-text">
                                        <i class="fas fa-info-circle"></i>
                                        <span>List the ingredients you have at home, separated by commas. The AI will prioritize these in your meal plan.</span>
                                    </div>
                                    <div class="ingredient-suggestions">
                                        <div class="suggestion-category">
                                            <span class="category-label">Common ingredients:</span>
                                            <div class="suggestion-tags">
                                                <span class="tag" onclick="addIngredient('chicken')">Chicken</span>
                                                <span class="tag" onclick="addIngredient('rice')">Rice</span>
                                                <span class="tag" onclick="addIngredient('fish')">Fish</span>
                                                <span class="tag" onclick="addIngredient('eggs')">Eggs</span>
                                                <span class="tag" onclick="addIngredient('vegetables')">Vegetables</span>
                                                <span class="tag" onclick="addIngredient('pork')">Pork</span>
                                                <span class="tag" onclick="addIngredient('fruits')">Fruits</span>
                                                <span class="tag" onclick="addIngredient('beef')">Beef</span>
                                            </div>
                                        </div>
                                        <div class="suggestion-category">
                                            <span class="category-label">Filipino staples:</span>
                                            <div class="suggestion-tags">
                                                <span class="tag" onclick="addIngredient('bangus')">Bangus</span>
                                                <span class="tag" onclick="addIngredient('kangkong')">Kangkong</span>
                                                <span class="tag" onclick="addIngredient('malunggay')">Malunggay</span>
                                                <span class="tag" onclick="addIngredient('sitaw')">Sitaw</span>
                                                <span class="tag" onclick="addIngredient('kalabasa')">Kalabasa</span>
                                                <span class="tag" onclick="addIngredient('saging')">Saging</span>
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
                            </div>
                            <h3>No Children Found</h3>
                            <p>You need to add at least one child to your account before generating meal plans.</p>
                            <a href="{{ route('parent.children') }}" class="ultra-button secondary">
                                <div class="button-content">
                                    <i class="fas fa-plus button-icon"></i>
                                    <span class="button-text">Add Your First Child</span>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Meal Plan Generated Success Banner -->
            @if(session('meal_plan') || session('last_meal_plan'))
                <div class="ultra-card success-banner">
                    <div class="banner-content">
                        <div class="banner-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="banner-text">
                            <h3>Meal Plan Successfully Generated!</h3>
                            <p>Your personalized 7-day meal plan for {{ session('child_name') }} is ready to view</p>
                        </div>
                        <button onclick="openMealPlanModal()" class="ultra-button primary banner-action">
                            <div class="button-content">
                                <i class="fas fa-table button-icon"></i>
                                <span class="button-text">View Meal Plan</span>
                            </div>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Wide Meal Plan Modal -->
@if(session('meal_plan') || session('last_meal_plan'))
<div id="mealPlanModal" class="meal-plan-modal">
    <div class="modal-overlay" onclick="closeMealPlanModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="modal-title-section">
                    <h2>7-Day Personalized Meal Plan</h2>
                    <p>{{ session('child_name') }} • {{ session('plan_date') ?? now()->format('F d, Y') }}</p>
                </div>
            </div>
            <div class="modal-actions">
                <button onclick="printMealPlan()" class="modal-action-btn" title="Print">
                    <i class="fas fa-print"></i>
                </button>
                <button onclick="copyMealPlan()" class="modal-action-btn" title="Copy">
                    <i class="fas fa-copy"></i>
                </button>
                <button onclick="downloadMealPlan()" class="modal-action-btn" title="Download">
                    <i class="fas fa-download"></i>
                </button>
                <button onclick="closeMealPlanModal()" class="modal-close-btn" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="modal-body">
            <div class="meal-plan-table-wrapper">
                <div id="mealPlanTableContent">
                    <!-- Meal Plan Header Info -->
                    <div class="meal-plan-header-info">
                        <div class="info-item">
                            <i class="fas fa-user"></i>
                            <span>{{ session('child_name') ?? 'Patient9 Testcase' }}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            <span>{{ session('plan_date') ?? now()->format('F d, Y') }}</span>
                        </div>
                    </div>

                    <!-- Weekly Meal Schedule Title -->
                    <div class="meal-schedule-title">
                        <i class="fas fa-calendar-week"></i>
                        <h3>Weekly Meal Schedule</h3>
                    </div>

                    <!-- Responsive Table Wrapper -->
                    <div class="table-scroll-wrapper">
                        <table class="meal-schedule-table">
                            <thead>
                                <tr>
                                    <th class="meal-column">MEAL</th>
                                    <th>DAY 1</th>
                                    <th>DAY 2</th>
                                    <th>DAY 3</th>
                                    <th>DAY 4</th>
                                    <th>DAY 5</th>
                                    <th>DAY 6</th>
                                    <th>DAY 7</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="meal-row">
                                    <td class="meal-label">Breakfast</td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="breakfast-day1">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="breakfast-day2">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="breakfast-day3">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="breakfast-day4">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="breakfast-day5">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="breakfast-day6">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="breakfast-day7">Loading...</div>
                                    </td>
                                </tr>
                                <tr class="meal-row">
                                    <td class="meal-label">Lunch</td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="lunch-day1">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="lunch-day2">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="lunch-day3">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="lunch-day4">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="lunch-day5">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="lunch-day6">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="lunch-day7">Loading...</div>
                                    </td>
                                </tr>
                                <tr class="meal-row">
                                    <td class="meal-label">PM Snack</td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="snack-day1">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="snack-day2">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="snack-day3">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="snack-day4">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="snack-day5">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="snack-day6">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="snack-day7">Loading...</div>
                                    </td>
                                </tr>
                                <tr class="meal-row">
                                    <td class="meal-label">Dinner</td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="dinner-day1">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="dinner-day2">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="dinner-day3">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="dinner-day4">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="dinner-day5">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="dinner-day6">Loading...</div>
                                    </td>
                                    <td class="meal-cell">
                                        <div class="meal-content" id="dinner-day7">Loading...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden Raw Meal Plan Data for JavaScript parsing -->
                    <div id="rawMealPlanData" style="display: none;">
                        @if(session('meal_plan'))
                            {{ session('meal_plan') }}
                        @elseif(session('last_meal_plan'))
                            {{ session('last_meal_plan') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <div class="modal-footer-info">
                <span class="footer-badge">
                    <i class="fas fa-brain"></i>
                    AI Optimized
                </span>
                <span class="footer-badge">
                    <i class="fas fa-shield-alt"></i>
                    Nutritionally Balanced
                </span>
                <span class="footer-badge">
                    <i class="fas fa-heart"></i>
                    Age-Appropriate
                </span>
            </div>
            <button onclick="closeMealPlanModal()" class="ultra-button secondary">
                <div class="button-content">
                    <span class="button-text">Close</span>
                </div>
            </button>
        </div>
    </div>
</div>
@endif

@endsection
