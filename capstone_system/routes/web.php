<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NutritionistController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\InventoryTransactionController;

// Authentication Routes
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('login.root.post'); // Handle POST to root
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes (Protected by auth and role middleware)
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/patients', [AdminController::class, 'patients'])->name('patients');
    Route::get('/assessments', [AdminController::class, 'assessments'])->name('assessments');
    Route::get('/inventory', [AdminController::class, 'inventory'])->name('inventory');
    
    // User CRUD routes
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}', [AdminController::class, 'getUser'])->name('users.get');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
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
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.logs');

});

// Nutritionist Routes (Protected by auth and role middleware)
Route::middleware(['auth', 'role:Nutritionist'])->prefix('nutritionist')->name('nutritionist.')->group(function () {
    Route::get('/dashboard', [NutritionistController::class, 'dashboard'])->name('dashboard');
    Route::get('/patients', [NutritionistController::class, 'patients'])->name('patients');
    
    // Patient CRUD routes
    Route::post('/patients', [NutritionistController::class, 'storePatient'])->name('patients.store');
    Route::get('/patients/{id}', [NutritionistController::class, 'getPatient'])->name('patients.get');
    Route::put('/patients/{id}', [NutritionistController::class, 'updatePatient'])->name('patients.update');
    Route::delete('/patients/{id}', [NutritionistController::class, 'deletePatient'])->name('patients.delete');
    
    Route::get('/assessments', [NutritionistController::class, 'assessments'])->name('assessments');
    Route::get('/profile', [NutritionistController::class, 'profile'])->name('profile');
});

// Parent Routes (Protected by auth and role middleware)
Route::middleware(['auth', 'role:Parent'])->prefix('parent')->name('parent.')->group(function () {
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
