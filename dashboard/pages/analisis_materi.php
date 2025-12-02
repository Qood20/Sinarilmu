<?php
// dashboard/pages/analisis_materi.php - Halaman melihat semua penjabaran materi

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

// Ambil semua hasil analisis AI yang terkait dengan file pengguna
try {
    $stmt = $pdo->prepare("
        SELECT a.*, f.original_name, f.created_at as file_created_at
        FROM analisis_ai a
        JOIN upload_files f ON a.file_id = f.id
        WHERE f.user_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $analisis_list = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting analysis data: " . $e->getMessage());
    $analisis_list = [];
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Penjabaran Materi dari File</h2>

        <div class="mb-6 bg-blue-50 p-4 rounded-lg">
            <p class="text-gray-700">
                Berikut adalah penjabaran dan ringkasan materi dari file-file yang telah Anda upload. 
                Setiap file yang telah diproses oleh AI akan muncul di sini dengan analisis terperinci.
            </p>
        </div>

        <?php if (!empty($analisis_list)): ?>
            <div class="space-y-6">
                <?php foreach ($analisis_list as $analisis): ?>
                    <div class="border border-gray-200 rounded-xl p-6 bg-white hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800"><?php echo escape($analisis['original_name']); ?></h3>
                                <p class="text-sm text-gray-600">Diupload: <?php echo date('d M Y H:i', strtotime($analisis['file_created_at'])); ?></p>
                                <p class="text-sm text-gray-600 mt-1">Analisis dibuat: <?php echo date('d M Y H:i', strtotime($analisis['created_at'])); ?></p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                <?php echo ucfirst(escape($analisis['tingkat_kesulitan'])); ?>
                            </span>
                        </div>
                        
                        <div class="border-t border-gray-100 pt-4">
                            <h4 class="font-semibold text-gray-800 mb-2">Ringkasan:</h4>
                            <p class="text-gray-700 mb-4"><?php echo nl2br(escape($analisis['ringkasan'])); ?></p>
                            
                            <?php if (!empty($analisis['penjabaran_materi'])): ?>
                                <h4 class="font-semibold text-gray-800 mb-2">Penjabaran Materi:</h4>
                                <p class="text-gray-700"><?php echo nl2br(escape($analisis['penjabaran_materi'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($analisis['topik_terkait'])): ?>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <h4 class="font-semibold text-gray-800 mb-2">Topik Terkait:</h4>
                                <div class="flex flex-wrap gap-2">
                                    <?php
                                    $topik_array = json_decode($analisis['topik_terkait'], true);
                                    if (is_array($topik_array) && count($topik_array) > 0) {
                                        foreach ($topik_array as $topik) {
                                            echo '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">' . escape($topik) . '</span>';
                                        }
                                    } elseif ($analisis['topik_terkait'] !== '[]') {  // Jika bukan array kosong dalam bentuk string
                                        // Jika formatnya string JSON bukan array, coba tampilkan langsung
                                        echo '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Topik Tersedia</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mt-4">Belum Ada Penjabaran Materi</h3>
                <p class="text-gray-600 mt-2">Unggah file terlebih dahulu untuk mendapatkan penjabaran materi dari AI.</p>
                <a href="?page=upload" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium">
                    Unggah File
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>