<?php
// includes/get_ai_result.php - Endpoint untuk mengambil hasil AI dari database

// Cek apakah sesi sudah aktif sebelum memulai sesi baru
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit;
}

require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
    
    if ($file_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID file tidak valid.']);
        exit;
    }
    
    try {
        global $pdo;
        
        // Pastikan file milik pengguna yang sedang login dan ambil data terbaru untuk file ini
        $stmt = $pdo->prepare("
            SELECT a.ringkasan, a.penjabaran_materi, a.topik_terkait, a.created_at
            FROM analisis_ai a
            JOIN upload_files f ON a.file_id = f.id
            WHERE a.file_id = ? AND f.user_id = ?
            ORDER BY a.created_at DESC  -- Ambil hasil terbaru
            LIMIT 1  -- Hanya ambil satu hasil terbaru untuk memastikan tidak ada duplikasi
        ");
        $stmt->execute([$file_id, $_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Logging untuk verifikasi bahwa data yang benar diambil
            error_log("Mengambil hasil AI untuk file_id: " . $file_id . " pada: " . $result['created_at']);

            // Query awal sudah mengambil semua yang dibutuhkan, jadi tidak perlu query lagi
            $content = '<div class="prose max-w-none">';
            $content .= '<h4 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">';
            $content .= '<svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
            $content .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
            $content .= '</svg>';
            $content .= 'Ringkasan Materi';
            $content .= '</h4>';
            $content .= '<div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-4">';
            $content .= '<p class="text-gray-700 whitespace-pre-line">' . nl2br(escape($result['ringkasan'])) . '</p>';
            $content .= '</div>';

            if (!empty($result['penjabaran_materi']) && $result['penjabaran_materi'] != $result['ringkasan']) {
                $content .= '<h4 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">';
                $content .= '<svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                $content .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>';
                $content .= '</svg>';
                $content .= 'Penjabaran Materi';
                $content .= '</h4>';
                $content .= '<div class="bg-green-50 p-4 rounded-lg border border-green-200">';
                $content .= '<p class="text-gray-700 whitespace-pre-line">' . nl2br(escape($result['penjabaran_materi'])) . '</p>';
                $content .= '</div>';
            }

            // Topik terkait dihapus sesuai permintaan pengguna
            // Kode untuk menampilkan topik terkait dihapus

            $content .= '<div class="mt-6 pt-4 border-t border-gray-200">';
            $content .= '<p class="text-sm text-gray-600">Analisis ini dibuat secara otomatis oleh AI berdasarkan file yang Anda unggah.</p>';
            $content .= '</div>';
            $content .= '</div>';

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'content' => $content]);
        } else {
            // Cek apakah file pernah diproses tapi belum ada hasil AI
            $stmt = $pdo->prepare("SELECT status FROM upload_files WHERE id = ? AND user_id = ?");
            $stmt->execute([$file_id, $_SESSION['user_id']]);
            $file = $stmt->fetch();

            if ($file) {
                $status = $file['status'];
                $content = '<div class="prose max-w-none">';
                $content .= '<h4 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">';
                $content .= '<svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                $content .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>';
                $content .= '</svg>';
                $content .= 'Status File: ' . ucfirst($status);
                $content .= '</h4>';

                $content .= '<div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">';

                switch($status) {
                    case 'processing':
                        $content .= '<p class="text-gray-700">File masih dalam proses analisis oleh AI. Silakan coba lagi dalam beberapa menit.</p>';
                        break;
                    case 'ai_error':
                        $content .= '<p class="text-gray-700">Terjadi kesalahan saat memproses file dengan AI. Mungkin akan berhasil jika Anda mengunggah kembali.</p>';
                        break;
                    case 'completed':
                        $content .= '<p class="text-gray-700">File telah selesai diproses oleh AI, tetapi hasil analisis belum tersedia saat ini.</p>';
                        break;
                    case 'uploaded':
                        $content .= '<p class="text-gray-700">File telah diupload, tetapi belum diproses oleh AI. Silakan klik tombol "Buat Soal" untuk memulai proses analisis.</p>';
                        break;
                    default:
                        $content .= '<p class="text-gray-700">File memiliki status: ' . ucfirst($status) . '. Status ini menunjukkan tahapan proses file dengan AI.</p>';
                        break;
                }

                $content .= '</div>';

                // Tambahkan instruksi berikutnya
                $content .= '<div class="mt-4 text-sm text-gray-600">';
                $content .= '<p class="mb-2">Anda bisa mencoba hal-hal berikut:</p>';
                $content .= '<ul class="list-disc pl-5 space-y-1">';
                $content .= '<li>Coba lagi nanti untuk melihat hasil analisis AI</li>';
                $content .= '<li>Klik tombol "Buat Soal" untuk membuat soal latihan dari materi ini</li>';
                $content .= '<li>Unggah kembali file jika tidak ada perubahan setelah beberapa menit</li>';
                $content .= '</ul>';
                $content .= '</div>';

                // Dapatkan nama file dari tabel upload_files
                $fileStmt = $pdo->prepare("SELECT original_name FROM upload_files WHERE id = ?");
                $fileStmt->execute([$file_id]);
                $fileInfo = $fileStmt->fetch();

                $content .= '<div class="mt-6 pt-4 border-t border-gray-200">';
                $content .= '<p class="text-sm text-gray-600">File: ' . escape($fileInfo['original_name'] ?? 'Tidak diketahui') . '</p>';
                $content .= '</div>';
                $content .= '</div>';

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'content' => $content]); // Kita tetap kirim success=true agar bisa tampilkan ke pengguna
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File tidak ditemukan.']);
            }
        }
    } catch (Exception $e) {
        error_log("Error getting AI result: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengambil hasil AI.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
}