<?php
// admin/pages/material_content.php - Sistem pengelolaan materi pelajaran oleh admin

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil semua materi pelajaran
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.full_name as created_by_name
        FROM materi_pelajaran m
        LEFT JOIN users u ON m.created_by = u.id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute();
    $materials = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting materials: " . $e->getMessage());
    $materials = [];
}

// Proses upload materi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $kelas = $_POST['kelas'] ?? '';
        $mata_pelajaran = $_POST['mata_pelajaran'] ?? '';
        $sub_topik = $_POST['sub_topik'] ?? '';
        $topik_spesifik = $_POST['topik_spesifik'] ?? '';
        $file = $_FILES['material_file'];

        // Validasi input
        if (empty($judul) || empty($kelas) || empty($mata_pelajaran)) {
            $_SESSION['error'] = "Judul, kelas, dan mata pelajaran wajib diisi.";
        } else {
            // Validasi file
            $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_types)) {
                $_SESSION['error'] = "Format file tidak diperbolehkan. Format yang diperbolehkan: " . implode(', ', $allowed_types);
            } else {
                // Buat direktori jika belum ada
                $upload_dir = dirname(__DIR__, 2) . '/uploads/materials/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Bersihkan nama file dari emoji sebelum disimpan menggunakan fungsi global
                $clean_original_name = clean_filename_from_emojis($file['name']);

                // Buat nama file unik
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $clean_original_name);
                $file_path = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO materi_pelajaran
                            (judul, deskripsi, kelas, mata_pelajaran, sub_topik, topik_spesifik, file_path, original_name, file_size, file_type, created_by, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");

                        $result = $stmt->execute([
                            $judul,
                            $deskripsi,
                            $kelas,
                            $mata_pelajaran,
                            $sub_topik,
                            $topik_spesifik,
                            '/uploads/materials/' . $filename, // Simpan path relatif
                            $clean_original_name, // Gunakan nama file yang sudah dibersihkan
                            $file['size'],
                            $file_ext,
                            $_SESSION['user_id'],
                            'aktif'
                        ]);
                        
                        if ($result) {
                            // Catat aktivitas
                            log_activity($_SESSION['user_id'], 'Upload Materi', "Mengunggah materi: $judul");
                            $_SESSION['success'] = "Materi berhasil diunggah.";
                            
                            // Refresh data
                            $stmt = $pdo->prepare("
                                SELECT m.*, u.full_name as created_by_name
                                FROM materi_pelajaran m
                                LEFT JOIN users u ON m.created_by = u.id
                                ORDER BY m.created_at DESC
                            ");
                            $stmt->execute();
                            $materials = $stmt->fetchAll();
                        } else {
                            $_SESSION['error'] = "Gagal menyimpan materi.";
                        }
                    } catch (Exception $e) {
                        error_log("Error saving material: " . $e->getMessage());
                        $_SESSION['error'] = "Gagal menyimpan materi: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error'] = "Gagal mengunggah file.";
                }
            }
        }
    } else {
        $_SESSION['error'] = "Silakan pilih file untuk diunggah.";
    }
}

// Proses hapus materi
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    try {
        // Ambil path file untuk dihapus
        $stmt = $pdo->prepare("SELECT file_path FROM materi_pelajaran WHERE id = ?");
        $stmt->execute([$delete_id]);
        $material = $stmt->fetch();
        
        if ($material) {
            // Hapus file fisik jika ada
            $full_path = dirname(__DIR__, 2) . $material['file_path'];
            if (file_exists($full_path)) {
                unlink($full_path);
            }
            
            // Hapus dari database
            $stmt = $pdo->prepare("DELETE FROM materi_pelajaran WHERE id = ?");
            $result = $stmt->execute([$delete_id]);
            
            if ($result) {
                log_activity($_SESSION['user_id'], 'Hapus Materi', "Menghapus materi ID: $delete_id");
                $_SESSION['success'] = "Materi berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus materi.";
            }
        } else {
            $_SESSION['error'] = "Materi tidak ditemukan.";
        }
    } catch (Exception $e) {
        error_log("Error deleting material: " . $e->getMessage());
        $_SESSION['error'] = "Gagal menghapus materi.";
    }
    
    // Refresh data
    $stmt = $pdo->prepare("
        SELECT m.*, u.full_name as created_by_name
        FROM materi_pelajaran m
        LEFT JOIN users u ON m.created_by = u.id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute();
    $materials = $stmt->fetchAll();
}

// Proses update status
if (isset($_POST['update_status'])) {
    $material_id = (int)$_POST['material_id'];
    $new_status = $_POST['new_status'] ?? 'aktif';
    
    try {
        $stmt = $pdo->prepare("UPDATE materi_pelajaran SET status = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$new_status, $material_id]);
        
        if ($result) {
            log_activity($_SESSION['user_id'], 'Update Status Materi', "Memperbarui status materi ID: $material_id ke $new_status");
            $_SESSION['success'] = "Status materi berhasil diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui status materi.";
        }
    } catch (Exception $e) {
        error_log("Error updating material status: " . $e->getMessage());
        $_SESSION['error'] = "Gagal memperbarui status materi.";
    }
    
    // Refresh data
    $stmt = $pdo->prepare("
        SELECT m.*, u.full_name as created_by_name
        FROM materi_pelajaran m
        LEFT JOIN users u ON m.created_by = u.id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute();
    $materials = $stmt->fetchAll();
}
?>

<div class="max-w-6xl mx-auto">
    <!-- Upload Form -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Upload Materi Pelajaran</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo escape($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo escape($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="judul" class="block text-sm font-medium text-gray-700 mb-1">Judul Materi</label>
                    <input type="text" id="judul" name="judul" required class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="mata_pelajaran" class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                    <select id="mata_pelajaran" name="mata_pelajaran" required onchange="updateSubTopics()" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Mata Pelajaran</option>
                        <option value="matematika">Matematika</option>
                        <option value="fisika">Fisika</option>
                        <option value="kimia">Kimia</option>
                        <option value="biologi">Biologi</option>
                        <option value="bahasa_indonesia">Bahasa Indonesia</option>
                        <option value="bahasa_inggris">Bahasa Inggris</option>
                        <option value="sejarah">Sejarah</option>
                        <option value="geografi">Geografi</option>
                        <option value="ekonomi">Ekonomi</option>
                        <option value="sosiologi">Sosiologi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label for="kelas" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                    <select id="kelas" name="kelas" required class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Kelas</option>
                        <option value="10">Kelas 10</option>
                        <option value="11">Kelas 11</option>
                        <option value="12">Kelas 12</option>
                    </select>
                </div>

                <div>
                    <label for="sub_topik" class="block text-sm font-medium text-gray-700 mb-1">Sub Topik</label>
                    <select id="sub_topik" name="sub_topik" onchange="updateTopikSpesifik()" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Sub Topik</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih sub topik sesuai mata pelajaran yang dipilih</p>
                </div>

                <div id="topik_spesifik_container" style="display:none;">
                    <label for="topik_spesifik" class="block text-sm font-medium text-gray-700 mb-1">Topik Spesifik</label>
                    <select id="topik_spesifik" name="topik_spesifik" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Topik Spesifik</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih topik spesifik dalam sub topik</p>
                </div>

                <div>
                    <label for="material_file" class="block text-sm font-medium text-gray-700 mb-1">File Materi</label>
                    <input type="file" id="material_file" name="material_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png" required class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format yang diperbolehkan: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, JPG, JPEG, PNG</p>
                </div>
            </div>

            <div>
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <div>
                <button type="submit" name="upload_material" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Upload Materi
                </button>
            </div>
        </form>
    </div>

    <!-- Materials List -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Daftar Materi Pelajaran</h2>
            <div class="text-sm text-gray-600">
                Total: <?php echo count($materials); ?> materi
            </div>
        </div>

        <?php if (!empty($materials)): ?>
            <!-- Filter Section -->
            <div class="mb-6 flex flex-wrap gap-4 items-center">
                <div>
                    <label for="filter_kelas" class="block text-sm font-medium text-gray-700 mb-1">Filter Kelas</label>
                    <select id="filter_kelas" onchange="filterMaterials()" class="border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Kelas</option>
                        <option value="10">Kelas 10</option>
                        <option value="11">Kelas 11</option>
                        <option value="12">Kelas 12</option>
                    </select>
                </div>

                <div>
                    <label for="filter_matpel" class="block text-sm font-medium text-gray-700 mb-1">Filter Mata Pelajaran</label>
                    <select id="filter_matpel" onchange="filterMaterials()" class="border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Mata Pelajaran</option>
                        <option value="matematika">Matematika</option>
                        <option value="fisika">Fisika</option>
                        <option value="kimia">Kimia</option>
                        <option value="biologi">Biologi</option>
                        <option value="bahasa_indonesia">Bahasa Indonesia</option>
                        <option value="bahasa_inggris">Bahasa Inggris</option>
                        <option value="sejarah">Sejarah</option>
                        <option value="geografi">Geografi</option>
                        <option value="ekonomi">Ekonomi</option>
                        <option value="sosiologi">Sosiologi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label for="filter_subtopik" class="block text-sm font-medium text-gray-700 mb-1">Filter Sub Topik</label>
                    <select id="filter_subtopik" onchange="filterMaterials()" class="border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Sub Topik</option>
                        <option value="Aljabar">Aljabar</option>
                        <option value="Barisan dan Deret">Barisan dan Deret</option>
                        <option value="Trigonometri">Trigonometri</option>
                        <option value="Statistika">Statistika</option>
                        <option value="Fungsi Kuadrat">Fungsi Kuadrat</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="materials_table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub Topik</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diupload Oleh</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($materials as $material): ?>
                        <tr class="material-row" data-kelas="<?php echo escape($material['kelas']); ?>" data-matpel="<?php echo escape($material['mata_pelajaran']); ?>" data-subtopik="<?php echo escape($material['sub_topik']); ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo escape($material['judul']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo escape(substr($material['deskripsi'], 0, 50)) . (strlen($material['deskripsi']) > 50 ? '...' : ''); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                    Kelas <?php echo escape($material['kelas']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
                                echo escape($pelajaran_map[$material['mata_pelajaran']] ?? $material['mata_pelajaran']);
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if (!empty($material['sub_topik'])): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                        <?php echo escape($material['sub_topik']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                                        Umum
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <?php if (in_array(strtolower(pathinfo($material['original_name'], PATHINFO_EXTENSION)), ['pdf'])): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    <?php elseif (in_array(strtolower(pathinfo($material['original_name'], PATHINFO_EXTENSION)), ['doc', 'docx'])): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-medium"><?php echo escape($material['original_name']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo format_file_size($material['file_size']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo escape($material['created_by_name'] ?? 'Sistem'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y', strtotime($material['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="post" class="inline">
                                    <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded px-2 py-1">
                                        <option value="aktif" <?php echo $material['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="nonaktif" <?php echo $material['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?php echo $material['file_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3">Lihat</a>
                                <a href="?page=material_content&delete_id=<?php echo $material['id']; ?>" onclick="return confirm('Yakin ingin menghapus materi ini?')" class="text-red-600 hover:text-red-900">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada materi</h3>
                <p class="mt-1 text-sm text-gray-500">Upload materi pelajaran pertama dengan menggunakan form di atas.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterMaterials() {
    const kelasFilter = document.getElementById('filter_kelas').value;
    const matpelFilter = document.getElementById('filter_matpel').value;
    const subtopikFilter = document.getElementById('filter_subtopik').value;
    const rows = document.querySelectorAll('.material-row');

    rows.forEach(row => {
        const rowKelas = row.getAttribute('data-kelas');
        const rowMatpel = row.getAttribute('data-matpel');
        const rowSubtopik = row.getAttribute('data-subtopik');
        let show = true;

        if (kelasFilter && rowKelas !== kelasFilter) {
            show = false;
        }

        if (matpelFilter && rowMatpel !== matpelFilter) {
            show = false;
        }

        if (subtopikFilter && rowSubtopik !== subtopikFilter) {
            show = false;
        }

        if (show) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Fungsi untuk format ukuran file
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Sub-topik definition
const subTopics = {
    'matematika': [
        'Aljabar',
        'Barisan dan Deret',
        'Eksponen dan Logaritma',
        'Fungsi Kuadrat',
        'Geometri',
        'Integral',
        'Limit',
        'Logika',
        'Matriks',
        'Peluang',
        'Persamaan dan Pertidaksamaan',
        'Program Linear',
        'Statistika',
        'Suku Banyak',
        'Trigonometri',
        'Vektor'
    ],
    'fisika': [
        'Gerak Lurus',
        'Gerak Parabola',
        'Gelombang',
        'Hukum Newton',
        'Induksi Elektromagnetik',
        'Kalor',
        'Listrik Dinamis',
        'Listrik Statis',
        'Momentum dan Impuls',
        'Optik',
        'Panas',
        'Radiasi',
        'Relativitas',
        'Suhu',
        'Teori Kinetik Gas',
        'Usaha dan Energi'
    ],
    'kimia': [
        'Asam Basa',
        'Gas Ideal',
        'Hidrolisis',
        'Ikatan Kimia',
        'Kimia Anorganik',
        'Kimia Organik',
        'Kinetika Reaksi',
        'Konsentrasi Larutan',
        'Kopling',
        'Larutan',
        'Pereaksi Pembatas',
        'Reaksi Redoks',
        'Sel Elektrokimia',
        'Stoikiometri',
        'Termokimia',
        'Titrasi'
    ],
    'biologi': [
        'Anatomi dan Fisiologi Tumbuhan',
        'Bioteknologi',
        'Ekologi',
        'Evolusi',
        'Genetika',
        'Herediter dan Variasi',
        'Jaringan Tumbuhan',
        'Keanekaragaman Hayati',
        'Metabolisme',
        'Pewarisan Sifat',
        'Sistem Gerak',
        'Sistem Imun',
        'Sistem Pencernaan',
        'Sistem Pernapasan',
        'Sistem Peredaran Darah',
        'Virus dan Bakteri'
    ],
    'bahasa_indonesia': [
        'Analisis Teks',
        'Cerita Fiksi',
        'Cerita Nonfiksi',
        'Gagasan Pokok',
        'Grammar',
        'Kaidah Bahasa',
        'Kata Baku',
        'Kata Tidak Baku',
        'Makna Kata',
        'Majas',
        'Menyunting',
        'Paragraf',
        'Pembacaan Puisi',
        'Peribahasa',
        'Sintaksis',
        'Struktur Teks'
    ],
    'bahasa_inggris': [
        'Conditional Sentences',
        'Direct-Indirect Speech',
        'Grammar',
        'Irregular Verbs',
        'Modal Verbs',
        'Passive Voice',
        'Phrasal Verbs',
        'Present Perfect',
        'Reported Speech',
        'Simple Past',
        'Simple Present',
        'Tenses',
        'Verb Patterns',
        'Vocabulary',
        'Writing'
    ],
    'sejarah': [
        'Kebudayaan Prasejarah',
        'Kolonialisme',
        'Masa Demokrasi',
        'Masa Kemerdekaan',
        'Masa Orde Baru',
        'Masa Reformasi',
        'Masyarakat Praaksara',
        'Peradaban Dunia',
        'Perang Dunia',
        'Pergerakan Nasional',
        'Proklamasi',
        'Sejarah Kerajaan Nusantara',
        'Sejarah Sosial',
        'Teknologi Masa Lalu',
        'Unifikasi Nasional'
    ],
    'geografi': [
        'Atmosfer',
        'Biosfer',
        'Hidrosfer',
        'Litosfer',
        'Manusia dan Lingkungan',
        'Peta dan Penginderaan Jauh',
        'Perubahan Iklim',
        'Pola Keruangan Desa',
        'Pola Keruangan Kota',
        'Pola Keruangan Wilayah',
        'Persebaran Penduduk',
        'Pertumbuhan Penduduk',
        'Wilayah Administratif',
        'Wilayah Fungsional',
        'Wilayah Formal'
    ],
    'ekonomi': [
        'APBN dan APBD',
        'Badan Usaha',
        'Bank dan Lembaga Keuangan',
        'Dimensi Ekonomi',
        'Elasitisitas',
        'Fungsi Konsumsi dan Tabungan',
        'Fungsi Produksi dan Biaya',
        'Hukum Permintaan dan Penawaran',
        'Inflasi',
        'Kebijakan Fiskal',
        'Kebijakan Moneter',
        'Mekanisme Pasar',
        'Pasar Input dan Output',
        'Perdagangan Internasional',
        'Perekonomian Terbuka'
    ],
    'sosiologi': [
        'Fungsi Sosial',
        'Interaksi Sosial',
        'Konflik Sosial',
        'Lembaga Sosial',
        'Manfaat Sosial',
        'Masalah Sosial',
        'Masyarakat Majemuk',
        'Norma Sosial',
        'Pembangunan Sosial',
        'Perilaku Sosial',
        'Perubahan Sosial',
        'Proses Sosialisasi',
        'Sistem Stratifikasi',
        'Tertib Sosial',
        'Wawasan Sosial'
    ],
    'lainnya': [
        'Umum',
        'Lainnya'
    ]
};

// Definisi topik spesifik untuk setiap sub-topik
const topikSpesifikMap = {
    'matematika': {
        'Program Linear': [
            'Program Linear Dua Variabel',
            'Model Matematika Program Linear',
            'Nilai Optimum Fungsi Objektif'
        ],
        'Matriks': [
            'Operasi Hitung Matriks',
            'Determinan dan Invers Matriks',
            'Penerapan Matriks'
        ],
        'Vektor': [
            'Operasi Vektor',
            'Perkalian Skalar Dua Vektor',
            'Proyeksi Ortogonal Vektor'
        ],
        'Barisan dan Deret': [
            'Barisan Aritmetika',
            'Barisan Geometri',
            'Deret Aritmetika dan Geometri'
        ],
        'Trigonometri': [
            'Aturan Sinus dan Cosinus',
            'Jumlah dan Selisih Sudut',
            'Persamaan Trigonometri'
        ],
        'Statistika': [
            'Ukuran Pemusatan Data',
            'Ukuran Letak Data',
            'Ukuran Penyebaran Data'
        ],
        'Fungsi Kuadrat': [
            'Grafik Fungsi Kuadrat',
            'Hubungan Grafik dengan Persamaan',
            'Penerapan Fungsi Kuadrat'
        ],
        'Persamaan dan Pertidaksamaan': [
            'Persamaan Linear',
            'Pertidaksamaan Linear',
            'Nilai Mutlak'
        ],
        'Eksponen dan Logaritma': [
            'Sifat-sifat Eksponen',
            'Sifat-sifat Logaritma',
            'Persamaan dan Pertidaksamaan Eksponen dan Logaritma'
        ],
        'Aljabar': [
            'Bentuk Aljabar',
            'Pemfaktoran',
            'Fungsi dan Relasi'
        ],
        'Limit': [
            'Konsep Limit',
            'Teorema Limit',
            'Limit Fungsi Aljabar'
        ],
        'Integral': [
            'Integral Tak Tentu',
            'Integral Tertentu',
            'Penerapan Integral'
        ],
        'Logika': [
            'Pernyataan dan Kalimat Terbuka',
            'Operasi Logika',
            'Penarikan Kesimpulan'
        ],
        'Peluang': [
            'Aturan Perkalian',
            'Permutasi dan Kombinasi',
            'Peluang Kejadian Majemuk'
        ],
        'Suku Banyak': [
            'Operasi Pembagian Suku Banyak',
            'Teorema Sisa dan Teorema Faktor',
            'Akar-akar Rasional'
        ]
    },
    'fisika': {
        'Gerak Lurus': [
            'Gerak Lurus Beraturan',
            'Gerak Lurus Berubah Beraturan',
            'Jatuh Bebas dan Gerak Vertikal'
        ],
        'Gerak Parabola': [
            'Komponen Gerak Parabola',
            'Persamaan Gerak Parabola',
            'Penerapan Gerak Parabola'
        ],
        'Hukum Newton': [
            'Hukum I Newton',
            'Hukum II Newton',
            'Hukum III Newton'
        ],
        'Usaha dan Energi': [
            'Konsep Usaha',
            'Energi Kinetik dan Potensial',
            'Kekekalan Energi Mekanik'
        ],
        'Momentum dan Impuls': [
            'Konsep Momentum dan Impuls',
            'Hukum Kekekalan Momentum',
            'Tumbukan'
        ],
        'Suhu dan Kalor': [
            'Pemuaian Zat',
            'Kalor dan Perubahan Suhu',
            'Azas Black'
        ],
        'Gelombang': [
            'Gelombang Mekanik',
            'Gelombang Bunyi',
            'Gelombang Cahaya'
        ],
        'Listrik Statis': [
            'Gaya Coulomb',
            'Medan Listrik',
            'Potensial Listrik'
        ],
        'Listrik Dinamis': [
            'Hukum Ohm',
            'Hukum Kirchhoff',
            'Rangkaian Listrik'
        ],
        'Induksi Elektromagnetik': [
            'GGL Induksi',
            'Hukum Faraday dan Lenz',
            'Transformator'
        ]
    },
    'kimia': {
        'Struktur Atom': [
            'Perkembangan Teori Atom',
            'Konfigurasi Elektron',
            'Sistem Periodik Unsur'
        ],
        'Ikatan Kimia': [
            'Ikatan Ion',
            'Ikatan Kovalen',
            'Ikatan Logam'
        ],
        'Reaksi Redoks': [
            'Bilangan Oksidasi',
            'Penyetaraan Reaksi Redoks',
            'Sel Elektrokimia'
        ],
        'Hidrolisis Garam': [
            'Jenis-jenis Garam',
            'pH Larutan Garam',
            'Penerapan Hidrolisis'
        ],
        'Asam Basa': [
            'Teori Asam Basa',
            'pH dan Derajat Keasaman',
            'Titrasi Asam Basa'
        ],
        'Termokimia': [
            'Perubahan Entalpi',
            'Hukum Hess',
            'Energi Ikatan'
        ],
        'Larutan': [
            'Konsentrasi Larutan',
            'Sifat Koligatif',
            'pH dan Keseimbangan'
        ],
        'Redoks': [
            'Oksidasi dan Reduksi',
            'Reaksi Autoredoks',
            'Elektrokimia'
        ]
    },
    'biologi': {
        'Struktur dan Fungsi Sel': [
            'Organel Sel',
            'Membran Sel',
            'Transpor Membran'
        ],
        'Sistem Pencernaan': [
            'Organ Pencernaan',
            'Proses Pencernaan',
            'Gangguan Pencernaan'
        ],
        'Sistem Pernapasan': [
            'Organ Pernapasan',
            'Mekanisme Pernapasan',
            'Gangguan Pernapasan'
        ],
        'Sistem Peredaran Darah': [
            'Jantung dan Pembuluh Darah',
            'Mekanisme Sirkulasi',
            'Gangguan Sirkulasi'
        ],
        'Genetika': [
            'Hukum Mendel',
            'Pewarisan Sifat',
            'Peta Silang'
        ],
        'Ekologi': [
            'Interaksi Makhluk Hidup',
            'Rantai dan Jaring Makanan',
            'Daur Biogeokimia'
        ],
        'Evolusi': [
            'Teori Evolusi',
            'Bukti Evolusi',
            'Faktor Evolusi'
        ],
        'Bioteknologi': [
            'Prinsip Bioteknologi',
            'Penerapan Bioteknologi',
            'Dampak Bioteknologi'
        ]
    }
};

function updateSubTopics() {
    const mataPelajaranSelect = document.getElementById('mata_pelajaran');
    const subTopikSelect = document.getElementById('sub_topik');
    const topikSpesifikContainer = document.getElementById('topik_spesifik_container');
    const topikSpesifikSelect = document.getElementById('topik_spesifik');

    // Clear existing options except the first one
    subTopikSelect.innerHTML = '<option value="">Pilih Sub Topik</option>';
    topikSpesifikSelect.innerHTML = '<option value="">Pilih Topik Spesifik</option>';
    topikSpesifikContainer.style.display = 'none'; // Hide specific topic container initially

    const selectedMataPelajaran = mataPelajaranSelect.value;

    if (selectedMataPelajaran && subTopics[selectedMataPelajaran]) {
        const topics = subTopics[selectedMataPelajaran];

        topics.forEach(topic => {
            const option = document.createElement('option');
            option.value = topic;
            option.textContent = topic;
            subTopikSelect.appendChild(option);
        });
    }
}

function updateTopikSpesifik() {
    const mataPelajaranSelect = document.getElementById('mata_pelajaran');
    const subTopikSelect = document.getElementById('sub_topik');
    const topikSpesifikSelect = document.getElementById('topik_spesifik');
    const topikSpesifikContainer = document.getElementById('topik_spesifik_container');

    // Reset and hide the specific topic selector initially
    topikSpesifikSelect.innerHTML = '<option value="">Pilih Topik Spesifik</option>';
    topikSpesifikContainer.style.display = 'none';

    const selectedMataPelajaran = mataPelajaranSelect.value;
    const selectedSubTopik = subTopikSelect.value;

    if (selectedMataPelajaran && selectedSubTopik && topikSpesifikMap[selectedMataPelajaran] && topikSpesifikMap[selectedMataPelajaran][selectedSubTopik]) {
        const spesifikTopiks = topikSpesifikMap[selectedMataPelajaran][selectedSubTopik];

        spesifikTopiks.forEach(topik => {
            const option = document.createElement('option');
            option.value = topik;
            option.textContent = topik;
            topikSpesifikSelect.appendChild(option);
        });

        // Show the specific topic container only if there are specific topics available
        topikSpesifikContainer.style.display = 'block';
    }
}
</script>

<script>
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>