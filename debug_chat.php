<?php
// debug_chat.php - File untuk debug sistem chat

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Cek apakah pengguna sudah login (untuk debugging, kita bisa abaikan sementara)
if (!isset($_SESSION['user_id'])) {
    echo "Pengguna belum login, menggunakan user_id 1 untuk debugging<br>";
    $_SESSION['user_id'] = 1;
    $_SESSION['full_name'] = 'Debug User';
}

require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/ai_handler.php';

global $pdo;

// Tampilkan info konfigurasi
echo "<h3>Info Konfigurasi:</h3>";
echo "API Key: " . (defined('OPENROUTER_API_KEY') ? 'Tersedia' : 'Tidak ditemukan') . "<br>";
echo "Base URL: " . (defined('OPENROUTER_BASE_URL') ? OPENROUTER_BASE_URL : 'Tidak didefinisikan') . "<br>";
echo "Model Default: " . (defined('OPENROUTER_DEFAULT_MODEL') ? OPENROUTER_DEFAULT_MODEL : 'Tidak didefinisikan') . "<br>";

// Coba koneksi database
echo "<h3>Pengujian Koneksi Database:</h3>";
try {
    if ($pdo) {
        echo "Koneksi database berhasil<br>";
        
        // Coba query sederhana
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM upload_files WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        echo "Jumlah file upload untuk user: " . $result['count'] . "<br>";
        
        // Coba query tabel chat_ai
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM chat_ai WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        echo "Jumlah chat dalam database untuk user: " . $result['count'] . "<br>";
    } else {
        echo "Koneksi database gagal<br>";
    }
} catch (Exception $e) {
    echo "Error saat pengujian database: " . $e->getMessage() . "<br>";
}

// Coba inisialisasi AI Handler
echo "<h3>Pengujian AI Handler:</h3>";
try {
    $aiHandler = new AIHandler();
    echo "AI Handler berhasil diinisialisasi<br>";
} catch (Exception $e) {
    echo "Error saat inisialisasi AI Handler: " . $e->getMessage() . "<br>";
}

// Coba kirim pesan dummy
echo "<h3>Pengujian Pengiriman Pesan:</h3>";
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

    echo "Jumlah file referensi ditemukan: " . count($referensi_files) . "<br>";

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

    $konteks .= "Pertanyaan pengguna: Apa itu matematika?\n";
    $konteks .= "Jawab pertanyaan ini berdasarkan materi dari file-file di atas. Jika tidak ada informasi relevan, berikan jawaban berdasarkan pengetahuan umum dan sebutkan bahwa informasi tidak ditemukan di file yang telah diupload.";

    echo "Konteks yang akan dikirim: " . substr($konteks, 0, 200) . "...<br>";

    $aiHandler = new AIHandler();
    $pesan_ai = $aiHandler->sendMessage($konteks);
    echo "Respons dari AI: " . substr($pesan_ai, 0, 200) . "...<br>";
    
    // Coba simpan ke database
    $stmt = $pdo->prepare("INSERT INTO chat_ai (user_id, pesan_pengguna, pesan_ai, topik_terkait) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$_SESSION['user_id'], 'Apa itu matematika?', $pesan_ai, json_encode([])]);
    if ($result) {
        echo "Pesan berhasil disimpan ke database<br>";
    } else {
        echo "Gagal menyimpan pesan ke database<br>";
    }
    
} catch (Exception $e) {
    echo "Error saat pengujian pengiriman pesan: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}

echo "<h3>Selesai Debug</h3>";
?>