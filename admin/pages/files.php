<?php
// admin/pages/files.php - Kelola file yang diunggah oleh pengguna

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil semua file dari database dengan pencarian opsional
$search = $_GET['search'] ?? '';
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT f.id, f.filename, f.original_name, f.file_path, f.file_size, f.file_type, f.description, f.created_at,
            f.status, u.full_name as user_name
            FROM upload_files f
            LEFT JOIN users u ON f.user_id = u.id
            WHERE f.original_name LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?
            ORDER BY f.created_at DESC
        ");
        $search_term = '%' . $search . '%';
        $stmt->execute([$search_term, $search_term, $search_term]);
    } else {
        $stmt = $pdo->query("
            SELECT f.id, f.filename, f.original_name, f.file_path, f.file_size, f.file_type, f.description, f.created_at,
            f.status, u.full_name as user_name
            FROM upload_files f
            LEFT JOIN users u ON f.user_id = u.id
            ORDER BY f.created_at DESC
        ");
    }
    $files = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting files: " . $e->getMessage());
    $files = [];
}
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Kelola File Pengguna</h2>
        </div>
        
        <div class="mb-4 flex">
            <form method="GET" class="flex w-full">
                <input type="hidden" name="page" value="files">
                <input type="text" name="search" value="<?php echo escape($_GET['search'] ?? ''); ?>" placeholder="Cari file..." class="flex-1 border border-gray-300 rounded-l-lg py-2 px-4 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <button type="submit" class="bg-blue-600 text-white px-4 rounded-r-lg hover:bg-blue-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ukuran</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo escape($file['original_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($file['user_name'] ?? 'Tidak Dikenal'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($file['created_at'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php
                            // Format ukuran file
                            $size = $file['file_size'];
                            if ($size >= 1024 * 1024) {
                                echo round($size / (1024 * 1024), 2) . ' MB';
                            } else if ($size >= 1024) {
                                echo round($size / 1024, 2) . ' KB';
                            } else {
                                echo $size . ' B';
                            }
                        ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo strtoupper(escape($file['file_type'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="?page=file_detail&id=<?php echo $file['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Detail</a>
                            <a href="<?php echo escape($file['file_path']); ?>" target="_blank" class="text-green-600 hover:text-green-900 mr-3">Lihat</a>
                            <a href="?page=delete_file&id=<?php echo $file['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Anda yakin ingin menghapus file ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>