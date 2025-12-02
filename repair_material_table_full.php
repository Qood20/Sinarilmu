<?php
// repair_material_table_full.php - File untuk perbaikan lengkap tabel materi_pelajaran

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Perbaikan Tabel Materi Pelajaran - Sinar Ilmu</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-50'>
    <div class='max-w-4xl mx-auto p-8'>
        <h1 class='text-3xl font-bold text-center text-gray-800 mb-8'>🔧 Perbaikan Tabel Materi Pelajaran</h1>
        
        <div class='bg-white rounded-xl shadow-lg p-8'>";

try {
    // Mulai transaksi
    $pdo->beginTransaction();
    
    // Cek apakah tabel materi_pelajaran (nama yang benar) ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
    if ($stmt->rowCount() == 0) {
        // Buat tabel jika tidak ada
        $sql = "CREATE TABLE materi_pelajaran (
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
        
        CREATE INDEX idx_materi_pelajaran_kelas ON materi_pelajaran(kelas);
        CREATE INDEX idx_materi_pelajaran_mata_pelajaran ON materi_pelajaran(mata_pelajaran);
        CREATE INDEX idx_materi_pelajaran_created_by ON materi_pelajaran(created_by);
        ";
        
        $pdo->exec($sql);
        echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200 mb-4'>
            <h2 class='text-xl font-bold text-green-800'>✅ Pembuatan Tabel</h2>
            <p class='text-green-700'>Tabel 'materi_pelajaran' berhasil dibuat dengan struktur lengkap.</p>
        </div>";
    } else {
        // Periksa apakah kolom-kolom penting sudah ada
        $columnsStmt = $pdo->query("DESCRIBE materi_pelajaran");
        $existingColumns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Kolom yang diperlukan
        $requiredColumns = [
            'judul', 'deskripsi', 'kelas', 'mata_pelajaran', 'sub_topik', 
            'topik_spesifik', 'file_path', 'original_name', 'file_size', 
            'file_type', 'created_by', 'status', 'created_at', 'updated_at'
        ];
        
        $missingColumns = array_diff($requiredColumns, $existingColumns);
        
        if (empty($missingColumns)) {
            echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200 mb-4'>
                <h2 class='text-xl font-bold text-green-800'>✅ Struktur Tabel</h2>
                <p class='text-green-700'>Tabel 'materi_pelajaran' sudah memiliki semua kolom yang diperlukan.</p>
            </div>";
        } else {
            echo "<div class='p-4 bg-yellow-50 rounded-lg border border-yellow-200 mb-4'>
                <h2 class='text-xl font-bold text-yellow-800'>⚠️ Kolom Tambahan</h2>
                <p class='text-yellow-700'>Menambahkan kolom yang hilang: " . implode(', ', $missingColumns) . "</p>";
                
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
            echo "<p class='text-green-700 mt-2'>✅ Kolom tambahan berhasil ditambahkan.</p>";
        </div>";
    }
}

// Cek juga tabel lama dan buat relasinya jika diperlukan
$tableCheck = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
if ($tableCheck->rowCount() > 0) {
    echo "<div class='p-4 bg-blue-50 rounded-lg border border-blue-200 mb-4'>
        <h2 class='text-xl font-bold text-blue-800'>📊 Status Tabel</h2>
        <p class='text-blue-700'>Tabel 'materi_pelajaran' ditemukan dan siap digunakan.</p>
        
        <!-- Jumlah data -->
        try {
            $countStmt = $pdo->query("SELECT COUNT(*) as total FROM materi_pelajaran WHERE status = 'aktif'");
            $count = $countStmt->fetch();
            echo "<p class='text-blue-700 mt-2'>Jumlah materi aktif: <strong>" . $count['total'] . "</strong> file</p>";
        } catch (Exception $e) {
            echo "<p class='text-red-700 mt-2'>Tidak dapat menghitung jumlah data: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='p-4 bg-red-50 rounded-lg border border-red-200 mb-4'>
        <h2 class='text-xl font-bold text-red-800'>❌ Kesalahan</h2>
        <p class='text-red-700'>Error saat memperbaiki tabel: " . $e->getMessage() . "</p>
    </div>";
    $pdo->rollback();
    exit;
}

// Commit transaksi
$pdo->commit();

echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200 mb-6'>
    <h2 class='text-xl font-bold text-green-800'>✅ Perbaikan Selesai</h2>
    <p class='text-green-700'>Struktur tabel materi pelajaran telah diperbaiki dan siap digunakan.</p>
</div>";

echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-4'>";
echo "<a href='dashboard/?page=analisis_materi' class='block p-4 bg-blue-100 hover:bg-blue-200 rounded-lg border border-blue-300 text-center transition-colors'>
    <h3 class='font-bold text-blue-800 mb-2'>📚 Lihat Materi</h3>
    <p class='text-blue-700 text-sm'>Akses halaman materi pelajaran</p>
</a>";

echo "<a href='admin/' class='block p-4 bg-purple-100 hover:bg-purple-200 rounded-lg border border-purple-300 text-center transition-colors'>
    <h3 class='font-bold text-purple-800 mb-2'>🔧 Admin Panel</h3>
    <p class='text-purple-700 text-sm'>Manage materi pelajaran</p>
</a>";
echo "</div>";

echo "<div class='mt-8 p-4 bg-yellow-50 rounded-lg border border-yellow-200'>
    <h3 class='font-bold text-yellow-800 mb-2'>💡 Catatan Penting:</h3>
    <ul class='list-disc pl-5 text-yellow-700 space-y-1'>
        <li>Jika masalah masih berlangsung, restart Apache dan MySQL di XAMPP</li>
        <li>Pastikan direktori uploads/materials memiliki izin tulis</li>
        <li>Setelah perbaikan ini, fungsi upload materi dan penampilan materi akan berfungsi normal</li>
    </ul>
</div>";

echo "        </div>
    </div>
</body>
</html>";
?>