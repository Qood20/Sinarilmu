<?php
// pages/process_register.php - Proses registrasi pengguna

ob_start(); // Start output buffering
session_start(); // Pastikan session dimulai di sini
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Semua field harus diisi.";
        redirect('../?page=register');
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Kata sandi dan konfirmasi kata sandi tidak cocok.";
        redirect('../?page=register');
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Kata sandi minimal 6 karakter.";
        redirect('../?page=register');
    }
    
    // Cek apakah email atau username sudah digunakan
    $existing_user = get_user_by_email_or_username($email);
    if ($existing_user) {
        $_SESSION['error'] = "Email sudah terdaftar. Gunakan email lain.";
        redirect('../?page=register');
    }
    
    $existing_user = get_user_by_email_or_username($username);
    if ($existing_user) {
        $_SESSION['error'] = "Username sudah digunakan. Gunakan username lain.";
        redirect('../?page=register');
    }
    
    // Buat akun pengguna baru
    $user_id = create_user($full_name, $email, $username, $password);
    if ($user_id) {
        // Catat aktivitas registrasi
        log_activity($user_id, 'Registrasi Akun', 'Pengguna mendaftar ke sistem');

        $_SESSION['success'] = "Akun berhasil dibuat. Silakan login.";
        header('Location: ../?page=login');
        ob_end_clean(); // Clean the output buffer
        exit;
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat membuat akun. Silakan coba lagi.";
        header('Location: ../?page=register');
        ob_end_clean(); // Clean the output buffer
        exit;
    }
} else {
    // Jika tidak melalui POST, kembali ke halaman register
    header('Location: ../?page=register');
    ob_end_clean(); // Clean the output buffer
    exit;
}