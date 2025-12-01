<?php
// dashboard/pages/home.php - Halaman beranda dashboard pengguna

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Ambil data pengguna
$user = get_user_by_id($_SESSION['user_id']);
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Halo, <?php echo escape($user['full_name']); ?>!</h2>
        <p class="text-gray-600 mt-2">Selamat datang kembali di Sinar Ilmu. Lanjutkan perjalanan belajarmu hari ini.</p>
    </div>
    
    <!-- Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-blue-600">5</div>
            <div class="text-gray-600 mt-2">File Diunggah</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-green-600">12</div>
            <div class="text-gray-600 mt-2">Soal Dikerjakan</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-yellow-600">3</div>
            <div class="text-gray-600 mt-2">Nilai Rata-rata</div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Belajar Terakhir</h3>
        <div class="mt-4 space-y-4">
            <div class="border-l-4 border-blue-500 pl-4 py-1">
                <div class="font-medium">Matematika - Aljabar</div>
                <div class="text-sm text-gray-600">File diunggah: 25 November 2025</div>
            </div>
            <div class="border-l-4 border-green-500 pl-4 py-1">
                <div class="font-medium">Fisika - Gerak Lurus</div>
                <div class="text-sm text-gray-600">Soal dikerjakan: 24 November 2025</div>
            </div>
            <div class="border-l-4 border-yellow-500 pl-4 py-1">
                <div class="font-medium">Kimia - Struktur Atom</div>
                <div class="text-sm text-gray-600">File diunggah: 23 November 2025</div>
            </div>
        </div>
    </div>
</div>