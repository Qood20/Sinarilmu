<?php
// dashboard/process_profile.php - Proses update profil pengguna

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
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi input
    if (empty($full_name) || empty($email) || empty($username)) {
        $_SESSION['error'] = "Nama lengkap, email, dan username harus diisi.";
        header('Location: ?page=profile');
        ob_end_clean(); // Clean the output buffer
        exit;
    }
    
    // Jika password diisi, cek konfirmasi
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Kata sandi dan konfirmasi kata sandi tidak cocok.";
            header('Location: ?page=profile');
            ob_end_clean(); // Clean the output buffer
            exit;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = "Kata sandi minimal 6 karakter.";
            header('Location: ?page=profile');
            ob_end_clean(); // Clean the output buffer
            exit;
        }
        
        // Hash password baru
        $encrypted_password = encrypt_password($password);
        $password_update = ", password = :password";
    } else {
        // Tidak ada perubahan password
        $password_update = "";
    }
    
    // Cek apakah email atau username sudah digunakan oleh pengguna lain
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :current_user_id");
    $stmt->execute([
        'email' => $email,
        'username' => $username,
        'current_user_id' => $_SESSION['user_id']
    ]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email atau username sudah digunakan oleh pengguna lain.";
        header('Location: ?page=profile');
        ob_end_clean(); // Clean the output buffer
        exit;
    }
    
    // Update data pengguna
    $sql = "UPDATE users SET full_name = :full_name, email = :email, username = :username" . $password_update . " WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    
    $params = [
        'full_name' => $full_name,
        'email' => $email,
        'username' => $username,
        'user_id' => $_SESSION['user_id']
    ];
    
    if (!empty($password)) {
        $params['password'] = $encrypted_password;
    }
    
    if ($stmt->execute($params)) {
        // Update session data
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;
        
        // Catat aktivitas perubahan profil
        log_activity($_SESSION['user_id'], 'Update Profil', 'Pengguna memperbarui profil');
        
        $_SESSION['success'] = "Profil berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil.";
    }
    
    header('Location: ?page=profile');
    ob_end_clean(); // Clean the output buffer
    exit;
} else {
    header('Location: ?page=profile');
    ob_end_clean(); // Clean the output buffer
    exit;
}