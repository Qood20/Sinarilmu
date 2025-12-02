<?php
// dashboard/delete_exercises.php - Proses penghapusan latihan soal oleh user

session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

require_once '../includes/functions.php';
require_once '../config/database.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $analisis_id = isset($_POST['analisis_id']) ? (int)$_POST['analisis_id'] : 0;
    
    if ($analisis_id <= 0) {
        $_SESSION['error'] = "ID analisis tidak valid.";
        header('Location: ?page=exercises');
        exit;
    }
    
    try {
        // Cek apakah analisis milik user ini
        $checkStmt = $pdo->prepare("SELECT a.id FROM analisis_ai a JOIN upload_files f ON a.file_id = f.id WHERE a.id = ? AND f.user_id = ?");
        $checkStmt->execute([$analisis_id, $_SESSION['user_id']]);
        $analisis = $checkStmt->fetch();
        
        if (!$analisis) {
            $_SESSION['error'] = "Analisis tidak ditemukan atau bukan milik Anda.";
            header('Location: ?page=exercises');
            exit;
        }
        
        // Hapus soal-soal terkait dengan analisis ini
        $deleteQuestionsStmt = $pdo->prepare("DELETE FROM bank_soal_ai WHERE analisis_id = ?");
        $deleteQuestionsStmt->execute([$analisis_id]);
        
        // Update status file menjadi 'completed' lagi karena tidak ada soal
        // Tapi jangan hapus analisisnya karena ringkasan dan penjelasan mungkin masih berguna
        // Kalau ingin hapus semuanya termasuk analisis, uncomment baris berikut:
        // $deleteAnalysisStmt = $pdo->prepare("DELETE FROM analisis_ai WHERE id = ?");
        // $deleteAnalysisStmt->execute([$analisis_id]);
        
        $_SESSION['success'] = "Latihan soal berhasil dihapus.";
        
        // Catat aktivitas
        log_activity($_SESSION['user_id'], 'Hapus Latihan Soal', 'Menghapus latihan soal untuk analisis_id: ' . $analisis_id);
        
    } catch (Exception $e) {
        error_log("Error deleting exercises: " . $e->getMessage());
        $_SESSION['error'] = "Terjadi kesalahan saat menghapus latihan soal.";
    }
}

header('Location: ?page=exercises');
exit;
?>