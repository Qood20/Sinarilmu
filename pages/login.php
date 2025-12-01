<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-center text-gray-900 mb-6">Masuk ke Akun Anda</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo escape($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="pages/process_login.php" method="post" class="space-y-6">
        <div>
            <label for="email_username" class="block text-sm font-medium text-gray-700">Email / Username</label>
            <input type="text" id="email_username" name="email_username" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
            <input type="password" id="password" name="password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="remember_me" class="ml-2 block text-sm text-gray-900">Ingat saya</label>
            </div>

            <div class="text-sm">
                <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Lupa kata sandi?</a>
            </div>
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Masuk
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            Belum punya akun?
            <a href="?page=register" class="font-medium text-blue-600 hover:text-blue-500">Daftar di sini</a>
        </p>
    </div>
</div>