<?php
// dashboard/pages/exercise_detail.php - Halaman mengerjakan soal latihan

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

global $pdo;

// Ambil ID analisis dari parameter URL
$analisis_id = isset($_GET['analisis_id']) ? (int)$_GET['analisis_id'] : 0;

if ($analisis_id <= 0) {
    header('Location: ?page=exercises');
    exit;
}

// Ambil soal-soal untuk analisis tertentu
try {
    $stmt = $pdo->prepare("
        SELECT bs.*, a.ringkasan
        FROM bank_soal_ai bs
        JOIN analisis_ai a ON bs.analisis_id = a.id
        WHERE bs.analisis_id = ? AND bs.user_id = ?
        ORDER BY bs.created_at DESC
    ");
    $stmt->execute([$analisis_id, $_SESSION['user_id']]);
    $questions = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting exercise detail: " . $e->getMessage());
    $questions = [];
}

// Proses submit jawaban jika ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_answers'])) {
    $soal_ids = $_POST['soal_ids'] ?? [];
    $jawaban_pengguna = $_POST['jawaban'] ?? [];

    $total_benar = 0;
    $total_soal = count($soal_ids);

    foreach ($soal_ids as $index => $soal_id) {
        $jawaban = $jawaban_pengguna[$soal_id] ?? '';

        // Simpan jawaban ke database
        $stmt = $pdo->prepare("
            INSERT INTO hasil_soal_user (user_id, soal_id, jawaban_user, status_jawaban, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        // Untuk saat ini, kita simpan jawaban sebagai 'dijawab' karena kita tidak menyimpan kunci jawaban
        $status = !empty($jawaban) ? 'dijawab' : 'tidak_dijawab';
        $stmt->execute([$_SESSION['user_id'], $soal_id, $jawaban, $status]);

        // Hitung jawaban benar (dalam implementasi nyata, kita bandingkan dengan kunci jawaban)
        if (!empty($jawaban)) {
            $total_benar++;
        }
    }

    // Hitung nilai
    $nilai = $total_soal > 0 ? ($total_benar / $total_soal) * 100 : 0;

    // Redirect dengan pesan
    $_SESSION['success'] = "Latihan selesai! Nilai Anda: " . round($nilai, 2) . "/100";
    header('Location: ?page=exercises');
    exit;
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Mengerjakan Soal Latihan</h2>

        <?php if (!empty($questions)): ?>
            <form method="post" id="exerciseForm">
                <input type="hidden" name="submit_answers" value="1">
                
                <?php foreach ($questions as $index => $question): ?>
                    <div class="border border-gray-200 rounded-xl p-6 mb-6 bg-gray-50">
                        <div class="flex items-start mb-4">
                            <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0">
                                <?php echo $index + 1; ?>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Soal <?php echo $index + 1; ?></h3>
                        </div>
                        
                        <div class="bg-white p-4 rounded-lg mb-4">
                            <p class="text-gray-700 whitespace-pre-line"><?php echo escape($question['soal']); ?></p>
                        </div>
                        
                        <div class="mt-4">
                            <label for="jawaban_<?php echo $question['id']; ?>" class="block text-sm font-medium text-gray-700 mb-2">
                                Jawaban Anda:
                            </label>
                            <textarea 
                                id="jawaban_<?php echo $question['id']; ?>" 
                                name="jawaban[<?php echo $question['id']; ?>]" 
                                rows="4" 
                                class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Tulis jawaban Anda di sini..."
                                required
                            ></textarea>
                        </div>
                        
                        <input type="hidden" name="soal_ids[]" value="<?php echo $question['id']; ?>">
                    </div>
                <?php endforeach; ?>
                
                <div class="flex justify-center mt-8">
                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-8 py-4 rounded-xl shadow-lg font-bold text-lg transition-all duration-300 transform hover:scale-[1.02]"
                    >
                        <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Selesaikan Latihan
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mt-4">Belum Ada Soal</h3>
                <p class="text-gray-600 mt-2">Tidak ada soal latihan tersedia untuk topik ini.</p>
                <a href="?page=exercises" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium">
                    Kembali ke Latihan Soal
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>