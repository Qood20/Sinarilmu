<?php
// dashboard/pages/analisis_materi.php - Halaman untuk melihat materi berdasarkan subjek

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
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Materi Kelas 11</h2>

        <div class="mb-8 bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-xl border border-blue-200">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-1">Pilih Subjek untuk Kelas 11</h3>
                    <p class="text-gray-700">
                        Klik pada salah satu subjek di bawah ini untuk melihat materi pelajaran yang tersedia untuk kelas 11.
                    </p>
                </div>
            </div>
        </div>

        <!-- Grid untuk 4 kolom subjek -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Kolom Matematika -->
            <a href="?page=matematika_kelas11" class="block bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-blue-200 transform hover:scale-105 transition-transform">
                <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Matematika</h3>
                <p class="text-gray-600">Materi kelas 11</p>
            </a>

            <!-- Kolom Fisika -->
            <a href="?page=fisika_kelas11" class="block bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-green-200 transform hover:scale-105 transition-transform">
                <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.874-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Fisika</h3>
                <p class="text-gray-600">Materi kelas 11</p>
            </a>

            <!-- Kolom Biologi -->
            <a href="?page=biologi_kelas11" class="block bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-yellow-200 transform hover:scale-105 transition-transform">
                <div class="bg-yellow-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Biologi</h3>
                <p class="text-gray-600">Materi kelas 11</p>
            </a>

            <!-- Kolom Kimia -->
            <a href="?page=kimia_kelas11" class="block bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-purple-200 transform hover:scale-105 transition-transform">
                <div class="bg-purple-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Kimia</h3>
                <p class="text-gray-600">Materi kelas 11</p>
            </a>
        </div>

        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Petunjuk Penggunaan:
            </h3>
            <ul class="list-disc pl-5 text-blue-700 space-y-2">
                <li>Klik pada salah satu subjek untuk melihat materi pelajaran kelas 11</li>
                <li>Setiap materi akan dilengkapi dengan penjelasan dan contoh soal</li>
                <li>Gunakan fitur pencarian untuk menemukan topik tertentu</li>
            </ul>
        </div>
    </div>
</div>