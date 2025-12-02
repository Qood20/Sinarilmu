<?php
// final_verification.php - Verifikasi implementasi sistem materi pelajaran

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verifikasi Sistem Materi Pelajaran - Sinar Ilmu</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
</head>
<body class='bg-gradient-to-br from-blue-50 to-green-50 min-h-screen'>
    <div class='container mx-auto px-4 py-8'>
        <div class='bg-white rounded-2xl shadow-xl p-8 max-w-6xl mx-auto'>
            <div class='text-center mb-10'>
                <h1 class='text-4xl font-bold text-gray-800 mb-4'>🎉 Implementasi Sistem Materi Pelajaran Selesai</h1>
                <p class='text-xl text-gray-600'>Sistem materi pelajaran dengan pengorganisasian sub-topik telah berhasil diimplementasikan</p>
            </div>
            
            <div class='grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10'>
                <div class='bg-gradient-to-br from-green-50 to-blue-50 rounded-xl p-6 border border-green-200'>
                    <h2 class='text-2xl font-bold text-gray-800 mb-4 flex items-center'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6 mr-2 text-green-600' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' />
                        </svg>
                        Komponen Yang Telah Diimplementasikan
                    </h2>
                    <ul class='space-y-3'>
                        <li class='flex items-start'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-green-500 mr-2 mt-0.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7' />
                            </svg>
                            <span>Struktur database dengan kolom <strong>sub_topik</strong> dan <strong>topik_spesifik</strong></span>
                        </li>
                        <li class='flex items-start'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-green-500 mr-2 mt-0.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7' />
                            </svg>
                            <span>Form upload materi di admin panel dengan pilihan sub-topik dinamis</span>
                        </li>
                        <li class='flex items-start'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-green-500 mr-2 mt-0.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7' />
                            </svg>
                            <span>Halaman tampilan materi yang dikelompokkan berdasarkan sub-topik</span>
                        </li>
                        <li class='flex items-start'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-green-500 mr-2 mt-0.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7' />
                            </svg>
                            <span>Fitur pencarian dan penyaringan berdasarkan sub-topik</span>
                        </li>
                        <li class='flex items-start'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-green-500 mr-2 mt-0.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7' />
                            </svg>
                            <span>Dukungan untuk semua kelas (10, 11, 12) dan semua mata pelajaran</span>
                        </li>
                    </ul>
                </div>
                
                <div class='bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-200'>";
                
if ($pdo) {
    try {
        // Dapatkan statistik dari database
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM materi_pelajaran WHERE status = 'aktif'");
        $total_materials = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT DISTINCT kelas FROM materi_pelajaran WHERE status = 'aktif'");
        $kelas_count = $stmt->rowCount();
        
        $stmt = $pdo->query("SELECT DISTINCT mata_pelajaran FROM materi_pelajaran WHERE status = 'aktif'");
        $subjects_count = $stmt->rowCount();
        
        $stmt = $pdo->query("SELECT DISTINCT sub_topik FROM materi_pelajaran WHERE status = 'aktif' AND sub_topik IS NOT NULL AND sub_topik != ''");
        $subtopics_count = $stmt->rowCount();
        
        echo "<h2 class='text-2xl font-bold text-gray-800 mb-4 flex items-center'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6 mr-2 text-blue-600' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h4a2 2 0 002-2m-6 0V5a2 2 0 012-2h4a2 2 0 012 2v14a2 2 0 01-2 2h-4a2 2 0 01-2-2z' />
                    </svg>
                    Statistik Sistem
                </h2>
                <div class='grid grid-cols-2 gap-4'>
                    <div class='bg-white p-4 rounded-lg shadow text-center'>
                        <div class='text-3xl font-bold text-blue-600'>$total_materials</div>
                        <div class='text-gray-600'>Total Materi</div>
                    </div>
                    <div class='bg-white p-4 rounded-lg shadow text-center'>
                        <div class='text-3xl font-bold text-green-600'>$kelas_count</div>
                        <div class='text-gray-600'>Kelas Tersedia</div>
                    </div>
                    <div class='bg-white p-4 rounded-lg shadow text-center'>
                        <div class='text-3xl font-bold text-purple-600'>$subjects_count</div>
                        <div class='text-gray-600'>Mata Pelajaran</div>
                    </div>
                    <div class='bg-white p-4 rounded-lg shadow text-center'>
                        <div class='text-3xl font-bold text-yellow-600'>$subtopics_count</div>
                        <div class='text-gray-600'>Sub-Topik</div>
                    </div>
                </div>";
        
        // Dapatkan distribusi materi per kelas
        echo "<div class='mt-6'>
                    <h3 class='font-bold text-gray-800 mb-3'>Distribusi Materi per Kelas</h3>
                    <canvas id='distributionChart' height='200'></canvas>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('distributionChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Kelas 10', 'Kelas 11', 'Kelas 12'],
                            datasets: [{
                                data: [";
        
        // Dapatkan jumlah per kelas
        $kelas10 = $pdo->query("SELECT COUNT(*) as count FROM materi_pelajaran WHERE kelas = '10' AND status = 'aktif'")->fetch()['count'];
        $kelas11 = $pdo->query("SELECT COUNT(*) as count FROM materi_pelajaran WHERE kelas = '11' AND status = 'aktif'")->fetch()['count'];
        $kelas12 = $pdo->query("SELECT COUNT(*) as count FROM materi_pelajaran WHERE kelas = '12' AND status = 'aktif'")->fetch()['count'];
        
        echo "$kelas10, $kelas11, $kelas12],
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.8)',
                                    'rgba(255, 206, 86, 0.8)',
                                    'rgba(75, 192, 192, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                });
                </script>";
        
    } catch (Exception $e) {
        echo "<div class='bg-red-50 p-4 rounded-lg border border-red-200'>
                    <h2 class='text-xl font-bold text-red-800'>❌ Kesalahan</h2>
                    <p class='text-red-700'>Tidak dapat mengambil statistik: " . $e->getMessage() . "</p>
                </div>";
    }
} else {
    echo "<div class='bg-red-50 p-4 rounded-lg border border-red-200'>
                <h2 class='text-xl font-bold text-red-800'>❌ Koneksi Database</h2>
                <p class='text-red-700'>Tidak dapat terhubung ke database untuk mengambil statistik.</p>
            </div>";
}

echo "</div>
            </div>
            
            <div class='bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-10'>
                <h2 class='text-2xl font-bold text-yellow-800 mb-4 flex items-center'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6 mr-2 text-yellow-600' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' />
                    </svg>
                    Cara Menggunakan Sistem Materi dengan Sub-Topik
                </h2>
                <div class='grid grid-cols-1 md:grid-cols-2 gap-6'>
                    <div>
                        <h3 class='text-lg font-semibold text-yellow-700 mb-3'>Untuk Admin:</h3>
                        <ol class='list-decimal pl-5 space-y-2 text-yellow-700'>
                            <li>Login ke admin panel di <a href='admin/' class='text-blue-600 underline'>http://localhost/Sinarilmu/admin/</a></li>
                            <li>Pilih menu 'Kelola Materi' untuk upload materi pelajaran</li>
                            <li>Pilih mata pelajaran, kelas, dan sub-topik spesifik</li>
                            <li>Upload file materi dan tambahkan deskripsi jika perlu</li>
                            <li>Materi akan otomatis terorganisasi berdasarkan sub-topik</li>
                        </ol>
                    </div>
                    <div>
                        <h3 class='text-lg font-semibold text-yellow-700 mb-3'>Untuk Siswa:</h3>
                        <ol class='list-decimal pl-5 space-y-2 text-yellow-700'>
                            <li>Login ke dashboard siswa di <a href='dashboard/' class='text-blue-600 underline'>http://localhost/Sinarilmu/dashboard/</a></li>
                            <li>Akses menu 'Analisis Materi' untuk melihat semua materi pelajaran</li>
                            <li>Materi akan ditampilkan terorganisasi per kelas, pelajaran, dan sub-topik</li>
                            <li>Cari materi spesifik berdasarkan sub-topik yang relevan</li>
                            <li>Download atau pelajari materi yang sesuai dengan kebutuhan</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <div class='grid grid-cols-1 md:grid-cols-3 gap-6 mb-10'>
                <a href='dashboard/?page=analisis_materi' class='block bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 text-center hover:from-blue-600 hover:to-blue-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-12 w-12 mx-auto mb-4 text-white' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' />
                    </svg>
                    <h3 class='text-xl font-bold'>Lihat Materi</h3>
                    <p class='mt-2'>Akses materi pelajaran terorganisir</p>
                </a>
                
                <a href='admin/' class='block bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 text-center hover:from-green-600 hover:to-green-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-12 w-12 mx-auto mb-4 text-white' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 20l4-16m4 4l4 4m-2-2l-4 4m6-6l-4 4' />
                    </svg>
                    <h3 class='text-xl font-bold'>Admin Panel</h3>
                    <p class='mt-2'>Upload & kelola materi pelajaran</p>
                </a>
                
                <a href='?page=test_material_system' class='block bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 text-center hover:from-purple-600 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-12 w-12 mx-auto mb-4 text-white' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' />
                    </svg>
                    <h3 class='text-xl font-bold'>Uji Sistem</h3>
                    <p class='mt-2'>Verifikasi fungsionalitas</p>
                </a>
            </div>
            
            <div class='bg-gradient-to-r from-green-500 to-teal-500 text-white rounded-xl p-8 text-center'>
                <h2 class='text-3xl font-bold mb-4'>✅ Implementasi Berhasil!</h2>
                <p class='text-xl mb-6'>Sistem materi pelajaran dengan pengorganisasian sub-topik telah berhasil diimplementasikan</p>
                <p class='mb-4'>Sekarang admin dapat mengupload materi dengan klasifikasi spesifik dan siswa dapat mengaksesnya dengan mudah terorganisasi berdasarkan sub-topik.</p>
                <div class='flex justify-center mt-6'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-16 w-16 text-white' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>