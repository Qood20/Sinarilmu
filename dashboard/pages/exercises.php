<?php
// dashboard/pages/exercises.php - Halaman latihan soal

require_once dirname(__DIR__, 2) . '/includes/functions.php';

global $pdo;

// Ambil soal-soal berdasarkan topik yang telah di-generate dari file pengguna
try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT a.topik_terkait
        FROM analisis_ai a
        JOIN upload_files f ON a.file_id = f.id
        WHERE f.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $topics = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Ambil semua soal yang terkait dengan file pengguna
    $stmt = $pdo->prepare("
        SELECT bs.*, a.topik_terkait
        FROM bank_soal_ai bs
        JOIN analisis_ai a ON bs.analisis_id = a.id
        JOIN upload_files f ON a.file_id = f.id
        WHERE f.user_id = ?
        ORDER BY bs.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $questions = $stmt->fetchAll();

    // Ambil riwayat latihan pengguna
    $stmt = $pdo->prepare("
        SELECT hs.*, bs.soal
        FROM hasil_soal_user hs
        JOIN bank_soal_ai bs ON hs.soal_id = bs.id
        WHERE hs.user_id = ?
        ORDER BY hs.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $exercise_history = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting exercise data: " . $e->getMessage());
    $topics = [];
    $questions = [];
    $exercise_history = [];
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Latihan Soal</h2>
        
        <div class="mb-6">
            <p class="text-gray-600">
                Kerjakan soal-soal yang di-generate berdasarkan materi yang telah kamu pelajari. Sistem akan memeriksa jawabanmu dan memberikan pembahasan.
            </p>
        </div>
        
        <!-- Filter Soal -->
        <div class="mb-6 flex flex-wrap gap-4">
            <div>
                <label for="subject_filter" class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                <select id="subject_filter" class="border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option>Semua Mata Pelajaran</option>
                    <option>Matematika</option>
                    <option>Fisika</option>
                    <option>Kimia</option>
                    <option>Biologi</option>
                </select>
            </div>
            
            <div>
                <label for="level_filter" class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kesulitan</label>
                <select id="level_filter" class="border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option>Semua Tingkat</option>
                    <option>Mudah</option>
                    <option>Sedang</option>
                    <option>Sulit</option>
                </select>
            </div>
        </div>
        
        <!-- Daftar Soal -->
        <div class="space-y-6">
            <?php if (!empty($questions)): ?>
                <?php
                // Kelompokkan soal berdasarkan topik
                $grouped_questions = [];
                foreach ($questions as $question) {
                    $topik = $question['topik_terkait'] ?? 'Topik Tidak Diketahui';
                    if (!isset($grouped_questions[$topik])) {
                        $grouped_questions[$topik] = [];
                    }
                    $grouped_questions[$topik][] = $question;
                }
                ?>

                <?php foreach ($grouped_questions as $topik => $soal_per_topik): ?>
                    <div class="border border-gray-200 rounded-lg p-6 mb-4">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900"><?php echo escape($topik); ?></h3>
                                <p class="text-sm text-gray-600 mt-1"><?php echo count($soal_per_topik); ?> soal • Tingkat: <?php echo escape($soal_per_topik[0]['tingkat_kesulitan'] ?? 'Tidak Diketahui'); ?></p>
                            </div>
                            <button onclick="startExercise(<?php echo $soal_per_topik[0]['analisis_id']; ?>, <?php echo count($soal_per_topik); ?>)" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Kerjakan
                            </button>
                        </div>

                        <!-- Pratinjau soal -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="font-medium text-gray-800">Contoh Soal:</p>
                            <p class="text-gray-700 mt-1"><?php echo strlen($soal_per_topik[0]['soal']) > 100 ? substr($soal_per_topik[0]['soal'], 0, 100) . '...' : $soal_per_topik[0]['soal']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-gray-500">Belum ada soal yang tersedia.</p>
                    <p class="text-sm text-gray-400 mt-2">Silakan unggah file untuk menghasilkan soal latihan otomatis.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Riwayat Latihan -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Latihan</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Soal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($exercise_history)): ?>
                            <?php foreach ($exercise_history as $history): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo strlen($history['soal']) > 50 ? substr($history['soal'], 0, 50) . '...' : $history['soal']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($history['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                        echo $history['nilai'] >= 75 ? 'bg-green-100 text-green-800' :
                                            ($history['nilai'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                        ?>">
                                        <?php echo $history['nilai']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo escape($history['status_jawaban']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Belum ada riwayat latihan
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk memulai latihan soal
    function startExercise(analisisId, totalSoal) {
        // Redirect ke halaman khusus untuk mengerjakan soal
        window.location.href = `?page=exercise_detail&analisis_id=\${analisisId}`;
    }
</script>