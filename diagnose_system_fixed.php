<?php
// diagnose_system.php - File untuk mendiagnosis sistem

session_start();
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnosis Sistem Sinar Ilmu</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100'>
    <div class='max-w-4xl mx-auto p-8'>
        <h1 class='text-3xl font-bold text-center text-gray-800 mb-8'>🔍 Diagnosis Sistem Sinar Ilmu</h1>
        
        <div class='bg-white rounded-xl shadow-lg p-6 mb-6'>
            <h2 class='text-xl font-bold text-gray-800 mb-4'>📊 Informasi Dasar</h2>
            
            <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>
                <div class='border border-gray-200 p-4 rounded-lg'>
                    <h3 class='font-semibold text-gray-700'>Status Koneksi Database</h3>
                    <p class='text-" . ($pdo ? "green" : "red") . "-600'>" . ($pdo ? "✅ Terhubung" : "❌ Gagal Terhubung") . "</p>
                </div>
                
                <div class='border border-gray-200 p-4 rounded-lg'>
                    <h3 class='font-semibold text-gray-700'>Status Login</h3>
                    <p class='text-" . (isset($_SESSION['user_id']) ? "green" : "red") . "-600'>" . (isset($_SESSION['user_id']) ? "✅ Sudah Login" : "❌ Belum Login") . "</p>
                </div>
            </div>
        </div>";

// Test database connection
if ($pdo) {
    echo "<div class='bg-white rounded-xl shadow-lg p-6 mb-6'>
        <h2 class='text-xl font-bold text-gray-800 mb-4'>🗄️ Status Tabel Database</h2>
        
        <div class='space-y-3'>";
    
    // Check if important tables exist
    $tables_to_check = [
        'users', 
        'materi_pelajaran', 
        'upload_files', 
        'analisis_ai'
    ];
    
    foreach ($tables_to_check as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $result->rowCount() > 0;
        
        echo "<div class='flex items-center p-3 bg-" . ($exists ? "green" : "red") . "-50 border border-" . ($exists ? "green" : "red") . "-200 rounded-lg'>
            <div class='w-3 h-3 rounded-full bg-" . ($exists ? "green" : "red") . "-500 mr-3'></div>
            <span class='font-medium text-gray-800'>Table '$table'</span>
            <span class='ml-auto text-" . ($exists ? "green" : "red") . "-700'>" . ($exists ? "✅ Ada" : "❌ Tidak Ada") . "</span>
        </div>";
    }
    
    echo "</div></div>";
    
    // Count materials per class
    echo "<div class='bg-white rounded-xl shadow-lg p-6 mb-6'>
        <h2 class='text-xl font-bold text-gray-800 mb-4'>📈 Statistik Materi Pelajaran</h2>
        
        <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>";
    
    for ($i = 10; $i <= 12; $i++) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM materi_pelajaran WHERE kelas = ? AND status = 'aktif'");
            $stmt->execute([$i]);
            $count = $stmt->fetch();
            
            echo "<div class='border border-gray-200 p-4 rounded-lg text-center'>
                <div class='text-3xl font-bold text-blue-600 mb-2'>$count[count]</div>
                <div class='text-gray-700'>Kelas $i</div>
            </div>";
        } catch (Exception $e) {
            echo "<div class='border border-gray-200 p-4 rounded-lg text-center'>
                <div class='text-3xl font-bold text-red-600 mb-2'>Error</div>
                <div class='text-gray-700'>Kelas $i</div>
            </div>";
        }
    }
    
    echo "</div></div>";
}

// Check file structure
echo "<div class='bg-white rounded-xl shadow-lg p-6 mb-6'>
    <h2 class='text-xl font-bold text-gray-800 mb-4'>🗂️ File-file Halaman Materi</h2>
    
    <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>";

$page_files = [
    'analisis_materi.php',
    'matematika_kelas10.php', 
    'fisika_kelas10.php', 
    'biologi_kelas10.php', 
    'kimia_kelas10.php',
    'matematika_kelas11.php', 
    'fisika_kelas11.php', 
    'biologi_kelas11.php', 
    'kimia_kelas11.php',
    'matematika_kelas12.php', 
    'fisika_kelas12.php', 
    'biologi_kelas12.php', 
    'kimia_kelas12.php'
];

$pages_path = 'dashboard/pages/';
foreach ($page_files as $file) {
    $exists = file_exists($pages_path . $file);
    echo "<div class='flex items-center p-3 bg-" . ($exists ? "green" : "red") . "-50 border border-" . ($exists ? "green" : "red") . "-200 rounded-lg'>
        <div class='w-3 h-3 rounded-full bg-" . ($exists ? "green" : "red") . "-500 mr-3'></div>
        <span class='font-medium text-gray-800'>$file</span>
        <span class='ml-auto text-" . ($exists ? "green" : "red") . "-700'>" . ($exists ? "✅ Ada" : "❌ Tidak Ada") . "</span>
    </div>";
}

echo "</div></div>";

// Provide navigation links
echo "<div class='bg-white rounded-xl shadow-lg p-6'>
    <h2 class='text-xl font-bold text-gray-800 mb-4'>🧭 Tautan Navigasi</h2>
    
    <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";
    
echo "<a href='dashboard/' class='block p-4 bg-blue-100 hover:bg-blue-200 rounded-lg text-center transition-colors'>
    <div class='font-bold text-blue-800'>Dashboard Siswa</div>
    <div class='text-blue-700 text-sm'>Halaman utama siswa</div>
</a>";

echo "<a href='dashboard/?page=analisis_materi' class='block p-4 bg-green-100 hover:bg-green-200 rounded-lg text-center transition-colors'>
    <div class='font-bold text-green-800'>Analisis Materi</div>
    <div class='text-green-700 text-sm'>Halaman materi pelajaran</div>
</a>";

echo "<a href='admin/' class='block p-4 bg-purple-100 hover:bg-purple-200 rounded-lg text-center transition-colors'>
    <div class='font-bold text-purple-800'>Admin Panel</div>
    <div class='text-purple-700 text-sm'>Halaman admin</div>
</a>";

echo "</div></div>";

echo "</div>
</body>
</html>";
?>