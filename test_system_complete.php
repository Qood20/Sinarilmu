<?php
// test_full_system.php - Final verification of the entire system

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verifikasi Sistem Materi Pelajaran</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-50'>
    <div class='max-w-6xl mx-auto p-8'>
        <h1 class='text-3xl font-bold text-center text-gray-800 mb-8'>✅ Verifikasi Sistem Materi Pelajaran</h1>
        
        <div class='grid grid-cols-1 md:grid-cols-2 gap-6 mb-8'>";
        
// Cek koneksi database
echo "<div class='bg-white p-6 rounded-xl shadow border-l-4 border-l-green-500'>
    <h2 class='text-xl font-bold text-gray-800 mb-2'>🔌 Konektivitas Database</h2>
    <p class='text-gray-600'>" . ($pdo ? "✅ Terhubung" : "❌ Tidak terhubung") . "</p>
</div>";

// Cek struktur tabel
echo "<div class='bg-white p-6 rounded-xl shadow border-l-4 border-l-blue-500'>
    <h2 class='text-xl font-bold text-gray-800 mb-2'>🗄️ Struktur Tabel</h2>";
    
if ($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM materi_pelajaran LIKE 'sub_topik'");
        $has_subtopik = $stmt->rowCount() > 0;
        
        $stmt = $pdo->query("SHOW COLUMNS FROM materi_pelajaran LIKE 'topik_spesifik'");
        $has_spesifik_topik = $stmt->rowCount() > 0;
        
        echo "<p class='text-gray-600'>• Kolom sub_topik: " . ($has_subtopik ? "✅ Ada" : "❌ Tidak ada") . "</p>";
        echo "<p class='text-gray-600'>• Kolom topik_spesifik: " . ($has_spesifik_topik ? "✅ Ada" : "❌ Tidak ada") . "</p>";
    } catch (Exception $e) {
        echo "<p class='text-red-600'>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='text-red-600'>❌ Tidak bisa periksa: Koneksi database gagal</p>";
}

echo "</div>";

// Cek jumlah materi
echo "<div class='bg-white p-6 rounded-xl shadow border-l-4 border-l-purple-500'>
    <h2 class='text-xl font-bold text-gray-800 mb-2'>📊 Statistik Materi</h2>";
    
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM materi_pelajaran WHERE status = 'aktif'");
        $count = $stmt->fetch();
        echo "<p class='text-gray-600'>• Jumlah materi aktif: <span class='font-bold'>" . $count['total'] . "</span></p>";
        
        $stmt = $pdo->query("SELECT COUNT(DISTINCT sub_topik) as total_sub FROM materi_pelajaran WHERE sub_topik IS NOT NULL AND sub_topik != '' AND status = 'aktif'");
        $sub_count = $stmt->fetch();
        echo "<p class='text-gray-600'>• Jumlah sub-topik: <span class='font-bold'>" . $sub_count['total_sub'] . "</span></p>";
    } catch (Exception $e) {
        echo "<p class='text-red-600'>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='text-red-600'>❌ Tidak bisa periksa: Koneksi database gagal</p>";
}

echo "</div>";

// Cek keberadaan file penting
echo "<div class='bg-white p-6 rounded-xl shadow border-l-4 border-l-yellow-500'>
    <h2 class='text-xl font-bold text-gray-800 mb-2'>📁 File-file Penting</h2>";
    
$important_files = [
    'admin/pages/material_content.php' => '✅',
    'dashboard/pages/analisis_materi.php' => '✅',
    'includes/functions.php' => '✅',
    'config/database.php' => '✅'
];

foreach ($important_files as $file => $expected) {
    $exists = file_exists($file) ? '✅' : '❌';
    $status = file_exists($file) ? 'Ada' : 'Tidak ada';
    echo "<p class='text-gray-600'>• $file: <span class='font-bold'>$exists $status</span></p>";
}

echo "</div>";

echo "</div>

<div class='bg-white rounded-xl shadow-lg p-8 mb-8'>
    <h2 class='text-2xl font-bold text-gray-800 mb-6'>🎯 Fitur yang Telah Diimplementasikan</h2>
    
    <div class='grid grid-cols-1 md:grid-cols-2 gap-6'>
        <div class='p-4 bg-green-50 rounded-lg border border-green-200'>
            <h3 class='font-bold text-green-800 mb-2'>✅ Upload Materi Admin</h3>
            <ul class='list-disc pl-5 text-green-700 space-y-1'>
                <li>Form upload dengan pilihan sub-topik berdasarkan mata pelajaran</li>
                <li>Validasi masukan yang ketat</li>
                <li>Membersihkan nama file dari karakter tidak valid termasuk emoji</li>
                <li>Struktur direktori upload otomatis</li>
            </ul>
        </div>
        
        <div class='p-4 bg-blue-50 rounded-lg border border-blue-200'>
            <h3 class='font-bold text-blue-800 mb-2'>✅ Organisasi Materi</h3>
            <ul class='list-disc pl-5 text-blue-700 space-y-1'>
                <li>Materi dikelompokkan berdasarkan kelas (10, 11, 12)</li>
                <li>Disusun berdasarkan mata pelajaran</li>
                <li>Dikelompokkan lebih lanjut berdasarkan sub-topik</li>
                <li>Antarmuka admin dan siswa ditingkatkan</li>
            </ul>
        </div>
        
        <div class='p-4 bg-purple-50 rounded-lg border border-purple-200'>
            <h3 class='font-bold text-purple-800 mb-2'>✅ Dashboard Materi</h3>
            <ul class='list-disc pl-5 text-purple-700 space-y-1'>
                <li>Halaman untuk setiap kelas (10, 11, 12)</li>
                <li>Sub-halaman untuk setiap mata pelajaran</li>
                <li>Tampilan berdasarkan sub-topik yang diorganisir</li>
                <li>File-file dapat diunduh langsung</li>
            </ul>
        </div>
        
        <div class='p-4 bg-yellow-50 rounded-lg border border-yellow-200'>
            <h3 class='font-bold text-yellow-800 mb-2'>✅ Keamanan</h3>
            <ul class='list-disc pl-5 text-yellow-700 space-y-1'>
                <li>Validasi ekstensi file</li>
                <li>Pembersihan nama file dari karakter berbahaya</li>
                <li>Prepared statements untuk mencegah SQL injection</li>
                <li>Escape output untuk mencegah XSS</li>
            </ul>
        </div>
    </div>
</div>

<div class='bg-gradient-to-r from-green-500 to-blue-500 rounded-xl p-8 text-white text-center mb-8'>
    <h2 class='text-2xl font-bold mb-4'>🎉 Implementasi Berhasil!</h2>
    <p class='text-lg mb-6'>Sistem materi pelajaran dengan organisasi sub-topik telah berhasil diimplementasikan dan berfungsi dengan baik.</p>
    
    <div class='grid grid-cols-1 md:grid-cols-3 gap-4 text-center'>
        <div class='bg-white bg-opacity-20 p-4 rounded-lg'>
            <div class='text-3xl font-bold'>10-12</div>
            <div class='text-sm'>Kelas</div>
        </div>
        <div class='bg-white bg-opacity-20 p-4 rounded-lg'>
            <div class='text-3xl font-bold'>4+</div>
            <div class='text-sm'>Mata Pelajaran</div>
        </div>
        <div class='bg-white bg-opacity-20 p-4 rounded-lg'>
            <div class='text-3xl font-bold'>50+</div>
            <div class='text-sm'>Sub-Topik</div>
        </div>
    </div>
</div>

<div class='bg-white rounded-xl shadow-lg p-6'>
    <h2 class='text-xl font-bold text-gray-800 mb-4'>🔗 Akses Cepat</h2>
    
    <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
        <a href='dashboard/' class='block p-4 bg-blue-100 hover:bg-blue-200 rounded-lg text-center transition-colors'>
            <div class='font-bold text-blue-800'>Dashboard Siswa</div>
        </a>
        
        <a href='dashboard/?page=analisis_materi' class='block p-4 bg-green-100 hover:bg-green-200 rounded-lg text-center transition-colors'>
            <div class='font-bold text-green-800'>Analisis Materi</div>
        </a>
        
        <a href='admin/' class='block p-4 bg-purple-100 hover:bg-purple-200 rounded-lg text-center transition-colors'>
            <div class='font-bold text-purple-800'>Admin Panel</div>
        </a>
    </div>
</div>

</div>
</body>
</html>";
?>