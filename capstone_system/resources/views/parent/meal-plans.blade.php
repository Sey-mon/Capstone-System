@extends('layouts.dashboard')

@section('title', 'Meal Plans')

@section('page-title', 'Meal Plans')
@section('page-subtitle', 'Generate personalized meal plans for your children')

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-utensils mr-2"></i>
                        Generate Meal Plan for Your Child
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($children->count() > 0)
                        <form method="POST" action="{{ route('parent.meal-plans.generate') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="patient_id">Select Child</label>
                                        <select name="patient_id" id="patient_id" class="form-control" required>
                                            <option value="">Choose a child...</option>
                                            @foreach($children as $child)
                                                <option value="{{ $child->patient_id }}" {{ old('patient_id') == $child->patient_id ? 'selected' : '' }}>
                                                    {{ $child->first_name }} {{ $child->last_name }} 
                                                    ({{ $child->age_months }} months old)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('patient_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="available_foods">Available Foods</label>
                                        <input type="text" 
                                               name="available_foods" 
                                               id="available_foods" 
                                               class="form-control" 
                                               placeholder="e.g., rice, chicken, vegetables, milk..."
                                               value="{{ old('available_foods') }}"
                                               required>
                                        <small class="form-text text-muted">
                                            Enter foods you have available at home, separated by commas
                                        </small>
                                        @error('available_foods')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-magic mr-2"></i>
                                    Generate Meal Plan
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            You need to have at least one child registered to generate meal plans.
                            <a href="{{ route('parent.bind-child') }}" class="alert-link">Bind a child to your account</a> first.
                        </div>
                    @endif
                </div>
            </div>

            @if(session('meal_plan'))
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-clipboard-list mr-2"></i>
                            Generated Meal Plan for {{ session('child_name') }}
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="meal-plan-content">
                            <pre class="bg-light p-3 rounded">{{ session('meal_plan') }}</pre>
                        </div>
                        <div class="mt-3">
                            <button onclick="printMealPlan()" class="btn btn-secondary">
                                <i class="fas fa-print mr-2"></i>
                                Print Meal Plan
                            </button>
                            <button onclick="copyMealPlan()" class="btn btn-info">
                                <i class="fas fa-copy mr-2"></i>
                                Copy to Clipboard
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.meal-plan-content pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    border: 1px solid #ddd;
    max-height: 400px;
    overflow-y: auto;
}
</style>

<script>
function printMealPlan() {
    const mealPlanContent = document.querySelector('.meal-plan-content').innerHTML;
    const childName = "{{ session('child_name') }}";
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Meal Plan for ${childName}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { color: #333; }
                    pre { white-space: pre-wrap; word-wrap: break-word; }
                </style>
            </head>
            <body>
                <h1>Meal Plan for ${childName}</h1>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
                ${mealPlanContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function copyMealPlan() {
    const mealPlanText = document.querySelector('.meal-plan-content pre').textContent;
    navigator.clipboard.writeText(mealPlanText).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-info');
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.add('btn-info');
            btn.classList.remove('btn-success');
        }, 2000);
    });
}
</script>
@endsection
