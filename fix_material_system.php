<?php
// fix_material_system.php - Perbaikan menyeluruh sistem materi pelajaran

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Perbaikan Sistem Materi Pelajaran - Sinar Ilmu</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100'>
    <div class='max-w-4xl mx-auto p-8'>
        <h1 class='text-3xl font-bold text-center text-gray-800 mb-8'>🔧 Perbaikan Sistem Materi Pelajaran</h1>
        
        <div class='bg-white rounded-xl shadow-lg p-8'>";

// Periksa koneksi database
if ($pdo) {
    echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
        <h2 class='text-xl font-bold text-green-800'>✅ Koneksi Database</h2>
        <p class='text-green-700'>Koneksi ke database berhasil.</p>
    </div>";
    
    try {
        // Periksa apakah tabel materi_pelajaran ada
        $table_result = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
        
        if ($table_result->rowCount() == 0) {
            // Buat tabel materi_pelajaran jika tidak ada
            $create_sql = "
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
            
            $pdo->exec($create_sql);
            echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
                <h2 class='text-xl font-bold text-green-800'>✅ Pembuatan Tabel</h2>
                <p class='text-green-700'>Tabel 'materi_pelajaran' berhasil dibuat dengan struktur lengkap.</p>
            </div>";
        } else {
            // Periksa struktur tabel dan tambahkan kolom jika diperlukan
            $columns_stmt = $pdo->query("DESCRIBE materi_pelajaran");
            $columns = $columns_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $existing_columns = array_column($columns, 'Field');
            
            // Kolom-kolom yang dibutuhkan
            $required_columns = [
                'id', 'judul', 'deskripsi', 'kelas', 'mata_pelajaran', 'sub_topik', 
                'topik_spesifik', 'file_path', 'original_name', 'file_size', 
                'file_type', 'created_by', 'status', 'created_at', 'updated_at'
            ];
            
            $missing_columns = array_diff($required_columns, $existing_columns);
            
            if (empty($missing_columns)) {
                echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
                    <h2 class='text-xl font-bold text-green-800'>✅ Struktur Tabel</h2>
                    <p class='text-green-700'>Tabel 'materi_pelajaran' sudah memiliki semua kolom yang diperlukan.</p>
                </div>";
            } else {
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
                
                echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
                    <h2 class='text-xl font-bold text-green-800'>✅ Perbaikan Struktur</h2>
                    <p class='text-green-700'>Kolom yang hilang telah ditambahkan: " . implode(', ', $missing_columns) . "</p>
                </div>";
            }
        }
        
        // Periksa jumlah materi yang ada
        $count_result = $pdo->query("SELECT COUNT(*) as total FROM materi_pelajaran WHERE status = 'aktif'");
        $count = $count_result->fetch();
        
        echo "<div class='mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200'>
            <h2 class='text-xl font-bold text-blue-800'>📊 Statistik Materi</h2>
            <p class='text-blue-700'>Jumlah total materi aktif: <strong>" . $count['total'] . "</strong> file</p>
        </div>";
        
        // Buat direktori upload jika belum ada
        $upload_dir = 'uploads/materials';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
            echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
                <h2 class='text-xl font-bold text-green-800'>📁 Direktori Upload</h2>
                <p class='text-green-700'>Direktori upload '$upload_dir' berhasil dibuat.</p>
            </div>";
        } else {
            echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
                <h2 class='text-xl font-bold text-green-800'>📁 Direktori Upload</h2>
                <p class='text-green-700'>Direktori upload '$upload_dir' sudah ada.</p>
            </div>";
        }
        
        echo "<div class='mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200'>
            <h2 class='text-xl font-bold text-blue-800'>✅ Perbaikan Selesai</h2>
            <p class='text-blue-700'>Sistem materi pelajaran telah diperbaiki dan siap digunakan.</p>
        </div>";
        
    } catch (Exception $e) {
        echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
            <h2 class='text-xl font-bold text-red-800'>❌ Kesalahan</h2>
            <p class='text-red-700'>Error: " . $e->getMessage() . "</p>
        </div>";
    }
} else {
    echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
        <h2 class='text-xl font-bold text-red-800'>❌ Koneksi Database</h2>
        <p class='text-red-700'>Tidak dapat terhubung ke database. Pastikan MySQL berjalan dan konfigurasi benar.</p>
    </div>";
}

echo "<div class='grid grid-cols-1 md:grid-cols-3 gap-4 mb-8'>";
echo "<a href='dashboard/?page=analisis_materi' class='bg-green-100 hover:bg-green-200 border border-green-300 text-green-800 font-semibold py-3 px-4 rounded-lg text-center transition-colors'>Lihat Materi Pelajaran</a>";
echo "<a href='admin/' class='bg-purple-100 hover:bg-purple-200 border border-purple-300 text-purple-800 font-semibold py-3 px-4 rounded-lg text-center transition-colors'>Admin Panel</a>";
echo "<a href='?page=home' class='bg-blue-100 hover:bg-blue-200 border border-blue-300 text-blue-800 font-semibold py-3 px-4 rounded-lg text-center transition-colors'>Beranda</a>";
echo "</div>";

echo "<div class='p-4 bg-yellow-50 rounded-lg border border-yellow-200'>
    <h3 class='font-bold text-yellow-800 mb-2'>💡 Panduan Penggunaan:</h3>
    <ul class='list-disc pl-5 space-y-1 text-yellow-700'>
        <li>Admin dapat mengupload materi dengan menentukan kelas, mata pelajaran, sub-topik, dan topik spesifik</li>
        <li>Siswa dapat melihat materi terorganisir berdasarkan kelas, pelajaran, dan sub-topik</li>
        <li>Semua materi tersedia di menu Analisis Materi</li>
    </ul>
</div>";

echo "        </div>
    </div>
</body>
</html>";
?>