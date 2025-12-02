<?php
// includes/functions.php - Fungsi-fungsi umum untuk aplikasi

require_once dirname(__DIR__) . '/config/database.php';

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