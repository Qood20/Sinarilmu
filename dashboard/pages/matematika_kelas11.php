<?php
// dashboard/pages/matematika_kelas11.php - Halaman materi matematika kelas 11

// Cek apakah sesi sudah aktif sebelum memulai sesi baru
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

require_once '../includes/functions.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="flex items-center mb-6">
            <a href="?page=analisis_materi" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Matematika Kelas 11</h2>
        <p class="text-gray-600 mb-8">Materi pelajaran matematika untuk kelas 11 SMA</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Materi 1 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Program Linear</h3>
                <p class="text-gray-600 mb-4">Memahami konsep dan penerapan program linear dalam kehidupan sehari-hari</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 2 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Matriks</h3>
                <p class="text-gray-600 mb-4">Operasi dan penerapan matriks dalam berbagai permasalahan matematika</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 3 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Vektor</h3>
                <p class="text-gray-600 mb-4">Konsep vektor dalam bidang dan ruang</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 4 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Barisan dan Deret</h3>
                <p class="text-gray-600 mb-4">Pola bilangan, barisan aritmetika dan geometri</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 5 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Transformasi Geometri</h3>
                <p class="text-gray-600 mb-4">Refleksi, translasi, rotasi, dan dilatasi</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Materi 6 -->
            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="bg-blue-100 text-blue-800 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Limit Fungsi</h3>
                <p class="text-gray-600 mb-4">Konsep dasar dan penerapan limit fungsi aljabar</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                    Pelajari Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="mt-10 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tips Belajar:
            </h3>
            <ul class="list-disc pl-5 text-blue-700 space-y-2">
                <li>Pelajari materi secara bertahap dan lakukan latihan soal secara rutin</li>
                <li>Gunakan fitur tanya Sinar jika ada konsep yang kurang dipahami</li>
                <li>Berlatihlah dengan berbagai jenis soal untuk memperkuat pemahaman</li>
            </ul>
        </div>
    </div>
</div>