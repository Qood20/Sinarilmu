<?php
// dashboard/pages/biologi_kelas10.php - Halaman materi biologi kelas 10

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
            <a href="?page=materi_kelas10" class="text-yellow-600 hover:text-yellow-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Kelas 10
            </a>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Biologi Kelas 10</h2>
        <p class="text-gray-600 mb-8">Materi pelajaran biologi untuk kelas 10 SMA</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Materi 1 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-yellow-100 text-yellow-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Ciri-ciri Makhluk Hidup</h3>
                <p class="text-gray-600 mb-4">Memahami ciri-ciri makhluk hidup dan klasifikasinya</p>
                <a href="#" class="text-yellow-600 hover:text-yellow-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 2 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-yellow-100 text-yellow-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Struktur Sel</h3>
                <p class="text-gray-600 mb-4">Memahami struktur dan fungsi organel sel</p>
                <a href="#" class="text-yellow-600 hover:text-yellow-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 3 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-yellow-100 text-yellow-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Jaringan pada Tumbuhan dan Hewan</h3>
                <p class="text-gray-600 mb-4">Memahami struktur dan fungsi jaringan pada organisme</p>
                <a href="#" class="text-yellow-600 hover:text-yellow-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 4 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-yellow-100 text-yellow-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Sistem Gerak pada Manusia</h3>
                <p class="text-gray-600 mb-4">Struktur tulang dan otot serta mekanisme gerak</p>
                <a href="#" class="text-yellow-600 hover:text-yellow-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 5 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-yellow-100 text-yellow-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Sistem Pencernaan</h3>
                <p class="text-gray-600 mb-4">Proses pencernaan makanan dalam tubuh</p>
                <a href="#" class="text-yellow-600 hover:text-yellow-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 6 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-yellow-100 text-yellow-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Klasifikasi Makhluk Hidup</h3>
                <p class="text-gray-600 mb-4">Sistem klasifikasi dan taksonomi makhluk hidup</p>
                <a href="#" class="text-yellow-600 hover:text-yellow-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Admin Uploaded Materials Section for Biology -->
        <?php
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u.full_name as uploaded_by
                FROM materi_pelajaran m
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.kelas = '10' AND m.mata_pelajaran = 'biologi' AND m.status = 'aktif'
                ORDER BY m.created_at DESC
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Materi Tambahan dari Admin
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($admin_materials as $material): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-2 rounded-lg mr-3">
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
                        <a href="<?php echo $material['file_path']; ?>" target="_blank" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Pelajari Sekarang
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-10 bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tips Belajar:
            </h3>
            <ul class="list-disc pl-5 text-yellow-700 space-y-2">
                <li>Gunakan diagram dan gambar untuk memahami struktur tubuh</li>
                <li>Buat ringkasan untuk setiap sistem organ</li>
                <li>Lakukan observasi langsung untuk memperkuat pemahaman konsep</li>
            </ul>
        </div>
    </div>
</div>