<?php
// test_password_reset.php - Script untuk menguji fungsi reset password

require_once 'includes/functions.php';

echo "=== Testing Password Reset Functionality ===\n";

// Test 1: Coba request password reset untuk email yang tidak ada
echo "\nTest 1: Request password reset for non-existent email\n";
$result = request_password_reset('nonexistent@example.com');
if ($result) {
    echo "✓ Request successful (expected behavior - prevents email enumeration)\n";
} else {
    echo "✗ Request failed\n";
}

// Test 2: Coba request password reset untuk email yang valid
echo "\nTest 2: Request password reset for existing email\n";
$result = request_password_reset('budi@example.com');
if ($result) {
    echo "✓ Request successful, token should be in database\n";
    
    // Ambil token terbaru dari database
    $stmt = $pdo->prepare("
        SELECT token 
        FROM password_reset_tokens 
        WHERE user_id = (SELECT id FROM users WHERE email = ?) 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute(['budi@example.com']);
    $token_data = $stmt->fetch();
    
    if ($token_data) {
        echo "✓ Token generated: " . substr($token_data['token'], 0, 10) . "...\n";
        
        // Test 3: Validasi token
        echo "\nTest 3: Validate the generated token\n";
        $valid_token = validate_password_reset_token($token_data['token']);
        if ($valid_token) {
            echo "✓ Token is valid\n";
            echo "  User ID: " . $valid_token['user_id'] . "\n";
            echo "  Token: " . substr($valid_token['token'], 0, 10) . "...\n";
            echo "  Expires at: " . $valid_token['expires_at'] . "\n";
        } else {
            echo "✗ Token is invalid\n";
        }
        
        // Test 4: Reset password dengan token
        echo "\nTest 4: Reset password with token\n";
        $reset_result = reset_user_password($token_data['token'], 'newpassword123');
        if ($reset_result) {
            echo "✓ Password reset successful\n";
            
            // Coba login dengan password baru
            echo "\nTest 5: Test login with new password\n";
            $user = get_user_by_email_or_username('budi@example.com');
            if ($user && verify_password('newpassword123', $user['password'])) {
                echo "✓ Login with new password successful\n";
                
                // Reset password ke password default lagi
                $default_password = encrypt_password('password');
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$default_password, 'budi@example.com']);
                echo "✓ Password reset back to default\n";
            } else {
                echo "✗ Login with new password failed\n";
            }
        } else {
            echo "✗ Password reset failed\n";
        }
    } else {
        echo "✗ No token found in database\n";
    }
} else {
    echo "✗ Request failed\n";
}

// Test 5: Validasi token yang tidak valid
echo "\nTest 6: Validate invalid token\n";
$invalid_token = validate_password_reset_token('invalid_token_12345');
if (!$invalid_token) {
    echo "✓ Invalid token correctly rejected\n";
} else {
    echo "✗ Invalid token was accepted\n";
}

echo "\n=== Testing Complete ===\n";