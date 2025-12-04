<?php
// dashboard/pages/change_password.php - Halaman ganti kata sandi terpisah

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Ambil data pengguna
$user = get_user_by_id($_SESSION['user_id']);

// Tampilkan pesan error atau sukses jika ada
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';

// Hapus session setelah ditampilkan
if (isset($_SESSION['error'])) unset($_SESSION['error']);
if (isset($_SESSION['success'])) unset($_SESSION['success']);
?>

<div class="max-w-md mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Ganti Kata Sandi</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo escape($success); ?>
            </div>
        <?php endif; ?>

        <form action="process_password_change.php" method="post" class="space-y-4">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Kata Sandi Saat Ini</label>
                <input type="password" id="current_password" name="current_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">Kata Sandi Baru</label>
                <input type="password" id="new_password" name="new_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Kata sandi minimal 6 karakter</p>
            </div>

            <div>
                <label for="confirm_new_password" class="block text-sm font-medium text-gray-700">Konfirmasi Kata Sandi Baru</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Ganti Kata Sandi
                </button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="?page=profile" class="text-sm text-blue-600 hover:text-blue-800">Kembali ke Profil</a>
        </div>
    </div>
</div>