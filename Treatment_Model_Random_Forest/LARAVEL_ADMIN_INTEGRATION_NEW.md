# Laravel Admin Integration Guide

## 🚀 Quick Setup Steps

### 1. **Laravel Configuration**

Add to your `.env` file:
```env
MALNUTRITION_API_URL=http://127.0.0.1:8001
MALNUTRITION_API_KEY=malnutrition-api-key-2025
```

Add to `config/services.php`:
```php
'malnutrition_api' => [
    'base_url' => env('MALNUTRITION_API_URL', 'http://127.0.0.1:8001'),
    'api_key' => env('MALNUTRITION_API_KEY'),
],
```

### 2. **Create Service Class**

Create `app/Services/MalnutritionService.php`:
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MalnutritionService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.malnutrition_api.base_url');
        $this->apiKey = config('services.malnutrition_api.api_key');
    }

    private function getToken()
    {
        $token = Cache::get('malnutrition_token');
        if (!$token) {
            $response = Http::post($this->baseUrl . '/auth/token', [
                'api_key' => $this->apiKey
            ]);
            
            if ($response->successful()) {
                $token = $response->json()['access_token'];
                Cache::put('malnutrition_token', $token, 25 * 60); // 25 minutes
            }
        }
        return $token;
    }

    public function assessChild($childData, $socioData = [])
    {
        $token = $this->getToken();
        
        $response = Http::withToken($token)
            ->post($this->baseUrl . '/assess/complete', [
                'child_data' => $childData,
                'socioeconomic_data' => $socioData
            ]);

        return $response->json();
    }

    public function assessMalnutritionOnly($childData)
    {
        $token = $this->getToken();
        
        $response = Http::withToken($token)
            ->post($this->baseUrl . '/assess/malnutrition-only', $childData);

        return $response->json();
    }

    public function getWhoStandards($gender, $indicator)
    {
        $token = $this->getToken();
        
        $response = Http::withToken($token)
            ->get($this->baseUrl . "/reference/who-standards/{$gender}/{$indicator}");

        return $response->json();
    }

    public function getTreatmentProtocols()
    {
        $token = $this->getToken();
        
        $response = Http::withToken($token)
            ->get($this->baseUrl . '/reference/treatment-protocols');

        return $response->json();
    }
}
```

### 3. **Create Controller**

Create `app/Http/Controllers/MalnutritionController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Services\MalnutritionService;
use Illuminate\Http\Request;

class MalnutritionController extends Controller
{
    private $malnutritionService;

    public function __construct(MalnutritionService $malnutritionService)
    {
        $this->malnutritionService = $malnutritionService;
    }

    // Assessment form
    public function index()
    {
        return view('malnutrition.assessment');
    }

    // Process assessment
    public function assess(Request $request)
    {
        $request->validate([
            'age_months' => 'required|integer|min:0|max:60',
            'weight_kg' => 'required|numeric|min:1|max:50',
            'height_cm' => 'required|numeric|min:30|max:150',
            'gender' => 'required|in:male,female',
        ]);

        $childData = [
            'age_months' => $request->age_months,
            'weight_kg' => $request->weight_kg,
            'height_cm' => $request->height_cm,
            'gender' => $request->gender,
            'muac_cm' => $request->muac_cm,
            'has_edema' => $request->has('has_edema'),
            'appetite' => $request->appetite ?? 'good',
            'diarrhea_days' => $request->diarrhea_days ?? 0,
            'fever_days' => $request->fever_days ?? 0,
            'vomiting' => $request->has('vomiting'),
        ];

        $socioData = [
            'is_4ps_beneficiary' => $request->has('is_4ps_beneficiary'),
            'household_size' => $request->household_size ?? 4,
            'has_electricity' => $request->has('has_electricity'),
            'has_clean_water' => $request->has('has_clean_water'),
            'mother_education' => $request->mother_education ?? 'primary',
            'father_present' => $request->has('father_present'),
        ];

        try {
            $result = $this->malnutritionService->assessChild($childData, $socioData);
            
            return view('malnutrition.results', compact('result', 'childData'));
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Assessment failed: ' . $e->getMessage()]);
        }
    }

    // Quick assessment (malnutrition only)
    public function quickAssess(Request $request)
    {
        $childData = [
            'age_months' => $request->age_months,
            'weight_kg' => $request->weight_kg,
            'height_cm' => $request->height_cm,
            'gender' => $request->gender,
            'muac_cm' => $request->muac_cm,
            'has_edema' => $request->has('has_edema'),
        ];

        try {
            $result = $this->malnutritionService->assessMalnutritionOnly($childData);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

### 4. **Create Admin Controller**

Create `app/Http/Controllers/Admin/MalnutritionAdminController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MalnutritionService;
use Illuminate\Http\Request;

class MalnutritionAdminController extends Controller
{
    private $malnutritionService;

    public function __construct(MalnutritionService $malnutritionService)
    {
        $this->middleware('auth');
        $this->middleware('can:manage-malnutrition'); // Optional permission check
        $this->malnutritionService = $malnutritionService;
    }

    // Admin dashboard
    public function dashboard()
    {
        try {
            $protocols = $this->malnutritionService->getTreatmentProtocols();
            return view('admin.malnutrition.dashboard', compact('protocols'));
        } catch (\Exception $e) {
            return view('admin.malnutrition.dashboard')->withErrors(['error' => $e->getMessage()]);
        }
    }

    // WHO Standards management
    public function whoStandards()
    {
        try {
            // Get sample data for both genders and indicators
            $maleWfa = $this->malnutritionService->getWhoStandards('male', 'wfa');
            $femaleWfa = $this->malnutritionService->getWhoStandards('female', 'wfa');
            $maleLhfa = $this->malnutritionService->getWhoStandards('male', 'lhfa');
            $femaleLhfa = $this->malnutritionService->getWhoStandards('female', 'lhfa');

            return view('admin.malnutrition.who-standards', compact(
                'maleWfa', 'femaleWfa', 'maleLhfa', 'femaleLhfa'
            ));
        } catch (\Exception $e) {
            return view('admin.malnutrition.who-standards')->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Treatment protocols management
    public function treatmentProtocols()
    {
        try {
            $protocols = $this->malnutritionService->getTreatmentProtocols();
            return view('admin.malnutrition.treatment-protocols', compact('protocols'));
        } catch (\Exception $e) {
            return view('admin.malnutrition.treatment-protocols')->withErrors(['error' => $e->getMessage()]);
        }
    }

    // API health check
    public function apiStatus()
    {
        try {
            $response = Http::get(config('services.malnutrition_api.base_url') . '/health');
            $status = $response->json();
            
            return view('admin.malnutrition.api-status', compact('status'));
        } catch (\Exception $e) {
            $status = ['status' => 'error', 'message' => $e->getMessage()];
            return view('admin.malnutrition.api-status', compact('status'));
        }
    }
}
```

### 5. **Routes**

Add to `routes/web.php`:
```php
use App\Http\Controllers\MalnutritionController;
use App\Http\Controllers\Admin\MalnutritionAdminController;

// Public assessment routes
Route::prefix('malnutrition')->name('malnutrition.')->group(function () {
    Route::get('/', [MalnutritionController::class, 'index'])->name('index');
    Route::post('/assess', [MalnutritionController::class, 'assess'])->name('assess');
});

// API routes for AJAX calls
Route::prefix('api/malnutrition')->name('api.malnutrition.')->group(function () {
    Route::post('/quick-assess', [MalnutritionController::class, 'quickAssess'])->name('quick-assess');
});

// Admin routes
Route::prefix('admin/malnutrition')->name('admin.malnutrition.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [MalnutritionAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/who-standards', [MalnutritionAdminController::class, 'whoStandards'])->name('who-standards');
    Route::get('/treatment-protocols', [MalnutritionAdminController::class, 'treatmentProtocols'])->name('treatment-protocols');
    Route::get('/api-status', [MalnutritionAdminController::class, 'apiStatus'])->name('api-status');
});
```

### 6. **Sample Blade Template**

Create `resources/views/malnutrition/assessment.blade.php`:
```html
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Child Malnutrition Assessment</div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('malnutrition.assess') }}">
                        @csrf
                        
                        <h5>Child Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="age_months" class="form-label">Age (months) *</label>
                                <input type="number" class="form-control" id="age_months" name="age_months" 
                                       value="{{ old('age_months') }}" min="0" max="60" required>
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender *</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="weight_kg" class="form-label">Weight (kg) *</label>
                                <input type="number" step="0.1" class="form-control" id="weight_kg" name="weight_kg" 
                                       value="{{ old('weight_kg') }}" min="1" max="50" required>
                            </div>
                            <div class="col-md-6">
                                <label for="height_cm" class="form-label">Height (cm) *</label>
                                <input type="number" step="0.1" class="form-control" id="height_cm" name="height_cm" 
                                       value="{{ old('height_cm') }}" min="30" max="150" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="muac_cm" class="form-label">MUAC (cm)</label>
                                <input type="number" step="0.1" class="form-control" id="muac_cm" name="muac_cm" 
                                       value="{{ old('muac_cm') }}" min="5" max="30">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="has_edema" name="has_edema" 
                                           {{ old('has_edema') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_edema">
                                        Has Edema (swelling)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Assess Child</button>
                        <button type="button" class="btn btn-secondary" onclick="quickAssess()">Quick Assessment</button>
                    </form>

                    <div id="result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function quickAssess() {
    const data = {
        age_months: $('#age_months').val(),
        weight_kg: $('#weight_kg').val(),
        height_cm: $('#height_cm').val(),
        gender: $('#gender').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.post('/api/malnutrition/quick-assess', data)
        .done(function(response) {
            if (response.success) {
                $('#result').html('<div class="alert alert-info">Diagnosis: ' + 
                    response.data.primary_diagnosis + '</div>');
            }
        })
        .fail(function() {
            $('#result').html('<div class="alert alert-danger">Assessment failed</div>');
        });
}
</script>
@endsection
```

### 7. **Admin Dashboard Template**

Create `resources/views/admin/malnutrition/dashboard.blade.php`:
```html
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1>Malnutrition System Admin</h1>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>API Status</h5>
                    <a href="{{ route('admin.malnutrition.api-status') }}" class="btn btn-light btn-sm">Check Status</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>WHO Standards</h5>
                    <a href="{{ route('admin.malnutrition.who-standards') }}" class="btn btn-light btn-sm">View Data</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Treatment Protocols</h5>
                    <a href="{{ route('admin.malnutrition.treatment-protocols') }}" class="btn btn-light btn-sm">View Protocols</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Assessment</h5>
                    <a href="{{ route('malnutrition.index') }}" class="btn btn-light btn-sm">New Assessment</a>
                </div>
            </div>
        </div>
    </div>

    @if(isset($protocols))
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Available Treatment Protocols</div>
                <div class="card-body">
                    <pre>{{ json_encode($protocols, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
```

### 8. **Authentication Setup**

Add to `app/Providers/AuthServiceProvider.php`:
```php
use Illuminate\Support\Facades\Gate;

public function boot()
{
    Gate::define('manage-malnutrition', function ($user) {
        return $user->role === 'admin' || $user->hasPermission('manage_malnutrition');
    });
}
```

## 🎯 **What You Need to Do:**

1. **Create Service Class** - Copy the MalnutritionService code
2. **Create Controllers** - Regular + Admin controllers
3. **Add Routes** - Copy the routes to your web.php
4. **Create Views** - Assessment form + Admin dashboard
5. **Setup Auth** - Protect admin routes with middleware
6. **Configure API** - Add URL and key to .env

## 📡 **Available API Endpoints:**

- `POST /assess/complete` - Full assessment with treatment plan
- `POST /assess/malnutrition-only` - Quick malnutrition check  
- `GET /reference/who-standards/{gender}/{indicator}` - WHO data
- `GET /reference/treatment-protocols` - Available protocols
- `GET /health` - API health check

## 🔧 **Admin Functions:**

- **Dashboard** - Overview of system status
- **WHO Standards** - View WHO reference data
- **Treatment Protocols** - View available protocols  
- **API Status** - Check if API is running
- **Assessment Interface** - Perform assessments

This gives you a complete Laravel integration with admin capabilities! 🚀
