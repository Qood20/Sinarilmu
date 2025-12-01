<?php
// dashboard/process_chat.php - Proses pengiriman pesan chat AI

ob_start(); // Start output buffering
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    ob_end_clean(); // Clean the output buffer
    exit;
}

require_once '../includes/functions.php';

// Definisikan fungsi di awal sebelum digunakan
/**
 * Fungsi sederhana untuk membuat jawaban AI berdasarkan pertanyaan
 */
function buat_jawaban_ai($pertanyaan) {
    $pertanyaan = strtolower($pertanyaan);

    // Jawaban berdasarkan kata kunci
    if (strpos($pertanyaan, 'hai') !== false || strpos($pertanyaan, 'halo') !== false || strpos($pertanyaan, 'hello') !== false) {
        return "Halo! Saya Sinar, asisten belajarmu. Ada yang bisa saya bantu?";
    } elseif (strpos($pertanyaan, 'siapa kamu') !== false || strpos($pertanyaan, 'apa itu') !== false) {
        return "Saya adalah Sinar, asisten belajar berbasis AI yang dirancang untuk membantu Anda memahami materi pelajaran dengan lebih mudah dan cepat.";
    } elseif (strpos($pertanyaan, 'matematika') !== false || strpos($pertanyaan, 'aljabar') !== false || strpos($pertanyaan, 'fungsi') !== false || strpos($pertanyaan, 'kuadrat') !== false) {
        return "Topik Matematika adalah salah satu keahlian saya. Saya bisa membantu menjelaskan konsep-konsep seperti persamaan kuadrat, fungsi, aljabar, dan topik matematika lainnya. Mau saya jelaskan lebih lanjut?";
    } elseif (strpos($pertanyaan, 'fisika') !== false || strpos($pertanyaan, 'newton') !== false || strpos($pertanyaan, 'hukum') !== false) {
        return "Fisika juga merupakan bidang yang saya kuasai. Saya bisa menjelaskan hukum Newton, gerak lurus, hukum kekekalan energi, dan topik fisika lainnya. Apa yang ingin Anda pelajari?";
    } elseif (strpos($pertanyaan, 'kimia') !== false || strpos($pertanyaan, 'atom') !== false || strpos($pertanyaan, 'molekul') !== false) {
        return "Kimia termasuk dalam bidang yang saya pelajari. Saya bisa membantu menjelaskan struktur atom, ikatan kimia, reaksi kimia, dan konsep kimia lainnya. Mau saya jelaskan lebih lanjut?";
    } elseif (strpos($pertanyaan, 'biologi') !== false || strpos($pertanyaan, 'sel') !== false || strpos($pertanyaan, 'tumbuhan') !== false) {
        return "Biologi adalah bidang yang menarik! Saya bisa membantu menjelaskan tentang struktur sel, sistem organ, klasifikasi makhluk hidup, dan topik biologi lainnya.";
    } else {
        return "Terima kasih atas pertanyaan Anda. Saya akan membantu menjawab sebaik mungkin. Untuk memahami materi secara lebih mendalam, Anda juga bisa mengunggah file materi pelajaran Anda agar saya bisa menganalisis dan memberikan soal latihan yang sesuai.";
    }
}

/**
 * Fungsi sederhana untuk mendeteksi topik dari pertanyaan
 */
function deteksi_topik($pertanyaan) {
    $pertanyaan = strtolower($pertanyaan);

    if (strpos($pertanyaan, 'matematika') !== false || strpos($pertanyaan, 'aljabar') !== false || strpos($pertanyaan, 'fungsi') !== false || strpos($pertanyaan, 'kuadrat') !== false || strpos($pertanyaan, 'geometri') !== false) {
        return "Matematika";
    } elseif (strpos($pertanyaan, 'fisika') !== false || strpos($pertanyaan, 'newton') !== false || strpos($pertanyaan, 'hukum') !== false || strpos($pertanyaan, 'gerak') !== false || strpos($pertanyaan, 'energi') !== false) {
        return "Fisika";
    } elseif (strpos($pertanyaan, 'kimia') !== false || strpos($pertanyaan, 'atom') !== false || strpos($pertanyaan, 'molekul') !== false || strpos($pertanyaan, 'reaksi') !== false) {
        return "Kimia";
    } elseif (strpos($pertanyaan, 'biologi') !== false || strpos($pertanyaan, 'sel') !== false || strpos($pertanyaan, 'tumbuhan') !== false || strpos($pertanyaan, 'hewan') !== false) {
        return "Biologi";
    } else {
        return "Umum";
    }
}

global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'send';

    if ($action === 'send') {
        $pesan_pengguna = trim($_POST['pesan'] ?? '');

        if (empty($pesan_pengguna)) {
            $_SESSION['error'] = "Pesan tidak boleh kosong.";
            header('Location: ?page=chat');
            exit;
        }

        // Buat jawaban AI sederhana berdasarkan isi pertanyaan
        $pesan_ai = buat_jawaban_ai($pesan_pengguna);

        // Deteksi topik berdasarkan kata kunci
        $topik_terkait = deteksi_topik($pesan_pengguna);

        // Simpan pesan pengguna ke database
        $stmt = $pdo->prepare("INSERT INTO chat_ai (user_id, pesan_pengguna, pesan_ai, topik_terkait) VALUES (?, ?, ?, ?)");

        if ($stmt->execute([$_SESSION['user_id'], $pesan_pengguna, $pesan_ai, $topik_terkait])) {
            // Catat aktivitas
            log_activity($_SESSION['user_id'], 'Kirim Chat', 'Mengirim pesan ke chat AI');

            $_SESSION['success'] = "Pesan berhasil dikirim.";
        } else {
            $_SESSION['error'] = "Gagal mengirim pesan.";
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