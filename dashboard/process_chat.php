<?php
// dashboard/process_chat.php - Proses pengiriman pesan chat AI

ob_start(); // Start output buffering

// Cek apakah sesi sudah aktif sebelum memulai sesi baru
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/ai_handler.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    ob_end_clean(); // Clean the output buffer
    exit;
}

require_once '../includes/functions.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'send';

    if ($action === 'send') {
        $pesan_pengguna = trim($_POST['pesan'] ?? '');

        if (empty($pesan_pengguna)) {
            $_SESSION['error'] = "Pesan tidak boleh kosong.";
            header('Location: ../dashboard/?page=chat');
            exit;
        }

        try {
            // Ambil file-file terbaru milik pengguna untuk referensi AI
            $stmt = $pdo->prepare("
                SELECT a.ringkasan, a.penjabaran_materi, f.original_name
                FROM analisis_ai a
                JOIN upload_files f ON a.file_id = f.id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $referensi_files = $stmt->fetchAll();

            // Bangun konteks dari file yang telah diupload
            $konteks = "Kamu adalah AI asisten pendidikan bernama Sinar Ilmu. ";
            $konteks .= "Berikut adalah materi dari file yang telah diupload oleh pengguna:\n\n";

            foreach ($referensi_files as $file) {
                $konteks .= "File: " . $file['original_name'] . "\n";
                $konteks .= "Ringkasan: " . $file['ringkasan'] . "\n";
                if (!empty($file['penjabaran_materi'])) {
                    $konteks .= "Penjabaran: " . $file['penjabaran_materi'] . "\n";
                }
                $konteks .= "\n";
            }

            $konteks .= "Pertanyaan pengguna: " . $pesan_pengguna . "\n";
            $konteks .= "Jawab pertanyaan ini berdasarkan materi dari file-file di atas. Jika tidak ada informasi relevan, berikan jawaban berdasarkan pengetahuan umum dan sebutkan bahwa informasi tidak ditemukan di file yang telah diupload.";

            // Gunakan AI handler yang sebenarnya untuk menghasilkan jawaban
            $aiHandler = new AIHandler();
            $pesan_ai = $aiHandler->sendMessage($konteks);

            // Simpan pesan pengguna dan AI ke database
            $stmt = $pdo->prepare("INSERT INTO chat_ai (user_id, pesan_pengguna, pesan_ai, topik_terkait) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$_SESSION['user_id'], $pesan_pengguna, $pesan_ai, json_encode([])])) {
                // Catat aktivitas
                log_activity($_SESSION['user_id'], 'Kirim Chat', 'Mengirim pesan ke chat AI');

                $_SESSION['success'] = "Pesan berhasil dikirim.";
            } else {
                $_SESSION['error'] = "Gagal mengirim pesan.";
            }
        } catch (Exception $e) {
            // Tangani kesalahan sistem
            error_log("Chat AI Error: " . $e->getMessage());
            $pesan_ai = "Maaf, terjadi kesalahan sistem saat memproses pertanyaan Anda. Silakan coba lagi.";

            // Tetap simpan percakapan agar pengguna tahu adanya masalah
            $stmt = $pdo->prepare("INSERT INTO chat_ai (user_id, pesan_pengguna, pesan_ai, topik_terkait) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $pesan_pengguna, $pesan_ai, json_encode([])]);

            $_SESSION['error'] = "Terjadi kesalahan sistem saat mengirim pesan.";
        }
    }
    elseif ($action === 'delete_chat') {
        // Hapus semua chat dari pengguna ini
        $stmt = $pdo->prepare("DELETE FROM chat_ai WHERE user_id = ?");
        if ($stmt->execute([$_SESSION['user_id']])) {
            log_activity($_SESSION['user_id'], 'Hapus Chat', 'Menghapus riwayat chat');
            $_SESSION['success'] = "Riwayat chat berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus riwayat chat.";
        }
    }
    elseif ($action === 'delete_single_chat') {
        // Hapus satu percakapan berdasarkan ID
        $chat_id = $_POST['chat_id'] ?? 0;
        if ($chat_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM chat_ai WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$chat_id, $_SESSION['user_id']])) {
                log_activity($_SESSION['user_id'], 'Hapus Chat Tunggal', 'Menghapus satu percakapan chat');
                $_SESSION['success'] = "Percakapan berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus percakapan.";
            }
        } else {
            $_SESSION['error'] = "ID percakapan tidak valid.";
        }
    }

    header('Location: ../dashboard/?page=chat');
    ob_end_clean(); // Clean the output buffer
    exit;
} else {
    header('Location: ../dashboard/?page=chat');
    ob_end_clean(); // Clean the output buffer
    exit;
}
?>