<?php
// dashboard/pages/kimia_kelas10.php - Halaman materi kimia kelas 10

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
                Kembali
            </a>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Kimia Kelas 10</h2>
        <p class="text-gray-600 mb-8">Materi pelajaran kimia untuk kelas 10 SMA</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Materi 1 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-purple-100 text-purple-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Struktur Atom dan Sistem Periodik</h3>
                <p class="text-gray-600 mb-4">Teori atom, konfigurasi elektron, dan sistem periodik unsur</p>
                <a href="#" class="text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 2 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-purple-100 text-purple-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Ikatan Kimia</h3>
                <p class="text-gray-600 mb-4">Ikatan ion, kovalen, dan logam beserta sifat-sifatnya</p>
                <a href="#" class="text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 3 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-purple-100 text-purple-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Persamaan Reaksi dan Hukum Dasar Kimia</h3>
                <p class="text-gray-600 mb-4">Penyetaraan reaksi dan hukum kekekalan massa</p>
                <a href="#" class="text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 4 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-purple-100 text-purple-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Stoikiometri</h3>
                <p class="text-gray-600 mb-4">Perhitungan kimia berdasarkan persamaan reaksi</p>
                <a href="#" class="text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 5 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-purple-100 text-purple-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Larutan Elektrolit dan Non-elektrolit</h3>
                <p class="text-gray-600 mb-4">Kemampuan larutan menghantarkan listrik</p>
                <a href="#" class="text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 6 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-purple-100 text-purple-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Reaksi Asam Basa</h3>
                <p class="text-gray-600 mb-4">Konsep asam basa Arrhenius, Bronsted-Lowry, dan pH</p>
                <a href="#" class="text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Admin Uploaded Materials Section for Chemistry -->
        <?php
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u.full_name as uploaded_by
                FROM materi_pelajaran m
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.kelas = '10' AND m.mata_pelajaran = 'kimia' AND m.status = 'aktif'
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Materi Tambahan dari Admin
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($admin_materials as $material): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-2 rounded-lg mr-3">
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
                        <a href="<?php echo $material['file_path']; ?>" target="_blank" class="text-purple-600 hover:text-purple-800 text-sm font-medium flex items-center">
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

        <div class="mt-10 bg-purple-50 border border-purple-200 rounded-xl p-6">
            <h3 class="font-semibold text-purple-800 mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tips Belajar:
            </h3>
            <ul class="list-disc pl-5 text-purple-700 space-y-2">
                <li>Hafalkan lambang unsur dan konfigurasi elektronnya</li>
                <li>Pahami konsep dasar sebelum masuk ke perhitungan</li>
                <li>Lakukan eksperimen sederhana untuk memahami reaksi kimia</li>
            </ul>
        </div>
    </div>
</div>