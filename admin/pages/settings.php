<?php
// admin/pages/settings.php - Pengaturan sistem untuk admin

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

// Ambil pengaturan sistem dari database atau buat default jika belum ada
$system_settings = [
    'app_name' => 'Sinar Ilmu',
    'app_description' => 'Aplikasi belajar berbasis kecerdasan buatan',
    'maintenance_mode' => false,
    'max_upload_size' => '10MB',
    'allowed_file_types' => 'pdf,doc,docx,jpg,jpeg,png',
    'contact_email' => 'admin@sinarilmu.com',
    'contact_phone' => '+62 812 3456 7890',
    'contact_address' => 'Jl. Pendidikan No. 123, Jakarta'
];

// Proses penyimpanan pengaturan jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Di sini akan ditambahkan logika untuk menyimpan pengaturan ke database
    // Untuk sekarang kita hanya menampilkan pesan bahwa pengaturan berhasil disimpan
    
    $system_settings['app_name'] = htmlspecialchars($_POST['app_name'] ?? 'Sinar Ilmu');
    $system_settings['app_description'] = htmlspecialchars($_POST['app_description'] ?? 'Aplikasi belajar berbasis kecerdasan buatan');
    $system_settings['maintenance_mode'] = isset($_POST['maintenance_mode']);
    $system_settings['max_upload_size'] = htmlspecialchars($_POST['max_upload_size'] ?? '10MB');
    $system_settings['allowed_file_types'] = htmlspecialchars($_POST['allowed_file_types'] ?? 'pdf,doc,docx,jpg,jpeg,png');
    $system_settings['contact_email'] = htmlspecialchars($_POST['contact_email'] ?? 'admin@sinarilmu.com');
    $system_settings['contact_phone'] = htmlspecialchars($_POST['contact_phone'] ?? '+62 812 3456 7890');
    $system_settings['contact_address'] = htmlspecialchars($_POST['contact_address'] ?? 'Jl. Pendidikan No. 123, Jakarta');
    
    $success_message = "Pengaturan berhasil diperbarui.";
}
?>

<div class="max-w-4xl mx-auto">
    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Pengaturan Sistem</h2>
        
        <form method="post" class="space-y-6">
            <div>
                <label for="app_name" class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                <input type="text" id="app_name" name="app_name" value="<?php echo htmlspecialchars($system_settings['app_name']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="app_description" class="block text-sm font-medium text-gray-700">Deskripsi Aplikasi</label>
                <textarea id="app_description" name="app_description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($system_settings['app_description']); ?></textarea>
            </div>
            
            <div class="flex items-center">
                <input id="maintenance_mode" name="maintenance_mode" type="checkbox" <?php echo $system_settings['maintenance_mode'] ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="maintenance_mode" class="ml-2 block text-sm text-gray-900">Mode Perawatan</label>
            </div>
            
            <div>
                <label for="max_upload_size" class="block text-sm font-medium text-gray-700">Ukuran Maksimal Upload (MB)</label>
                <input type="text" id="max_upload_size" name="max_upload_size" value="<?php echo htmlspecialchars($system_settings['max_upload_size']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="allowed_file_types" class="block text-sm font-medium text-gray-700">Tipe File yang Diizinkan</label>
                <input type="text" id="allowed_file_types" name="allowed_file_types" value="<?php echo htmlspecialchars($system_settings['allowed_file_types']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-sm text-gray-500">Pisahkan dengan koma, contoh: pdf,doc,docx,jpg,jpeg,png</p>
            </div>
            
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900">Kontak Informasi</h3>
                
                <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="contact_email" class="block text-sm font-medium text-gray-700">Email Kontak</label>
                        <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($system_settings['contact_email']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="sm:col-span-3">
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                        <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($system_settings['contact_phone']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="sm:col-span-6">
                        <label for="contact_address" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea id="contact_address" name="contact_address" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($system_settings['contact_address']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Tentang Sistem</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><span class="font-medium">Versi Aplikasi:</span> 1.0.0</p>
                <p><span class="font-medium">Dibangun dengan:</span> PHP Native, TailwindCSS</p>
                <p><span class="font-medium">Database:</span> MySQL</p>
            </div>
            <div>
                <p><span class="font-medium">Pengembang:</span> Tim Sinar Ilmu</p>
                <p><span class="font-medium">Tanggal Rilis:</span> November 2025</p>
                <p><span class="font-medium">Lisensi:</span> Proprietary</p>
            </div>
        </div>
    </div>
</div>