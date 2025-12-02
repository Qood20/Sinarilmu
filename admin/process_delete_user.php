<?php
// admin/process_delete_user.php - Proses untuk menghapus pengguna

ob_start(); // Start output buffering

// Cek apakah sesi sudah aktif sebelum memulai sesi baru
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['error'] = "ID pengguna tidak valid.";
    header('Location: ?page=users');
    exit;
}

// Ambil nama pengguna sebelum dihapus untuk log
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
    $user_name = $user['full_name'];
    
    // Hapus pengguna
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $result = $stmt->execute([$user_id]);
    
    if ($result) {
        // Catat aktivitas
        log_activity($_SESSION['user_id'], 'Hapus Pengguna', 'Menghapus pengguna: ' . $user_name);
        
        $_SESSION['success'] = "Pengguna berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus pengguna.";
    }
} else {
    $_SESSION['error'] = "Pengguna tidak ditemukan.";
}

header('Location: ?page=users');
ob_end_clean();
exit;