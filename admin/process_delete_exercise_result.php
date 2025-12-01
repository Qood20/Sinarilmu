<?php
// admin/process_delete_exercise_result.php - Proses untuk menghapus hasil latihan

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

$result_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($result_id <= 0) {
    $_SESSION['error'] = "ID hasil latihan tidak valid.";
    header('Location: ?page=exercises');
    exit;
}

// Ambil informasi sebelum dihapus untuk logging
$stmt = $pdo->prepare("SELECT hs.id, u.full_name FROM hasil_soal_user hs JOIN users u ON hs.user_id = u.id WHERE hs.id = ?");
$stmt->execute([$result_id]);
$exercise = $stmt->fetch();

if ($exercise) {
    // Hapus dari database
    $stmt = $pdo->prepare("DELETE FROM hasil_soal_user WHERE id = ?");
    $result = $stmt->execute([$result_id]);
    
    if ($result) {
        // Catat aktivitas
        log_activity($_SESSION['user_id'], 'Hapus Hasil Latihan', 'Menghapus hasil latihan pengguna: ' . $exercise['full_name']);
        
        $_SESSION['success'] = "Hasil latihan berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus hasil latihan.";
    }
} else {
    $_SESSION['error'] = "Hasil latihan tidak ditemukan.";
}

header('Location: ?page=exercises');
ob_end_clean();
exit;