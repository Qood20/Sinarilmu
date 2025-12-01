<?php
// admin/pages/content.php - Kelola konten halaman awal aplikasi

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil konten yang tersimpan dari tabel notifikasi atau tabel khusus konten
try {
    // Kita akan menyimpan konten dalam pengaturan sistem sebagai contoh
    $system_settings = [
        'home_description' => 'Selamat datang di Sinar Ilmu, aplikasi belajar berbasis kecerdasan buatan yang dirancang untuk membantu kamu memahami materi pelajaran dengan lebih mudah, cepat, dan interaktif. Unggah materi belajarmu, dapatkan penjabaran otomatis dari AI, serta latihan soal yang dibuat khusus sesuai kebutuhanmu. Mulai perjalanan belajarmu bersama Sinar Ilmu.',
        'about_content' => 'Sinar Ilmu merupakan aplikasi pembelajaran digital yang memanfaatkan teknologi AI untuk membantu pengguna memahami materi dengan lebih efektif. Melalui fitur unggahan file, latihan soal otomatis, hingga layanan tanya jawab interaktif, Sinar Ilmu hadir sebagai pendamping belajar yang cerdas, fleksibel, dan dapat digunakan kapan saja.',
        'contact_email' => 'info@sinarilmu.com',
        'contact_whatsapp' => '+62 812 3456 7890',
        'contact_instagram' => '@sinarilmu',
        'contact_address' => 'Jl. Pendidikan No. 123, Jakarta'
    ];
} catch (Exception $e) {
    error_log("Error getting content settings: " . $e->getMessage());
    $system_settings = [
        'home_description' => '',
        'about_content' => '',
        'contact_email' => '',
        'contact_whatsapp' => '',
        'contact_instagram' => '',
        'contact_address' => ''
    ];
}

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $system_settings['home_description'] = $_POST['home_description'] ?? '';
    $system_settings['about_content'] = $_POST['about_content'] ?? '';
    $system_settings['contact_email'] = $_POST['contact_email'] ?? '';
    $system_settings['contact_whatsapp'] = $_POST['contact_whatsapp'] ?? '';
    $system_settings['contact_instagram'] = $_POST['contact_instagram'] ?? '';
    $system_settings['contact_address'] = $_POST['contact_address'] ?? '';

    // Catat aktivitas
    log_activity($_SESSION['user_id'], 'Update Konten', 'Memperbarui konten halaman utama');

    $_SESSION['success'] = "Konten berhasil diperbarui.";
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Kelola Konten Halaman Awal</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo escape($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <div>
                <label for="home_description" class="block text-sm font-medium text-gray-700">Deskripsi Halaman Utama</label>
                <textarea id="home_description" name="home_description" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo escape($system_settings['home_description']); ?></textarea>
            </div>

            <div>
                <label for="about_content" class="block text-sm font-medium text-gray-700">Tentang Sinar Ilmu</label>
                <textarea id="about_content" name="about_content" rows="6" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo escape($system_settings['about_content']); ?></textarea>
            </div>

            <div>
                <label for="contact_info" class="block text-sm font-medium text-gray-700">Informasi Kontak</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                    <div>
                        <input type="text" name="contact_email" placeholder="Email" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo escape($system_settings['contact_email']); ?>">
                    </div>
                    <div>
                        <input type="text" name="contact_whatsapp" placeholder="WhatsApp" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo escape($system_settings['contact_whatsapp']); ?>">
                    </div>
                    <div>
                        <input type="text" name="contact_instagram" placeholder="Instagram" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo escape($system_settings['contact_instagram']); ?>">
                    </div>
                    <div>
                        <input type="text" name="contact_address" placeholder="Alamat" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo escape($system_settings['contact_address']); ?>">
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Statistik Penggunaan</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <?php
            try {
                $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $total_files = $pdo->query("SELECT COUNT(*) FROM upload_files")->fetchColumn();
                $avg_score = $pdo->query("SELECT COALESCE(AVG(nilai), 0) FROM hasil_soal_user")->fetchColumn();
            } catch (Exception $e) {
                $total_users = 0;
                $total_files = 0;
                $avg_score = 0;
            }
            ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl font-bold text-blue-600"><?php echo $total_users; ?></div>
                <div class="text-gray-600 mt-2">Total Pengguna</div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl font-bold text-green-600"><?php echo $total_files; ?></div>
                <div class="text-gray-600 mt-2">File Diunggah</div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl font-bold text-yellow-600"><?php echo number_format($avg_score, 1); ?></div>
                <div class="text-gray-600 mt-2">Nilai Rata-rata</div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Aktivitas Terbaru</h3>
            <ul class="space-y-3">
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT u.full_name, l.aksi, l.created_at
                        FROM log_aktivitas l
                        LEFT JOIN users u ON l.user_id = u.id
                        ORDER BY l.created_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute();
                    $activities = $stmt->fetchAll();
                } catch (Exception $e) {
                    $activities = [];
                }

                if (!empty($activities)) {
                    foreach ($activities as $activity) {
                        $time_diff = time() - strtotime($activity['created_at']);
                        $minutes = floor($time_diff / 60);
                        if ($minutes < 1) {
                            $time_ago = "Baru saja";
                        } else if ($minutes < 60) {
                            $time_ago = $minutes . " menit yang lalu";
                        } else {
                            $hours = floor($time_diff / 3600);
                            if ($hours < 24) {
                                $time_ago = $hours . " jam yang lalu";
                            } else {
                                $time_ago = date('d M Y', strtotime($activity['created_at']));
                            }
                        }
                        ?>
                        <li class="flex justify-between py-2 border-b border-gray-100">
                            <span><?php echo escape($activity['full_name'] ?? 'Sistem'); ?> <?php echo escape($activity['aksi']); ?></span>
                            <span class="text-gray-500"><?php echo $time_ago; ?></span>
                        </li>
                        <?php
                    }
                } else {
                    echo "<li class='text-center text-gray-500 py-4'>Tidak ada aktivitas terbaru</li>";
                }
                ?>
            </ul>
        </div>
    </div>
</div>