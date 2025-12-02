<?php
// admin/process_delete_material.php - Proses penghapusan materi pelajaran

session_start();
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// 1. Keamanan: Cek apakah pengguna adalah admin
if (!is_admin()) {
    $_SESSION['upload_error'] = "Akses tidak sah.";
    header('Location: ../?page=material_content');
    exit;
}

global $pdo;

// 2. Validasi Input
$material_id = $_GET['id'] ?? null;

if (empty($material_id)) {
    $_SESSION['upload_error'] = "ID Materi tidak valid.";
    header('Location: ../?page=material_content');
    exit;
}

try {
    // 3. Ambil path file dari database sebelum menghapus record
    $stmt = $pdo->prepare("SELECT file_path FROM materi_pelajaran WHERE id = ?");
    $stmt->execute([$material_id]);
    $material = $stmt->fetch();

    if ($material) {
        $file_path = dirname(__DIR__, 2) . '/' . $material['file_path'];

        // 4. Hapus file fisik dari server
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // 5. Hapus record dari database
        $delete_stmt = $pdo->prepare("DELETE FROM materi_pelajaran WHERE id = ?");
        $delete_stmt->execute([$material_id]);

        // Catat aktivitas
        log_activity($_SESSION['user_id'], 'Hapus Materi', "Materi dengan ID #$material_id telah dihapus.");

        $_SESSION['upload_success'] = "Materi berhasil dihapus.";
    } else {
        $_SESSION['upload_error'] = "Materi tidak ditemukan.";
    }

} catch (PDOException $e) {
    error_log("Database error on material deletion: " . $e->getMessage());
    $_SESSION['upload_error'] = "Terjadi error pada database saat menghapus materi.";
}

// 6. Redirect kembali ke halaman materi
header('Location: ../?page=material_content');
exit;
