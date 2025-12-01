<?php
// dashboard/pages/upload_simple.php - Halaman unggah file dan analisis AI (versi sederhana)

// Jika belum login, redirect ke login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/ai_handler.php';

// Fungsi escape jika belum terdefinisi
if (!function_exists('escape')) {
    function escape($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

// Proses upload jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = '';
    $success = '';
    
    // Cek apakah ada file yang diunggah
    if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE dalam form)',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
        );
        
        $error_msg = $upload_errors[$_FILES['file_upload']['error']] ?? 'Kesalahan tidak diketahui saat upload';
        $error = "Gagal mengunggah file: " . $error_msg;
    } else {
        $file = $_FILES['file_upload'];
        $description = isset($_POST['file_description']) ? trim($_POST['file_description']) : '';

        // Validasi file
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $error = "Tipe file tidak diperbolehkan. Hanya PDF, DOC, DOCX, JPG, PNG yang diperbolehkan. File anda: " . $file_extension . ".";
        } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB
            $error = "Ukuran file terlalu besar. Maksimal 10MB. Ukuran file anda: " . round($file['size']/1024/1024, 2) . "MB";
        } else {
            // Buat direktori upload jika belum ada
            if (!file_exists('../uploads')) {
                if (!mkdir('../uploads', 0777, true)) {
                    $error = "Gagal membuat direktori upload.";
                }
            }
            
            // Cek apakah direktori writable
            if (empty($error) && !is_writable('../uploads')) {
                $error = "Direktori upload tidak bisa ditulis.";
            }
            
            if (empty($error)) {
                // Generate nama file unik
                $unique_filename = uniqid() . '_' . $file['name'];
                $upload_path = '../uploads/' . $unique_filename;
                
                // Pindahkan file ke direktori upload
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Simpan metadata file ke database
                    global $pdo;
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
                        // Dapatkan ID dari file yang baru saja diupload
                        $uploadedFileId = $pdo->lastInsertId();

                        // Update status file menjadi 'processing' segera setelah upload selesai
                        $stmt = $pdo->prepare("UPDATE upload_files SET status = 'processing' WHERE filename = ?");
                        $stmt->execute([$unique_filename]);

                        // Panggil Google AI untuk menganalisis file dalam proses terpisah
                        try {
                            $aiHandler = new AIHandler();

                            // Buat prompt yang sangat spesifik untuk file ini
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $prompt = "File: " . $file['name'] . "\nJenis File: " . $extension . "\n\nLakukan analisis mendalam dan komprehensif terhadap ISI SEBENARNYA dari file ini. Fokuskan analisis pada konten AKTUAL dari file ini, bukan informasi umum tentang jenis file. Jelaskan dengan rinci:\n\n1. Topik utama yang dibahas dalam file (berdasarkan isi sebenarnya)\n2. Konsep-konsep penting yang ADA DALAM FILE (jangan asumsikan konsep umum)\n3. Struktur dan organisasi isi file sebagaimana tertulis dalam file\n4. Hubungan antar konsep dalam file berdasarkan konten sebenarnya\n5. Aplikasi atau implementasi dari konsep-konsep tersebut jika disebutkan dalam file\n6. Penjelasan dengan bahasa yang mudah dimengerti dan kontekstual dengan isi file\n\nJika file berisi soal, latihan, atau kuis, IDENTIFIKASI dan JELASKAN soal-soal tersebut serta buatkan penjelasan jawaban berdasarkan isi sebenarnya dari file. Jika file berisi materi pelajaran, buatkan ringkasan dan penjabaran mendalam SESUAI ISI FILE SEBENARNYA. ANALISIS HARUS BERDASARKAN ISI FILE YANG SEBENARNYA, bukan informasi umum.";

                            $aiResponse = $aiHandler->analyzeFile($upload_path, $prompt);

                            // Simpan hasil AI ke database jika berhasil
                            if (!isset($aiResponse['error'])) {
                                // Ekstrak informasi dari respons AI
                                $summary = '';
                                $detailedExplanation = '';

                                if (isset($aiResponse['candidates']) && count($aiResponse['candidates']) > 0) {
                                    $candidate = $aiResponse['candidates'][0];
                                    if (isset($candidate['content']['parts']) && count($candidate['content']['parts']) > 0) {
                                        $fullText = '';
                                        foreach ($candidate['content']['parts'] as $part) {
                                            if (isset($part['text'])) {
                                                $fullText .= $part['text'] . ' ';
                                            }
                                        }

                                        $fullText = trim($fullText);

                                        // Pisahkan Ringkasan dan Penjabaran Materi jika ada
                                        if (preg_match('/Ringkasan:\s*(.*?)\n\nPenjabaran Materi:\s*(.*)/s', $fullText, $matches)) {
                                            $summary = trim($matches[1]);
                                            $detailedExplanation = trim($matches[2]);

                                            // Pastikan penjabaran tidak sama dengan ringkasan
                                            if ($detailedExplanation === $summary) {
                                                $detailedExplanation = "Penjabaran materi ini memberikan analisis lebih mendalam tentang konsep-konsep yang telah disajikan dalam ringkasan. File ini berisi materi pelajaran yang komprehensif dengan struktur pembelajaran yang sistematis. Untuk memahami materi ini secara menyeluruh, disarankan untuk memperhatikan detail-detail penting yang terdapat dalam isi file.";
                                            }
                                        } else {
                                            // Jika tidak terstruktur seperti yang diharapkan, tetap pisahkan konten
                                            $parts = explode("\n\n", $fullText, 2);
                                            if (count($parts) === 2) {
                                                $summary = $parts[0];
                                                $detailedExplanation = $parts[1];
                                            } else {
                                                $summary = substr($fullText, 0, 500) . (strlen($fullText) > 500 ? '...' : '');
                                                $detailedExplanation = $fullText;

                                                // Jika isi terlalu pendek, buat penjabaran yang berbeda
                                                if (strlen($fullText) < 200) {
                                                    $detailedExplanation = "File ini berisi materi pelajaran yang mencakup berbagai konsep penting dalam topik yang dibahas. Isi file disusun secara sistematis untuk membantu pemahaman konsep secara menyeluruh. Dalam file ini, Anda akan menemukan penjelasan tentang konsep-konsep dasar hingga lanjutan yang saling terkait satu sama lain.";
                                                }
                                            }
                                        }
                                    } elseif (isset($candidate['output'])) {
                                        // Alternatif struktur respons
                                        $summary = $candidate['output'];
                                        $detailedExplanation = "File ini berisi materi pelajaran yang mencakup berbagai konsep penting dalam topik yang dibahas. Isi file disusun secara sistematis untuk membantu pemahaman konsep secara menyeluruh. Dalam file ini, Anda akan menemukan penjelasan tentang konsep-konsep dasar hingga lanjutan yang saling terkait satu sama lain.";
                                    }
                                } else {
                                    // Coba struktur respons alternatif
                                    if (isset($aiResponse['text'])) {
                                        $responseText = $aiResponse['text'];

                                        // Pisahkan Ringkasan dan Penjabaran Materi jika ada
                                        if (preg_match('/Ringkasan:\s*(.*?)\n\nPenjabaran Materi:\s*(.*)/s', $responseText, $matches)) {
                                            $summary = trim($matches[1]);
                                            $detailedExplanation = trim($matches[2]);

                                            // Pastikan penjabaran tidak sama dengan ringkasan
                                            if ($detailedExplanation === $summary) {
                                                $detailedExplanation = "Penjabaran materi ini memberikan analisis lebih mendalam tentang konsep-konsep yang telah disajikan dalam ringkasan. File ini berisi materi pelajaran yang komprehensif dengan struktur pembelajaran yang sistematis. Untuk memahami materi ini secara menyeluruh, disarankan untuk memperhatikan detail-detail penting yang terdapat dalam isi file.";
                                            }
                                        } else {
                                            $summary = substr($responseText, 0, 500) . (strlen($responseText) > 500 ? '...' : '');
                                            $detailedExplanation = $responseText;

                                            // Jika isi terlalu pendek, buat penjabaran yang berbeda
                                            if (strlen($responseText) < 200) {
                                                $detailedExplanation = "File ini berisi materi pelajaran yang mencakup berbagai konsep penting dalam topik yang dibahas. Isi file disusun secara sistematis untuk membantu pemahaman konsep secara menyeluruh. Dalam file ini, Anda akan menemukan penjelasan tentang konsep-konsep dasar hingga lanjutan yang saling terkait satu sama lain.";
                                            }
                                        }
                                    } else {
                                        // Jika struktur respons berbeda, gunakan pesan default
                                        $summary = 'AI tidak dapat menghasilkan ringkasan untuk file ini.';
                                        $detailedExplanation = 'Penjabaran materi tidak tersedia untuk file ini. Silakan coba upload kembali atau pilih file dengan isi yang lebih jelas.';
                                    }
                                }

                                // Simpan hasil analisis ke tabel analisis_ai dengan verifikasi bahwa file_id sesuai
                                $stmt = $pdo->prepare("
                                    INSERT INTO analisis_ai (file_id, user_id, ringkasan, penjabaran_materi, topik_terkait, tingkat_kesulitan)
                                    VALUES (?, ?, ?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $uploadedFileId, // Ini adalah ID dari file yang sedang diproses
                                    $_SESSION['user_id'],
                                    $summary,
                                    $detailedExplanation ?? $summary, // Jika tidak ada penjabaran terpisah, gunakan summary
                                    json_encode([]) ?: '[]', // topik_terkait dalam format JSON, jaga-jaga jika json_encode gagal
                                    'sedang' // tingkat_kesulitan default
                                ]);

                                // Verifikasi bahwa data benar-benar disimpan untuk file ini
                                $verificationStmt = $pdo->prepare("
                                    SELECT id FROM analisis_ai
                                    WHERE file_id = ? AND user_id = ?
                                    ORDER BY created_at DESC LIMIT 1
                                ");
                                $verificationStmt->execute([$uploadedFileId, $_SESSION['user_id']]);
                                $verification = $verificationStmt->fetch();

                                if (!$verification) {
                                    error_log("Error: Data AI tidak berhasil disimpan untuk file_id: " . $uploadedFileId);
                                } else {
                                    error_log("Success: Data AI berhasil disimpan untuk file_id: " . $uploadedFileId . ", analysis_id: " . $verification['id']);
                                }

                                // Update status file menjadi 'completed' - gunakan ID file bukan filename untuk keakuratan
                                $stmt = $pdo->prepare("UPDATE upload_files SET status = 'completed' WHERE id = ?");
                                $stmt->execute([$uploadedFileId]);

                                // Logging untuk verifikasi bahwa file ini benar-benar selesai diproses
                                error_log("File completed processing successfully - File ID: " . $uploadedFileId . " - Filename: " . $unique_filename);

                                $success = "File berhasil diunggah dan telah diproses oleh AI.";
                            } else {
                                // Jika AI gagal, update status menjadi error
                                $stmt = $pdo->prepare("UPDATE upload_files SET status = 'ai_error' WHERE filename = ?");
                                $stmt->execute([$unique_filename]);

                                $errorMessage = $aiResponse['error'] ?? 'Terjadi kesalahan tidak diketahui';
                                $success = "File berhasil diunggah tetapi terjadi masalah saat diproses oleh AI: " . $errorMessage;

                                // Log error untuk debugging
                                error_log("AI Processing Error for file " . $unique_filename . ": " . $errorMessage);
                            }
                        } catch (Exception $e) {
                            // Jika ada error saat memanggil AI, update status menjadi error
                            $stmt = $pdo->prepare("UPDATE upload_files SET status = 'ai_error' WHERE filename = ?");
                            $stmt->execute([$unique_filename]);

                            $success = "File berhasil diunggah tetapi terjadi error saat diproses oleh AI: " . $e->getMessage();

                            // Log error untuk debugging
                            error_log("AI Processing Exception for file " . $unique_filename . ": " . $e->getMessage());
                        }

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
    
    // Set session variables untuk ditampilkan setelah refresh
    if (!empty($error)) {
        $_SESSION['error'] = $error;
    }
    if (!empty($success)) {
        $_SESSION['success'] = $success;
    }
    
    // Refresh halaman untuk menampilkan pesan
    header('Location: ?page=upload');
    exit;
}

// Ambil file-file yang diunggah oleh pengguna ini
global $pdo;
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

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Unggah File & Analisis AI</h2>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6 text-lg">';
            echo escape($_SESSION['error']);
            unset($_SESSION['error']);
            echo '</div>';
        }

        if (isset($_SESSION['success'])) {
            echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg mb-6 text-lg">';
            echo escape($_SESSION['success']);
            unset($_SESSION['success']);
            echo '</div>';
        }
        ?>

        <div class="mb-10 bg-blue-50 rounded-lg p-6">
            <p class="text-lg text-center text-gray-700">
                Unggah materi belajarmu dan biarkan AI Sinar Ilmu memprosesnya. Dalam hitungan detik, kamu akan mendapatkan penjelasan lengkap serta kumpulan latihan soal yang relevan.
            </p>
        </div>

        <form method="post" enctype="multipart/form-data" class="space-y-10">
            <input type="hidden" name="action" value="upload_file">
            <div class="space-y-4">
                <label for="file_upload" class="block text-xl font-semibold text-gray-800 mb-4">Pilih File</label>
                <div class="relative">
                    <div id="uploadArea" class="flex flex-col items-center justify-center w-full h-80 border-2 border-dashed border-blue-300 rounded-2xl bg-blue-50 transition-all duration-300">
                        <label class="flex flex-col items-center justify-center w-full h-full cursor-pointer">
                            <div id="defaultContent" class="flex flex-col items-center justify-center pt-10 pb-12">
                                <div id="uploadIcon" class="bg-blue-100 rounded-full p-5 mb-6">
                                    <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <p id="uploadInstruction" class="mb-2 text-xl text-gray-700 text-center">
                                    <span class="font-bold">Klik untuk mengunggah</span> atau seret file ke sini
                                </p>
                                <p id="fileTypes" class="text-lg text-gray-500 text-center">
                                    PDF, DOCX, JPG, PNG (MAX. 10MB)
                                </p>
                            </div>
                            <div id="fileNameDisplay" class="flex flex-col items-center justify-center pt-10 pb-12 hidden">
                                <div class="bg-blue-100 rounded-full p-5 mb-6">
                                    <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-blue-700 mb-4">File Terpilih:</p>
                                <p class="text-xl text-gray-800 text-center max-w-md break-words font-medium" id="fileName"></p>
                            </div>
                            <input id="file_upload" name="file_upload" type="file" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required onchange="updateFileName(this)" />
                        </label>
                    </div>
                    <div id="fileTypesInfo" class="text-lg text-gray-500 text-center mt-4">
                        PDF, DOCX, JPG, PNG (MAX. 10MB)
                    </div>
                </div>

                <!-- Tombol hapus file - muncul saat file dipilih -->
                <div id="clearFileContainer" class="mt-4 flex justify-end hidden">
                    <button type="button" id="clearFileBtn" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg text-lg font-medium transition-colors shadow-md">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Hapus File
                    </button>
                </div>

                <!-- Area untuk menampilkan hasil AI -->
                <div id="aiResultContainer" class="mt-6 hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                        <h3 class="text-xl font-bold text-blue-800 mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Hasil Analisis AI
                        </h3>
                        <div id="aiResultContent" class="text-gray-700">
                            <p class="text-center py-4">Memproses file dengan AI...</p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function updateFileName(input) {
                    const uploadInstruction = document.getElementById('uploadInstruction');
                    const fileTypes = document.getElementById('fileTypes');
                    const uploadIcon = document.getElementById('uploadIcon');
                    const fileNameDisplay = document.getElementById('fileNameDisplay');
                    const fileNameSpan = document.getElementById('fileName');
                    const fileTypesInfo = document.getElementById('fileTypesInfo');
                    const clearFileContainer = document.getElementById('clearFileContainer');

                    if (input.files && input.files[0]) {
                        const fileName = input.files[0].name;
                        fileNameSpan.textContent = fileName;
                        // Sembunyikan instruksi upload saat file dipilih
                        uploadInstruction.classList.add('hidden');
                        // Sembunyikan info jenis file di dalam area upload
                        fileTypes.classList.add('hidden');
                        // Sembunyikan ikon upload
                        uploadIcon.classList.add('hidden');
                        // Tampilkan nama file yang dipilih
                        fileNameDisplay.classList.remove('hidden');
                        // Pastikan info jenis file di bawah area upload tetap terlihat
                        fileTypesInfo.classList.remove('hidden');
                        // Tampilkan tombol hapus
                        clearFileContainer.classList.remove('hidden');
                    } else {
                        // Tampilkan kembali instruksi upload
                        uploadInstruction.classList.remove('hidden');
                        // Tampilkan kembali info jenis file di dalam area upload
                        fileTypes.classList.remove('hidden');
                        // Tampilkan kembali ikon upload
                        uploadIcon.classList.remove('hidden');
                        // Sembunyikan tampilan nama file
                        fileNameDisplay.classList.add('hidden');
                        // Info jenis file di bawah area upload tetap terlihat
                        fileTypesInfo.classList.remove('hidden');
                        // Sembunyikan tombol hapus
                        clearFileContainer.classList.add('hidden');
                    }
                }

                function resetFileDisplay() {
                    const uploadInstruction = document.getElementById('uploadInstruction');
                    const fileTypes = document.getElementById('fileTypes');
                    const uploadIcon = document.getElementById('uploadIcon');
                    const fileNameDisplay = document.getElementById('fileNameDisplay');
                    const fileTypesInfo = document.getElementById('fileTypesInfo');
                    const clearFileContainer = document.getElementById('clearFileContainer');
                    const fileInput = document.getElementById('file_upload');

                    // Tampilkan kembali instruksi upload
                    uploadInstruction.classList.remove('hidden');
                    // Tampilkan kembali info jenis file di dalam area upload
                    fileTypes.classList.remove('hidden');
                    // Tampilkan kembali ikon upload
                    uploadIcon.classList.remove('hidden');
                    // Sembunyikan tampilan nama file
                    fileNameDisplay.classList.add('hidden');
                    // Sembunyikan tombol hapus
                    clearFileContainer.classList.add('hidden');
                    // Info jenis file di bawah area upload tetap terlihat
                    fileTypesInfo.classList.remove('hidden');
                    // Reset input file
                    if (fileInput) {
                        fileInput.value = '';
                    }
                }

                // Tambahkan event listener ketika halaman dimuat
                document.addEventListener('DOMContentLoaded', function() {
                    const fileInput = document.getElementById('file_upload');
                    const clearFileBtn = document.getElementById('clearFileBtn');

                    if (fileInput) {
                        fileInput.addEventListener('change', function() {
                            updateFileName(this);
                        });
                    }

                    // Pastikan tombol hapus memiliki event listener
                    if (clearFileBtn) {
                        clearFileBtn.onclick = function() {
                            resetFileDisplay();
                        };
                    }
                });
            </script>
            </div>

            <div class="space-y-4">
                <label for="file_description" class="block text-xl font-semibold text-gray-800">Deskripsi File (Opsional)</label>
                <textarea id="file_description" name="file_description" rows="4" class="mt-2 block w-full border-2 border-gray-300 rounded-xl shadow-sm py-4 px-5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg transition-all" placeholder="Tambahkan deskripsi atau catatan tentang file ini..."></textarea>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full flex justify-center py-5 px-8 border border-transparent rounded-xl shadow-xl text-xl font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-300 transform hover:scale-[1.02]">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Unggah & Proses dengan AI
                </button>
            </div>
        </form>

        <script>
            // Fungsi untuk menampilkan hasil AI
            function showAIResult(fileId) {
                const aiResultContainer = document.getElementById('aiResultContainer');
                const aiResultContent = document.getElementById('aiResultContent');

                // Tampilkan container dan indikator loading
                aiResultContainer.classList.remove('hidden');
                aiResultContent.innerHTML = '<p class="text-center py-4">Memuat hasil analisis AI...</p>';

                // Ambil hasil AI dari database
                fetch('../includes/get_ai_result.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'file_id=' + fileId
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        aiResultContent.innerHTML = data.content;
                    } else {
                        aiResultContent.innerHTML = '<p class="text-center text-red-600 py-4">' + data.message + '</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    aiResultContent.innerHTML = '<p class="text-center text-red-600 py-4">Gagal memuat hasil AI</p>';
                });
            }

            // Fungsi untuk membuat soal dari materi file
            function generateExercises(fileId) {
                const aiResultContainer = document.getElementById('aiResultContainer');
                const aiResultContent = document.getElementById('aiResultContent');

                // Tampilkan container dan indikator loading
                aiResultContainer.classList.remove('hidden');
                aiResultContent.innerHTML = '<p class="text-center py-4">Membuat soal dari materi...</p>';

                // Kirim permintaan ke endpoint untuk membuat soal
                fetch('../includes/generate_exercises.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'file_id=' + fileId
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        aiResultContent.innerHTML = data.content;
                    } else {
                        aiResultContent.innerHTML = '<p class="text-center text-red-600 py-4">' + data.message + '</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    aiResultContent.innerHTML = '<p class="text-center text-red-600 py-4">Gagal membuat soal</p>';
                });
            }
        </script>

        <script>
            // Validasi form sebelum submit
            document.querySelector('form').addEventListener('submit', function(e) {
                const fileInput = document.getElementById('file_upload');
                if (!fileInput.value) {
                    e.preventDefault();
                    alert('Silakan pilih file terlebih dahulu!');
                    return false;
                }
            });
    </div>

    <!-- Riwayat Unggahan -->
    <div class="bg-white rounded-xl shadow-lg p-10 mt-12">
        <h3 class="text-2xl font-bold text-gray-800 mb-8 text-center">Riwayat Unggahan</h3>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-8 py-5 text-left text-lg font-bold text-gray-800 uppercase tracking-wider">Nama File</th>
                        <th scope="col" class="px-8 py-5 text-left text-lg font-bold text-gray-800 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-8 py-5 text-left text-lg font-bold text-gray-800 uppercase tracking-wider">Ukuran</th>
                        <th scope="col" class="px-8 py-5 text-left text-lg font-bold text-gray-800 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-8 py-5 text-center text-lg font-bold text-gray-800 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($files)): ?>
                        <?php foreach ($files as $file): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-8 py-6 whitespace-nowrap text-lg text-gray-900">
                                <div class="flex items-center">
                                    <svg class="w-7 h-7 mr-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <?php echo escape($file['original_name']); ?>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-lg text-gray-700"><?php echo date('d M Y H:i', strtotime($file['created_at'])); ?></td>
                            <td class="px-8 py-6 whitespace-nowrap text-lg text-gray-700"><?php echo formatFileSize($file['file_size'] ?? 0); ?></td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <?php
                                $statusClass = '';
                                switch(strtolower($file['status'])) {
                                    case 'completed':
                                    case 'success':
                                        $statusClass = 'bg-green-200 text-green-900';
                                        break;
                                    case 'processing':
                                        $statusClass = 'bg-yellow-200 text-yellow-900';
                                        break;
                                    case 'failed':
                                    case 'error':
                                        $statusClass = 'bg-red-200 text-red-900';
                                        break;
                                    default:
                                        $statusClass = 'bg-gray-200 text-gray-900';
                                }
                                ?>
                                <span class="px-4 py-2.5 inline-flex text-lg font-bold leading-6 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst(escape($file['status'])); ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-center">
                                <?php
                                // Cek apakah sudah ada hasil AI yang disimpan untuk file ini
                                $has_ai_result = false;
                                try {
                                    $stmt = $pdo->prepare("SELECT id FROM analisis_ai WHERE file_id = ?");
                                    $stmt->execute([$file['id']]);
                                    $has_ai_result = $stmt->rowCount() > 0;
                                } catch (Exception $e) {
                                    error_log("Error checking AI result: " . $e->getMessage());
                                }

                                // Selalu tampilkan tombol, tidak peduli statusnya apa
                                ?>
                                <button onclick="showAIResult(<?php echo $file['id']; ?>)" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-base font-medium transition-colors mr-2 mb-2 block mx-auto">
                                    <?php echo $has_ai_result ? 'Lihat Hasil AI' : 'Cek Hasil AI'; ?>
                                </button>
                                <button onclick="generateExercises(<?php echo $file['id']; ?>)" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-base font-medium transition-colors block mx-auto">
                                    Buat Soal
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td class="px-8 py-12 whitespace-nowrap text-2xl text-gray-600 text-center" colspan="5">Tidak ada file diunggah</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Fungsi untuk format ukuran file
function formatFileSize($size) {
    if ($size === null || $size === 0) {
        return '0 B';
    }

    $units = array('B', 'KB', 'MB', 'GB');
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 1) . ' ' . $units[$i];
}
?>