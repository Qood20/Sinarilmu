<?php
// admin/pages/edit_user.php - Halaman untuk mengedit pengguna

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil ID pengguna dari parameter
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['error'] = "ID pengguna tidak valid.";
    header('Location: ?page=users');
    exit;
}

// Ambil data pengguna
$stmt = $pdo->prepare("SELECT id, full_name, email, username, role, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "Pengguna tidak ditemukan.";
    header('Location: ?page=users');
    exit;
}

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';
    
    // Validasi
    if (empty($full_name) || empty($email) || empty($username)) {
        $_SESSION['error'] = "Nama lengkap, email, dan username harus diisi.";
    } else {
        // Cek apakah email/username sudah digunakan oleh pengguna lain
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $stmt->execute([$email, $username, $user_id]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email atau username sudah digunakan oleh pengguna lain.";
        } else {
            // Siapkan query update
            if (!empty($password)) {
                // Update dengan password baru
                $encrypted_password = encrypt_password($password);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, role = ?, password = ? WHERE id = ?");
                $result = $stmt->execute([$full_name, $email, $username, $role, $encrypted_password, $user_id]);
            } else {
                // Update tanpa password
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, role = ? WHERE id = ?");
                $result = $stmt->execute([$full_name, $email, $username, $role, $user_id]);
            }
            
            if ($result) {
                // Catat aktivitas
                log_activity($_SESSION['user_id'], 'Edit Pengguna', 'Mengedit data pengguna: ' . $full_name);
                
                $_SESSION['success'] = "Data pengguna berhasil diperbarui.";
                
                // Redirect kembali ke daftar pengguna
                header('Location: ?page=users');
                exit;
            } else {
                $_SESSION['error'] = "Gagal memperbarui data pengguna.";
            }
        }
    }
}

// Ambil data terbaru jika terjadi error validasi
if (isset($_SESSION['error'])) {
    $user['full_name'] = $_POST['full_name'] ?? $user['full_name'];
    $user['email'] = $_POST['email'] ?? $user['email'];
    $user['username'] = $_POST['username'] ?? $user['username'];
    $user['role'] = $_POST['role'] ?? $user['role'];
}
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Edit Pengguna</h2>
        
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
                <input type="text" id="full_name" name="full_name" value="<?php echo escape($user['full_name']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="<?php echo escape($user['email']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" value="<?php echo escape($user['username']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role" name="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi Baru (kosongkan jika tidak ingin diubah)</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Simpan Perubahan
                </button>
                <a href="?page=users" class="flex-1 py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>