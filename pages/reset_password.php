<?php
// pages/reset_password.php - Halaman untuk mereset password menggunakan token

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/includes/functions.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Jika pengguna sudah login, arahkan ke dashboard
if (is_logged_in()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/');
    } else {
        header('Location: dashboard/');
    }
    exit;
}

if (empty($token)) {
    $error = "Token reset password tidak valid.";
} else {
    // Validasi token
    $token_data = validate_password_reset_token($token);
    
    if (!$token_data) {
        $error = "Token reset password tidak valid atau telah kedaluwarsa.";
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || empty($confirm_password)) {
                $error = "Kata sandi dan konfirmasi kata sandi harus diisi.";
            } else if ($password !== $confirm_password) {
                $error = "Kata sandi dan konfirmasi kata sandi tidak cocok.";
            } else if (strlen($password) < 6) {
                $error = "Kata sandi minimal harus 6 karakter.";
            } else {
                // Reset password
                $result = reset_user_password($token, $password);
                
                if ($result) {
                    $success = "Kata sandi berhasil direset. Silakan login dengan kata sandi baru Anda.";
                } else {
                    $error = "Terjadi kesalahan saat mereset kata sandi. Silakan coba lagi.";
                }
            }
        }
    }
}
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 animate-fade-in-down delay-300">
                    Reset Kata Sandi
                </h2>
                <p class="mt-2 text-sm text-gray-600 animate-fade-in-up delay-500">
                    Masukkan kata sandi baru untuk akun Anda
                </p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg animate-fade-in-down delay-300">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php echo escape($error); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg animate-fade-in-down delay-300">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                <?php echo escape($success); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-center animate-fade-in-up delay-700">
                    <a href="?page=login" class="font-bold text-blue-600 hover:text-blue-500">Kembali ke halaman login</a>
                </div>
            <?php else if (!$error): ?>
                <form class="mt-8 space-y-6 animate-fade-in-up delay-500" action="?page=reset_password&token=<?php echo escape($token); ?>" method="post">
                    <div class="rounded-md shadow-sm -space-y-px">
                        <div>
                            <label for="password" class="sr-only">Kata Sandi Baru</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input id="password" name="password" type="password" required 
                                       class="appearance-none rounded-lg relative block w-full px-3 py-4 pl-12 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm transition duration-300 hover:shadow-md" 
                                       placeholder="Kata Sandi Baru">
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <label for="confirm_password" class="sr-only">Konfirmasi Kata Sandi Baru</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input id="confirm_password" name="confirm_password" type="password" required 
                                       class="appearance-none rounded-lg relative block w-full px-3 py-4 pl-12 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm transition duration-300 hover:shadow-md" 
                                       placeholder="Konfirmasi Kata Sandi Baru">
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg transform hover:scale-105 transition duration-300">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            Reset Kata Sandi
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if (!$success): ?>
            <div class="mt-6 text-center animate-fade-in-up delay-700">
                <p class="text-sm text-gray-600">
                    Tidak menerima email? 
                    <a href="?page=forgot_password" class="font-bold text-blue-600 hover:text-blue-500">Kirim ulang</a>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <div class="text-center text-sm text-gray-500 animate-fade-in-up delay-1000">
            <p>© 2025 Sinar Ilmu. Belajar lebih mudah dengan AI.</p>
        </div>
    </div>
</div>
</div>
</file>