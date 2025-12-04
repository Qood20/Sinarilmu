<?php
// dashboard/pages/materi_kelas12.php - Halaman utama materi kelas 12

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
            <a href="?page=analisis_materi" class="text-purple-600 hover:text-purple-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Semua Materi
            </a>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Materi Kelas 12</h2>
        <p class="text-gray-600 mb-8">Pilih mata pelajaran untuk kelas 12 SMA</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Matematika -->
            <a href="?page=matematika_kelas12" class="block bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-blue-200 transform hover:scale-105 transition-transform">
                <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 4h.01M12 4h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Matematika</h3>
                <p class="text-gray-600">Materi kelas 12</p>
            </a>

            <!-- Fisika -->
            <a href="?page=fisika_kelas12" class="block bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-green-200 transform hover:scale-105 transition-transform">
                <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.874-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Fisika</h3>
                <p class="text-gray-600">Materi kelas 12</p>
            </a>

            <!-- Kimia -->
            <a href="?page=kimia_kelas12" class="block bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-purple-200 transform hover:scale-105 transition-transform">
                <div class="bg-purple-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2V7a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Kimia</h3>
                <p class="text-gray-600">Materi kelas 12</p>
            </a>

            <!-- Biologi -->
            <a href="?page=biologi_kelas12" class="block bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-yellow-200 transform hover:scale-105 transition-transform">
                <div class="bg-yellow-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Biologi</h3>
                <p class="text-gray-600">Materi kelas 12</p>
            </a>
        </div>

        <!-- Tampilkan materi dari admin berdasarkan kelas 12 -->
        <?php
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u.full_name as uploaded_by
                FROM materi_pelajaran m
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.kelas = '12' AND m.status = 'aktif'
                ORDER BY m.mata_pelajaran, m.sub_topik, m.created_at DESC
            ");
            $stmt->execute();
            $admin_materials = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting admin materials for class 12: " . $e->getMessage());
            $admin_materials = [];
        }

        if (!empty($admin_materials)):
        ?>
        <div class="mt-10">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Materi dari Admin untuk Kelas 12</h3>
            
            <?php
            // Kelompokkan materi berdasarkan mata pelajaran dan sub_topik
            $grouped_materials = [];
            foreach ($admin_materials as $material) {
                $pelajaran = $material['mata_pelajaran'];
                $sub_topik = $material['sub_topik'] ?: 'Umum';
                
                if (!isset($grouped_materials[$pelajaran])) {
                    $grouped_materials[$pelajaran] = [];
                }
                
                if (!isset($grouped_materials[$pelajaran][$sub_topik])) {
                    $grouped_materials[$pelajaran][$sub_topik] = [];
                }
                
                $grouped_materials[$pelajaran][$sub_topik][] = $material;
            }
            ?>
            
            <?php foreach ($grouped_materials as $pelajaran => $subtopik_groups): ?>
            <?php foreach ($subtopik_groups as $sub_topik => $materials): ?>
            <div class="mb-6">
                <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm mr-3">
                        <?php
                        $subject_map = [
                            'matematika' => 'Matematika',
                            'fisika' => 'Fisika',
                            'kimia' => 'Kimia',
                            'biologi' => 'Biologi',
                            'bahasa_indonesia' => 'Bahasa Indonesia',
                            'bahasa_inggris' => 'Bahasa Inggris',
                            'sejarah' => 'Sejarah',
                            'geografi' => 'Geografi',
                            'ekonomi' => 'Ekonomi',
                            'sosiologi' => 'Sosiologi',
                            'lainnya' => 'Lainnya'
                        ];
                        echo htmlspecialchars($subject_map[$pelajaran] ?? $pelajaran);
                        ?>
                    </span>
                    <?php if ($sub_topik !== 'Umum'): ?>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                        <?php echo htmlspecialchars($sub_topik); ?>
                    </span>
                    <?php else: ?>
                    <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm">
                        Umum
                    </span>
                    <?php endif; ?>
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                                <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($material['judul']); ?></h4>
                                <p class="text-xs text-gray-600 mt-1"><?php echo format_file_size($material['file_size']); ?> • <?php echo date('d M Y', strtotime($material['created_at'])); ?></p>
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
                        <p class="text-xs text-gray-600 mt-2"><?php echo htmlspecialchars(substr($material['deskripsi'], 0, 80)); ?><?php echo strlen($material['deskripsi']) > 80 ? '...' : ''; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="mt-8 bg-purple-50 border border-purple-200 rounded-xl p-6">
            <h3 class="font-semibold text-purple-800 mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tips Belajar:
            </h3>
            <ul class="list-disc pl-5 text-purple-700 space-y-2">
                <li>Pelajari materi secara menyeluruh untuk persiapan ujian nasional</li>
                <li>Gunakan materi dari admin sebagai referensi tambahan</li>
                <li>Gunakan fitur tanya Sinar untuk memperjelas konsep yang sulit</li>
                <li>Berlatihlah dengan berbagai jenis soal untuk menghadapi ujian</li>
            </ul>
        </div>
    </div>
</div>