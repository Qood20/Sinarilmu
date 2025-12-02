<?php
// full_repair_system.php - File untuk perbaikan menyeluruh sistem materi pelajaran

// Cek dan mulai sesi jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

// 1. Periksa koneksi database
echo "<div class='mb-6'>";
if ($pdo) {
    echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200 mb-4'>
        <h2 class='text-xl font-bold text-green-800'>✅ Koneksi Database</h2>
        <p class='text-green-700'>Koneksi database berhasil terhubung</p>
    </div>";
} else {
    echo "<div class='p-4 bg-red-50 rounded-lg border border-red-200 mb-4'>
        <h2 class='text-xl font-bold text-red-800'>❌ Koneksi Database</h2>
        <p class='text-red-700'>Koneksi database gagal. Pastikan MySQL berjalan dan konfigurasi database benar.</p>
    </div>";
    die();
}
echo "</div>";

// 2. Periksa dan perbaiki tabel materi_pelajaran
echo "<div class='mb-6'>";
try {
    // Cek apakah tabel ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
    
    if ($stmt->rowCount() == 0) {
        // Buat tabel jika belum ada
        $createTableSQL = "
        CREATE TABLE materi_pelajaran (
            id INT AUTO_INCREMENT PRIMARY KEY,
            judul VARCHAR(255) NOT NULL,
            deskripsi TEXT,
            kelas ENUM('10', '11', '12') NOT NULL,
            mata_pelajaran ENUM(
                'matematika', 'fisika', 'kimia', 'biologi',
                'bahasa_indonesia', 'bahasa_inggris', 'sejarah', 'geografi',
                'ekonomi', 'sosiologi', 'lainnya'
            ) NOT NULL,
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
        
        $pdo->exec($createTableSQL);
        echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200'>
            <h2 class='text-xl font-bold text-green-800'>✅ Pembuatan Tabel</h2>
            <p class='text-green-700'>Tabel 'materi_pelajaran' berhasil dibuat.</p>
        </div>";
    } else {
        // Periksa kolom-kolom penting
        $columns = $pdo->query("DESCRIBE materi_pelajaran")->fetchAll();
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = ['judul', 'kelas', 'mata_pelajaran', 'sub_topik', 'topik_spesifik', 'file_path', 'original_name', 'created_by', 'status'];
        $missingColumns = array_diff($requiredColumns, $columnNames);
        
        if (empty($missingColumns)) {
            echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200'>
                <h2 class='text-xl font-bold text-green-800'>✅ Tabel Materi_pelajaran</h2>
                <p class='text-green-700'>Tabel 'materi_pelajaran' sudah ada dengan semua kolom yang diperlukan.</p>
            </div>";
        } else {
            echo "<div class='p-4 bg-yellow-50 rounded-lg border border-yellow-200'>
                <h2 class='text-xl font-bold text-yellow-800'>⚠️ Kolom Hilang</h2>
                <p class='text-yellow-700'>Beberapa kolom hilang: " . implode(', ', $missingColumns) . "</p>";
                
                // Tambahkan kolom yang hilang
                foreach ($missingColumns as $col) {
                    if ($col === 'sub_topik') {
                        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN sub_topik VARCHAR(255) AFTER mata_pelajaran");
                    } elseif ($col === 'topik_spesifik') {
                        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN topik_spesifik VARCHAR(255) AFTER sub_topik");
                    } elseif ($col === 'judul') {
                        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN judul VARCHAR(255) NOT NULL DEFAULT 'Untitled' AFTER id");
                    } elseif ($col === 'file_path') {
                        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN file_path VARCHAR(500) NOT NULL DEFAULT '/files/default.pdf' AFTER topik_spesifik");
                    } elseif ($col === 'original_name') {
                        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN original_name VARCHAR(255) NOT NULL DEFAULT 'file.pdf' AFTER file_path");
                    } elseif ($col === 'created_by') {
                        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER file_type");
                    } elseif ($col === 'status') {
                        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN status ENUM('aktif', 'nonaktif') DEFAULT 'aktif' AFTER created_by");
                    }
                }
                
                echo "<p class='text-green-700 mt-2'>Kolom yang hilang telah ditambahkan.</p>";
            </div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='p-4 bg-red-50 rounded-lg border border-red-200'>
        <h2 class='text-xl font-bold text-red-800'>❌ Kesalahan Tabel</h2>
        <p class='text-red-700'>Error: " . $e->getMessage() . "</p>
    </div>";
}
echo "</div>";

// 3. Periksa direktori upload
echo "<div class='mb-6'>";
$uploadDir = 'uploads/materials';
if (!file_exists($uploadDir)) {
    // Buat direktori jika belum ada
    if (mkdir($uploadDir, 0755, true)) {
        echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200'>
            <h2 class='text-xl font-bold text-green-800'>✅ Direktori Upload</h2>
            <p class='text-green-700'>Direktori '$uploadDir' berhasil dibuat.</p>
        </div>";
    } else {
        echo "<div class='p-4 bg-red-50 rounded-lg border border-red-200'>
            <h2 class='text-xl font-bold text-red-800'>❌ Direktori Upload</h2>
            <p class='text-red-700'>Gagal membuat direktori '$uploadDir'.</p>
        </div>";
    }
} else {
    echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200'>
        <h2 class='text-xl font-bold text-green-800'>✅ Direktori Upload</h2>
        <p class='text-green-700'>Direktori '$uploadDir' sudah ada.</p>
    </div>";
}
echo "</div>";

// 4. Periksa jumlah data
echo "<div class='mb-6'>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materi_pelajaran WHERE status = 'aktif'");
    $count = $stmt->fetch();
    echo "<div class='p-4 bg-blue-50 rounded-lg border border-blue-200'>
        <h2 class='text-xl font-bold text-blue-800'>📊 Statistik Data</h2>
        <p class='text-blue-700'>Jumlah total materi aktif: <strong>" . $count['total'] . "</strong> file</p>
    </div>";
    $jumlah_materi = $count['total'];
} catch (Exception $e) {
    echo "<div class='p-4 bg-red-50 rounded-lg border border-red-200'>
        <h2 class='text-xl font-bold text-red-800'>❌ Kesalahan Statistik</h2>
        <p class='text-red-700'>Tidak dapat menghitung data materi: " . $e->getMessage() . "</p>
    </div>";
    $jumlah_materi = 0;
}
echo "</div>";

// 5. Informasi sistem
echo "<div class='mb-8 p-6 bg-yellow-50 rounded-xl border border-yellow-200'>";
echo "<h2 class='text-2xl font-bold text-yellow-800 mb-4'>🔧 Panduan Penggunaan Sistem Setelah Perbaikan</h2>";
echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>";
echo "<div>";
echo "<h3 class='text-lg font-semibold text-yellow-700 mb-3'>Untuk Admin:</h3>";
echo "<ul class='list-disc pl-5 space-y-2 text-yellow-700'>";
echo "<li>Login ke admin panel: <a href='admin/' class='text-blue-600 underline'>http://localhost/Sinarilmu/admin/</a></li>";
echo "<li>Menuju ke \"Kelola Materi\" untuk upload materi pelajaran</li>";
echo "<li>Pilih mata pelajaran, kelas, dan sub-topik spesifik</li>";
echo "<li>Gunakan topik spesifik untuk mengelompokkan materi lebih detail</li>";
echo "</ul>";
echo "</div>";

echo "<div>";
echo "<h3 class='text-lg font-semibold text-yellow-700 mb-3'>Untuk Siswa:</h3>";
echo "<ul class='list-disc pl-5 space-y-2 text-yellow-700'>";
echo "<li>Login ke dashboard siswa: <a href='dashboard/' class='text-blue-600 underline'>http://localhost/Sinarilmu/dashboard/</a></li>";
echo "<li>Akses \"Analisis Materi\" untuk melihat materi pelajaran</li>";
echo "<li>Materi akan ditampilkan terorganisir berdasarkan kelas, mata pelajaran, dan sub-topik</li>";
echo "<li>Cari materi spesifik menggunakan filter yang tersedia</li>";
echo "</ul>";
echo "</div>";
echo "</div>";
echo "</div>";

// 6. Tombol akses cepat
echo "<div class='grid grid-cols-1 md:grid-cols-3 gap-4'>";
echo "<a href='?page=test_material_system' class='block p-4 bg-green-100 hover:bg-green-200 rounded-lg border border-green-300 text-center transition-colors'>";
echo "<h3 class='font-bold text-green-800 mb-2'>🧪 Uji Sistem</h3>";
echo "<p class='text-green-700 text-sm'>Uji fungsionalitas sistem materi</p>";
echo "</a>";

echo "<a href='dashboard/?page=analisis_materi' class='block p-4 bg-blue-100 hover:bg-blue-200 rounded-lg border border-blue-300 text-center transition-colors'>";
echo "<h3 class='font-bold text-blue-800 mb-2'>📚 Lihat Materi</h3>";
echo "<p class='text-blue-700 text-sm'>Akses halaman materi pelajaran</p>";
echo "</a>";

echo "<a href='admin/' class='block p-4 bg-purple-100 hover:bg-purple-200 rounded-lg border border-purple-300 text-center transition-colors'>";
echo "<h3 class='font-bold text-purple-800 mb-2'>🔧 Admin Panel</h3>";
echo "<p class='text-purple-700 text-sm'>Manage materi pelajaran</p>";
echo "</a>";
echo "</div>";

echo "        </div>
    </div>
    
    <div class='text-center mt-8 text-gray-600'>
        <p>Perbaikan sistem selesai. Jika masih ada masalah, coba restart Apache dan MySQL di XAMPP.</p>
    </div>
</body>
</html>";
?>