<?php
// dashboard/pages/matematika_kelas10.php - Halaman materi matematika kelas 10

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
            <a href="?page=materi_kelas10" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Kelas 10
            </a>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Matematika Kelas 10</h2>
        <p class="text-gray-600 mb-8">Materi pelajaran matematika untuk kelas 10 SMA</p>

        <!-- Sub-topik Matematika Kelas 10 -->
        <div class="mb-10">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center">Konsep-Konsep dalam Matematika Kelas 10</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Konsep Eksponen dan Logaritma -->
                <a href="?page=matematika_kelas10&konsep=eksponen_logaritma" class="block border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                    <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Eksponen dan Logaritma</h3>
                    <p class="text-gray-600 mb-4">Memahami konsep eksponen dan logaritma serta penerapannya</p>
                    <div class="text-blue-600 font-medium inline-flex items-center">
                        Pelajari Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Konsep Persamaan dan Fungsi Kuadrat -->
                <a href="?page=matematika_kelas10&konsep=persamaan_kuadrat" class="block border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                    <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Persamaan & Fungsi Kuadrat</h3>
                    <p class="text-gray-600 mb-4">Grafik, akar-akar, dan penerapan fungsi kuadrat</p>
                    <div class="text-blue-600 font-medium inline-flex items-center">
                        Pelajari Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Konsep Sistem Persamaan Linear -->
                <a href="?page=matematika_kelas10&konsep=spl" class="block border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                    <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">SPL (Sistem Persamaan Linear)</h3>
                    <p class="text-gray-600 mb-4">Penyelesaian SPL dengan berbagai metode</p>
                    <div class="text-blue-600 font-medium inline-flex items-center">
                        Pelajari Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Konsep Trigonometri Dasar -->
                <a href="?page=matematika_kelas10&konsep=trigonometri" class="block border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                    <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Trigonometri Dasar</h3>
                    <p class="text-gray-600 mb-4">Perbandingan trigonometri dan identitas dasar</p>
                    <div class="text-blue-600 font-medium inline-flex items-center">
                        Pelajari Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Konsep Geometri Analitik -->
                <a href="?page=matematika_kelas10&konsep=geometri_analitik" class="block border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                    <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Geometri Analitik</h3>
                    <p class="text-gray-600 mb-4">Garis lurus, lingkaran, dan persamaan</p>
                    <div class="text-blue-600 font-medium inline-flex items-center">
                        Pelajari Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Konsep Statistika -->
                <a href="?page=matematika_kelas10&konsep=statistika" class="block border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                    <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Statistika</h3>
                    <p class="text-gray-600 mb-4">Ukuran pemusatan, penyebaran, dan penyajian data</p>
                    <div class="text-blue-600 font-medium inline-flex items-center">
                        Pelajari Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>

        <?php
        // Ambil parameter konsep dari URL
        $selected_konsep = $_GET['konsep'] ?? '';

        // Mapping konsep ke sub_topik yang sesuai
        $konsep_map = [
            'eksponen_logaritma' => ['Eksponen', 'Logaritma'],
            'persamaan_kuadrat' => ['Persamaan Kuadrat', 'Fungsi Kuadrat'],
            'spl' => ['Sistem Persamaan Linear', 'SPL'],
            'trigonometri' => ['Trigonometri', 'Perbandingan Trigonometri'],
            'geometri_analitik' => ['Geometri Analitik', 'Garis Lurus', 'Lingkaran'],
            'statistika' => ['Statistika', 'Ukuran Pemusatan', 'Ukuran Penyebaran']
        ];

        // Filter berdasarkan konsep jika ada
        global $pdo;
        try {
            if (!empty($selected_konsep) && isset($konsep_map[$selected_konsep])) {
                // Query untuk mencari berdasarkan sub_topik
                $placeholders = str_repeat('?,', count($konsep_map[$selected_konsep]) - 1) . '?';
                $sql = "SELECT m.*, u.full_name as uploaded_by
                        FROM materi_pelajaran m
                        LEFT JOIN users u ON m.created_by = u.id
                        WHERE m.kelas = ? AND m.mata_pelajaran = ?
                        AND m.sub_topik IN ($placeholders)
                        AND m.status = 'aktif'
                        ORDER BY m.sub_topik, m.created_at DESC";

                $search_params = array_merge(['10', 'matematika'], $konsep_map[$selected_konsep]);

                $stmt = $pdo->prepare($sql);
                $stmt->execute($search_params);
            } else {
                // Tampilkan semua jika tidak ada filter konsep
                $stmt = $pdo->prepare("
                    SELECT m.*, u.full_name as uploaded_by
                    FROM materi_pelajaran m
                    LEFT JOIN users u ON m.created_by = u.id
                    WHERE m.kelas = '10' AND m.mata_pelajaran = 'matematika' AND m.status = 'aktif'
                    ORDER BY m.sub_topik, m.created_at DESC
                ");
                $stmt->execute();
            }

            $admin_materials = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting admin materials: " . $e->getMessage());
            $admin_materials = [];
        }

        if (!empty($admin_materials)):
        ?>
        <div class="mt-10">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <?php
                    if (!empty($selected_konsep)) {
                        $konsep_names = [
                            'eksponen_logaritma' => 'Eksponen dan Logaritma',
                            'persamaan_kuadrat' => 'Persamaan & Fungsi Kuadrat',
                            'spl' => 'Sistem Persamaan Linear',
                            'trigonometri' => 'Trigonometri Dasar',
                            'geometri_analitik' => 'Geometri Analitik',
                            'statistika' => 'Statistika'
                        ];
                        echo 'Materi untuk: ' . ($konsep_names[$selected_konsep] ?? 'Topik Terpilih');
                    } else {
                        echo 'Semua Materi Tambahan dari Admin';
                    }
                    ?>
                </h3>
                <?php if (!empty($selected_konsep)): ?>
                <a href="?page=matematika_kelas10" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded">
                    Lihat Semua
                </a>
                <?php endif; ?>
            </div>

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
                            <a href="view_material.php?file=<?php echo urlencode($material['file_path']); ?>&id=<?php echo $material['id']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Pelajari Sekarang
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