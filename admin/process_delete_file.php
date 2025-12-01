<?php
// admin/process_delete_file.php - Proses untuk menghapus file

ob_start(); // Start output buffering
session_start();

require_once '../includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($file_id <= 0) {
    $_SESSION['error'] = "ID file tidak valid.";
    header('Location: ?page=files');
    exit;
}

// Ambil informasi file sebelum dihapus untuk keperluan log dan penghapusan fisik
$stmt = $pdo->prepare("SELECT original_name, file_path FROM upload_files WHERE id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if ($file) {
    $file_name = $file['original_name'];
    $file_path = $file['file_path'];
    
    // Hapus dari database
    $stmt = $pdo->prepare("DELETE FROM upload_files WHERE id = ?");
    $result = $stmt->execute([$file_id]);
    
    if ($result) {
        // Hapus file fisik jika ada
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Catat aktivitas
        log_activity($_SESSION['user_id'], 'Hapus File', 'Menghapus file: ' . $file_name);
        
        $_SESSION['success'] = "File berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus file dari database.";
    }
} else {
    $_SESSION['error'] = "File tidak ditemukan.";
}

header('Location: ?page=files');
ob_end_clean();
exit;