<?php
// dashboard/pages/matematika_kelas12.php - Halaman materi matematika kelas 12

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
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="flex items-center mb-6">
            <a href="?page=materi_kelas12" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Kelas 12
            </a>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Matematika Kelas 12</h2>
        <p class="text-gray-600 mb-8">Materi pelajaran matematika untuk kelas 12 SMA</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Materi 1 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Matriks dan Determinan</h3>
                <p class="text-gray-600 mb-4">Operasi matriks, determinan, dan invers matriks</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 2 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Vektor</h3>
                <p class="text-gray-600 mb-4">Operasi vektor dalam bidang dan ruang</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 3 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Barisan dan Deret</h3>
                <p class="text-gray-600 mb-4">Barisan aritmetika dan geometri serta deret tak hingga</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 4 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Limit Fungsi Trigonometri</h3>
                <p class="text-gray-600 mb-4">Konsep limit dan aplikasi pada fungsi trigonometri</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 5 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Turunan Fungsi</h3>
                <p class="text-gray-600 mb-4">Konsep turunan dan aplikasi dalam permasalahan nyata</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 6 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Integral</h3>
                <p class="text-gray-600 mb-4">Integral tak tentu dan tentu serta aplikasinya</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Admin Uploaded Materials Section for Math -->
        <?php
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u.full_name as uploaded_by
                FROM materi_pelajaran m
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.kelas = '12' AND m.mata_pelajaran = 'matematika' AND m.status = 'aktif'
                ORDER BY m.sub_topik, m.created_at DESC
            ");
            $stmt->execute();
            $admin_materials = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting admin materials: " . $e->getMessage());
            $admin_materials = [];
        }

        if (!empty($admin_materials)):
        ?>
        <div class="mt-10">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Materi Tambahan dari Admin
            </h3>

            <?php
            // Group materials by sub_topik
            $grouped_materials = [];
            foreach ($admin_materials as $material) {
                $sub_topik = $material['sub_topik'] ?: 'Umum';

                if (!isset($grouped_materials[$sub_topik])) {
                    $grouped_materials[$sub_topik] = [];
                }

                $grouped_materials[$sub_topik][] = $material;
            }
            ?>

            <?php foreach ($grouped_materials as $sub_topik => $materials): ?>
            <?php if (count($materials) > 0): ?>
            <div class="mb-6">
                <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.874-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <?php if ($sub_topik !== 'Umum'): ?>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                            <?php echo escape($sub_topik); ?>
                        </span>
                    <?php else: ?>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                            Materi Umum
                        </span>
                    <?php endif; ?>
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($materials as $material): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                <?php if (in_array(strtolower(pathinfo($material['original_name'], PATHINFO_EXTENSION)), ['pdf'])): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                <?php elseif (in_array(strtolower(pathinfo($material['original_name'], PATHINFO_EXTENSION)), ['doc', 'docx'])): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-800"><?php echo escape($material['judul']); ?></h4>
                                <p class="text-xs text-gray-600 mt-1"><?php echo format_file_size($material['file_size']); ?> • <?php echo date('d M', strtotime($material['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="mt-3 flex justify-between items-center">
                            <a href="<?php echo $material['file_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh
                            </a>
                        </div>
                        <?php if (!empty($material['deskripsi'])): ?>
                        <p class="text-xs text-gray-600 mt-2"><?php echo escape(substr($material['deskripsi'], 0, 80)); ?><?php echo strlen($material['deskripsi']) > 80 ? '...' : ''; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="mt-10 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tips Belajar:
            </h3>
            <ul class="list-disc pl-5 text-blue-700 space-y-2">
                <li>Pelajari materi secara bertahap dan lakukan latihan soal secara rutin</li>
                <li>Gunakan fitur tanya Sinar jika ada konsep yang kurang dipahami</li>
                <li>Berlatihlah dengan berbagai jenis soal untuk memperkuat pemahaman</li>
            </ul>
        </div>
    </div>
</div>