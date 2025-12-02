<?php
// dashboard/pages/analisis_materi.php - Halaman untuk melihat materi berdasarkan subjek

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
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Pilih Kelas dan Subjek</h2>

        <div class="mb-8 bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-xl border border-blue-200">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-1">Pilih Kelas dan Subjek</h3>
                    <p class="text-gray-700">
                        Klik pada salah satu kelas dan subjek di bawah ini untuk melihat materi pelajaran yang tersedia.
                    </p>
                </div>
            </div>
        </div>

        <!-- Kelas 10 -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Kelas 10</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Kolom Matematika -->
                <a href="?page=matematika_kelas10" class="block bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-blue-200 transform hover:scale-105 transition-transform">
                    <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Matematika</h3>
                    <p class="text-gray-600">Materi kelas 10</p>
                </a>

                <!-- Kolom Fisika -->
                <a href="?page=fisika_kelas10" class="block bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-green-200 transform hover:scale-105 transition-transform">
                    <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.874-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Fisika</h3>
                    <p class="text-gray-600">Materi kelas 10</p>
                </a>

                <!-- Kolom Biologi -->
                <a href="?page=biologi_kelas10" class="block bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-yellow-200 transform hover:scale-105 transition-transform">
                    <div class="bg-yellow-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Biologi</h3>
                    <p class="text-gray-600">Materi kelas 10</p>
                </a>

                <!-- Kolom Kimia -->
                <a href="?page=kimia_kelas10" class="block bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-purple-200 transform hover:scale-105 transition-transform">
                    <div class="bg-purple-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Kimia</h3>
                    <p class="text-gray-600">Materi kelas 10</p>
                </a>
            </div>
        </div>

        <!-- Kelas 11 -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Kelas 11</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Kolom Matematika -->
                <a href="?page=matematika_kelas11" class="block bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-blue-200 transform hover:scale-105 transition-transform">
                    <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Matematika</h3>
                    <p class="text-gray-600">Materi kelas 11</p>
                </a>

                <!-- Kolom Fisika -->
                <a href="?page=fisika_kelas11" class="block bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-green-200 transform hover:scale-105 transition-transform">
                    <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.874-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Fisika</h3>
                    <p class="text-gray-600">Materi kelas 11</p>
                </a>

                <!-- Kolom Biologi -->
                <a href="?page=biologi_kelas11" class="block bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-yellow-200 transform hover:scale-105 transition-transform">
                    <div class="bg-yellow-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Biologi</h3>
                    <p class="text-gray-600">Materi kelas 11</p>
                </a>

                <!-- Kolom Kimia -->
                <a href="?page=kimia_kelas11" class="block bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-purple-200 transform hover:scale-105 transition-transform">
                    <div class="bg-purple-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Kimia</h3>
                    <p class="text-gray-600">Materi kelas 11</p>
                </a>
            </div>
        </div>

        <!-- Kelas 12 -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Kelas 12</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Kolom Matematika -->
                <a href="?page=matematika_kelas12" class="block bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-blue-200 transform hover:scale-105 transition-transform">
                    <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Matematika</h3>
                    <p class="text-gray-600">Materi kelas 12</p>
                </a>

                <!-- Kolom Fisika -->
                <a href="?page=fisika_kelas12" class="block bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-green-200 transform hover:scale-105 transition-transform">
                    <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.874-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Fisika</h3>
                    <p class="text-gray-600">Materi kelas 12</p>
                </a>

                <!-- Kolom Biologi -->
                <a href="?page=biologi_kelas12" class="block bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-yellow-200 transform hover:scale-105 transition-transform">
                    <div class="bg-yellow-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Biologi</h3>
                    <p class="text-gray-600">Materi kelas 12</p>
                </a>

                <!-- Kolom Kimia -->
                <a href="?page=kimia_kelas12" class="block bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-purple-200 transform hover:scale-105 transition-transform">
                    <div class="bg-purple-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Kimia</h3>
                    <p class="text-gray-600">Materi kelas 12</p>
                </a>
            </div>
        </div>

        <!-- Admin Uploaded Materials Section - All Classes and Subjects -->
        <?php
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u.full_name as uploaded_by
                FROM materi_pelajaran m
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.status = 'aktif'
                ORDER BY m.kelas, m.mata_pelajaran, m.sub_topik, m.created_at DESC
            ");
            $stmt->execute();
            $admin_materials = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting admin materials: " . $e->getMessage());
            $admin_materials = [];
        }

        if (!empty($admin_materials)):
        ?>
        <div class="mt-12">
            <div class="flex items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Materi dari Admin</h3>
                <div class="ml-3 bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    <?php echo count($admin_materials); ?> file
                </div>
            </div>

            <?php
            // Group materials by class, mata pelajaran and sub_topik
            $grouped_materials = [];
            foreach ($admin_materials as $material) {
                $kelas = $material['kelas'];
                $pelajaran = $material['mata_pelajaran'];
                $sub_topik = $material['sub_topik'] ?: 'Umum';

                if (!isset($grouped_materials[$kelas])) {
                    $grouped_materials[$kelas] = [];
                }

                if (!isset($grouped_materials[$kelas][$pelajaran])) {
                    $grouped_materials[$kelas][$pelajaran] = [];
                }

                if (!isset($grouped_materials[$kelas][$pelajaran][$sub_topik])) {
                    $grouped_materials[$kelas][$pelajaran][$sub_topik] = [];
                }

                $grouped_materials[$kelas][$pelajaran][$sub_topik][] = $material;
            }
            ?>

            <?php foreach ($grouped_materials as $kelas => $pelajaran_groups): ?>
            <?php foreach ($pelajaran_groups as $pelajaran => $subtopic_groups): ?>
            <?php foreach ($subtopic_groups as $sub_topik => $materials): ?>
            <div class="mb-8">
                <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm mr-3">
                        <?php
                        $pelajaran_map = [
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
                        echo escape($pelajaran_map[$pelajaran] ?? $pelajaran);
                        ?>
                    </span>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm mr-3">
                        Kelas <?php echo escape($kelas); ?>
                    </span>
                    <?php if ($sub_topik !== 'Umum'): ?>
                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">
                        <?php echo escape($sub_topik); ?>
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
                        <div class="flex items-start">
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
                                <h5 class="font-medium text-gray-800"><?php echo escape($material['judul']); ?></h5>
                                <?php if (!empty($material['topik_spesifik'])): ?>
                                <p class="text-xs text-purple-600 font-medium mt-1"><?php echo escape($material['topik_spesifik']); ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-600 mt-1"><?php echo format_file_size($material['file_size']); ?> • <?php echo date('d M', strtotime($material['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-between items-center">
                            <a href="<?php echo $material['file_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs font-medium flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Petunjuk Penggunaan:
            </h3>
            <ul class="list-disc pl-5 text-blue-700 space-y-2">
                <li>Klik pada salah satu subjek untuk melihat materi pelajaran kelas 11</li>
                <li>Setiap materi akan dilengkapi dengan penjelasan dan contoh soal</li>
                <li>Gunakan fitur pencarian untuk menemukan topik tertentu</li>
            </ul>
        </div>
    </div>
</div>