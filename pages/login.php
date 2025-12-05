<?php
// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include file fungsi umum - path relatif ke root direktori
require_once dirname(__DIR__) . '/includes/functions.php';
?>

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

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }

    .animate-fade-in-down {
        animation: fade-in-down 1s ease-out;
    }

    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out;
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
</style>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-purple-100 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Animated floating elements in background -->
    <div class="absolute top-20 left-10 w-6 h-6 bg-yellow-300 rounded-full opacity-20 animate-float"></div>
    <div class="absolute top-1/3 right-20 w-4 h-4 bg-blue-300 rounded-full opacity-20 animate-float" style="animation-delay: 0.5s;"></div>
    <div class="absolute bottom-1/4 left-1/4 w-5 h-5 bg-purple-300 rounded-full opacity-20 animate-float" style="animation-delay: 1s;"></div>
    <div class="absolute bottom-20 right-1/3 w-3 h-3 bg-pink-300 rounded-full opacity-20 animate-float" style="animation-delay: 1.5s;"></div>

    <div class="max-w-md w-full space-y-8 animate-fade-in-up">
        <div class="bg-white p-10 rounded-2xl shadow-xl relative">
            <div class="text-center mb-8 animate-fade-in-down">
                <div class="mx-auto h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center animate-float">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 animate-fade-in-down delay-300">
                    Masuk ke Akun Anda
                </h2>
                <p class="mt-2 text-sm text-gray-600 animate-fade-in-up delay-500">
                    Akses fitur belajar AI terbaik untuk meningkatkan pemahamanmu
                </p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg animate-fade-in-down delay-300">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php echo escape($_SESSION['error']); unset($_SESSION['error']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6 animate-fade-in-up delay-500" action="pages/process_login.php" method="post">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email_username" class="sr-only">Email / Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                            <input id="email_username" name="email_username" type="text" required class="appearance-none rounded-lg relative block w-full px-3 py-4 pl-12 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm transition duration-300 hover:shadow-md" placeholder="Email atau Username">
                        </div>
                    </div>
                    <div class="mt-5">
                        <label for="password" class="sr-only">Kata Sandi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" required class="appearance-none rounded-lg relative block w-full px-3 py-4 pl-12 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm transition duration-300 hover:shadow-md" placeholder="Kata Sandi">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">Ingat saya</label>
                    </div>
                    <div class="text-sm">
                        <a href="?page=forgot_password" class="font-medium text-blue-600 hover:text-blue-500">Lupa kata sandi?</a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg transform hover:scale-105 transition duration-300">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Masuk ke Akun
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center animate-fade-in-up delay-700">
                <p class="text-sm text-gray-600">
                    Belum punya akun?
                    <a href="?page=register" class="font-bold text-blue-600 hover:text-blue-500">Daftar di sini</a>
                </p>
            </div>
        </div>

        <div class="text-center text-sm text-gray-500 animate-fade-in-up delay-1000">
            <p>© 2025 Sinar Ilmu. Belajar lebih mudah dengan AI.</p>
        </div>
    </div>
</div>