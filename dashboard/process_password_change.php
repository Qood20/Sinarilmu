<?php
// dashboard/process_password_change.php - Proses ganti kata sandi

// Cek apakah sesi sudah aktif sebelum memulai sesi baru
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start(); // Start output buffering

if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    ob_end_clean(); // Clean the output buffer
    exit;
}

require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // Validasi input
    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $_SESSION['error'] = "Semua kolom harus diisi.";
        header('Location: ?page=change_password');
        ob_end_clean(); // Clean the output buffer
        exit;
    }

    // Validasi panjang password
    if (strlen($new_password) < 6) {
        $_SESSION['error'] = "Kata sandi baru minimal harus 6 karakter.";
        header('Location: ?page=change_password');
        ob_end_clean(); // Clean the output buffer
        exit;
    }

    // Validasi konfirmasi password
    if ($new_password !== $confirm_new_password) {
        $_SESSION['error'] = "Kata sandi baru dan konfirmasi tidak cocok.";
        header('Location: ?page=change_password');
        ob_end_clean(); // Clean the output buffer
        exit;
    }

    // Ambil data pengguna saat ini
    $user = get_user_by_id($_SESSION['user_id']);
    if (!$user) {
        $_SESSION['error'] = "Pengguna tidak ditemukan.";
        header('Location: ?page=change_password');
        ob_end_clean(); // Clean the output buffer
        exit;
    }

    // Verifikasi password saat ini
    if (!verify_password($current_password, $user['password'])) {
        $_SESSION['error'] = "Kata sandi saat ini salah.";
        header('Location: ?page=change_password');
        ob_end_clean(); // Clean the output buffer
        exit;
    }

    // Enkripsi password baru
    $encrypted_new_password = encrypt_password($new_password);

    // Update password di database
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
    $result = $stmt->execute([
        'password' => $encrypted_new_password,
        'user_id' => $_SESSION['user_id']
    ]);

    if ($result) {
        // Catat aktivitas perubahan password
        log_activity($_SESSION['user_id'], 'Ganti Kata Sandi', 'Pengguna mengganti kata sandi');

        $_SESSION['success'] = "Kata sandi berhasil diubah.";
    } else {
        $_SESSION['error'] = "Gagal mengubah kata sandi.";
    }

    header('Location: ?page=change_password');
    ob_end_clean(); // Clean the output buffer
    exit;
} else {
    header('Location: ?page=change_password');
    ob_end_clean(); // Clean the output buffer
    exit;
}
?>