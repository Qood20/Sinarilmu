<?php
// test_complete_system.php - File untuk menguji sistem materi yang telah diperbaiki

// Mulai sesi jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

require_once 'includes/functions.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Uji Sistem Materi Pelajaran - Sinar Ilmu</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-50'>
    <div class='max-w-6xl mx-auto p-8'>
        <h1 class='text-3xl font-bold text-gray-800 mb-8'>🧪 Uji Sistem Materi Pelajaran</h1>
        
        <div class='bg-white rounded-xl shadow-lg p-8'>";
        
// Cek koneksi database
global $pdo;
if ($pdo) {
    echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
        <h2 class='text-xl font-bold text-green-800 mb-2'>✅ Koneksi Database</h2>
        <p class='text-green-700'>Koneksi database berhasil terhubung</p>
    </div>";
    
    // Cek tabel materi_pelajaran
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
                <h2 class='text-xl font-bold text-green-800 mb-2'>✅ Tabel Materi Pelajaran</h2>
                <p class='text-green-700'>Tabel 'materi_pelajaran' ditemukan dengan kolom:</p>";
                
            $columns_stmt = $pdo->query("DESCRIBE materi_pelajaran");
            $columns = $columns_stmt->fetchAll();
            echo "<ul class='mt-2 list-disc pl-5'>";
            foreach ($columns as $col) {
                echo "<li class='text-green-700'>• " . $col['Field'] . " (" . $col['Type'] . ")</li>";
            }
            echo "</ul>";
            echo "</div>";
            
            // Tampilkan jumlah materi
            $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM materi_pelajaran WHERE status = 'aktif'");
            $count = $count_stmt->fetch();
            echo "<div class='mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200'>
                <h2 class='text-xl font-bold text-blue-800 mb-2'>📊 Statistik Materi</h2>
                <p class='text-blue-700'>Jumlah total materi aktif: <span class='font-bold'>" . $count['total'] . "</span> file</p>
            </div>";
        } else {
            echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
                <h2 class='text-xl font-bold text-red-800 mb-2'>❌ Tabel Materi Pelajaran</h2>
                <p class='text-red-700'>Tabel 'materi_pelajaran' tidak ditemukan</p>
            </div>";
        }
    } catch (Exception $e) {
        echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
            <h2 class='text-xl font-bold text-red-800 mb-2'>❌ Kesalahan Tabel</h2>
            <p class='text-red-700'>Error saat memeriksa tabel: " . $e->getMessage() . "</p>
        </div>";
    }
} else {
    echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
        <h2 class='text-xl font-bold text-red-800 mb-2'>❌ Koneksi Database</h2>
        <p class='text-red-700'>Koneksi database gagal</p>
    </div>";
}

echo "<div class='mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200'>
    <h2 class='text-xl font-bold text-yellow-800 mb-2'>🔧 Panduan Penggunaan Sistem</h2>
    <ol class='list-decimal pl-5 space-y-2'>
        <li class='text-yellow-700'><strong>Login Admin:</strong> Akses <a href='admin/' class='text-blue-600 underline'>http://localhost/Sinarilmu/admin/</a> untuk mengupload materi</li>
        <li class='text-yellow-700'><strong>Upload Materi:</strong> Gunakan menu 'Kelola Materi' untuk mengupload file dengan sub-topik spesifik</li>
        <li class='text-yellow-700'><strong>Akses Materi:</strong> Siswa dapat mengakses materi dari dashboard masing-masing kelas dan mata pelajaran</li>
        <li class='text-yellow-700'><strong>Struktur Navigasi:</strong> Analisis Materi → Pilih Kelas → Pilih Mata Pelajaran → Akses materi berdasarkan sub-topik</li>
    </ol>
</div>

<div class='mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200'>
    <h2 class='text-xl font-bold text-indigo-800 mb-2'>✨ Fitur Terbaru Sistem Materi</h2>
    <ul class='list-disc pl-5 space-y-2'>
        <li class='text-indigo-700'><strong>Organisasi Sub-topik:</strong> Materi dikelompokkan berdasarkan sub-topik spesifik (misal: Program Linear, Matriks, dll)</li>
        <li class='text-indigo-700'><strong>Pengelompokan Otomatis:</strong> Di halaman siswa, materi otomatis dikelompokkan berdasarkan mata pelajaran, kelas, dan sub-topik</li>
        <li class='text-indigo-700'><strong>Antarmuka Admin:</strong> Form upload materi dengan pilihan sub-topik dinamis berdasarkan mata pelajaran</li>
        <li class='text-indigo-700'><strong>Penyaringan:</strong> Kemampuan menyaring materi berdasarkan kelas, mata pelajaran, dan sub-topik</li>
    </ul>
</div>";

// Jika login sebagai admin, tampilkan tambahan info
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    echo "<div class='mb-6 p-4 bg-purple-50 rounded-lg border border-purple-200'>
        <h2 class='text-xl font-bold text-purple-800 mb-2'>🔑 Admin Panel Info</h2>
        <p class='text-purple-700'>Anda login sebagai admin. Akses panel admin untuk mengelola materi pelajaran:</p>
        <a href='admin/' class='inline-block mt-2 px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700'>Ke Admin Panel</a>
    </div>";
}

echo "<div class='p-4 bg-gray-50 rounded-lg border border-gray-200'>
    <h2 class='text-xl font-bold text-gray-800 mb-2'>🔄 Langkah Selanjutnya</h2>
    <div class='flex flex-wrap gap-4'>
        <a href='?page=analisis_materi' class='px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700'>Lihat Materi Pelajaran</a>
        <a href='.' class='px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700'>Beranda</a>
    </div>
</div>";

echo "      </div>
    </div>
</body>
</html>";
?>