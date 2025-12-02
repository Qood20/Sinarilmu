<?php
// admin/pages/material_content.php - Kelola materi pelajaran

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil semua materi dari database
try {
    $stmt = $pdo->query("
        SELECT m.*, u.full_name as creator_name
        FROM materi_pelajaran m
        LEFT JOIN users u ON m.created_by = u.id
        ORDER BY m.created_at DESC
    ");
    $materials = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting materials: " . $e->getMessage());
    $materials = [];
}

// Daftar untuk dropdown
$kelas_options = ['10', '11', '12'];
$mata_pelajaran_options = ['matematika', 'fisika', 'kimia', 'biologi', 'bahasa_indonesia', 'bahasa_inggris', 'sejarah', 'geografi', 'ekonomi', 'sosiologi', 'lainnya'];

?>

<div class="max-w-6xl mx-auto">
    <!-- Form Tambah Materi -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Tambah Materi Pelajaran Baru</h2>
        
        <?php if (isset($_SESSION['upload_error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Error:</strong> <?php echo escape($_SESSION['upload_error']); unset($_SESSION['upload_error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['upload_success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo escape($_SESSION['upload_success']); unset($_SESSION['upload_success']); ?>
            </div>
        <?php endif; ?>

        <form action="pages/process_upload_material.php" method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="judul" class="block text-sm font-medium text-gray-700">Judul Materi</label>
                <input type="text" id="judul" name="judul" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select id="kelas" name="kelas" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <?php foreach ($kelas_options as $kelas): ?>
                            <option value="<?php echo $kelas; ?>"><?php echo "Kelas " . $kelas; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="mata_pelajaran" class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                    <select id="mata_pelajaran" name="mata_pelajaran" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <?php foreach ($mata_pelajaran_options as $mapel): ?>
                            <option value="<?php echo $mapel; ?>"><?php echo ucwords(str_replace('_', ' ', $mapel)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="sub_topik" class="block text-sm font-medium text-gray-700">Sub-Topik (Opsional)</label>
                <input type="text" id="sub_topik" name="sub_topik" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700">File Materi</label>
                <input type="file" id="file" name="file" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Unggah Materi
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Materi -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Materi Pelajaran</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mapel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengunggah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($materials)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada materi yang diunggah.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($materials as $material): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo escape($material['judul']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo ucwords(str_replace('_', ' ', escape($material['mata_pelajaran']))); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($material['kelas']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($material['creator_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($material['created_at'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?php echo escape(get_base_url() . '/' . $material['file_path']); ?>" target="_blank" class="text-green-600 hover:text-green-900 mr-3">Lihat</a>
                            <a href="?page=delete_material&id=<?php echo $material['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Anda yakin ingin menghapus materi ini? Ini juga akan menghapus file terkait.')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
