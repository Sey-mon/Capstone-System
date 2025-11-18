@extends('layouts.dashboard')

@section('title', 'My Meal Plans')

@section('page-title', 'My Meal Plans')
@section('page-subtitle', 'View all generated meal plans for your children')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{ asset('css/parent/view-meal-plans.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/parent/view-meal-plans.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="view-meal-plans-wrapper">
    <!-- Enhanced Header Section -->
    <div class="premium-header-section">
        <div class="header-background-gradient"></div>
        <div class="header-content-wrapper">
            <div class="header-left">
                <div class="page-icon-premium">
                    <div class="icon-glow"></div>
                    <i class="fas fa-book-medical"></i>
                    <div class="icon-pulse"></div>
                </div>
                <div class="page-info">
                    <h1 class="page-main-title">My Meal Plans Collection</h1>
                    <p class="page-description">Browse, manage, and track all nutritional meal plans for your children</p>
                </div>
            </div>
            <div class="header-right">
                <div class="stats-card-group">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $mealPlans->total() }}</div>
                            <div class="stat-label">Total Plans</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-child"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $children->count() }}</div>
                            <div class="stat-label">Children</div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('parent.meal-plans') }}" class="premium-btn generate-btn">
                    <div class="btn-shine"></div>
                    <i class="fas fa-magic"></i>
                    <span>Generate New Plan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid meal-plans-container">
        <div class="row">
            <div class="col-12">
                
                @if(session('success'))
                    <div class="premium-alert success-alert">
                        <div class="alert-icon-wrapper">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Success!</h4>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if($mealPlans->count() > 0)
                    <!-- Enhanced Filter & Control Section -->
                    <div class="control-panel">
                        <div class="control-panel-inner">
                            <div class="filter-section">
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <i class="fas fa-filter"></i> Filter by Child
                                    </label>
                                    <div class="select-wrapper">
                                        <select id="childFilter" class="premium-select">
                                            <option value="">All Children</option>
                                            @foreach($children as $child)
                                                <option value="{{ $child->patient_id }}">
                                                    {{ $child->first_name }} {{ $child->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <i class="fas fa-sort"></i> Sort by
                                    </label>
                                    <div class="select-wrapper">
                                        <select id="sortFilter" class="premium-select">
                                            <option value="newest">Newest First</option>
                                            <option value="oldest">Oldest First</option>
                                            <option value="child">Child Name</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="view-controls">
                                <label class="filter-label">
                                    <i class="fas fa-eye"></i> View
                                </label>
                                <div class="view-toggle-group">
                                    <button class="view-toggle-btn active" data-view="grid" title="Grid View">
                                        <i class="fas fa-th-large"></i>
                                    </button>
                                    <button class="view-toggle-btn" data-view="list" title="List View">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Premium Meal Plans Grid -->
                    <div class="meal-plans-grid-premium" id="mealPlansGrid">
                        @foreach($mealPlans as $plan)
                            <div class="meal-plan-card-premium" data-child-id="{{ $plan->patient_id }}">
                                <div class="card-glow-effect"></div>
                                <div class="card-header-premium">
                                    <div class="child-avatar">
                                        <i class="fas fa-child"></i>
                                    </div>
                                    <div class="child-info">
                                        <h3 class="child-name">{{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</h3>
                                        <div class="plan-meta">
                                            <span class="meta-item">
                                                <i class="fas fa-calendar-alt"></i>
                                                {{ $plan->generated_at->format('M d, Y') }}
                                            </span>
                                            <span class="meta-item">
                                                <i class="fas fa-clock"></i>
                                                {{ $plan->generated_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-actions-menu">
                                        <button class="action-menu-btn" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end premium-dropdown">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="viewMealPlan({{ $plan->plan_id }}); return false;">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="printMealPlan({{ $plan->plan_id }}); return false;">
                                                    <i class="fas fa-print"></i> Print
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="downloadMealPlan({{ $plan->plan_id }}); return false;">
                                                    <i class="fas fa-download"></i> Download PDF
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteMealPlan({{ $plan->plan_id }}); return false;">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card-body-premium">
                                    @if($plan->notes)
                                        <div class="notes-section-compact">
                                            <div class="notes-header">
                                                <i class="fas fa-sticky-note"></i>
                                                <span>Notes</span>
                                            </div>
                                            <p class="notes-text">{{ Str::limit($plan->notes, 100) }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="plan-preview-section">
                                        <div class="preview-header">
                                            <i class="fas fa-utensils"></i>
                                            <span>Meal Plan Preview</span>
                                        </div>
                                        <div class="plan-content-preview">
                                            @php
                                                // Extract Day 1 meals from plan_details
                                                $extractDay1Meals = function($planDetails) {
                                                    // Look for Day 1 section
                                                    if (preg_match('/\*\*Day 1\*\*:(.*?)(?=\*\*Day 2\*\*|$)/s', $planDetails, $day1Match)) {
                                                        $day1Content = $day1Match[1];
                                                        
                                                        // Extract meals from Day 1
                                                        $meals = [];
                                                        if (preg_match('/\*\*Breakfast \(Almusal\)\*\*:(.*?)(?=\*\*Lunch|$)/s', $day1Content, $breakfast)) {
                                                            $meals[] = 'Breakfast: ' . trim(strip_tags($breakfast[1]));
                                                        }
                                                        if (preg_match('/\*\*Lunch \(Tanghalian\)\*\*:(.*?)(?=\*\*Snack|$)/s', $day1Content, $lunch)) {
                                                            $meals[] = 'Lunch: ' . trim(strip_tags($lunch[1]));
                                                        }
                                                        if (preg_match('/\*\*Snack \(Meryenda\)\*\*:(.*?)(?=\*\*Dinner|$)/s', $day1Content, $snack)) {
                                                            $meals[] = 'Snack: ' . trim(strip_tags($snack[1]));
                                                        }
                                                        if (preg_match('/\*\*Dinner \(Hapunan\)\*\*:(.*?)(?=\*\*|$)/s', $day1Content, $dinner)) {
                                                            $meals[] = 'Dinner: ' . trim(strip_tags($dinner[1]));
                                                        }
                                                        
                                                        return !empty($meals) ? 'Day 1 - ' . implode(' | ', array_slice($meals, 0, 2)) : null;
                                                    }
                                                    return null;
                                                };
                                                
                                                $day1Preview = $extractDay1Meals($plan->plan_details);
                                            @endphp
                                            
                                            {{ $day1Preview ?? Str::limit(strip_tags($plan->plan_details), 180) }}
                                        </div>
                                    </div>

                                    <div class="plan-tags">
                                        <span class="tag tag-success">
                                            <i class="fas fa-check-circle"></i> AI Generated
                                        </span>
                                        <span class="tag tag-info">
                                            <i class="fas fa-leaf"></i> Nutritious
                                        </span>
                                    </div>
                                </div>

                                <div class="card-footer-premium">
                                    <button class="btn-premium primary-btn" onclick="viewMealPlan({{ $plan->plan_id }})">
                                        <i class="fas fa-arrow-right"></i>
                                        <span>View Full Plan</span>
                                    </button>
                                    <div class="quick-actions">
                                        <button class="icon-btn" onclick="printMealPlan({{ $plan->plan_id }})" title="Print">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <button class="icon-btn" onclick="downloadMealPlan({{ $plan->plan_id }})" title="Download">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($mealPlans->hasPages())
                        <div class="mt-4">
                            {{ $mealPlans->links() }}
                        </div>
                    @endif
                @else
                    <!-- Premium Empty State -->
                    <div class="empty-state-premium">
                        <div class="empty-state-background">
                            <div class="floating-icon icon-1"><i class="fas fa-utensils"></i></div>
                            <div class="floating-icon icon-2"><i class="fas fa-apple-alt"></i></div>
                            <div class="floating-icon icon-3"><i class="fas fa-carrot"></i></div>
                            <div class="floating-icon icon-4"><i class="fas fa-drumstick-bite"></i></div>
                        </div>
                        <div class="empty-state-content">
                            <div class="empty-icon-wrapper">
                                <div class="icon-circle">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="icon-pulse"></div>
                            </div>
                            <h2 class="empty-title">No Meal Plans Yet</h2>
                            <p class="empty-description">
                                You haven't generated any meal plans yet. Start creating personalized, 
                                AI-powered nutrition plans tailored for your children's needs!
                            </p>
                            <a href="{{ route('parent.meal-plans') }}" class="premium-btn large-btn">
                                <div class="btn-shine"></div>
                                <i class="fas fa-magic"></i>
                                <span>Generate Your First Meal Plan</span>
                            </a>
                            <div class="empty-features">
                                <div class="feature-item">
                                    <i class="fas fa-brain"></i>
                                    <span>AI-Powered</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-heart"></i>
                                    <span>Personalized</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-leaf"></i>
                                    <span>Nutritious</span>
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
