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

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <!-- Header Section -->
        <div class="mb-10 text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 w-16 h-16 rounded-full flex items-center justify-center mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-800">Latihan Soal</h2>
            </div>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Kerjakan soal-soal yang di-generate berdasarkan materi yang telah kamu pelajari. Sistem akan memeriksa jawabanmu dan memberikan pembahasan.
            </p>
        </div>

        <!-- Achievement Badges Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-r from-yellow-100 to-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center">
                <div class="bg-yellow-500 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-yellow-700">Soal Dikerjakan</p>
                    <p class="text-xl font-bold text-gray-800">
                        <?php echo count($exercise_history); ?>
                    </p>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-100 to-blue-50 border border-blue-200 rounded-xl p-4 flex items-center">
                <div class="bg-blue-500 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-blue-700">Nilai Rata-rata</p>
                    <p class="text-xl font-bold text-gray-800">
                        <?php
                        $avg_score = 0;
                        if (!empty($exercise_history)) {
                            $total = 0;
                            foreach ($exercise_history as $history) {
                                $total += $history['nilai'];
                            }
                            $avg_score = round($total / count($exercise_history), 1);
                        }
                        echo $avg_score;
                        ?>%
                    </p>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-100 to-green-50 border border-green-200 rounded-xl p-4 flex items-center">
                <div class="bg-green-500 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-green-700">Latihan Terakhir</p>
                    <p class="text-xl font-bold text-gray-800">
                        <?php
                        if (!empty($exercise_history)) {
                            echo date('d M', strtotime($exercise_history[0]['created_at']));
                        } else {
                            echo 'Belum';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="mb-8 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-200">
            <div class="flex flex-wrap gap-6 items-center">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    <label for="subject_filter" class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                </div>
                <select id="subject_filter" class="border border-gray-300 rounded-lg shadow-sm py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Semua Mata Pelajaran</option>
                    <option value="matematika">Matematika</option>
                    <option value="fisika">Fisika</option>
                    <option value="kimia">Kimia</option>
                    <option value="biologi">Biologi</option>
                </select>

                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <label for="level_filter" class="block text-sm font-medium text-gray-700">Tingkat Kesulitan</label>
                </div>
                <select id="level_filter" class="border border-gray-300 rounded-lg shadow-sm py-2 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white">
                    <option value="">Semua Tingkat</option>
                    <option value="mudah">Mudah</option>
                    <option value="sedang">Sedang</option>
                    <option value="sulit">Sulit</option>
                </select>
            </div>
        </div>

        <!-- Instructions Section -->
        <div class="mb-8 bg-blue-50 rounded-xl p-5 border border-blue-200 flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h4 class="font-semibold text-blue-800 mb-1">Cara Mengerjakan Soal:</h4>
                <ul class="text-blue-700 text-sm list-disc pl-5 space-y-1">
                    <li>Pilih kelompok soal yang ingin kamu kerjakan</li>
                    <li>Klik tombol "Kerjakan" pada kelompok soal yang dipilih</li>
                    <li>Setiap jawaban akan langsung diperiksa dan diberi pembahasan</li>
                </ul>
            </div>
        </div>

        <!-- Soal Section -->
        <?php if (!empty($questions)): ?>
            <div class="space-y-6">
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
                    <div class="border-2 border-gray-200 rounded-xl p-6 mb-6 hover:border-blue-300 transition-all duration-300 shadow-sm hover:shadow-md">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h3 class="text-xl font-bold text-gray-800"><?php echo escape($topik); ?></h3>
                                    <?php if ($soal_per_topik[0]['tingkat_kesulitan'] === 'mudah'): ?>
                                        <span class="ml-3 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Mudah</span>
                                    <?php elseif ($soal_per_topik[0]['tingkat_kesulitan'] === 'sedang'): ?>
                                        <span class="ml-3 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">Sedang</span>
                                    <?php else: ?>
                                        <span class="ml-3 px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Sulit</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-600">
                                    <span class="font-semibold"><?php echo count($soal_per_topik); ?></span> soal tersedia •
                                    Rata-rata nilai:
                                    <span class="font-semibold">
                                        <?php
                                        $total_score = 0;
                                        $count_score = 0;
                                        foreach ($exercise_history as $history) {
                                            foreach ($soal_per_topik as $soal) {
                                                if ($history['soal_id'] == $soal['id']) {
                                                    $total_score += $history['nilai'];
                                                    $count_score++;
                                                }
                                            }
                                        }
                                        echo $count_score > 0 ? round($total_score / $count_score, 1) : '0';
                                        ?>%
                                    </span>
                                </p>
                            </div>
                            <button onclick="startExercise(<?php echo $soal_per_topik[0]['analisis_id']; ?>, <?php echo count($soal_per_topik); ?>)" class="mt-3 md:mt-0 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                                <span class="flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Kerjakan Soal
                                </span>
                            </button>
                        </div>

                        <!-- Pratinjau soal -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-100">
                            <div class="flex items-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <p class="font-medium text-gray-800">Contoh Soal:</p>
                            </div>
                            <p class="text-gray-700 mt-1"><?php echo strlen($soal_per_topik[0]['soal']) > 100 ? substr($soal_per_topik[0]['soal'], 0, 100) . '...' : $soal_per_topik[0]['soal']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <div class="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum Ada Soal Tersedia</h3>
                <p class="text-gray-600 max-w-md mx-auto mb-6">
                    Silakan unggah file materi terlebih dahulu untuk menghasilkan soal latihan otomatis.
                </p>
                <a href="?page=upload" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg">
                    <span class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Unggah File Sekarang
                    </span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Riwayat Latihan -->
        <div class="mt-12">
            <div class="flex items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-800">Riwayat Latihan Terakhir</h3>
            </div>

            <?php if (!empty($exercise_history)): ?>
                <div class="overflow-hidden rounded-xl border border-gray-200">
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
                            <?php foreach ($exercise_history as $history): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo strlen($history['soal']) > 50 ? substr($history['soal'], 0, 50) . '...' : $history['soal']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($history['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                        <?php
                                        echo $history['nilai'] >= 75 ? 'bg-green-100 text-green-800' :
                                            ($history['nilai'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                        ?>">
                                        <?php echo $history['nilai']; ?>%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                        echo $history['status_jawaban'] === 'benar' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        ?>">
                                        <?php echo escape($history['status_jawaban']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 rounded-xl p-8 text-center border border-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-600">Belum ada riwayat latihan</p>
                    <p class="text-sm text-gray-500 mt-1">Kerjakan soal pertama kamu sekarang!</p>
                </div>
            <?php endif; ?>
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