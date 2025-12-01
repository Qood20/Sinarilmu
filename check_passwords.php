<?php
// check_passwords.php - File untuk memeriksa password dalam database

require_once 'includes/functions.php';

global $pdo;

try {
    $result = $pdo->query("SELECT id, full_name, email, username, password, role FROM users");
    $users = $result->fetchAll();
    
    echo "<h3>Pemeriksaan Password Pengguna:</h3>";
    foreach ($users as $user) {
        $isAdmin = ($user['role'] === 'admin') ? ' (admin)' : '';
        echo "<p><strong>" . htmlspecialchars($user['full_name']) . $isAdmin . "</strong><br>";
        echo "Email: " . htmlspecialchars($user['email']) . "<br>";
        echo "Username: " . htmlspecialchars($user['username']) . "<br>";
        echo "Password dalam DB: " . substr($user['password'], 0, 10) . "..." . "<br>";
        
        // Coba verifikasi password default "password"
        $passwordMatch = verify_password('password', $user['password']);
        echo "Password cocok dengan 'password': " . ($passwordMatch ? 'YA' : 'TIDAK') . "<br>";
        echo "</p><hr>";
    }
} catch (Exception $e) {
    echo "<p>Error mengakses database: " . $e->getMessage() . "</p>";
}
?>
<br>
<a href="index.php">Kembali ke Home</a>