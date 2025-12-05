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
    // Redirect ke halaman latihan jika ID tidak valid
    $_SESSION['error'] = "ID analisis tidak valid.";
    header('Location: ?page=exercises');
    exit;
}

// Cek apakah analisis benar-benar ada dan milik user ini
try {
    $checkStmt = $pdo->prepare("SELECT a.id FROM analisis_ai a JOIN upload_files f ON a.file_id = f.id WHERE a.id = ? AND f.user_id = ?");
    $checkStmt->execute([$analisis_id, $_SESSION['user_id']]);
    $analisis = $checkStmt->fetch();

    if (!$analisis) {
        $_SESSION['error'] = "Analisis tidak ditemukan atau bukan milik Anda.";
        header('Location: ?page=exercises');
        exit;
    }
} catch (Exception $e) {
    error_log("Error checking analisis ownership: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat memverifikasi akses analisis.";
    header('Location: ?page=exercises');
    exit;
}

// Tambahkan penanganan error untuk query database
try {
    $stmt = $pdo->prepare("
        SELECT bs.*, a.ringkasan
        FROM bank_soal_ai bs
        JOIN analisis_ai a ON bs.analisis_id = a.id
        WHERE bs.analisis_id = ? AND a.user_id = ?
        ORDER BY bs.created_at ASC
    ");
    $stmt->execute([$analisis_id, $_SESSION['user_id']]);
    $questions = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting exercise detail: " . $e->getMessage() . " - Analisis ID: " . $analisis_id . " - User ID: " . $_SESSION['user_id']);
    $questions = [];
    // Jika terjadi error, kembali ke halaman latihan dengan pesan error
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil soal latihan.";
    header('Location: ?page=exercises');
    exit;
}

// Ambil soal-soal untuk analisis tertentu
try {
    $stmt = $pdo->prepare("
        SELECT bs.*, a.ringkasan
        FROM bank_soal_ai bs
        JOIN analisis_ai a ON bs.analisis_id = a.id
        WHERE bs.analisis_id = ? AND a.user_id = ?
        ORDER BY bs.created_at ASC
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

    // Ambil kunci jawaban untuk semua soal yang dikerjakan
    $placeholders = implode(',', array_fill(0, $total_soal, '?'));
    $stmt_kunci = $pdo->prepare("SELECT id, kunci_jawaban FROM bank_soal_ai WHERE id IN ($placeholders)");
    $stmt_kunci->execute($soal_ids);
    $kunci_jawaban_map = [];
    while ($row = $stmt_kunci->fetch(PDO::FETCH_ASSOC)) {
        $kunci_jawaban_map[$row['id']] = $row['kunci_jawaban'];
    }

    // Hapus riwayat pengerjaan sebelumnya untuk analisis_id ini (opsional, tergantung kebutuhan)
    // $delete_prev_results_stmt = $pdo->prepare("DELETE FROM hasil_soal_user WHERE user_id = ? AND soal_id IN (SELECT id FROM bank_soal_ai WHERE analisis_id = ?)");
    // $delete_prev_results_stmt->execute([$_SESSION['user_id'], $analisis_id]);

    foreach ($soal_ids as $soal_id) {
        $jawaban = $jawaban_pengguna[$soal_id] ?? '';
        $kunci_jawaban = $kunci_jawaban_map[$soal_id] ?? null;
        $status_jawaban = 'tidak_dijawab';
        $nilai_soal = 0; // Nilai per soal, bisa 100 atau 0

        if (!empty($jawaban)) {
            if (strtoupper($jawaban) === strtoupper($kunci_jawaban)) {
                $status_jawaban = 'benar';
                $total_benar++;
                $nilai_soal = 100;
            } else {
                $status_jawaban = 'salah';
                $nilai_soal = 0;
            }
        }

        // Simpan jawaban ke database
        // Cek apakah sudah ada hasil untuk soal ini oleh user ini
        $check_stmt = $pdo->prepare("SELECT id FROM hasil_soal_user WHERE user_id = ? AND soal_id = ?");
        $check_stmt->execute([$_SESSION['user_id'], $soal_id]);
        $existing_result = $check_stmt->fetch();

        if ($existing_result) {
            // Update jika sudah ada
            $update_stmt = $pdo->prepare("
                UPDATE hasil_soal_user SET jawaban_user = ?, status_jawaban = ?, nilai = ?, created_at = NOW()
                WHERE id = ?
            ");
            $update_stmt->execute([$jawaban, $status_jawaban, $nilai_soal, $existing_result['id']]);
        } else {
            // Insert jika belum ada
            $insert_stmt = $pdo->prepare("
                INSERT INTO hasil_soal_user (user_id, soal_id, jawaban_user, status_jawaban, nilai, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $insert_stmt->execute([$_SESSION['user_id'], $soal_id, $jawaban, $status_jawaban, $nilai_soal]);
        }
    }

    // Hitung nilai total
    $nilai_akhir = $total_soal > 0 ? ($total_benar / $total_soal) * 100 : 0;

    // Redirect ke halaman hasil
    $_SESSION['exercise_result'] = [
        'total_soal' => $total_soal,
        'total_benar' => $total_benar,
        'nilai_akhir' => round($nilai_akhir, 2)
    ];
    header('Location: ?page=exercise_detail&analisis_id=' . $analisis_id . '&result=true');
    exit;
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Mengerjakan Soal Latihan</h2>

        <?php
        // Tampilkan hasil jika parameter result=true ada di URL
        if (isset($_GET['result']) && $_GET['result'] == 'true' && isset($_SESSION['exercise_result'])):
            $result = $_SESSION['exercise_result'];
            unset($_SESSION['exercise_result']); // Hapus session setelah ditampilkan

            // Ambil kembali detail jawaban untuk review
            $soal_ids = array_column($questions, 'id');
            $placeholders = implode(',', array_fill(0, count($soal_ids), '?'));
            $review_stmt = $pdo->prepare("
                SELECT h.soal_id, h.jawaban_user, h.status_jawaban, b.soal, b.pilihan_a, b.pilihan_b, b.pilihan_c, b.pilihan_d, b.kunci_jawaban, b.pembahasan
                FROM hasil_soal_user h
                JOIN bank_soal_ai b ON h.soal_id = b.id
                WHERE h.user_id = ? AND h.soal_id IN ($placeholders)
                ORDER BY b.id
            ");
            $review_stmt->execute(array_merge([$_SESSION['user_id']], $soal_ids));
            $review_data = $review_stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        ?>
            <!-- Tampilan Hasil Latihan -->
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Hasil Latihan</h2>
                <p class="text-lg text-gray-600 mb-6">Berikut adalah ringkasan pengerjaan soal Anda.</p>

                <div class="bg-gradient-to-r from-blue-100 to-purple-100 border-2 border-blue-200 rounded-xl p-8 max-w-md mx-auto">
                    <p class="text-xl text-gray-700">Nilai Akhir Anda:</p>
                    <p class="text-6xl font-bold text-blue-600 my-4"><?php echo $result['nilai_akhir']; ?></p>
                    <p class="text-lg font-medium text-gray-800">
                        <?php echo $result['total_benar']; ?> dari <?php echo $result['total_soal']; ?> soal dijawab dengan benar.
                    </p>
                    <div class="mt-4">
                        <div class="inline-block bg-white px-4 py-2 rounded-lg shadow">
                            <p class="font-semibold text-green-600">Benar: <?php echo $result['total_benar']; ?></p>
                        </div>
                        <div class="inline-block bg-white px-4 py-2 rounded-lg shadow ml-2">
                            <p class="font-semibold text-red-600">Salah: <?php echo $result['total_soal'] - $result['total_benar']; ?></p>
                        </div>
                        <div class="mt-3">
                            <p class="text-lg font-semibold text-gray-700">Persentase:
                                <span class="<?php echo $result['nilai_akhir'] >= 75 ? 'text-green-600' : ($result['nilai_akhir'] >= 50 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                    <?php echo round(($result['total_benar'] / $result['total_soal']) * 100, 1); ?>%
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <a href="?page=exercises" class="mt-8 inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                    Kembali ke Daftar Latihan
                </a>
            </div>

            <!-- Review Jawaban -->
            <div class="mt-12">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Review Jawaban</h3>
                <div class="space-y-6">
                <?php foreach ($questions as $index => $question):
                    $review = $review_data[$question['id']] ?? null;
                    if (!$review) continue;

                    $user_answer = $review['jawaban_user'];
                    $correct_answer = $review['kunci_jawaban'];
                    $status = $review['status_jawaban'];
                ?>
                    <div class="border rounded-xl p-6 <?php echo $status === 'benar' ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50'; ?>">
                        <div class="flex items-start mb-4">
                            <div class="bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 flex-shrink-0">
                                <?php echo $index + 1; ?>
                            </div>
                            <p class="text-gray-800 font-semibold flex-1"><?php echo escape($question['soal']); ?></p>
                        </div>
                        
                        <div class="space-y-2 ml-11">
                            <?php
                            $options = ['a' => 'pilihan_a', 'b' => 'pilihan_b', 'c' => 'pilihan_c', 'd' => 'pilihan_d'];
                            foreach ($options as $key => $col):
                                $is_user_answer = (strtoupper($user_answer) === strtoupper($key));
                                $is_correct_answer = (strtoupper($correct_answer) === strtoupper($key));
                                $option_class = 'border-gray-300';
                                if ($is_correct_answer) $option_class = 'border-green-500 bg-green-100 font-bold';
                                if ($is_user_answer && !$is_correct_answer) $option_class = 'border-red-500 bg-red-100';
                            ?>
                                <div class="flex items-center p-2 border rounded-lg <?php echo $option_class; ?>">
                                    <?php if ($is_user_answer && !$is_correct_answer): ?>
                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    <?php elseif ($is_correct_answer): ?>
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <?php endif; ?>
                                    <span class="text-gray-800"><?php echo strtoupper($key); ?>. <?php echo escape($question[$col]); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($review['pembahasan'])): ?>
                        <div class="mt-4 ml-11 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-1">Pembahasan:</h4>
                            <p class="text-sm text-blue-900 whitespace-pre-line"><?php echo escape($review['pembahasan']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>

        <?php elseif (!empty($questions)): ?>
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
                        
                        <div class="mt-4 space-y-2">
                            <?php
                            $options = [
                                'a' => $question['pilihan_a'],
                                'b' => $question['pilihan_b'],
                                'c' => $question['pilihan_c'],
                                'd' => $question['pilihan_d']
                            ];
                            foreach ($options as $key => $value):
                                if (!empty($value)):
                            ?>
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
                                    <input type="radio" name="jawaban[<?php echo $question['id']; ?>]" value="<?php echo $key; ?>" class="form-radio h-5 w-5 text-blue-600" required>
                                    <span class="ml-3 text-gray-700"><?php echo strtoupper($key); ?>. <?php echo escape($value); ?></span>
                                </label>
                            <?php
                                endif;
                            endforeach;
                            ?>
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