@extends('layouts.dashboard')

@section('title', 'Meal Plans')

@section('page-title', 'Meal Plans')
@section('page-subtitle', 'Generate and manage AI-powered meal plans')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/meal-plans.css') }}?v={{ filemtime(public_path('css/nutritionist/meal-plans.css')) }}">
@endpush

@section('content')
    <div class="meal-plans-container">
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-header">
                <div class="filter-header-left">
                    <i class="fas fa-filter"></i>
                    <span>Filter Meal Plans</span>
                </div>
                <button type="button" class="btn-clear-filters" id="reset-program-filters">
                    <i class="fas fa-times"></i>
                    Clear All
                </button>
            </div>
            <div class="filter-grid">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Search</label>
                    <input type="text" id="program-search" class="filter-input" placeholder="Search by barangay...">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-wallet"></i> Budget Level</label>
                    <select id="program-budget-filter" class="filter-select">
                        <option value="">All Budget Levels</option>
                        <option value="low">Low Budget</option>
                        <option value="moderate">Moderate Budget</option>
                        <option value="high">High Budget</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-baby"></i> Age Group</label>
                    <select id="program-age-filter" class="filter-select">
                        <option value="">All Age Groups</option>
                        <option value="all">All Ages (6mo-5y)</option>
                        <option value="6-12months">Infants (6-12mo)</option>
                        <option value="12-24months">Toddlers (12-24mo)</option>
                        <option value="24-60months">Preschoolers (24-60mo)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> Date From</label>
                    <input type="date" id="date-from-filter" class="filter-input">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> Date To</label>
                    <input type="date" id="date-to-filter" class="filter-input">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-list"></i> Per Page</label>
                    <select id="per-page-filter" class="filter-select">
                        <option value="12">12</option>
                        <option value="24">24</option>
                        <option value="48">48</option>
                        <option value="all">All</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Feeding Program Section -->
        <div class="feeding-program-section">
            <div class="section-header">
                <div class="section-title-group">
                    <i class="fas fa-utensils"></i>
                    <div>
                        <h2 class="section-title">Feeding Program Meal Plans</h2>
                        <p class="section-subtitle">Create and manage AI-powered meal plans for Filipino children</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button type="button" class="btn-count">
                        <i class="fas fa-file-alt"></i>
                        {{ $feedingProgramPlans->count() }} {{ $feedingProgramPlans->count() === 1 ? 'plan' : 'plans' }}
                    </button>
                    <button type="button" class="btn-primary" id="open-feeding-program-btn">
                        <i class="fas fa-plus"></i>
                        Create New Plan
                    </button>
                </div>
            </div>

            <!-- Meal Plans Grid -->
            <div class="plans-grid" id="program-plans-list" @if($feedingProgramPlans->count() === 0) style="display: none;" @endif>
                @foreach($feedingProgramPlans as $plan)
                <div class="plan-card" 
                     data-plan-id="{{ $plan->program_plan_id }}"
                     data-budget="{{ $plan->budget_level }}"
                     data-age-group="{{ $plan->target_age_group }}"
                     data-barangay="{{ strtolower($plan->barangay ?? '') }}"
                     data-duration="{{ $plan->program_duration_days }}"
                     data-timestamp="{{ $plan->generated_at ? $plan->generated_at->timestamp : 0 }}">
                    <div class="plan-card-header">
                        <div class="plan-avatar">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="plan-info">
                            <h3 class="plan-title">
                                {{ ucfirst($plan->budget_level) }} Budget Plan
                            </h3>
                            <div class="plan-meta">
                                <span class="meta-item">
                                    <i class="fas fa-baby"></i>
                                    @if($plan->target_age_group === 'all')
                                        All Ages
                                    @elseif($plan->target_age_group === '6-12months')
                                        6-12mo
                                    @elseif($plan->target_age_group === '12-24months')
                                        12-24mo
                                    @else
                                        24-60mo
                                    @endif
                                </span>
                                @if($plan->total_children)
                                <span class="meta-item">
                                    <i class="fas fa-users"></i>
                                    {{ $plan->total_children }} children
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="plan-status">
                            <span class="status-badge status-completed">
                                <i class="fas fa-check-circle"></i>
                                COMPLETED
                            </span>
                        </div>
                    </div>

                    <div class="plan-card-body">
                        <div class="plan-detail-row">
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-calendar"></i>
                                    GENERATED DATE
                                </label>
                                <div class="detail-value">
                                    {{ $plan->generated_at ? $plan->generated_at->format('M d, Y') : 'N/A' }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-map-marker-alt"></i>
                                    LOCATION
                                </label>
                                <div class="detail-value budget-badge budget-{{ $plan->budget_level }}">
                                    {{ $plan->barangay ?? 'Not Specified' }}
                                </div>
                            </div>
                        </div>

                        <div class="plan-detail-row">
                            <div class="detail-item full-width">
                                <label>
                                    <i class="fas fa-hourglass-half"></i>
                                    PROGRAM DURATION
                                </label>
                                <div class="detail-value">{{ $plan->program_duration_days }} Days Program</div>
                            </div>
                        </div>

                        @if($plan->available_ingredients)
                        <div class="plan-detail-row">
                            <div class="detail-item full-width">
                                <label>
                                    <i class="fas fa-carrot"></i>
                                    AVAILABLE INGREDIENTS
                                </label>
                                <div class="detail-value ingredients-text">
                                    {{ Str::limit($plan->available_ingredients, 100) }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="plan-card-footer">
                        <button type="button" class="btn-action btn-view view-program-plan-btn" 
                                data-plan-id="{{ $plan->program_plan_id }}">
                            <i class="fas fa-eye"></i>
                            View Details
                        </button>
                        <button type="button" class="btn-action btn-delete delete-program-plan-btn" 
                                data-plan-id="{{ $plan->program_plan_id }}">
                            <i class="fas fa-trash"></i>
                            Delete
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="empty-state" @if($feedingProgramPlans->count() > 0) style="display: none;" @endif>
                <i class="fas fa-clipboard-list"></i>
                <h3>No Meal Plans Yet</h3>
                <p>Create your first feeding program meal plan to get started!</p>
                <button type="button" class="btn-primary" id="open-feeding-program-btn-empty">
                    <i class="fas fa-plus"></i>Create Program Plan
                </button>
            </div>
        </div>

        <!-- Results Section -->
        <div id="results-section" class="results-section" style="display: none;">
            <div class="section-header">
                <h2 class="section-title" id="results-title">Results</h2>
                <button type="button" class="btn-secondary" id="close-results-btn">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
            <div class="results-content" id="results-content">
                <!-- Results will be loaded here dynamically -->
            </div>
        </div>
    </div>

    <!-- Hidden data for JavaScript -->
    <script>
        // API Configuration
        window.API_CONFIG = {
            LLM_API_URL: '{{ config('services.llm.api_url', 'http://127.0.0.1:8002') }}'
        };
    </script>
    
    <script id="barangays-data" type="application/json">
        @json($barangays)
    </script>
@endsection

@push('scripts')
    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="{{ asset('js/nutritionist/meal-plans.js') }}?v={{ filemtime(public_path('js/nutritionist/meal-plans.js')) }}"></script>
@endpush
