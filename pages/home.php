<!-- Animations CSS -->
<style>
    @keyframes fade-in-down {
        0% {
            opacity: 0;
            transform: translateY(-20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

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

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }

    .animate-fade-in-down {
        animation: fade-in-down 1s ease-out;
    }

    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out;
    }

    .animate-spin-slow {
        animation: spin 20s linear infinite;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
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

    .delay-1000 {
        animation-delay: 1s;
    }
</style>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="relative z-10">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight animate-fade-in-down">
                    Belajar Lebih Mudah dengan <span class="text-yellow-300">AI</span>
                </h1>
                <p class="mt-6 text-xl text-blue-100 max-w-2xl animate-fade-in-up delay-300">
                    Aplikasi belajar berbasis kecerdasan buatan yang dirancang untuk membantu kamu memahami materi pelajaran dengan lebih mudah, cepat, dan interaktif.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row gap-4 animate-fade-in-up delay-500">
                    <a href="?page=register" class="px-8 py-4 bg-yellow-400 text-gray-900 font-bold rounded-lg hover:bg-yellow-300 transition duration-300 text-center shadow-lg transform hover:scale-105 transition-transform">
                        Daftar Sekarang
                    </a>
                    <a href="?page=login" class="px-8 py-4 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition duration-300 text-center shadow-lg transform hover:scale-105 transition-transform">
                        Masuk ke Akun
                    </a>
                </div>
            </div>
            <div class="flex justify-center relative">
                <!-- Animated floating elements -->
                <div class="absolute top-10 left-10 w-4 h-4 bg-yellow-300 rounded-full animate-bounce opacity-70"></div>
                <div class="absolute top-32 right-16 w-3 h-3 bg-pink-300 rounded-full animate-ping opacity-70"></div>
                <div class="absolute bottom-20 left-20 w-2 h-2 bg-blue-300 rounded-full animate-bounce opacity-70 delay-1000"></div>

                <div class="relative animate-float">
                    <div class="bg-white/20 backdrop-blur-sm rounded-full w-80 h-80 flex items-center justify-center">
                        <div class="bg-white/30 backdrop-blur-sm rounded-full w-64 h-64 flex items-center justify-center animate-pulse">
                            <div class="bg-white/40 backdrop-blur-sm rounded-full w-48 h-48 flex items-center justify-center animate-bounce">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating particles background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-3 h-3 bg-yellow-300 rounded-full opacity-30 animate-ping"></div>
        <div class="absolute top-1/3 right-1/3 w-2 h-2 bg-blue-300 rounded-full opacity-30 animate-ping delay-1000"></div>
        <div class="absolute bottom-1/4 left-1/3 w-4 h-4 bg-pink-300 rounded-full opacity-30 animate-bounce delay-500"></div>
        <div class="absolute bottom-1/3 right-1/4 w-2 h-2 bg-purple-300 rounded-full opacity-30 animate-ping delay-700"></div>
    </div>
</div>

<!-- Fitur Aplikasi -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Fitur Unggulan</h2>
        <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
            Temukan berbagai kemudahan dalam belajar dengan teknologi AI
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-gradient-to-br from-blue-50 to-white p-8 rounded-xl shadow-lg border border-blue-100 transform hover:-translate-y-2 transition duration-300">
            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-4">Unggah File</h3>
            <p class="text-gray-600 text-center">Unggah berbagai jenis file materi belajar seperti PDF, DOCX, JPG, PNG untuk dianalisis oleh AI.</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-white p-8 rounded-xl shadow-lg border border-green-100 transform hover:-translate-y-2 transition duration-300">
            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-4">Analisis AI</h3>
            <p class="text-gray-600 text-center">AI akan menganalisis file yang diunggah dan memberikan penjelasan materi secara otomatis.</p>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-white p-8 rounded-xl shadow-lg border border-purple-100 transform hover:-translate-y-2 transition duration-300">
            <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-4">Latihan Soal</h3>
            <p class="text-gray-600 text-center">Dapatkan latihan soal yang dibuat khusus berdasarkan materi yang telah Anda pelajari.</p>
        </div>
    </div>
</div>

<!-- Statistik dan Benefit -->
<div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-5xl font-bold mb-2">1000+</div>
                <div class="text-xl">Soal Latihan</div>
            </div>
            <div>
                <div class="text-5xl font-bold mb-2">50+</div>
                <div class="text-xl">Materi Belajar</div>
            </div>
            <div>
                <div class="text-5xl font-bold mb-2">24/7</div>
                <div class="text-xl">Akses</div>
            </div>
        </div>
    </div>
</div>

<!-- Deskripsi Aplikasi -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Tentang Sinar Ilmu</h2>
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-8 shadow-md">
            <p class="text-xl text-gray-700 leading-relaxed">
                Sinar Ilmu merupakan aplikasi pembelajaran digital yang memanfaatkan teknologi AI untuk membantu pengguna memahami materi dengan lebih efektif. Melalui fitur unggahan file, latihan soal otomatis, hingga layanan tanya jawab interaktif, Sinar Ilmu hadir sebagai pendamping belajar yang cerdas, fleksibel, dan dapat digunakan kapan saja.
            </p>
        </div>
    </div>
</div>