<?php
// admin/pages/exercises.php - Kelola soal dan hasil latihan

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil hasil latihan dari semua pengguna
try {
    $stmt = $pdo->prepare("
        SELECT hs.*, bs.soal, u.full_name as user_name, u.email as user_email
        FROM hasil_soal_user hs
        JOIN bank_soal_ai bs ON hs.soal_id = bs.id
        JOIN users u ON hs.user_id = u.id
        ORDER BY hs.created_at DESC
    ");
    $stmt->execute();
    $exercise_results = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting exercise results: " . $e->getMessage());
    $exercise_results = [];
}
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Kelola Soal dan Hasil Latihan</h2>
            <div class="flex space-x-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Tambah Soal
                </button>
                <button class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Ekspor Data
                </button>
            </div>
        </div>
        
        <div class="mb-4 flex">
            <input type="text" placeholder="Cari soal atau pengguna..." class="flex-1 border border-gray-300 rounded-l-lg py-2 px-4 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <button class="bg-blue-600 text-white px-4 rounded-r-lg hover:bg-blue-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Soal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($exercise_results)): ?>
                        <?php foreach ($exercise_results as $result): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo escape($result['user_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?php echo escape(substr($result['soal'], 0, 50) . '...'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y H:i', strtotime($result['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php
                                    echo $result['nilai'] >= 75 ? 'bg-green-100 text-green-800' :
                                        ($result['nilai'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                    ?>">
                                    <?php echo $result['nilai']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo escape($result['status_jawaban']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="?page=exercise_detail&id=<?php echo $result['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Lihat</a>
                                <a href="?page=delete_exercise_result&id=<?php echo $result['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Anda yakin ingin menghapus hasil latihan ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Tidak ada hasil latihan ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>