<?php

require_once 'bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// Test email uniqueness validation
echo "Testing email uniqueness validation...\n\n";

// Test 1: Check existing active user email
$existingUser = User::whereNull('deleted_at')->first();
if ($existingUser) {
    echo "Test 1: Trying to register with existing active user email: {$existingUser->email}\n";
    
    $validator = Validator::make([
        'email' => $existingUser->email,
        'first_name' => 'Test',
        'last_name' => 'User'
    ], [
        'email' => 'required|string|email|max:255|unique:users,email,NULL,user_id,deleted_at,NULL',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255'
    ]);
    
    if ($validator->fails()) {
        echo "✅ PASS: Validation correctly rejected duplicate email\n";
        echo "Errors: " . implode(', ', $validator->errors()->get('email')) . "\n\n";
    } else {
        echo "❌ FAIL: Validation should have rejected duplicate email\n\n";
    }
}

// Test 2: Check soft-deleted user email (should be allowed)
$softDeletedUser = User::onlyTrashed()->first();
if ($softDeletedUser) {
    echo "Test 2: Trying to register with soft-deleted user email: {$softDeletedUser->email}\n";
    
    $validator = Validator::make([
        'email' => $softDeletedUser->email,
        'first_name' => 'Test',
        'last_name' => 'User'
    ], [
        'email' => 'required|string|email|max:255|unique:users,email,NULL,user_id,deleted_at,NULL',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255'
    ]);
    
    if ($validator->passes()) {
        echo "✅ PASS: Validation correctly allowed reuse of soft-deleted user email\n\n";
    } else {
        echo "❌ FAIL: Validation should have allowed reuse of soft-deleted user email\n";
        echo "Errors: " . implode(', ', $validator->errors()->get('email')) . "\n\n";
    }
} else {
    echo "Test 2: No soft-deleted users found, skipping this test\n\n";
}

// Test 3: Check unique email (should pass)
echo "Test 3: Trying to register with unique email: newemail@test.com\n";

$validator = Validator::make([
    'email' => 'newemail@test.com',
    'first_name' => 'Test',
    'last_name' => 'User'
], [
    'email' => 'required|string|email|max:255|unique:users,email,NULL,user_id,deleted_at,NULL',
    'first_name' => 'required|string|max:255',
    'last_name' => 'required|string|max:255'
]);

if ($validator->passes()) {
    echo "✅ PASS: Validation correctly allowed unique email\n\n";
} else {
    echo "❌ FAIL: Validation should have allowed unique email\n";
    echo "Errors: " . implode(', ', $validator->errors()->get('email')) . "\n\n";
}

echo "Email validation tests completed!\n";
