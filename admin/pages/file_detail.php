<?php
// admin/pages/file_detail.php - Halaman detail file untuk admin

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil ID file dari parameter
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($file_id <= 0) {
    $_SESSION['error'] = "ID file tidak valid.";
    header('Location: ?page=files');
    exit;
}

// Ambil data file
$stmt = $pdo->prepare("
    SELECT f.*, u.full_name as user_name, u.email as user_email
    FROM upload_files f
    LEFT JOIN users u ON f.user_id = u.id
    WHERE f.id = ?
");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if (!$file) {
    $_SESSION['error'] = "File tidak ditemukan.";
    header('Location: ?page=files');
    exit;
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Detail File</h2>
            <a href="?page=files" class="text-blue-600 hover:text-blue-900">
                ← Kembali ke daftar file
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Nama Asli File</h3>
                    <p class="mt-1 text-gray-900"><?php echo escape($file['original_name']); ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Nama File Sistem</h3>
                    <p class="mt-1 text-gray-900"><?php echo escape($file['filename']); ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Jenis File</h3>
                    <p class="mt-1 text-gray-900"><?php echo strtoupper(escape($file['file_type'])); ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Ukuran File</h3>
                    <p class="mt-1 text-gray-900">
                        <?php
                        $size = $file['file_size'];
                        if ($size >= 1024 * 1024) {
                            echo round($size / (1024 * 1024), 2) . ' MB';
                        } else if ($size >= 1024) {
                            echo round($size / 1024, 2) . ' KB';
                        } else {
                            echo $size . ' B';
                        }
                        ?>
                    </p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Status</h3>
                    <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        <?php echo $file['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                           ($file['status'] === 'processing' ? 'bg-yellow-100 text-yellow-800' : 
                           ($file['status'] === 'failed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')); ?>">
                        <?php echo escape($file['status']); ?>
                    </span>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Tanggal Diunggah</h3>
                    <p class="mt-1 text-gray-900"><?php echo date('d M Y H:i:s', strtotime($file['created_at'])); ?></p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Pengguna Pemilik</h3>
                    <p class="mt-1 text-gray-900"><?php echo escape($file['user_name'] ?? 'Tidak Dikenal'); ?></p>
                    <p class="text-gray-600"><?php echo escape($file['user_email'] ?? 'Tidak Tersedia'); ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Deskripsi</h3>
                    <p class="mt-1 text-gray-900"><?php echo !empty($file['description']) ? escape($file['description']) : 'Tidak ada deskripsi'; ?></p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Lokasi File</h3>
                    <p class="mt-1 text-gray-900 break-words"><?php echo escape($file['file_path']); ?></p>
                </div>
                
                <?php if ($file['status'] === 'completed'): ?>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Hasil Analisis</h3>
                    <div class="mt-1">
                        <a href="?page=file_analysis&id=<?php echo $file['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Lihat Hasil Analisis
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-8 flex space-x-4">
            <a href="<?php echo escape($file['file_path']); ?>" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Lihat File
            </a>
            <a href="?page=delete_file&id=<?php echo $file['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Anda yakin ingin menghapus file ini?')">
                Hapus File
            </a>
        </div>
    </div>
</div>