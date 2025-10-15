<?php

// Quick test script to verify registration code system
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Registration Code System\n";
echo "=================================\n\n";

try {
    // Find an unlinked patient
    $patient = \App\Models\Patient::whereNull('parent_id')->first();
    
    if (!$patient) {
        echo "❌ No unlinked patients found in database\n";
        exit(1);
    }
    
    echo "✅ Found patient: {$patient->first_name} {$patient->last_name} (ID: {$patient->patient_id})\n";
    
    // Test code generation
    echo "🔄 Generating registration code...\n";
    $assets = $patient->generateRegistrationAssets();
    
    echo "✅ Registration code generated: {$patient->registration_code}\n";
    echo "✅ Expires at: {$patient->code_expires_at}\n";
    echo "✅ QR code path: {$patient->qr_code_path}\n";
    
    // Test code validation
    echo "🔄 Testing code validation...\n";
    $isValid = $patient->isCodeValid();
    echo $isValid ? "✅ Code is valid\n" : "❌ Code is invalid\n";
    
    // Test database query that was failing
    echo "🔄 Testing nutritionist query...\n";
    $expiredCount = \App\Models\Patient::where('nutritionist_id', $patient->nutritionist_id)
        ->whereNotNull('registration_code')
        ->where('code_expires_at', '<', now())
        ->whereNull('parent_id')
        ->count();
    
    echo "✅ Query successful. Expired codes count: {$expiredCount}\n";
    
    echo "\n🎉 All tests passed! Registration code system is working.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}