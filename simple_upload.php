<?php
// simple_upload.php - Versi upload file yang paling sederhana mungkin

session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

require_once '../includes/functions.php';

global $pdo;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
        $error = "Gagal mengunggah file.";
    } else {
        $file = $_FILES['file_upload'];
        $description = isset($_POST['file_description']) ? trim($_POST['file_description']) : '';

        // Validasi file
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $error = "Tipe file tidak diperbolehkan. Hanya PDF, DOC, DOCX, JPG, PNG yang diperbolehkan.";
        } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB
            $error = "Ukuran file terlalu besar. Maksimal 10MB.";
        } else {
            // Buat direktori upload jika belum ada
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777, true);
            }

            // Generate nama file unik
            $unique_filename = uniqid() . '_' . $file['name'];
            $upload_path = '../uploads/' . $unique_filename;

            // Pindahkan file ke direktori upload
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Simpan metadata file ke database
                $stmt = $pdo->prepare("INSERT INTO upload_files (user_id, filename, original_name, file_path, file_size, file_type, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([
                    $_SESSION['user_id'],
                    $unique_filename,
                    $file['name'],
                    $upload_path,
                    $file['size'],
                    $file_extension,
                    $description
                ]);

                if ($result) {
                    $success = "File berhasil diunggah dan sedang diproses oleh AI.";
                    
                    // Catat aktivitas upload
                    log_activity($_SESSION['user_id'], 'Upload File', 'Mengunggah file: ' . $file['name']);
                } else {
                    $error = "Gagal menyimpan data file ke database.";
                }
            } else {
                $error = "Gagal menyimpan file.";
            }
        }
    }
}

// Ambil file-file yang diunggah oleh pengguna ini
try {
    $stmt = $pdo->prepare("
        SELECT id, original_name, created_at, status, file_path
        FROM upload_files
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $files = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting user files: " . $e->getMessage());
    $files = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah File - Sinar Ilmu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-3xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Unggah File & Analisis AI</h2>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <p class="text-gray-600">
                    Unggah materi belajarmu dan biarkan AI Sinar Ilmu memprosesnya. Dalam hitungan detik, kamu akan mendapatkan penjelasan lengkap serta kumpulan latihan soal yang relevan.
                </p>
            </div>

            <form method="post" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="file_upload" class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                    <label class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500">
                                <span class="font-semibold">Klik untuk mengunggah</span> atau seret file ke sini
                            </p>
                            <p class="text-xs text-gray-500">
                                PDF, DOCX, JPG, PNG (MAX. 10MB)
                            </p>
                        </div>
                        <input id="file_upload" name="file_upload" type="file" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required />
                    </label>
                </div>

                <div>
                    <label for="file_description" class="block text-sm font-medium text-gray-700">Deskripsi File (Opsional)</label>
                    <textarea id="file_description" name="file_description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Unggah & Proses dengan AI
                    </button>
                </div>
            </form>
        </div>

        <!-- Riwayat Unggahan -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Unggahan</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($files)): ?>
                            <?php foreach ($files as $file): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($file['original_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($file['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?php echo ucfirst(htmlspecialchars($file['status'])); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" colspan="3">Tidak ada file diunggah</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>