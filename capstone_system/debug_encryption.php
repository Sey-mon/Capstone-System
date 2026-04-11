<?php
// Debug file - Upload to public/debug_encryption.php on production, then delete after checking

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use App\Services\DataEncryptionService;

$encService = new DataEncryptionService();

// Test encryption/decryption
$testEmail = "test@example.com";
$encrypted = $encService->encryptUserData($testEmail);
$decrypted = $encService->decryptUserData($encrypted);

echo "<pre>";
echo "=== ENCRYPTION DEBUG ===\n";
echo "Original: " . $testEmail . "\n";
echo "Encrypted: " . $encrypted . "\n";
echo "Decrypted: " . $decrypted . "\n";
echo "Match: " . ($testEmail === $decrypted ? "✓ YES" : "✗ NO") . "\n\n";

echo "APP_ENCRYPTION_KEY from env: " . env('APP_ENCRYPTION_KEY') . "\n";
echo "USER_DATA_ENCRYPTION_KEY from env: " . env('USER_DATA_ENCRYPTION_KEY') . "\n";
echo "Config encryption_key: " . config('app.encryption_key') . "\n";
echo "Config user_data_key: " . config('app.user_data_key') . "\n";
echo "</pre>";
?>
