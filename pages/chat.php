<?php
// pages/chat.php - Halaman chat bot AI

// Cek apakah sesi sudah aktif sebelum memulai sesi baru
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

require_once '../includes/functions.php';
require_once '../includes/ai_handler.php';

global $pdo;

// Proses pengiriman pesan jika formulir dikirim
$chat_history = [];

// Validasi koneksi database
if ($pdo === null) {
    die("Koneksi database tidak tersedia.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan_pengguna'])) {
    $pesan_pengguna = trim($_POST['pesan_pengguna']);
    if (!empty($pesan_pengguna)) {
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

            // Gunakan AI untuk menghasilkan jawaban
            $aiHandler = new AIHandler();
            try {
                $aiResponse = $aiHandler->analyzeText($konteks);
            } catch (Exception $e) {
                // Jika terjadi error otentikasi atau koneksi API, gunakan fallback
                if (strpos($e->getMessage(), 'HTTP error: 401') !== false ||
                    strpos($e->getMessage(), '401') !== false ||
                    strpos($e->getMessage(), 'API') !== false) {
                    // Buat respons fallback dalam format yang sama seperti respons API normal
                    $fallbackResponse = $aiHandler->getFallbackResponse($konteks);
                    $aiResponse = [
                        'candidates' => [
                            [
                                'content' => [
                                    'parts' => [
                                        [
                                            'text' => $fallbackResponse
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                } else {
                    // Jika error selain otentikasi API, lemparkan kembali
                    throw $e;
                }
            }

            if (!isset($aiResponse['error'])) {
                $jawaban_ai = '';
                if (isset($aiResponse['candidates']) && count($aiResponse['candidates']) > 0) {
                    $candidate = $aiResponse['candidates'][0];
                    if (isset($candidate['content']['parts']) && count($candidate['content']['parts']) > 0) {
                        $jawaban_ai = $candidate['content']['parts'][0]['text'];
                    }
                }

                // Simpan percakapan ke database
                $stmt = $pdo->prepare("
                    INSERT INTO chat_ai (user_id, pesan_pengguna, pesan_ai, topik_terkait)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $pesan_pengguna,
                    $jawaban_ai,
                    json_encode([]) // topik_terkait
                ]);

                // Ambil riwayat percakapan
                $stmt = $pdo->prepare("
                    SELECT pesan_pengguna, pesan_ai, created_at
                    FROM chat_ai
                    WHERE user_id = ?
                    ORDER BY created_at DESC
                    LIMIT 10
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $chat_history = $stmt->fetchAll();
            } else {
                // Jika AI gagal, buat jawaban default
                $error_msg = $aiResponse['error'];

                // Tangani kesalahan berdasarkan jenisnya
                if (strpos($error_msg, 'timeout') !== false || strpos($error_msg, 'cURL error') !== false) {
                    $pesan_ai = "Maaf, koneksi ke server AI sedang bermasalah atau timeout. Silakan coba lagi nanti.";
                } else {
                    $pesan_ai = "Maaf, terjadi kesalahan saat memproses pertanyaan Anda: " . htmlspecialchars($error_msg);
                }

                // Simpan percakapan ke database
                $stmt = $pdo->prepare("
                    INSERT INTO chat_ai (user_id, pesan_pengguna, pesan_ai, topik_terkait)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $pesan_pengguna,
                    $pesan_ai,
                    json_encode([])
                ]);

                // Ambil riwayat percakapan
                $stmt = $pdo->prepare("
                    SELECT pesan_pengguna, pesan_ai, created_at
                    FROM chat_ai
                    WHERE user_id = ?
                    ORDER BY created_at DESC
                    LIMIT 10
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $chat_history = $stmt->fetchAll();
            }
        } catch (Exception $e) {
            error_log("Chat AI Error: " . $e->getMessage());

            // Tangani kesalahan berdasarkan jenisnya
            $pesan_error = $e->getMessage();
            if (strpos($pesan_error, 'timeout') !== false || strpos($pesan_error, 'cURL error') !== false) {
                $pesan_ai = "Maaf, koneksi ke server AI sedang bermasalah atau timeout. Silakan coba lagi nanti.";
            } else {
                $pesan_ai = "Maaf, terjadi kesalahan sistem saat memproses pertanyaan Anda. Silakan coba lagi. (" . htmlspecialchars($pesan_error) . ")";
            }

            // Simpan percakapan error ke database
            $stmt = $pdo->prepare("
                INSERT INTO chat_ai (user_id, pesan_pengguna, pesan_ai, topik_terkait)
                VALUES (?, ?, ?, ?)
            ");
            try {
                $stmt->execute([
                    $_SESSION['user_id'],
                    $pesan_pengguna,
                    $pesan_ai,
                    json_encode([])
                ]);
            } catch (PDOException $db_e) {
                error_log("Gagal menyimpan pesan error ke database: " . $db_e->getMessage());
            }

            // Ambil riwayat percakapan
            $stmt = $pdo->prepare("
                SELECT pesan_pengguna, pesan_ai, created_at
                FROM chat_ai
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $chat_history = $stmt->fetchAll();
        }
    }
} else {
    // Jika bukan POST atau pesan kosong, ambil riwayat
    $stmt = $pdo->prepare("
        SELECT pesan_pengguna, pesan_ai, created_at
        FROM chat_ai
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $chat_history = $stmt->fetchAll();
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Tanya Sinar - AI Asisten Belajar</h2>

        <!-- Area percakapan -->
        <div id="chatContainer" class="bg-gray-50 rounded-lg p-6 mb-6 h-96 overflow-y-auto">
            <?php if (empty($chat_history)): ?>
                <div class="flex flex-col items-center justify-center h-full text-center text-gray-500">
                    <svg class="w-16 h-16 mb-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    <p class="text-lg">Mulai percakapan dengan AI Sinar Ilmu</p>
                    <p class="text-sm mt-2">Tanyakan apa saja tentang materi yang telah kamu upload</p>
                </div>
            <?php else: ?>
                <?php foreach (array_reverse($chat_history) as $chat): ?>
                    <!-- Pesan Pengguna -->
                    <div class="mb-4 flex justify-end">
                        <div class="bg-blue-500 text-white rounded-lg p-4 max-w-3/4">
                            <p><?php echo nl2br(escape($chat['pesan_pengguna'])); ?></p>
                            <p class="text-xs opacity-75 mt-1"><?php echo date('H:i', strtotime($chat['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <!-- Pesan AI -->
                    <div class="mb-6 flex justify-start">
                        <div class="bg-green-100 text-gray-800 rounded-lg p-4 max-w-3/4">
                            <div class="flex items-center mb-1">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                                <span class="font-semibold">Sinar AI</span>
                            </div>
                            <p class="whitespace-pre-line"><?php echo nl2br(escape($chat['pesan_ai'])); ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo date('H:i', strtotime($chat['created_at'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Form kirim pesan -->
        <form method="post" class="flex gap-4">
            <input 
                type="text" 
                name="pesan_pengguna" 
                placeholder="Tanyakan sesuatu tentang materi yang telah kamu upload..." 
                class="flex-1 border-2 border-gray-300 rounded-xl py-4 px-6 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
            >
            <button 
                type="submit" 
                class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-8 py-4 rounded-xl shadow-lg font-bold transition-all duration-300 transform hover:scale-[1.02]"
            >
                <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                </svg>
                Kirim
            </button>
        </form>
    </div>
</div>

<script>
    // Auto scroll ke pesan terbaru
    document.addEventListener('DOMContentLoaded', function() {
        const chatContainer = document.getElementById('chatContainer');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    });
</script>