<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NutritionistController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\InventoryTransactionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Authentication Routes
// Forgot Password
Route::get('/forgot-password', function() {
    return view('auth.forgot-password');
})->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

// Contact Admin
Route::get('/contact-admin', function() {
    return view('auth.contact-admin');
})->name('contact.admin');
Route::post('/contact-admin', [AuthController::class, 'sendContactAdmin'])->name('contact.admin.send');
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('login.root.post'); // Handle POST to root
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

// Verification Gate - Paywall style for logged-in unverified users
Route::get('/verify-to-continue', [AuthController::class, 'showVerificationGate'])->middleware('auth')->name('verification.gate');

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Public resend verification email (for logged-out users)
Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1')->name('verification.resend');

// Logout after resend
Route::post('/resend-and-logout', [AuthController::class, 'resendAndLogout'])->middleware('auth')->name('resend.logout');

// Development-only email verification bypass
if (app()->environment(['local', 'development'])) {
    Route::get('/dev/verify-email/{email}', [AuthController::class, 'devVerifyEmail'])->name('dev.verify');
    Route::get('/dev/verification-panel', [AuthController::class, 'showDevVerificationPanel'])->name('dev.panel');
}

Route::get('/email/verified', function () {
    return view('auth.verification-success');
})->name('verification.success');

// CSRF Token Refresh Route
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// CSRF Test Page (for debugging)
Route::get('/test-csrf', function () {
    return view('test-csrf');
})->name('test.csrf');

// Test route for debugging registration
Route::post('/test-registration', function (Request $request) {
    return response()->json([
        'message' => 'Form submission successful!',
        'data' => $request->except(['password', 'password_confirmation']),
        'csrf_token_received' => $request->header('X-CSRF-TOKEN') ?? 'not_provided'
    ]);
})->name('test.registration');

// Registration Routes
Route::get('/register', [AuthController::class, 'showRegistrationOptions'])->name('register');
Route::get('/register/parent', [AuthController::class, 'showParentRegistration'])->name('register.parent');
Route::post('/register/parent', [AuthController::class, 'registerParent'])->name('register.parent.post');
Route::get('/register/success', function () {
    return view('auth.registration-success');
})->name('registration.success');
Route::get('/apply/nutritionist', [AuthController::class, 'showNutritionistApplication'])->name('apply.nutritionist');
Route::post('/apply/nutritionist', [AuthController::class, 'applyNutritionist'])->name('apply.nutritionist.post');

// Legal Pages
Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');
Route::get('/privacy', function () {
    return view('legal.privacy');
})->name('privacy');

// Admin Routes (Protected by auth, verified email, and role middleware)
Route::middleware(['auth', 'verified', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/map-data', [AdminController::class, 'getMapData'])->name('dashboard.map-data');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/patients', [AdminController::class, 'patients'])->name('patients');
    Route::get('/assessments', [AdminController::class, 'assessments'])->name('assessments');
    Route::get('/inventory', [AdminController::class, 'inventory'])->name('inventory');
    
    // User CRUD routes
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}', [AdminController::class, 'getUser'])->name('users.get');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{id}/restore', [AdminController::class, 'restoreUser'])->name('users.restore');
    Route::post('/users/{id}/activate', [AdminController::class, 'activateUser'])->name('users.activate');
    Route::post('/users/{id}/deactivate', [AdminController::class, 'deactivateUser'])->name('users.deactivate');
    Route::get('/users-with-trashed', [AdminController::class, 'getUsersWithTrashed'])->name('users.with-trashed');
    
    // Nutritionist application routes
    Route::get('/nutritionist-applications', [AdminController::class, 'getPendingNutritionistApplications'])->name('nutritionist.applications');
    Route::post('/nutritionist-applications/{id}/approve', [AdminController::class, 'approveNutritionist'])->name('nutritionist.approve');
    Route::post('/nutritionist-applications/{id}/reject', [AdminController::class, 'rejectNutritionist'])->name('nutritionist.reject');
    
    // Patient CRUD routes
    Route::post('/patients', [AdminController::class, 'storePatient'])->name('patients.store');
    Route::get('/patients/{id}', [AdminController::class, 'getPatient'])->name('patients.get');
    Route::put('/patients/{id}', [AdminController::class, 'updatePatient'])->name('patients.update');
    Route::delete('/patients/{id}', [AdminController::class, 'deletePatient'])->name('patients.delete');
    
    // Inventory CRUD routes
    Route::post('/inventory', [AdminController::class, 'storeInventoryItem'])->name('inventory.store');
    Route::get('/inventory/{id}', [AdminController::class, 'getInventoryItem'])->name('inventory.get');
    Route::put('/inventory/{id}', [AdminController::class, 'updateInventoryItem'])->name('inventory.update');
    Route::delete('/inventory/{id}', [AdminController::class, 'deleteInventoryItem'])->name('inventory.delete');
    
    // Stock In/Out routes
    Route::post('/inventory/{id}/stock-in', [AdminController::class, 'stockIn'])->name('inventory.stock.in');
    Route::post('/inventory/{id}/stock-out', [AdminController::class, 'stockOut'])->name('inventory.stock.out');
    
    // System Management routes
    Route::get('/system-management', [AdminController::class, 'systemManagement'])->name('system.management');
    
    // API Management routes
    Route::get('/api-management', [AdminController::class, 'apiManagement'])->name('api.management');
    Route::get('/who-standards', [AdminController::class, 'whoStandards'])->name('who.standards');
    Route::get('/treatment-protocols', [AdminController::class, 'treatmentProtocols'])->name('treatment.protocols');
    Route::get('/api-status', [AdminController::class, 'apiStatus'])->name('api.status');
    
    // Category CRUD routes
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{id}', [AdminController::class, 'getCategory'])->name('categories.get');
    Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])->name('categories.delete');
    
    // Barangay CRUD routes
    Route::post('/barangays', [AdminController::class, 'storeBarangay'])->name('barangays.store');
    Route::get('/barangays/{id}', [AdminController::class, 'getBarangay'])->name('barangays.get');
    Route::put('/barangays/{id}', [AdminController::class, 'updateBarangay'])->name('barangays.update');
    Route::delete('/barangays/{id}', [AdminController::class, 'deleteBarangay'])->name('barangays.delete');
    
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/user-activity', [AdminController::class, 'generateUserActivityReport'])->name('reports.user-activity');
    Route::get('/reports/inventory', [AdminController::class, 'generateInventoryReport'])->name('reports.inventory');
    Route::get('/reports/assessment-trends', [AdminController::class, 'generateAssessmentTrendsReport'])->name('reports.assessment-trends');
    Route::get('/reports/low-stock', [AdminController::class, 'generateLowStockReport'])->name('reports.low-stock');
    
    // PDF Download routes
    Route::post('/reports/user-activity/download', [AdminController::class, 'downloadUserActivityReport'])->name('reports.user-activity.download');
    Route::post('/reports/inventory/download', [AdminController::class, 'downloadInventoryReport'])->name('reports.inventory.download');
    Route::post('/reports/assessment-trends/download', [AdminController::class, 'downloadAssessmentTrendsReport'])->name('reports.assessment-trends.download');
    Route::post('/reports/low-stock/download', [AdminController::class, 'downloadLowStockReport'])->name('reports.low-stock.download');
    
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.logs');

});

// Nutritionist Routes (Protected by auth, verified email, and role middleware)
Route::middleware(['auth', 'verified', 'role:Nutritionist'])->prefix('nutritionist')->name('nutritionist.')->group(function () {
    Route::get('/dashboard', [NutritionistController::class, 'dashboard'])->name('dashboard');
    Route::get('/patients', [NutritionistController::class, 'patients'])->name('patients');
    
    // Patient CRUD routes
    Route::post('/patients', [NutritionistController::class, 'storePatient'])->name('patients.store');
    Route::get('/patients/{id}', [NutritionistController::class, 'getPatient'])->name('patients.get');
    Route::put('/patients/{id}', [NutritionistController::class, 'updatePatient'])->name('patients.update');
    Route::delete('/patients/{id}', [NutritionistController::class, 'deletePatient'])->name('patients.delete');
    
    Route::get('/assessments', [NutritionistController::class, 'assessments'])->name('assessments');
    Route::post('/assessments', [NutritionistController::class, 'assessments'])->name('assessments.ajax');
    Route::get('/profile', [NutritionistController::class, 'profile'])->name('profile');
    
    // Profile update routes
    Route::put('/profile/personal', [NutritionistController::class, 'updatePersonalInfo'])->name('profile.update.personal');
    Route::put('/profile/professional', [NutritionistController::class, 'updateProfessionalInfo'])->name('profile.update.professional');
    
    // Assessment routes
    Route::get('/patients/{patientId}/assess', [NutritionistController::class, 'showAssessmentForm'])->name('patients.assess');
    Route::post('/assessment/perform', [NutritionistController::class, 'performAssessment'])->name('assessment.perform');
    Route::post('/assessment/quick', [NutritionistController::class, 'quickAssessment'])->name('assessment.quick');
    Route::get('/assessment/{assessmentId}/pdf', [NutritionistController::class, 'downloadAssessmentPDF'])->name('assessment.pdf');
    
    // Meal Plan and Nutrition routes
    Route::get('/meal-plans', [NutritionistController::class, 'mealPlans'])->name('meal-plans');
    Route::post('/nutrition/analysis', [NutritionistController::class, 'generateNutritionAnalysis'])->name('nutrition.analysis');
    Route::post('/nutrition/meal-plan', [NutritionistController::class, 'generateMealPlan'])->name('nutrition.meal-plan');
    Route::post('/nutrition/assessment', [NutritionistController::class, 'generatePatientAssessment'])->name('nutrition.assessment');
    Route::get('/nutrition/foods', [NutritionistController::class, 'getFoodsData'])->name('nutrition.foods');
    Route::post('/nutrition/patient-meal-plans', [NutritionistController::class, 'getPatientMealPlans'])->name('nutrition.patient-meal-plans');
    Route::get('/nutrition/knowledge-base', [NutritionistController::class, 'getKnowledgeBase'])->name('nutrition.knowledge-base');
    Route::post('/nutrition/meal-plan-detail', [NutritionistController::class, 'getMealPlanDetail'])->name('nutrition.meal-plan-detail');
    Route::get('/nutrition/test-api', [NutritionistController::class, 'testNutritionAPI'])->name('nutrition.test-api');
});

// Parent Routes (Protected by auth, verified email, and role middleware)
Route::middleware(['auth', 'verified', 'role:Parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
    Route::get('/children', [ParentController::class, 'children'])->name('children');
    Route::get('/assessments', [ParentController::class, 'assessments'])->name('assessments');
    Route::get('/profile', [ParentController::class, 'profile'])->name('profile');
});

// Redirect authenticated users to their appropriate dashboard
Route::middleware('auth')->get('/dashboard', function () {
    $user = Auth::user();
    $roleName = $user->role->role_name ?? null;
    
    switch ($roleName) {
        case 'Admin':
            return redirect()->route('admin.dashboard');
        case 'Nutritionist':
            return redirect()->route('nutritionist.dashboard');
        case 'Parent':
            return redirect()->route('parent.dashboard');
        default:
            Auth::logout();
            return redirect()->route('login')->withErrors(['error' => 'Invalid user role.']);
    }
})->name('dashboard');
