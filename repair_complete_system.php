<?php
// repair_complete_material_system.php - Perbaikan menyeluruh sistem materi pelajaran

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Perbaikan Sistem Materi Pelajaran - Sinar Ilmu</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-50'>
    <div class='max-w-4xl mx-auto p-8'>
        <h1 class='text-3xl font-bold text-center text-gray-800 mb-8'>🔧 Perbaikan Sistem Materi Pelajaran</h1>
        
        <div class='bg-white rounded-xl shadow-lg p-8'>";

if ($pdo) {
    echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
        <h2 class='text-xl font-bold text-green-800'>✅ Koneksi Database</h2>
        <p class='text-green-700'>Koneksi database berhasil terhubung</p>
    </div>";
    
    try {
        // Periksa dan perbaiki tabel materi_pelajaran
        $stmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
        
        if ($stmt->rowCount() == 0) {
            // Buat tabel jika tidak ada
            $createTableQuery = "
            CREATE TABLE materi_pelajaran (
                id INT AUTO_INCREMENT PRIMARY KEY,
                judul VARCHAR(255) NOT NULL,
                deskripsi TEXT,
                kelas ENUM('10', '11', '12') NOT NULL,
                mata_pelajaran ENUM('matematika', 'fisika', 'kimia', 'biologi', 'bahasa_indonesia', 'bahasa_inggris', 'sejarah', 'geografi', 'ekonomi', 'sosiologi', 'lainnya') NOT NULL,
                sub_topik VARCHAR(255),
                topik_spesifik VARCHAR(255),
                file_path VARCHAR(500) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_size INT,
                file_type VARCHAR(50),
                created_by INT NOT NULL,
                status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
            );
            
            CREATE INDEX idx_kelas_pelajaran ON materi_pelajaran(kelas, mata_pelajaran);
            CREATE INDEX idx_sub_topik ON materi_pelajaran(sub_topik);
            ";
            
            $pdo->exec($createTableQuery);
            echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
                <h2 class='text-xl font-bold text-green-800'>✅ Pembuatan Tabel</h2>
                <p class='text-green-700'>Tabel 'materi_pelajaran' berhasil dibuat dengan struktur lengkap.</p>
            </div>";
        } else {
            // Periksa apakah kolom-kolom penting sudah ada
            $columns_check = $pdo->query("DESCRIBE materi_pelajaran");
            $existing_columns = $columns_check->fetchAll(PDO::FETCH_COLUMN);
            
            $required_columns = ['sub_topik', 'topik_spesifik', 'deskripsi', 'file_size', 'file_type', 'status', 'updated_at'];
            $missing_columns = array_diff($required_columns, $existing_columns);
            
            if (empty($missing_columns)) {
                echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
                    <h2 class='text-xl font-bold text-green-800'>✅ Struktur Tabel</h2>
                    <p class='text-green-700'>Tabel 'materi_pelajaran' sudah memiliki semua kolom yang diperlukan.</p>
                </div>";
            } else {
                echo "<div class='mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200'>
                    <h2 class='text-xl font-bold text-yellow-800'>⚠️ Kolom Tambahan</h2>
                    <p class='text-yellow-700'>Menambahkan kolom yang hilang: " . implode(', ', $missing_columns) . "</p>";
                    
                foreach ($missing_columns as $col) {
                    switch ($col) {
                        case 'sub_topik':
                            $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN sub_topik VARCHAR(255) AFTER mata_pelajaran");
                            break;
                        case 'topik_spesifik':
                            $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN topik_spesifik VARCHAR(255) AFTER sub_topik");
                            break;
                        case 'deskripsi':
                            $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN deskripsi TEXT AFTER judul");
                            break;
                        case 'file_size':
                            $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN file_size INT AFTER original_name");
                            break;
                        case 'file_type':
                            $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN file_type VARCHAR(50) AFTER file_size");
                            break;
                        case 'status':
                            $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN status ENUM('aktif', 'nonaktif') DEFAULT 'aktif' AFTER created_by");
                            break;
                        case 'updated_at':
                            $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
                            break;
                    }
                }
                echo "<p class='text-green-700 mt-2'>✅ Kolom tambahan berhasil ditambahkan.</p>
            </div>";
        }
        
        // Periksa jumlah data
        $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM materi_pelajaran WHERE status = 'aktif'");
        $count = $count_stmt->fetch();
        echo "<div class='mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200'>
            <h2 class='text-xl font-bold text-blue-800'>📊 Statistik Data</h2>
            <p class='text-blue-700'>Jumlah total materi aktif: <strong>" . $count['total'] . "</strong> file</p>
        </div>";
    } catch (Exception $e) {
        echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
            <h2 class='text-xl font-bold text-red-800'>❌ Kesalahan</h2>
            <p class='text-red-700'>Error saat memperbaiki tabel: " . $e->getMessage() . "</p>
        </div>";
    }
    
    // Periksa direktori upload
    $upload_dir = 'uploads/materials';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
        echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
            <h2 class='text-xl font-bold text-green-800'>✅ Direktori Upload</h2>
            <p class='text-green-700'>Direktori '$upload_dir' berhasil dibuat.</p>
        </div>";
    } else {
        echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
            <h2 class='text-xl font-bold text-green-800'>✅ Direktori Upload</h2>
            <p class='text-green-700'>Direktori '$upload_dir' sudah ada.</p>
        </div>";
    }
} else {
    echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
        <h2 class='text-xl font-bold text-red-800'>❌ Koneksi Database</h2>
        <p class='text-red-700'>Koneksi database gagal. Pastikan MySQL berjalan dan konfigurasi benar.</p>
    </div>";
}

echo "<div class='mb-8 p-6 bg-yellow-50 rounded-xl border border-yellow-200'>
    <h2 class='text-2xl font-bold text-yellow-800 mb-4'>💡 Panduan Penggunaan Sistem Setelah Perbaikan</h2>
    <div class='grid grid-cols-1 md:grid-cols-2 gap-6'>
        <div>
            <h3 class='text-lg font-semibold text-yellow-700 mb-3'>Untuk Admin:</h3>
            <ul class='list-disc pl-5 space-y-2 text-yellow-700'>
                <li>Login ke admin panel: <a href='admin/' class='text-blue-600 underline'>http://localhost/Sinarilmu/admin/</a></li>
                <li>Menuju ke 'Kelola Materi' untuk upload materi pelajaran</li>
                <li>Pilih mata pelajaran, kelas, dan sub-topik spesifik</li>
                <li>Gunakan topik spesifik untuk pengelompokan lebih lanjut</li>
            </ul>
        </div>
        <div>
            <h3 class='text-lg font-semibold text-yellow-700 mb-3'>Untuk Siswa:</h3>
            <ul class='list-disc pl-5 space-y-2 text-yellow-700'>
                <li>Login ke dashboard siswa: <a href='dashboard/' class='text-blue-600 underline'>http://localhost/Sinarilmu/dashboard/</a></li>
                <li>Akses 'Analisis Materi' untuk melihat materi pelajaran</li>
                <li>Materi terorganisir berdasarkan kelas, mata pelajaran, dan sub-topik</li>
                <li>Cari materi spesifik menggunakan filter yang tersedia</li>
            </ul>
        </div>
    </div>
</div>";

echo "<div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
    <a href='dashboard/?page=analisis_materi' class='block p-4 bg-green-100 hover:bg-green-200 rounded-lg border border-green-300 text-center transition-colors'>
        <h3 class='font-bold text-green-800 mb-2'>📚 Lihat Materi</h3>
        <p class='text-green-700 text-sm'>Akses halaman materi pelajaran</p>
    </a>
    
    <a href='admin/' class='block p-4 bg-purple-100 hover:bg-purple-200 rounded-lg border border-purple-300 text-center transition-colors'>
        <h3 class='font-bold text-purple-800 mb-2'>🔧 Admin Panel</h3>
        <p class='text-purple-700 text-sm'>Manage materi pelajaran</p>
    </a>
    
    <a href='?page=test_material_system' class='block p-4 bg-blue-100 hover:bg-blue-200 rounded-lg border border-blue-300 text-center transition-colors'>
        <h3 class='font-bold text-blue-800 mb-2'>🧪 Uji Sistem</h3>
        <p class='text-blue-700 text-sm'>Uji fungsionalitas sistem</p>
    </a>
</div>";

echo "<div class='mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200 text-center'>
    <h3 class='font-bold text-gray-800 mb-2'>🔄 Jika Masih Ada Masalah</h3>
    <p class='text-gray-700 text-sm'>Coba restart Apache dan MySQL di XAMPP Control Panel</p>
</div>";

echo "        </div>
    </div>
</body>
</html>";
?>