<?php
// test_material_system.php - File untuk menguji sistem materi pelajaran

// Mulai sesi
session_start();

// Cek apakah kita bisa mengakses database
require_once 'config/database.php';

echo "<h2>✅ Uji Koneksi Sistem Materi Pelajaran</h2>";

// Uji koneksi database
if ($pdo) {
    echo "<p>✅ Koneksi database berhasil</p>";
    
    // Cek struktur tabel
    try {
        $stmt = $pdo->query("DESCRIBE materi_pelajaran");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Struktur Tabel 'materi_pelajaran':</h3>";
        foreach ($columns as $column) {
            echo "<p>• $column</p>";
        }
        
        // Cek jumlah materi
        $countStmt = $pdo->query("SELECT COUNT(*) FROM materi_pelajaran");
        $count = $countStmt->fetchColumn();
        echo "<p>Jumlah materi dalam database: $count</p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Error saat mengakses tabel: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Koneksi database gagal</p>";
}

// Info tentang sistem
echo "<h3>📁 Struktur Folder Aplikasi:</h3>";
echo "<p>• Folder Utama: C:/xampp/htdocs/Sinarilmu</p>";
echo "<p>• Folder Upload: C:/xampp/htdocs/Sinarilmu/uploads/materials</p>";

$uploadDir = 'uploads/materials';
if (is_dir($uploadDir)) {
    echo "<p>✅ Folder upload ditemukan</p>";
    $files = scandir($uploadDir);
    $materialFiles = array_filter($files, function($file) {
        return $file !== '.' && $file !== '..';
    });
    echo "<p>Jumlah file materi: " . count($materialFiles) . "</p>";
} else {
    echo "<p>❌ Folder upload tidak ditemukan</p>";
}

echo "<h3>🔧 Panduan Penggunaan Sistem Materi:</h3>";
echo "<ol>";
echo "<li>Login ke admin panel: http://localhost/Sinarilmu/admin/</li>";
echo "<li>Menuju ke 'Kelola Materi' untuk mengupload materi pelajaran</li>";
echo "<li>Pilih mata pelajaran, kelas, dan sub topik yang sesuai</li>";
echo "<li>Pilih topik spesifik jika tersedia untuk materi yang lebih detail</li>";
echo "<li>Login ke dashboard siswa untuk melihat dan mengakses materi</li>";
echo "</ol>";

echo "<h3>🔗 Link Penting:</h3>";
echo "<p>• Admin Panel: <a href='admin/'>http://localhost/Sinarilmu/admin/</a></p>";
echo "<p>• Dashboard Siswa: <a href='dashboard/'>http://localhost/Sinarilmu/dashboard/</a></p>";
echo "<p>• Homepage: <a href='./'>http://localhost/Sinarilmu/</a></p>";
?>