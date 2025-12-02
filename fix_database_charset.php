<?php
// fix_database_charset.php - Perbaikan charset database untuk mendukung emoji

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Perbaikan Charset Database</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100'>
    <div class='max-w-4xl mx-auto p-8'>
        <h1 class='text-3xl font-bold text-center text-gray-800 mb-8'>🔧 Perbaikan Charset Database untuk Mendukung Emoji</h1>
        
        <div class='bg-white rounded-xl shadow-lg p-6'>";

if ($pdo) {
    try {
        // Ubah charset database
        $pdo->exec("ALTER DATABASE dbsinarilmu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<div class='p-4 mb-4 bg-green-50 border border-green-200 rounded-lg'>
            <h2 class='font-bold text-green-800'>✅ Database Charset</h2>
            <p class='text-green-700'>Charset database diubah menjadi utf8mb4</p>
        </div>";
        
        // Ubah charset tabel materi_pelajaran
        $alterTableSql = "
        ALTER TABLE materi_pelajaran 
        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        
        ALTER TABLE materi_pelajaran 
        MODIFY COLUMN judul VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        MODIFY COLUMN deskripsi TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        MODIFY COLUMN sub_topik VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        MODIFY COLUMN topik_spesifik VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        MODIFY COLUMN file_path VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        MODIFY COLUMN original_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        
        $pdo->exec($alterTableSql);
        echo "<div class='p-4 mb-4 bg-green-50 border border-green-200 rounded-lg'>
            <h2 class='font-bold text-green-800'>✅ Tabel Materi_pelajaran</h2>
            <p class='text-green-700'>Charset tabel materi_pelajaran diubah menjadi utf8mb4</p>
        </div>";
        
        // Periksa charset saat ini
        $stmt = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'dbsinarilmu'");
        $charset_info = $stmt->fetch();
        
        echo "<div class='p-4 mb-4 bg-blue-50 border border-blue-200 rounded-lg'>
            <h2 class='font-bold text-blue-800'>📊 Informasi Charset Saat Ini</h2>
            <p class='text-blue-700'>Character Set: <strong>" . $charset_info['DEFAULT_CHARACTER_SET_NAME'] . "</strong></p>
            <p class='text-blue-700'>Collation: <strong>" . $charset_info['DEFAULT_COLLATION_NAME'] . "</strong></p>
        </div>";
        
        // Periksa charset kolom-kolom penting
        $stmt = $pdo->query("
            SELECT COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = 'dbsinarilmu' AND TABLE_NAME = 'materi_pelajaran' 
            AND DATA_TYPE IN ('varchar', 'text')
        ");
        $columns = $stmt->fetchAll();
        
        echo "<div class='p-4 bg-yellow-50 border border-yellow-200 rounded-lg'>
            <h2 class='font-bold text-yellow-800'>🔍 Charset Kolom-Kolom Tabel</h2>";
        
        foreach ($columns as $col) {
            echo "<p class='text-yellow-700'>• " . $col['COLUMN_NAME'] . ": " . ($col['CHARACTER_SET_NAME'] ?: 'binary') . "</p>";
        }
        
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div class='p-4 bg-red-50 border border-red-200 rounded-lg'>
            <h2 class='font-bold text-red-800'>❌ Kesalahan</h2>
            <p class='text-red-700'>Error: " . $e->getMessage() . "</p>
        </div>";
    }
} else {
    echo "<div class='p-4 bg-red-50 border border-red-200 rounded-lg'>
        <h2 class='font-bold text-red-800'>❌ Koneksi Database</h2>
        <p class='text-red-700'>Tidak dapat terhubung ke database</p>
    </div>";
}

echo "</div>

<div class='mt-8 bg-white rounded-xl shadow-lg p-6'>
    <h2 class='text-xl font-bold text-gray-800 mb-4'>🔧 Solusi Alternatif</h2>
    <p class='text-gray-700 mb-4'>Jika masih mengalami masalah, berikut solusi alternatif:</p>
    
    <div class='space-y-4'>
        <div class='p-4 bg-blue-50 border border-blue-200 rounded-lg'>
            <h3 class='font-bold text-blue-800'>1. Membersihkan Nama File</h3>
            <p class='text-blue-700'>Pastikan sistem membersihkan karakter emoji dari nama file sebelum menyimpan ke database</p>
        </div>
        
        <div class='p-4 bg-green-50 border border-green-200 rounded-lg'>
            <h3 class='font-bold text-green-800'>2. Restar Apache dan MySQL</h3>
            <p class='text-green-700'>Setelah perubahan database, restart layanan Apache dan MySQL di XAMPP</p>
        </div>
    </div>
</div>

</div>
</body>
</html>";
?>