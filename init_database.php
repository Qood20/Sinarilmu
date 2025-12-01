<?php
// init_database.php - Script untuk membuat tabel-tabel database Sinar Ilmu

require_once __DIR__ . '/config/database.php';

echo "Memulai inisialisasi database Sinar Ilmu...\n";

try {
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
    
} catch (PDOException $e) {
    echo "Error saat membuat database: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Proses inisialisasi selesai.\n";