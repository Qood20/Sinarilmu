<?php
// dashboard/pages/analisis_materi.php - Halaman utama untuk semua materi pelajaran

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

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Materi Pembelajaran</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Pilih kelas untuk melihat materi pelajaran yang tersedia.
            </p>
        </div>

        <!-- Kartu Pilihan Kelas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <a href="?page=materi_kelas10" class="block bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-blue-200 transform hover:scale-105 transition-transform">
                <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold">10</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Kelas 10</h3>
                <p class="text-gray-600">Tingkat SMA Kelas 1</p>
            </a>

            <a href="?page=materi_kelas11" class="block bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-green-200 transform hover:scale-105 transition-transform">
                <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold">11</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Kelas 11</h3>
                <p class="text-gray-600">Tingkat SMA Kelas 2</p>
            </a>

            <a href="?page=materi_kelas12" class="block bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl p-6 text-center shadow-md hover:shadow-lg transition-shadow border border-purple-200 transform hover:scale-105 transition-transform">
                <div class="bg-purple-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold">12</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Kelas 12</h3>
                <p class="text-gray-600">Tingkat SMA Kelas 3</p>
            </a>
        </div>

        <!-- Tautan ke upload untuk AI analisis -->
        <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-xl font-bold text-gray-800">Unggah File untuk Analisis AI</h3>
                    <p class="text-gray-600">Unggah materi pelajaran Anda untuk dianalisis dan dibuatkan soal latihan oleh AI</p>
                </div>
                <a href="?page=upload" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Unggah File
                </a>
            </div>
        </div>

        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tips Belajar:
            </h3>
            <ul class="list-disc pl-5 text-blue-700 space-y-2">
                <li>Pilih kelas terlebih dahulu untuk melihat materi pelajaran yang tersedia</li>
                <li>Gunakan fitur upload untuk menganalisis file materi Anda sendiri</li>
                <li>Manfaatkan fitur tanya Sinar untuk memperjelas konsep yang sulit</li>
            </ul>
        </div>
    </div>
</div>
</file>