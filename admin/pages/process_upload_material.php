<?php
// admin/pages/process_upload_material.php - Proses unggah file materi oleh admin

session_start();
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// 1. Keamanan: Cek apakah pengguna adalah admin dan request method adalah POST
if (!is_admin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['upload_error'] = "Akses tidak sah.";
    header('Location: ../?page=material_content');
    exit;
}

global $pdo;

// 2. Validasi Input Form
$judul = $_POST['judul'] ?? '';
$deskripsi = $_POST['deskripsi'] ?? '';
$kelas = $_POST['kelas'] ?? '';
$mata_pelajaran = $_POST['mata_pelajaran'] ?? '';
$sub_topik = $_POST['sub_topik'] ?? null;
$file = $_FILES['file'] ?? null;

if (empty($judul) || empty($kelas) || empty($mata_pelajaran) || $file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
    $_SESSION['upload_error'] = "Semua field yang wajib diisi harus diisi.";
    header('Location: ../?page=material_content');
    exit;
}

// 3. Proses File Upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['upload_error'] = "Terjadi error saat mengunggah file: " . get_upload_error_message($file['error']);
    header('Location: ../?page=material_content');
    exit;
}

// Cek tipe file yang diizinkan (misal: PDF, DOCX, PPTX)
$allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
if (!in_array($file['type'], $allowed_types)) {
    $_SESSION['upload_error'] = "Tipe file tidak diizinkan. Hanya PDF, DOCX, dan PPTX yang diperbolehkan.";
    header('Location: ../?page=material_content');
    exit;
}

// Cek ukuran file (misal: maks 10MB)
$max_size = 10 * 1024 * 1024; // 10 MB
if ($file['size'] > $max_size) {
    $_SESSION['upload_error'] = "Ukuran file terlalu besar. Maksimal 10MB.";
    header('Location: ../?page=material_content');
    exit;
}

// Buat nama file yang unik dan aman
$original_name = basename($file['name']);
$file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;

// Tentukan path penyimpanan
$upload_dir = dirname(__DIR__, 2) . '/uploads/materials/';
$file_path = 'uploads/materials/' . $unique_filename;
$destination = $upload_dir . $unique_filename;

// Pindahkan file ke direktori tujuan
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    $_SESSION['upload_error'] = "Gagal memindahkan file yang diunggah.";
    header('Location: ../?page=material_content');
    exit;
}

// 4. Simpan Metadata ke Database
try {
    $stmt = $pdo->prepare("
        INSERT INTO materi_pelajaran 
        (judul, deskripsi, kelas, mata_pelajaran, sub_topik, file_path, original_name, file_size, file_type, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $judul,
        $deskripsi,
        $kelas,
        $mata_pelajaran,
        $sub_topik,
        $file_path,
        $original_name,
        $file['size'],
        $file['type'],
        $_SESSION['user_id']
    ]);

    // Catat aktivitas
    log_activity($_SESSION['user_id'], 'Unggah Materi', "Materi baru '$judul' telah ditambahkan.");

    $_SESSION['upload_success'] = "Materi berhasil diunggah dan ditambahkan.";

} catch (PDOException $e) {
    // Jika database error, hapus file yang sudah terlanjur diunggah
    if (file_exists($destination)) {
        unlink($destination);
    }
    error_log("Database error on material upload: " . $e->getMessage());
    $_SESSION['upload_error'] = "Terjadi error pada database saat menyimpan materi.";
}

// 5. Redirect kembali ke halaman materi
header('Location: ../?page=material_content');
exit;

// Fungsi untuk pesan error upload yang lebih informatif
function get_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "File melebihi batas ukuran yang diizinkan server.";
        case UPLOAD_ERR_FORM_SIZE:
            return "File melebihi batas ukuran yang diizinkan form.";
        case UPLOAD_ERR_PARTIAL:
            return "File hanya terunggah sebagian.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Direktori sementara tidak ditemukan.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Gagal menulis file ke disk.";
        case UPLOAD_ERR_EXTENSION:
            return "Ekstensi PHP menghentikan unggahan file.";
        default:
            return "Terjadi error yang tidak diketahui.";
    }
}
