<?php
// pages/process_login.php - Proses login pengguna

ob_start(); // Start output buffering
session_start(); // Pastikan session dimulai di sini
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_username = trim($_POST['email_username']);
    $password = $_POST['password'];

    // Tambahkan logging untuk debugging
    error_log("Login attempt for: " . $email_username . " from IP: " . $_SERVER['REMOTE_ADDR']);

    if (empty($email_username) || empty($password)) {
        $_SESSION['error'] = "Email/Username dan kata sandi harus diisi.";
        error_log("Login failed: empty credentials");
        header('Location: ../?page=login');
        ob_end_clean(); // Clean the output buffer
        exit;
    }

    // Ambil data pengguna dari database
    $user = get_user_by_email_or_username($email_username);

    if ($user) {
        error_log("User found in DB: " . $user['email']);
        error_log("Password verification: " . (verify_password($password, $user['password']) ? "SUCCESS" : "FAILED"));
    } else {
        error_log("User not found in DB: " . $email_username);
    }

    if ($user && verify_password($password, $user['password'])) {
        // Login berhasil
        login_user($user);
        error_log("Login successful for: " . $user['email']);

        // Catat aktivitas login
        log_login_activity($user['id'], true);

        // Redirect ke dashboard
        if ($user['role'] === 'admin') {
            header('Location: ../admin/');
        } else {
            header('Location: ../dashboard/');
        }
        ob_end_clean(); // Clean the output buffer
        exit;
    } else {
        // Login gagal
        $_SESSION['error'] = "Email/Username atau kata sandi salah.";
        error_log("Login failed for: " . $email_username);

        // Catat aktivitas login gagal
        $user_id = $user ? $user['id'] : null;
        if ($user_id) {
            log_login_activity($user_id, false);
        }

        header('Location: ../?page=login');
        ob_end_clean(); // Clean the output buffer
        exit;
    }
} else {
    // Jika tidak melalui POST, kembali ke halaman login
    header('Location: ../?page=login');
    ob_end_clean(); // Clean the output buffer
    exit;
}