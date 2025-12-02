<?php
// dashboard/pages/home.php - Halaman beranda dashboard pengguna

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Ambil data pengguna
$user = get_user_by_id($_SESSION['user_id']);
?>

<!-- Animations CSS -->
<style>
    @keyframes fade-in-up {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.8s ease-out;
    }

    .animate-float {
        animation: float 4s ease-in-out infinite;
    }

    .animate-pulse {
        animation: pulse 2s ease-in-out infinite;
    }

    .delay-100 {
        animation-delay: 0.1s;
    }

    .delay-200 {
        animation-delay: 0.2s;
    }

    .delay-300 {
        animation-delay: 0.3s;
    }

    .delay-500 {
        animation-delay: 0.5s;
    }

    .delay-700 {
        animation-delay: 0.7s;
    }

    .transition-transform {
        transition: transform 0.3s ease;
    }

    .hover\\:animate-pulse:hover {
        animation: pulse 2s ease-in-out infinite;
    }
</style>

<div class="max-w-7xl mx-auto">
    <!-- Selamat Datang -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl shadow-lg p-8 mb-8 text-white animate-fade-in-up">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold">Halo, <?php echo escape($user['full_name']); ?>! 👋</h2>
                <p class="mt-2 text-blue-100 text-lg">Selamat datang kembali di Sinar Ilmu. Lanjutkan perjalanan belajarmu hari ini!</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="?page=upload" class="px-5 py-3 bg-yellow-400 text-gray-900 font-bold rounded-lg hover:bg-yellow-300 transition duration-300 shadow-lg transform hover:scale-105 transition-transform animate-pulse">
                        <i class="mr-2">📤</i> Unggah File
                    </a>
                    <a href="?page=exercises" class="px-5 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition duration-300 shadow-lg transform hover:scale-105 transition-transform">
                        <i class="mr-2">✏️</i> Kerjakan Soal
                    </a>
                </div>
            </div>
            <div class="mt-6 md:mt-0 animate-float">
                <div class="bg-white/20 backdrop-blur-sm rounded-full w-32 h-32 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-lg border border-blue-200 animate-fade-in-up delay-100 transform hover:scale-105 transition-transform">
            <div class="flex items-center">
                <div class="p-3 bg-blue-500 rounded-lg text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-3xl font-bold text-gray-900">5</div>
                    <div class="text-gray-600">File Diunggah</div>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 shadow-lg border border-green-200 animate-fade-in-up delay-200 transform hover:scale-105 transition-transform">
            <div class="flex items-center">
                <div class="p-3 bg-green-500 rounded-lg text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-3xl font-bold text-gray-900">12</div>
                    <div class="text-gray-600">Soal Dikerjakan</div>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 shadow-lg border border-yellow-200 animate-fade-in-up delay-300 transform hover:scale-105 transition-transform">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-500 rounded-lg text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-3xl font-bold text-gray-900">8.5</div>
                    <div class="text-gray-600">Nilai Rata-rata</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aktivitas Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 animate-fade-in-up delay-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Aktivitas Terbaru</h3>
                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Lihat semua</a>
            </div>
            <div class="space-y-4">
                <div class="flex items-start p-3 hover:bg-blue-50 rounded-lg transition duration-200 transform hover:scale-[1.02] transition-transform">
                    <div class="p-2 bg-blue-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="font-medium text-gray-900">Matematika - Aljabar</div>
                        <div class="text-sm text-gray-600">File diunggah: 25 November 2025</div>
                    </div>
                </div>
                <div class="flex items-start p-3 hover:bg-green-50 rounded-lg transition duration-200 transform hover:scale-[1.02] transition-transform">
                    <div class="p-2 bg-green-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="font-medium text-gray-900">Fisika - Gerak Lurus</div>
                        <div class="text-sm text-gray-600">Soal dikerjakan: 24 November 2025</div>
                    </div>
                </div>
                <div class="flex items-start p-3 hover:bg-yellow-50 rounded-lg transition duration-200 transform hover:scale-[1.02] transition-transform">
                    <div class="p-2 bg-yellow-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="font-medium text-gray-900">Kimia - Struktur Atom</div>
                        <div class="text-sm text-gray-600">File diunggah: 23 November 2025</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fitur Utama -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 animate-fade-in-up delay-700">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Fitur Utama</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="?page=upload" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200 border border-blue-100 transform hover:scale-[1.02] transition-transform">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="font-medium text-gray-900">Unggah File</div>
                        <div class="text-sm text-gray-600">Unggah dan analisis</div>
                    </div>
                </a>

                <a href="?page=exercises" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200 border border-green-100 transform hover:scale-[1.02] transition-transform">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="font-medium text-gray-900">Latihan Soal</div>
                        <div class="text-sm text-gray-600">Kerjakan soal</div>
                    </div>
                </a>

                <a href="?page=analisis_materi" class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200 border border-purple-100 transform hover:scale-[1.02] transition-transform">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.874-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="font-medium text-gray-900">Analisis Materi</div>
                        <div class="text-sm text-gray-600">Pelajari ringkasan</div>
                    </div>
                </a>

                <a href="?page=chat" class="flex items-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition duration-200 border border-yellow-100 transform hover:scale-[1.02] transition-transform">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="font-medium text-gray-900">Tanya Sinar</div>
                        <div class="text-sm text-gray-600">Tanya AI</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>