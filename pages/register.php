<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-center text-gray-900 mb-6">Buat Akun Baru</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo escape($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo escape($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form action="pages/process_register.php" method="post" class="space-y-6">
        <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input type="text" id="full_name" name="full_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" id="username" name="username" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
            <input type="password" id="password" name="password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Konfirmasi Kata Sandi</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Daftar
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            Sudah punya akun?
            <a href="?page=login" class="font-medium text-blue-600 hover:text-blue-500">Masuk di sini</a>
        </p>
    </div>
</div>