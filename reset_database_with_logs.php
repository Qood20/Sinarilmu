<?php
// reset_database_with_logs.php - Script untuk menghapus dan membuat ulang database dengan data lengkap

require_once __DIR__ . '/config/database.php';

echo "Memulai penghapusan dan pembuatan ulang database Sinar Ilmu dengan data dan log...\n";

try {
    // Hapus database jika sudah ada
    $pdo->exec("DROP DATABASE IF EXISTS dbsinarilmu");
    echo "Database lama dihapus (jika ada).\n";
    
    // Buat database baru
    $pdo->exec("CREATE DATABASE dbsinarilmu");
    echo "Database baru dibuat.\n";
    
    // Gunakan database baru
    $pdo->exec("USE dbsinarilmu");
    
    // Membaca file schema SQL
    $sql = file_get_contents('database_schema.sql');
    
    if ($sql === false) {
        throw new Exception("Gagal membaca file database_schema.sql");
    }
    
    // Eksekusi perintah SQL
    $pdo->exec($sql);
    
    echo "Database dan tabel-tabel telah berhasil dibuat!\n";
    echo "Database: dbsinarilmu\n";
    echo "Tabel yang dibuat: users, upload_files, analisis_ai, bank_soal_ai, hasil_soal_user, chat_ai, notifikasi, log_aktivitas\n";
    
    // Informasi login default
    echo "\nInformasi login default:\n";
    echo "Email admin: admin@sinarilmu.com\n";
    echo "Password admin: password\n";
    echo "Email pengguna uji: budi@example.com, ani@example.com, siti@example.com\n";
    echo "Password pengguna uji: password\n";
    
    // Tambahkan log aktivitas awal
    $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aksi, deskripsi, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    
    // Catat aktivitas pembuatan sistem
    $stmt->execute([
        1, // user_id (admin)
        'Sistem Diinisialisasi',
        'Database dan sistem Sinar Ilmu berhasil diinisialisasi ulang',
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Script'
    ]);
    
    echo "Data log aktivitas awal telah ditambahkan.\n";
    
} catch (PDOException $e) {
    echo "Error saat membuat database: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Proses inisialisasi selesai.\n";