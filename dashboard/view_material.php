<?php
// dashboard/view_material.php - Handler aman untuk menampilkan file materi dari admin

// Cek apakah sesi sudah aktif sebelum memulai sesi baru
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "<h1>403 Forbidden</h1><p>Anda harus login terlebih dahulu untuk mengakses file ini.</p>";
    exit;
}

require_once '../includes/functions.php';

// Ambil parameter file
$file_path = $_GET['file'] ?? '';
$material_id = $_GET['id'] ?? '';

// Validasi input
if (empty($file_path) || empty($material_id)) {
    header('HTTP/1.0 400 Bad Request');
    echo "<h1>400 Bad Request</h1><p>Parameter file tidak lengkap.</p>";
    exit;
}

// Sanitasi path untuk mencegah directory traversal
$file_path = str_replace('..', '', $file_path);
$file_path = str_replace('../', '', $file_path);

// Cek apakah file benar-benar merupakan file materi dari database
global $pdo;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT file_path, original_name, file_size, file_type FROM materi_pelajaran WHERE id = ? AND status = 'aktif'");
        $stmt->execute([$material_id]);
        $material = $stmt->fetch();
        
        if (!$material) {
            header('HTTP/1.0 404 Not Found');
            echo "<h1>404 Not Found</h1><p>File tidak ditemukan atau tidak aktif.</p>";
            exit;
        }
        
        // Cocokkan file_path dari database dengan yang diminta
        if ($material['file_path'] !== $file_path) {
            header('HTTP/1.0 403 Forbidden');
            echo "<h1>403 Forbidden</h1><p>Akses ke file ini tidak diizinkan.</p>";
            exit;
        }
        
        // Tentukan path lengkap file
        $full_path = dirname(__DIR__) . '/' . $material['file_path'];
        
        // Cek apakah file benar-benar ada
        if (!file_exists($full_path)) {
            header('HTTP/1.0 404 Not Found');
            echo "<h1>404 Not Found</h1><p>File fisik tidak ditemukan.</p>";
            exit;
        }
        
        // Baca file dan kirim ke browser
        $file_extension = strtolower(pathinfo($material['original_name'], PATHINFO_EXTENSION));
        
        // Set header berdasarkan tipe file
        $content_type = 'application/octet-stream'; // default
        
        // Tentukan content type berdasarkan ekstensi
        $mime_types = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed'
        ];
        
        if (isset($mime_types[$file_extension])) {
            $content_type = $mime_types[$file_extension];
        }
        
        // Set headers untuk file download/preview
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: inline; filename="' . basename($material['original_name']) . '"');
        header('Content-Length: ' . filesize($full_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Baca dan kirim file
        readfile($full_path);
        
        // Catat akses file jika perlu
        try {
            log_activity($_SESSION['user_id'], 'Akses Materi', "User mengakses file materi: {$material['original_name']}");
        } catch (Exception $e) {
            // Jika log gagal, lanjutkan saja
            error_log("Gagal mencatat log aktivitas: " . $e->getMessage());
        }
        
        exit;
        
    } catch (PDOException $e) {
        header('HTTP/1.0 500 Internal Server Error');
        echo "<h1>500 Internal Server Error</h1><p>Terjadi kesalahan saat mengakses database.</p>";
        error_log("Database error in view_material.php: " . $e->getMessage());
        exit;
    }
} else {
    header('HTTP/1.0 500 Internal Server Error');
    echo "<h1>500 Internal Server Error</h1><p>Koneksi database tidak tersedia.</p>";
    exit;
}
?>