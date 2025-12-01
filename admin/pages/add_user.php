<?php
// admin/pages/add_user.php - Halaman untuk menambah pengguna baru

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Proses penambahan pengguna jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Semua field harus diisi.";
    } else if ($password !== $confirm_password) {
        $_SESSION['error'] = "Kata sandi dan konfirmasi kata sandi tidak cocok.";
    } else if (strlen($password) < 6) {
        $_SESSION['error'] = "Kata sandi minimal 6 karakter.";
    } else {
        // Cek apakah email atau username sudah digunakan
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email atau username sudah digunakan.";
        } else {
            // Buat pengguna baru
            $user_id = create_user($full_name, $email, $username, $password);
            if ($user_id) {
                // Update role jika bukan user biasa
                if ($role !== 'user') {
                    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$role, $user_id]);
                }
                
                // Catat aktivitas
                log_activity($_SESSION['user_id'], 'Tambah Pengguna', 'Menambahkan pengguna baru: ' . $full_name);
                
                $_SESSION['success'] = "Pengguna berhasil ditambahkan.";
                
                // Redirect kembali ke daftar pengguna
                header('Location: ?page=users');
                exit;
            } else {
                $_SESSION['error'] = "Gagal menambahkan pengguna.";
            }
        }
    }
}
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Tambah Pengguna Baru</h2>
        
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
        
        <form method="post" class="space-y-6">
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo escape($_POST['full_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="<?php echo escape($_POST['email'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" value="<?php echo escape($_POST['username'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role" name="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="user" <?php echo ($_POST['role'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Konfirmasi Kata Sandi</label>
                <input type="password" id="confirm_password" name="confirm_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Tambah Pengguna
                </button>
                <a href="?page=users" class="flex-1 py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>