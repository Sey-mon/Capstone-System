<?php
// Debug file - Check if encryption keys are loaded correctly
// Delete after use!

require __DIR__ . '/../bootstrap/app.php';

use App\Services\DataEncryptionService;

echo "<pre>";
echo "=== ENCRYPTION KEY DEBUG ===\n\n";

echo "Environment variables:\n";
echo "APP_ENCRYPTION_KEY = " . (env('APP_ENCRYPTION_KEY') ? "✓ SET" : "✗ NOT SET") . "\n";
echo "USER_DATA_ENCRYPTION_KEY = " . (env('USER_DATA_ENCRYPTION_KEY') ? "✓ SET" : "✗ NOT SET") . "\n\n";

echo "Config values:\n";
echo "config('app.encryption_key') = " . (config('app.encryption_key') ? config('app.encryption_key') : "NOT SET") . "\n";
echo "config('app.user_data_key') = " . (config('app.user_data_key') ? config('app.user_data_key') : "NOT SET") . "\n\n";

// Test encryption/decryption
$encService = new DataEncryptionService();
$testEmail = "test@example.com";
$encrypted = $encService->encryptUserData($testEmail);
$decrypted = $encService->decryptUserData($encrypted);

echo "Test encryption/decryption:\n";
echo "Original: " . $testEmail . "\n";
echo "Encrypted: " . substr($encrypted, 0, 50) . "...\n";
echo "Decrypted: " . $decrypted . "\n";
echo "Result: " . ($testEmail === $decrypted ? "✓ WORKING" : "✗ FAILED") . "\n";
echo "</pre>";
?>
