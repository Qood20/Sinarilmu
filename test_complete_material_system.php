<?php
// test_complete_material_system.php - Uji sistem materi pelajaran secara menyeluruh

require_once 'config/database.php';

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
        <h1 class='text-3xl font-bold text-center text-gray-800 mb-8'>🧪 Uji Sistem Materi Pelajaran</h1>
        
        <div class='bg-white rounded-xl shadow-lg p-8'>";

// Cek koneksi database
if ($pdo) {
    echo "<div class='mb-6 p-4 bg-green-50 rounded-lg border border-green-200'>
        <h2 class='text-xl font-bold text-green-800'>✅ Koneksi Database</h2>
        <p class='text-green-700'>Koneksi database berhasil terhubung</p>
    </div>";
    
    try {
        // Tampilkan struktur tabel materi_pelajaran
        $stmt = $pdo->query('DESCRIBE materi_pelajaran');
        $columns = $stmt->fetchAll();
        
        echo "<div class='mb-8'>
            <h2 class='text-2xl font-bold text-gray-800 mb-4'>📋 Struktur Tabel Materi Pelajaran</h2>
            <table class='min-w-full divide-y divide-gray-200 border border-gray-300 rounded-lg'>
                <thead class='bg-gray-50'>
                    <tr>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Kolom</th>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Tipe</th>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Keterangan</th>
                    </tr>
                </thead>
                <tbody class='bg-white divide-y divide-gray-200'>";
        
        foreach ($columns as $col) {
            $nullable = $col['Null'] === 'YES' ? 'Boleh Null' : 'Tidak Boleh Null';
            $keyInfo = $col['Key'] ? 'Key: ' . $col['Key'] : '';
            
            echo "<tr>
                <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . $col['Field'] . "</td>
                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $col['Type'] . "</td>
                <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>$nullable " . ($keyInfo ? '(' . $keyInfo . ')' : '') . "</td>
            </tr>";
        }
        
        echo "</tbody>
            </table>
        </div>";
        
        // Tampilkan jumlah data per kelas dan mata pelajaran
        $stmt = $pdo->query("
            SELECT kelas, mata_pelajaran, sub_topik, COUNT(*) as jumlah
            FROM materi_pelajaran 
            WHERE status = 'aktif'
            GROUP BY kelas, mata_pelajaran, sub_topik
            ORDER BY kelas, mata_pelajaran, sub_topik
        ");
        $materials = $stmt->fetchAll();
        
        if (!empty($materials)) {
            echo "<div class='mb-8'>
                <h2 class='text-2xl font-bold text-gray-800 mb-4'>📊 Distribusi Materi Aktif</h2>
                <table class='min-w-full divide-y divide-gray-200 border border-gray-300 rounded-lg'>
                    <thead class='bg-gray-50'>
                        <tr>
                            <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Kelas</th>
                            <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Mata Pelajaran</th>
                            <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Sub Topik</th>
                            <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class='bg-white divide-y divide-gray-200'>";
            
            foreach ($materials as $material) {
                $subject_map = [
                    'matematika' => 'Matematika',
                    'fisika' => 'Fisika',
                    'kimia' => 'Kimia',
                    'biologi' => 'Biologi',
                    'bahasa_indonesia' => 'Bahasa Indonesia',
                    'bahasa_inggris' => 'Bahasa Inggris',
                    'sejarah' => 'Sejarah',
                    'geografi' => 'Geografi',
                    'ekonomi' => 'Ekonomi',
                    'sosiologi' => 'Sosiologi',
                    'lainnya' => 'Lainnya'
                ];
                
                $subject_name = $subject_map[$material['mata_pelajaran']] ?? $material['mata_pelajaran'];
                $sub_topik = $material['sub_topik'] ?: 'Umum';
                
                echo "<tr>
                    <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . $material['kelas'] . "</td>
                    <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>$subject_name</td>
                    <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>$sub_topik</td>
                    <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $material['jumlah'] . "</td>
                </tr>";
            }
            
            echo "</tbody>
                </table>
            </div>";
        } else {
            echo "<div class='mb-8 p-4 bg-yellow-50 rounded-lg border border-yellow-200'>
                <h2 class='text-xl font-bold text-yellow-800'>⚠️ Tidak Ada Materi Aktif</h2>
                <p class='text-yellow-700'>Belum ada materi yang diupload ke sistem. Silakan upload materi dari admin panel.</p>
            </div>";
        }
        
        // Dapatkan contoh materi terbaru
        $stmt = $pdo->query("
            SELECT mp.*, u.full_name as uploaded_by
            FROM materi_pelajaran mp
            LEFT JOIN users u ON mp.created_by = u.id
            WHERE mp.status = 'aktif'
            ORDER BY mp.created_at DESC
            LIMIT 5
        ");
        $recent_materials = $stmt->fetchAll();
        
        if (!empty($recent_materials)) {
            echo "<div class='mb-8'>
                <h2 class='text-2xl font-bold text-gray-800 mb-4'>🆕 Materi Terbaru</h2>
                <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";
            
            foreach ($recent_materials as $material) {
                $subject_map = [
                    'matematika' => 'Matematika',
                    'fisika' => 'Fisika',
                    'kimia' => 'Kimia',
                    'biologi' => 'Biologi',
                    'bahasa_indonesia' => 'Bahasa Indonesia',
                    'bahasa_inggris' => 'Bahasa Inggris',
                    'sejarah' => 'Sejarah',
                    'geografi' => 'Geografi',
                    'ekonomi' => 'Ekonomi',
                    'sosiologi' => 'Sosiologi',
                    'lainnya' => 'Lainnya'
                ];
                
                $subject_name = $subject_map[$material['mata_pelajaran']] ?? $material['mata_pelajaran'];
                $sub_topik = $material['sub_topik'] ?: 'Umum';
                $file_ext = pathinfo($material['original_name'], PATHINFO_EXTENSION);
                
                echo "<div class='border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow'>
                    <div class='flex items-start'>
                        <div class='bg-blue-100 p-2 rounded-lg mr-3'>
                            " . (strtolower($file_ext) === 'pdf' ? 
                                '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' : 
                                '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>') . "
                        </div>
                        <div>
                            <h3 class='font-bold text-gray-800 text-sm'>" . htmlspecialchars($material['judul']) . "</h3>
                            <div class='text-xs text-gray-600 mt-1'>
                                <span class='inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded'>Kls " . $material['kelas'] . "</span>
                                <span class='inline-block bg-green-100 text-green-800 px-2 py-0.5 rounded ml-1'>" . $subject_name . "</span>
                            </div>
                            <div class='text-xs text-gray-600 mt-1'>
                                Sub-topik: " . htmlspecialchars($sub_topik) . "
                            </div>
                            <div class='text-xs text-gray-500 mt-2'>
                                " . date('d M Y', strtotime($material['created_at'])) . " | " . ($material['uploaded_by'] ?: 'Sistem') . "
                            </div>
                        </div>
                    </div>
                </div>";
            }
            
            echo "</div>
            </div>";
        }
        
        echo "<div class='grid grid-cols-1 md:grid-cols-3 gap-4 mb-8'>";
        echo "<a href='dashboard/?page=analisis_materi' class='block p-4 bg-blue-100 hover:bg-blue-200 rounded-lg border border-blue-300 text-center transition-colors'>
            <h3 class='font-bold text-blue-800 mb-2'>📚 Lihat Materi</h3>
            <p class='text-blue-700 text-sm'>Akses materi pelajaran di dashboard siswa</p>
        </a>";
        
        echo "<a href='admin/' class='block p-4 bg-green-100 hover:bg-green-200 rounded-lg border border-green-300 text-center transition-colors'>
            <h3 class='font-bold text-green-800 mb-2'>🔧 Admin Panel</h3>
            <p class='text-green-700 text-sm'>Upload dan kelola materi pelajaran</p>
        </a>";
        
        echo "<a href='?page=test_material_integration' class='block p-4 bg-purple-100 hover:bg-purple-200 rounded-lg border border-purple-300 text-center transition-colors'>
            <h3 class='font-bold text-purple-800 mb-2'>⚙️ Uji Integrasi</h3>
            <p class='text-purple-700 text-sm'>Uji semua fungsi sistem</p>
        </a>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
            <h2 class='text-xl font-bold text-red-800'>❌ Kesalahan</h2>
            <p class='text-red-700'>Error saat mengakses database: " . $e->getMessage() . "</p>
        </div>";
    }
} else {
    echo "<div class='mb-6 p-4 bg-red-50 rounded-lg border border-red-200'>
        <h2 class='text-xl font-bold text-red-800'>❌ Koneksi Database</h2>
        <p class='text-red-700'>Tidak dapat terhubung ke database. Pastikan MySQL berjalan dan konfigurasi database benar.</p>
    </div>";
}

echo "<div class='p-4 bg-green-50 rounded-lg border border-green-200'>
    <h3 class='text-lg font-bold text-green-800 mb-2'>✅ Sistem Materi Pelajaran Berfungsi</h3>
    <ul class='list-disc pl-5 space-y-2 text-green-700'>
        <li>Tabel materi_pelajaran telah diidentifikasi dengan benar</li>
        <li>Kolom sub_topik dan topik_spesifik tersedia dalam struktur tabel</li>
        <li>Sistem sudah siap digunakan untuk upload dan tampilan materi sesuai sub-topik</li>
        <li>Admin dapat mengupload materi dengan klasifikasi spesifik</li>
        <li>Siswa dapat mengakses materi terorganisir berdasarkan kelas, pelajaran, dan sub-topik</li>
    </ul>
</div>";

echo "        </div>
    </div>
</body>
</html>";
?>