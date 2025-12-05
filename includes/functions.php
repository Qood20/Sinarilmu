<?php
// includes/functions.php - Fungsi-fungsi umum untuk aplikasi

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/config.php';

/**
 * Fungsi untuk mengamankan input dari serangan XSS
 */
function escape($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Fungsi untuk redirect ke halaman tertentu
 */
function redirect($location) {
    header("Location: $location");
    if (ob_get_level()) {
        ob_end_clean();
    }
    exit;
}

/**
 * Fungsi untuk mengecek apakah koneksi database tersedia
 */
function is_db_connected() {
    global $pdo;
    return $pdo !== null;
}

/**
 * Fungsi untuk mengecek apakah pengguna sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Fungsi untuk mendapatkan data pengguna berdasarkan ID
 */
function get_user_by_id($user_id) {
    global $pdo;

    if ($pdo === null) {
        error_log("Database not connected when trying to get user by ID");
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Fungsi untuk mendapatkan data pengguna berdasarkan email atau username
 */
function get_user_by_email_or_username($email_or_username) {
    global $pdo;

    if ($pdo === null) {
        error_log("Database not connected when trying to get user by email or username");
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email_or_username, $email_or_username]);
    return $stmt->fetch();
}

/**
 * Fungsi untuk membuat session pengguna
 */
function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
}

/**
 * Fungsi untuk logout pengguna
 */
function logout_user() {
    session_destroy();
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['full_name']);
    unset($_SESSION['role']);
}

/**
 * Fungsi untuk mengenkripsi password
 */
function encrypt_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Fungsi untuk memverifikasi password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Fungsi untuk mengecek apakah pengguna adalah admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Fungsi untuk mendapatkan semua pengguna (hanya untuk admin)
 */
function get_all_users() {
    global $pdo;

    if ($pdo === null) {
        error_log("Database not connected when trying to get all users");
        return [];
    }

    try {
        $stmt = $pdo->query("SELECT id, full_name, username, email, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting all users: " . $e->getMessage());
        return [];
    }
}

/**
 * Fungsi untuk membuat pengguna baru
 */
function create_user($full_name, $email, $username, $password) {
    global $pdo;

    if ($pdo === null) {
        error_log("Database not connected when trying to create user");
        return false;
    }

    $encrypted_password = encrypt_password($password);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, username, password, role) VALUES (?, ?, ?, ?, 'user')");
    $result = $stmt->execute([$full_name, $email, $username, $encrypted_password]);

    if ($result) {
        return $pdo->lastInsertId();
    }
    return false;
}

/**
 * Fungsi untuk mencatat aktivitas pengguna
 */
function log_activity($user_id, $action, $description = null) {
    global $pdo;

    if ($pdo === null) {
        error_log("Database not connected when trying to log activity");
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aksi, deskripsi, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $action,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * Fungsi untuk mencatat login
 */
function log_login_activity($user_id, $success = true) {
    $action = $success ? 'Login Berhasil' : 'Login Gagal';
    log_activity($user_id, $action, 'Percobaan login ' . ($success ? 'berhasil' : 'gagal'));
}

/**
 * Fungsi untuk format ukuran file
 */
function format_file_size($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, $k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

/**
 * Generate a random verification token
 */
function generate_verification_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Send password reset email
 * NOTE: This is a simplified implementation. In production, you would want to use a proper email library.
 */
function send_password_reset_email($email, $token) {
    // Build the reset URL - account for potential subdirectories
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname(dirname($_SERVER['SCRIPT_NAME']));
    // Ensure path doesn't end with a slash to avoid double slashes
    $path = rtrim($path, '/');

    $reset_url = "$protocol://$host$path/?page=reset_password&token=$token";

    $subject = "Permintaan Reset Kata Sandi - Sinar Ilmu";

    $message = "
    <html>
    <head>
        <title>Reset Kata Sandi</title>
    </head>
    <body>
        <h2>Permintaan Reset Kata Sandi</h2>
        <p>Anda menerima email ini karena ada permintaan untuk mereset kata sandi akun Anda di Sinar Ilmu.</p>
        <p>Klik tautan di bawah ini untuk mereset kata sandi Anda:</p>
        <p><a href='$reset_url'>Reset Kata Sandi</a></p>
        <p>Tautan ini akan kedaluwarsa dalam 1 jam.</p>
        <p>Jika Anda tidak melakukan permintaan reset kata sandi, abaikan email ini.</p>
    </body>
    </html>
    ";

    // Set content-type header for sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: noreply@sinarilmu.com' . "\r\n";

    // For now, we'll just return true to simulate sending
    // In production, you would use a proper email service
    error_log("Password reset email sent to: $email with token: $token");
    error_log("Password reset URL: $reset_url");
    return true;
}

/**
 * Check if the password reset tokens table exists
 */
function check_password_reset_table_exists() {
    global $pdo;

    if ($pdo === null) {
        return false;
    }

    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'password_reset_tokens'");
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        error_log("Error checking if password_reset_tokens table exists: " . $e->getMessage());
        return false;
    }
}

/**
 * Create the password reset tokens table
 */
function create_password_reset_table() {
    global $pdo;

    if ($pdo === null) {
        return false;
    }

    try {
        // Create the table
        $sql = "CREATE TABLE password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL,
            used BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );";

        $pdo->exec($sql);

        // Create indexes
        $pdo->exec("CREATE INDEX idx_password_reset_token ON password_reset_tokens(token);");
        $pdo->exec("CREATE INDEX idx_password_reset_expires ON password_reset_tokens(expires_at);");
        $pdo->exec("CREATE INDEX idx_password_reset_user_id ON password_reset_tokens(user_id);");

        return true;
    } catch (Exception $e) {
        error_log("Error creating password_reset_tokens table: " . $e->getMessage());
        return false;
    }
}

/**
 * Request password reset - generate token and send email
 */
function request_password_reset($email) {
    global $pdo;

    if ($pdo === null) {
        error_log("Database not connected when trying to request password reset");
        return false;
    }

    // Check if the table exists, and create it if necessary
    if (!check_password_reset_table_exists()) {
        if (!create_password_reset_table()) {
            error_log("Failed to create password_reset_tokens table");
            return false;
        }
    }

    try {
        // Find user by email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Even if email doesn't exist, return true to prevent email enumeration
            return true;
        }

        $user_id = $user['id'];

        // Generate a unique token
        $token = generate_verification_token();
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

        // Delete any existing unused tokens for this user
        $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? AND used = 0");
        $stmt->execute([$user_id]);

        // Insert the new token
        $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $result = $stmt->execute([$user_id, $token, $expires_at]);

        if ($result) {
            // Send email with the reset link
            return send_password_reset_email($email, $token);
        }

        return false;
    } catch (Exception $e) {
        error_log("Error requesting password reset: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate password reset token
 */
function validate_password_reset_token($token) {
    global $pdo;

    if ($pdo === null) {
        error_log("Database not connected when trying to validate password reset token");
        return false;
    }

    // Check if the table exists
    if (!check_password_reset_table_exists()) {
        error_log("Password reset tokens table does not exist");
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT prt.*, u.email
            FROM password_reset_tokens prt
            JOIN users u ON prt.user_id = u.id
            WHERE prt.token = ? AND prt.used = 0 AND prt.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch();

        return $token_data ? $token_data : false;
    } catch (Exception $e) {
        error_log("Error validating password reset token: " . $e->getMessage());
        return false;
    }
}

/**
 * Reset user password using token
 */
function reset_user_password($token, $new_password) {
    global $pdo;

    if ($pdo === null) {
        error_log("Database not connected when trying to reset user password");
        return false;
    }

    // Check if the table exists
    if (!check_password_reset_table_exists()) {
        error_log("Password reset tokens table does not exist");
        return false;
    }

    try {
        // First, validate the token
        $token_data = validate_password_reset_token($token);

        if (!$token_data) {
            return false;
        }

        // Hash the new password
        $encrypted_password = encrypt_password($new_password);

        // Update user's password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$encrypted_password, $token_data['user_id']]);

        if ($result) {
            // Mark the token as used
            $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
            $stmt->execute([$token_data['id']]);

            // Log the password reset activity
            log_activity($token_data['user_id'], 'Password Reset', 'Kata sandi berhasil direset melalui fitur lupa sandi');

            return true;
        }

        return false;
    } catch (Exception $e) {
        error_log("Error resetting user password: " . $e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk membersihkan nama file dari karakter emoji
 */
function clean_filename_from_emojis($filename) {
    // Remove emojis and other non-ASCII characters while preserving the file extension
    $path_info = pathinfo($filename);
    $basename = $path_info['filename'];
    $extension = isset($path_info['extension']) ? '.' . $path_info['extension'] : '';

    // Regular expression to remove emoji and other non-ASCII characters
    $cleaned_basename = preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]|[\x{1F900}-\x{1F9FF}]|[\x{1F018}-\x{1F270}]/u', '', $basename);

    // Replace any remaining non-ASCII characters with underscore
    $cleaned_basename = preg_replace('/[^\x20-\x7E]/u', '_', $cleaned_basename);

    // Clean any multiple underscores
    $cleaned_basename = preg_replace('/_+/', '_', $cleaned_basename);
    $cleaned_basename = trim($cleaned_basename, '_');

    // If cleaned name is empty, use a default name
    if (empty($cleaned_basename)) {
        $cleaned_basename = 'file_' . time();
    }

    return $cleaned_basename . $extension;
}