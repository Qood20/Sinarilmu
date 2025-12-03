<?php
// dashboard/pages/upload_simple.php - Halaman upload file dan analisis AI (versi sederhana)

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

// Variabel untuk pesan error dan success
$error = '';
$success = '';

// Proses upload jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_file') {
    // Validasi file
    if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE dalam form)',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
        ];

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

                        // Panggil AI untuk analisis dan pembuatan soal sekaligus
                        try {
                            $aiHandler = new AIHandler();

                            // Ekstraksi konten dari file sesuai jenisnya
                            $fileContent = extractFileContent($upload_path, $file_extension);

                            if ($fileContent === false) {
                                throw new Exception("Tidak dapat mengekstrak konten dari file jenis ini.");
                            }

                            // Batasi ukuran konten untuk menghindari batas API
                            $fileContent = substr($fileContent, 0, 12000); // Batasi hingga 12,000 karakter agar lebih fokus

                            $aiResponse = $aiHandler->getAnalysisAndExercises($fileContent, $file['name']);

                            if ($aiResponse) {
                                // 1. Parse respons AI
                                $analysisText = '';
                                $questionsJson = '';

                                // Coba beberapa format parsing untuk mendukung variasi output AI
                                if (preg_match('/---ANALYSIS_START---(.*?)---ANALYSIS_END---/s', $aiResponse, $analysisMatch)) {
                                    $analysisText = trim($analysisMatch[1]);
                                } else {
                                    // Jika tidak ada pembatas analisis, ambil bagian awal
                                    $analysisText = $aiResponse;
                                }

                                if (preg_match('/---QUESTIONS_START---(.*?)---QUESTIONS_END---/s', $aiResponse, $questionsMatch)) {
                                    $questionsJson = trim($questionsMatch[1]);
                                } else {
                                    // Jika tidak ada pembatas soal, coba ekstrak JSON di manapun
                                    $cleanResponse = preg_replace('/```json\s*|```/i', '', $aiResponse);
                                    $cleanResponse = preg_replace('/\s*```/', '', $cleanResponse);

                                    // Cari array JSON antara tanda kurung siku
                                    if (preg_match('/(\[.*\])/s', $cleanResponse, $jsonMatch)) {
                                        $questionsJson = trim($jsonMatch[1]);
                                    } else {
                                        $questionsJson = '';
                                    }
                                }

                                // Parse lebih lanjut untuk ringkasan dan penjabaran
                                $summary = '';
                                $detailedExplanation = '';
                                if (preg_match('/Ringkasan:\s*(.*?)\n\nPenjabaran Materi:\s*(.*)/s', $analysisText, $textMatch)) {
                                    $summary = trim($textMatch[1]);
                                    $detailedExplanation = trim($textMatch[2]);
                                } else {
                                    // Coba format alternatif
                                    if (preg_match('/Ringkasan:\s*(.*?)(?=Penjabaran Materi:|---|$)/s', $analysisText, $summaryMatch)) {
                                        $summary = trim($summaryMatch[1]);
                                    }
                                    if (preg_match('/Penjabaran Materi:\s*(.*?)(?=---|$)/s', $analysisText, $detailMatch)) {
                                        $detailedExplanation = trim($detailMatch[1]);
                                    } else {
                                        $detailedExplanation = $analysisText; // Fallback
                                    }
                                }

                                // 2. Simpan analisis ke database
                                // Cek apakah sudah ada analisis untuk file ini
                                $existingAnalysisStmt = $pdo->prepare("SELECT id FROM analisis_ai WHERE file_id = ? AND user_id = ?");
                                $existingAnalysisStmt->execute([$uploadedFileId, $_SESSION['user_id']]);
                                $existingAnalysis = $existingAnalysisStmt->fetch();

                                if ($existingAnalysis) {
                                    // Hapus soal-soal lama yang terkait dengan analisis file ini saja (hanya jika file diupload ulang)
                                    $deleteQuestionsStmt = $pdo->prepare("DELETE FROM bank_soal_ai WHERE analisis_id = ?");
                                    $deleteQuestionsStmt->execute([$existingAnalysis['id']]);

                                    // Update analisis yang sudah ada
                                    $updateStmt = $pdo->prepare("UPDATE analisis_ai SET ringkasan = ?, penjabaran_materi = ? WHERE id = ?");
                                    $updateStmt->execute([$summary, $detailedExplanation, $existingAnalysis['id']]);
                                    $analysisId = $existingAnalysis['id'];
                                } else {
                                    // Jika belum ada analisis sebelumnya, buat yang baru
                                    $insertStmt = $pdo->prepare("INSERT INTO analisis_ai (file_id, user_id, ringkasan, penjabaran_materi) VALUES (?, ?, ?, ?)");
                                    $insertStmt->execute([$uploadedFileId, $_SESSION['user_id'], $summary, $detailedExplanation]);
                                    $analysisId = $pdo->lastInsertId();
                                }

                                // 3. Simpan soal-soal ke tabel 'bank_soal_ai'
                                $questionsAdded = 0;

                                // Coba parsing JSON dengan berbagai pendekatan
                                $questions = null;
                                $jsonText = $questionsJson;

                                if (!empty($jsonText)) {
                                    // Bersihkan karakter aneh dan coba parsing
                                    $jsonText = preg_replace('/[^\x20-\x7E\x{00A0}-\x{D7FF}\x{E000}-\x{FFFD}\n\r\t]/u', '', $jsonText);
                                    $jsonText = preg_replace('/\\\\/', '\\', $jsonText); // Hapus escape ganda
                                    $jsonText = preg_replace('/,\s*]/', ']', $jsonText); // Hapus koma sebelum kurung siku tutup
                                    $jsonText = preg_replace('/,\s*}/', '}', $jsonText); // Hapus koma sebelum kurung kurawal tutup
                                    $jsonText = preg_replace('/,\s*,/', ',', $jsonText); // Hapus koma ganda
                                    $jsonText = trim($jsonText);

                                    // Coba parsing JSON
                                    $questions = json_decode($jsonText, true);

                                    // Jika parsing gagal, coba bersihkan lebih lanjut
                                    if (json_last_error() !== JSON_ERROR_NONE) {
                                        error_log("JSON parsing failed for: " . $jsonText);
                                        // Coba bersihkan dan parsing lagi
                                        $jsonText = preg_replace('/\s+/', ' ', $jsonText);
                                        $jsonText = trim($jsonText);
                                        $questions = json_decode($jsonText, true);
                                    }

                                    // Jika masih gagal, coba hapus karakter di luar array
                                    if (json_last_error() !== JSON_ERROR_NONE) {
                                        $cleanJson = $jsonText;
                                        // Hapus teks sebelum kurung buka pertama dan setelah kurung tutup terakhir
                                        if (preg_match('/(\[.*\])/', $cleanJson, $match)) {
                                            $cleanJson = $match[1];
                                            $questions = json_decode($cleanJson, true);
                                        }
                                    }

                                    if (json_last_error() !== JSON_ERROR_NONE) {
                                        error_log("Final JSON parsing failed. Error: " . json_last_error_msg());
                                    } else {
                                        error_log("JSON parsing successful. Questions count: " . (is_array($questions) ? count($questions) : '0'));
                                    }
                                }

                                if ($analysisId && !empty($questions) && is_array($questions) && json_last_error() === JSON_ERROR_NONE) {
                                    error_log("Processing " . count($questions) . " questions from AI response");
                                    $questionStmt = $pdo->prepare("INSERT INTO bank_soal_ai (analisis_id, user_id, soal, pilihan_a, pilihan_b, pilihan_c, pilihan_d, kunci_jawaban) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                                    foreach ($questions as $q) {
                                        // Pastikan ini adalah array soal yang valid
                                        if (!is_array($q)) continue;

                                        // Tangani berbagai format kunci yang mungkin dikembalikan oleh AI
                                        $soal = $q['soal'] ?? $q['question'] ?? $q['Question'] ?? ($q[0] ?? (is_array($q) && count($q) > 0 ? $q[array_keys($q)[0]] : 'Soal tidak tersedia'));
                                        $pilihan_key = null;

                                        // Cek beberapa kemungkinan kunci untuk pilihan
                                        if (isset($q['pilihan']) && is_array($q['pilihan'])) {
                                            $pilihan_key = 'pilihan';
                                        } elseif (isset($q['options']) && is_array($q['options'])) {
                                            $pilihan_key = 'options';
                                        } elseif (isset($q['Choices']) && is_array($q['Choices'])) {
                                            $pilihan_key = 'Choices';
                                        } elseif (isset($q['Pilihan']) && is_array($q['Pilihan'])) {
                                            $pilihan_key = 'Pilihan';
                                        } elseif (isset($q['A']) || isset($q['B']) || isset($q['C']) || isset($q['D'])) {
                                            // Jika pilihan langsung di root array
                                            $pilihan_key = 'root';
                                        }

                                        if ($pilihan_key && $pilihan_key !== 'root') {
                                            $pilihan = $q[$pilihan_key];
                                            $pilihan_a = $pilihan['a'] ?? $pilihan['A'] ?? (is_array($pilihan) ? ($pilihan[0] ?? '') : '');
                                            $pilihan_b = $pilihan['b'] ?? $pilihan['B'] ?? (is_array($pilihan) ? ($pilihan[1] ?? '') : '');
                                            $pilihan_c = $pilihan['c'] ?? $pilihan['C'] ?? (is_array($pilihan) ? ($pilihan[2] ?? '') : '');
                                            $pilihan_d = $pilihan['d'] ?? $pilihan['D'] ?? (is_array($pilihan) ? ($pilihan[3] ?? '') : '');
                                        } else {
                                            // Jika pilihan langsung di root array atau fallback
                                            $pilihan_a = $q['a'] ?? $q['A'] ?? (is_array($q) && isset($q[1]) ? $q[1] : '');
                                            $pilihan_b = $q['b'] ?? $q['B'] ?? (is_array($q) && isset($q[2]) ? $q[2] : '');
                                            $pilihan_c = $q['c'] ?? $q['C'] ?? (is_array($q) && isset($q[3]) ? $q[3] : '');
                                            $pilihan_d = $q['d'] ?? $q['D'] ?? (is_array($q) && isset($q[4]) ? $q[4] : '');
                                        }

                                        $kunci_jawaban = $q['kunci_jawaban'] ?? $q['correct_answer'] ?? $q['kunci'] ?? $q['answer'] ?? $q['Correct'] ?? (is_array($q) && count($q) > 0 ? array_keys($q)[0] : 'a');

                                        // Hanya simpan jika soalnya valid (memiliki isi yang berarti)
                                        if (trim($soal) !== '' && strlen(trim($soal)) > 5) {
                                            try {
                                                $result = $questionStmt->execute([
                                                    $analysisId,
                                                    $_SESSION['user_id'],
                                                    $soal,
                                                    $pilihan_a,
                                                    $pilihan_b,
                                                    $pilihan_c,
                                                    $pilihan_d,
                                                    $kunci_jawaban
                                                ]);
                                                if ($result) {
                                                    $questionsAdded++;
                                                    error_log("Successfully inserted question: " . substr($soal, 0, 50) . "...");
                                                } else {
                                                    error_log("Failed to execute question insert statement. Details: " . print_r([
                                                        'soal' => $soal,
                                                        'pilihan_a' => $pilihan_a,
                                                        'pilihan_b' => $pilihan_b,
                                                        'pilihan_c' => $pilihan_c,
                                                        'pilihan_d' => $pilihan_d,
                                                        'kunci_jawaban' => $kunci_jawaban
                                                    ], true));
                                                }
                                            } catch (Exception $qe) {
                                                error_log("Failed to insert question: " . $qe->getMessage() . ". Details: " . print_r([
                                                    'soal' => $soal,
                                                    'pilihan_a' => $pilihan_a,
                                                    'pilihan_b' => $pilihan_b,
                                                    'pilihan_c' => $pilihan_c,
                                                    'pilihan_d' => $pilihan_d,
                                                    'kunci_jawaban' => $kunci_jawaban
                                                ], true));
                                            }
                                        } else {
                                            error_log("Question too short or empty: '" . $soal . "'");
                                        }
                                    }
                                } else {
                                    error_log("No valid questions array found. questions var: " . (is_array($questions) ? 'is array' : 'not array') . ", count: " . (is_array($questions) ? count($questions) : 'N/A'));
                                    error_log("JSON Error: " . json_last_error_msg() . " - Content: " . $questionsJson);

                                    // Jika parsing JSON gagal, coba parsing manual untuk mencari soal
                                    if (!empty($questionsJson)) {
                                        $manualQuestions = parseQuestionsManually($questionsJson);
                                        if (!empty($manualQuestions)) {
                                            error_log("Found " . count($manualQuestions) . " questions via manual parsing");
                                            $questionStmt = $pdo->prepare("INSERT INTO bank_soal_ai (analisis_id, user_id, soal, pilihan_a, pilihan_b, pilihan_c, pilihan_d, kunci_jawaban) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                            foreach ($manualQuestions as $q) {
                                                try {
                                                    $result = $questionStmt->execute([
                                                        $analysisId,
                                                        $_SESSION['user_id'],
                                                        $q['question'],
                                                        $q['a'] ?? '',
                                                        $q['b'] ?? '',
                                                        $q['c'] ?? '',
                                                        $q['d'] ?? '',
                                                        $q['answer'] ?? 'a'
                                                    ]);
                                                    if ($result) {
                                                        $questionsAdded++;
                                                    }
                                                } catch (Exception $qe) {
                                                    error_log("Failed to insert manual question: " . $qe->getMessage());
                                                }
                                            }
                                        }
                                    }
                                }

                                // Jika tidak ada soal yang ditambahkan tapi analisis berhasil, tetap update status
                                if ($analysisId && $questionsAdded === 0) {
                                    error_log("No questions were saved for analysis_id: " . $analysisId . ", but summary was saved. Raw question content: " . $questionsJson);

                                    // Jika AI tidak menghasilkan soal yang valid, coba buat soal dasar dari konten file
                                    try {
                                        $basicQuestionsCreated = createBasicQuestionsFromContent($analysisId, $fileContent, $pdo, $_SESSION['user_id']);
                                        if ($basicQuestionsCreated > 0) {
                                            $questionsAdded = $basicQuestionsCreated;
                                            error_log("Created " . $basicQuestionsCreated . " basic questions as fallback for analysis_id: " . $analysisId);
                                        }
                                    } catch (Exception $e) {
                                        error_log("Error creating basic questions fallback: " . $e->getMessage());
                                    }
                                }

                                // 4. Update status file menjadi 'completed'
                                $stmt = $pdo->prepare("UPDATE upload_files SET status = 'completed' WHERE id = ?");
                                $stmt->execute([$uploadedFileId]);

                                // Tampilkan pesan sesuai dengan apakah soal berhasil dibuat atau tidak
                                if ($questionsAdded > 0) {
                                    $success = "File berhasil diunggah, dianalisis, dan " . $questionsAdded . " soal latihan telah dibuat.";
                                    // Tambahkan notifikasi ke database
                                    $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                                    $notifStmt->execute([
                                        $_SESSION['user_id'],
                                        "Upload Berhasil",
                                        "File " . $file['name'] . " berhasil diunggah dan menghasilkan " . $questionsAdded . " soal latihan.",
                                        "success"
                                    ]);
                                } else {
                                    // Jika sebelumnya sudah diset error karena fallback, gunakan pesan itu
                                    $success = "File berhasil diunggah dan dianalisis, " . ($questionsAdded > 0 ? $questionsAdded . " soal telah dibuat." : " tetapi tidak ada soal latihan yang dihasilkan.");
                                    // Tambahkan notifikasi ke database
                                    $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                                    $notifStmt->execute([
                                        $_SESSION['user_id'],
                                        "Upload Berhasil",
                                        "File " . $file['name'] . " berhasil diunggah dan dianalisis, tetapi tidak menghasilkan soal latihan.",
                                        "info"
                                    ]);
                                }
                            } else {
                                // Jika AI gagal, update status menjadi 'failed'
                                $stmt = $pdo->prepare("UPDATE upload_files SET status = 'failed' WHERE id = ?");
                                $stmt->execute([$uploadedFileId]);
                                $error = "File berhasil diunggah tetapi gagal diproses oleh AI.";

                                // Tambahkan notifikasi error
                                $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                                $notifStmt->execute([
                                    $_SESSION['user_id'],
                                    "Upload Gagal",
                                    "File " . $file['name'] . " berhasil diunggah tetapi gagal diproses oleh AI.",
                                    "error"
                                ]);
                            }
                        } catch (Exception $e) {
                            // Jika ada error saat memproses AI response, update status
                            $stmt = $pdo->prepare("UPDATE upload_files SET status = 'failed' WHERE id = ?");
                            $stmt->execute([$uploadedFileId]);
                            $error = "Terjadi error saat memproses file dengan AI: " . $e->getMessage();
                            error_log("AI Processing Exception for file ID " . $uploadedFileId . ": " . $e->getMessage());

                            // Tambahkan notifikasi error
                            $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                            $notifStmt->execute([
                                $_SESSION['user_id'],
                                "Error Upload",
                                "Terjadi error saat memproses file " . $file['name'] . ": " . $e->getMessage(),
                                "error"
                            ]);
                        }

                        // Catat aktivitas upload
                        log_activity($_SESSION['user_id'], 'Upload File', 'Mengunggah file: ' . $file['name']);
                    } else {
                        $error = "Gagal menyimpan data file ke database.";

                        // Tambahkan notifikasi error
                        $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                        $notifStmt->execute([
                            $_SESSION['user_id'],
                            "Upload Gagal",
                            "Gagal menyimpan data file ke database",
                            "error"
                        ]);
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
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 mb-6 rounded-lg text-lg">';
            echo escape($_SESSION['error']);
            echo '</div>';
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-6 py-4 mb-6 rounded-lg text-lg">';
            echo escape($_SESSION['success']);
            echo '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div class="bg-blue-50 p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-blue-800 mb-3">Cara Mengunggah File</h3>
                <ul class="space-y-2 text-blue-700">
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span>Pilih file dari komputer Anda</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span>File yang didukung: PDF, DOC, DOCX, JPG, PNG</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span>Ukuran maksimal: 10MB</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        <span>Setelah upload, file akan diproses oleh AI untuk membuat soal latihan</span>
                    </li>
                </ul>
            </div>
            <div class="bg-green-50 p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-green-800 mb-3">Materi yang Didukung</h3>
                <ul class="space-y-2 text-green-700">
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">•</span>
                        <span>Matematika, Fisika, Kimia, Biologi</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">•</span>
                        <span>Ilmu Pengetahuan Sosial, Bahasa</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">•</span>
                        <span>Materi pelajaran SMA/SMK/MA</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">•</span>
                        <span>Modul pelatihan dan ebook pendidikan</span>
                    </li>
                </ul>
            </div>
        </div>

        <form method="post" action="" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="action" value="upload_file">
            
            <div class="space-y-4">
                <label for="file_upload" class="block text-lg font-medium text-gray-700">Pilih File</label>
                <input 
                    type="file" 
                    id="file_upload" 
                    name="file_upload" 
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                    class="block w-full text-lg border-2 border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                >
                <p class="text-sm text-gray-500">Format yang didukung: PDF, DOC, DOCX, JPG, PNG. Maksimal 10MB.</p>
            </div>

            <div class="space-y-4">
                <label for="file_description" class="block text-lg font-medium text-gray-700">Deskripsi File (Opsional)</label>
                <textarea 
                    id="file_description" 
                    name="file_description" 
                    rows="3" 
                    class="block w-full text-lg border-2 border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Tambahkan deskripsi atau catatan tentang file ini..."><?php echo isset($_POST['file_description']) ? escape($_POST['file_description']) : ''; ?></textarea>
            </div>

            <div class="pt-4">
                <button 
                    type="submit" 
                    class="w-full flex justify-center py-4 px-6 border border-transparent rounded-xl shadow-md text-xl font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Unggah & Proses dengan AI
                </button>
            </div>
        </form>

        <!-- Riwayat Upload -->
        <div class="mt-12">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Riwayat Upload</h3>
            
            <?php if (!empty($files)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Upload</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($files as $file): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo escape(basename($file['original_name'])); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo escape($file['original_name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d M Y H:i', strtotime($file['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusClass = '';
                                    switch(strtolower($file['status'])) {
                                        case 'completed':
                                        case 'success':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'processing':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'failed':
                                        case 'error':
                                            $statusClass = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo ucfirst(escape($file['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <?php
                                    // Ambil analisis_id terkait file ini
                                    $analisisStmt = $pdo->prepare("SELECT id FROM analisis_ai WHERE file_id = ? AND user_id = ?");
                                    $analisisStmt->execute([$file['id'], $_SESSION['user_id']]);
                                    $analisis = $analisisStmt->fetch();
                                    $analisis_id_url = $analisis ? $analisis['id'] : 0;
                                    ?>
                                    <a href="?page=exercise_detail&analisis_id=<?php echo $analisis_id_url; ?>" class="text-blue-600 hover:text-blue-900 mr-4">Lihat Latihan</a>
                                    <a href="<?php echo escape($file['file_path']); ?>" target="_blank" class="text-green-600 hover:text-green-900">Lihat File</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h4 class="mt-4 text-lg font-medium text-gray-900">Belum Ada File Diunggah</h4>
                    <p class="mt-1 text-gray-500">File yang Anda upload akan muncul di sini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Fungsi helper untuk parsing soal secara manual dari respons AI jika JSON tidak valid
function parseQuestionsManually($text) {
    $questions = [];
    
    // Bersihkan teks dari karakter aneh
    $cleanText = preg_replace('/[^\x20-\x7E\x{00A0}-\x{D7FF}\x{E000}-\x{FFFD}\n\r\t]/u', ' ', $cleanText);
    
    // Coba berbagai pola untuk menemukan soal pilihan ganda
    $patterns = [
        // Pola untuk soal dengan nomor dan pilihan A, B, C, D
        '/(\d+\. [^A-D]+?)\s+A\.\s*(.*?)\s+B\.\s*(.*?)\s+C\.\s*(.*?)\s+D\.\s*(.*?)(?=\n\d+\.|\Z)/s',
        // Pola untuk soal tanpa nomor
        '/([^A-D\n]*?[^A-D\n]\?|Apa|Bagaimana|Mengapa).*?\s+A\.\s*(.*?)\s+B\.\s*(.*?)\s+C\.\s*(.*?)\s+D\.\s*(.*?)(?=\n[^A-D\n]*\?|Apa|Bagaimana|Mengapa|\Z)/s'
    ];
    
    foreach ($patterns as $pattern) {
        preg_match_all($pattern, $cleanText, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            if (count($match) >= 5) { // Pastikan ada cukup elemen
                $question = trim($match[1]);
                $options = [
                    'a' => trim($match[2]),
                    'b' => trim($match[3]), 
                    'c' => trim($match[4]),
                    'd' => trim($match[5])
                ];
                
                if (strlen($question) > 10 && strlen($options['a']) > 2) { // Pastikan soal dan opsi valid
                    $questions[] = [
                        'question' => $question,
                        'a' => $options['a'],
                        'b' => $options['b'],
                        'c' => $options['c'], 
                        'd' => $options['d'],
                        'answer' => 'a' // Default ke A, bisa dirubah sesuai kunci
                    ];
                }
            }
        }
    }
    
    return $questions;
}

// Fungsi untuk membuat soal dasar dari konten file jika AI tidak menghasilkan soal yang valid
function createBasicQuestionsFromContent($analysisId, $fileContent, $pdo, $userId) {
    $questionsCreated = 0;

    // Bersihkan konten dari tag dan karakter aneh
    $cleanContent = strip_tags($fileContent);
    $cleanContent = html_entity_decode($cleanContent);
    $cleanContent = preg_replace('/\s+/', ' ', $cleanContent); // Normalisasi spasi

    // Pisahkan konten menjadi paragraf/kalimat
    $sentences = preg_split('/[.!?]+/', $cleanContent);
    $sentences = array_filter($sentences, function($sentence) {
        $sentence = trim($sentence);
        return strlen($sentence) > 25 && 
               !preg_match('/(©|copyright|all rights reserved|halaman|page|gambar|tabel|source|sumber|author|penulis|tahun|publisher)/i', $sentence);
    });
    
    $sentences = array_values($sentences); // Re-index array

    // Buat soal dari kalimat-kalimat penting
    $validQuestions = [];
    
    foreach ($sentences as $sentence) {
        $sentence = trim($sentence);
        if (strlen($sentence) < 30 || strlen($sentence) > 150) continue; // Sesuaikan panjang kalimat

        // Identifikasi apakah kalimat mengandung konsep penting untuk dibuat soal
        if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi|fungsi|cara|langkah|proses|manfaat|tujuan|konsep|prinsip|rumus|bentuk|sifat|ciri|jenis|macam|contoh)/i', $sentence)) {
            $questionText = '';
            if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi)/i', $sentence)) {
                // Format soal definisi
                $questionText = 'Apa yang dimaksud dengan: "' . substr($sentence, 0, 60) . '..."?';
            } else if (preg_match('/(fungsi|manfaat|tujuan)/i', $sentence)) {
                // Format soal fungsi/manfaat
                $questionText = 'Apa fungsi/manfaat dari konsep dalam: "' . substr($sentence, 0, 50) . '..."?';
            } else if (preg_match('/(cara|langkah|proses)/i', $sentence)) {
                // Format soal proses/langkah
                $questionText = 'Apa yang dimaksud dengan proses: "' . substr($sentence, 0, 40) . '..."?';
            } else {
                // Format umum
                $questionText = 'Apa yang dapat dipelajari dari pernyataan: "' . substr($sentence, 0, 50) . '..."?';
            }

            // Buat pilihan jawaban dari fragmen kalimat
            $words = explode(' ', $sentence);
            $wordCount = count($words);
            
            $pilihanA = implode(' ', array_slice($words, 0, min(4, $wordCount))) . '...';
            $pilihanB = implode(' ', array_slice($words, max(0, $wordCount-4), 4)) . '...';
            $pilihanC = 'Pilihan terkait: ' . implode(' ', array_slice($words, max(0, intval($wordCount/2)), 4)) . '...';
            $pilihanD = 'Konsep yang berkaitan dengan: ' . substr($sentence, 0, 30) . '...';

            $validQuestions[] = [
                'question' => $questionText,
                'options' => [
                    'a' => $pilihanA,
                    'b' => $pilihanB,
                    'c' => $pilihanC, 
                    'd' => $pilihanD
                ],
                'answer' => 'a' // Jawaban default
            ];

            if (count($validQuestions) >= 5) { // Batasi jumlah soal dasar
                break;
            }
        }
    }

    if (!empty($validQuestions)) {
        // Simpan soal-soal ke database
        $stmt = $pdo->prepare("INSERT INTO bank_soal_ai (analisis_id, user_id, soal, pilihan_a, pilihan_b, pilihan_c, pilihan_d, kunci_jawaban) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($validQuestions as $q) {
            try {
                $stmt->execute([
                    $analysisId,
                    $userId,
                    $q['question'],
                    $q['options']['a'],
                    $q['options']['b'],
                    $q['options']['c'], 
                    $q['options']['d'],
                    $q['answer']
                ]);
                $questionsCreated++;
            } catch (Exception $e) {
                error_log("Error inserting basic question: " . $e->getMessage());
            }
        }
    }

    return $questionsCreated;
}

// Fungsi untuk mengekstrak konten dari berbagai jenis file
function extractFileContent($filepath, $extension) {
    $extension = strtolower($extension);

    // Pastikan file eksis sebelum mencoba ekstrak konten
    if (!file_exists($filepath)) {
        error_log("File tidak ditemukan: " . $filepath);
        return false;
    }

    switch ($extension) {
        case 'txt':
            $content = file_get_contents($filepath);
            if ($content === false) {
                error_log("Gagal membaca file TXT: " . $filepath);
                return false;
            }
            return $content;

        case 'pdf':
            $content = extractTextFromPDF($filepath);
            if ($content === false || empty($content)) {
                error_log("Gagal mengekstrak konten dari PDF: " . $filepath);
                // Jika ekstraksi PDF gagal, baca sebagai teks biner dan bersihkan
                $binary_content = file_get_contents($filepath);
                if ($binary_content !== false) {
                    $binary_content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', ' ', $binary_content);
                    return $binary_content;
                }
                return false;
            }
            return $content;

        case 'docx':
            $content = extractTextFromDOCX($filepath);
            if ($content === false || empty($content)) {
                error_log("Gagal mengekstrak konten dari DOCX: " . $filepath);
                // Jika ekstraksi DOCX gagal, baca sebagai teks biner dan bersihkan
                $binary_content = file_get_contents($filepath);
                if ($binary_content !== false) {
                    $binary_content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', ' ', $binary_content);
                    return $binary_content;
                }
                return false;
            }
            return $content;

        case 'doc':
            $content = extractTextFromDOC($filepath);
            if ($content === false || empty($content)) {
                error_log("Gagal mengekstrak konten dari DOC: " . $filepath);
                // Jika ekstraksi DOC gagal, baca sebagai teks biner dan bersihkan
                $binary_content = file_get_contents($filepath);
                if ($binary_content !== false) {
                    $binary_content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', ' ', $binary_content);
                    return $binary_content;
                }
                return false;
            }
            return $content;

        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            // Untuk file gambar, kembalikan deskripsi karena tidak bisa diekstrak teks tanpa OCR
            $imageInfo = getimagesize($filepath);
            $width = $imageInfo[0] ?? 0;
            $height = $imageInfo[1] ?? 0;
            return "File gambar: " . basename($filepath) . " - Dimensi: {$width}x{$height}px. Konten teks tidak dapat diekstrak tanpa OCR.";

        default:
            // Untuk file lainnya, coba baca sebagai teks biasa
            $content = file_get_contents($filepath);
            if ($content === false) {
                error_log("Gagal membaca file jenis: " . $extension . " dari: " . $filepath);
                return false;
            }
            return $content;
    }
}

// Fungsi untuk ekstrak teks dari file PDF
function extractTextFromPDF($filepath) {
    if (!file_exists($filepath)) {
        return false;
    }

    // Coba beberapa metode
    if (function_exists('exec')) {
        $output_lines = [];
        $return_code = 0;
        $command = 'pdftotext ' . escapeshellarg($filepath) . ' - 2>&1';
        exec($command, $output_lines, $return_code);

        if ($return_code === 0) {
            $output = implode("\n", $output_lines);
            // Pastikan kita mengembalikan konten jika ada
            if (!empty(trim($output))) {
                return $output;
            }
        } else {
            error_log("pdftotext command failed with return code: " . $return_code . " for file: " . $filepath);
        }
    }

    // Jika metode di atas gagal atau tidak tersedia, baca sebagai biner dan bersihkan karakter non-teks
    $content = file_get_contents($filepath);
    if ($content === false) {
        return false;
    }

    // Bersihkan karakter non-ASCII dan non-printable
    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', ' ', $content);
    return $content;
}

// Fungsi untuk ekstrak teks dari file DOCX
function extractTextFromDOCX($filepath) {
    if (!file_exists($filepath)) {
        return false;
    }

    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        $result = $zip->open($filepath);

        if ($result === true) {
            $content = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($content !== false && !empty($content)) {
                // Decode XML entities dan bersihkan markup
                $content = htmlspecialchars_decode($content, ENT_QUOTES);
                $content = strip_tags($content);
                $content = preg_replace('/\s+/', ' ', $content); // Normalisasi spasi
                return $content;
            } else {
                error_log("Tidak dapat mengambil konten dari dokumen DOCX: " . $filepath);
            }
        } else {
            error_log("Gagal membuka file DOCX sebagai arsip ZIP: " . $filepath . ", error code: " . $result);
        }
    } else {
        error_log("Kelas ZipArchive tidak tersedia di sistem ini");
    }

    // Jika ekstraksi DOCX gagal, coba baca sebagai biner dan bersihkan
    $binary_content = file_get_contents($filepath);
    if ($binary_content !== false) {
        $binary_content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', ' ', $binary_content);
        return $binary_content;
    }

    return false;
}

// Fungsi untuk ekstrak teks dari file DOC
function extractTextFromDOC($filepath) {
    if (!file_exists($filepath)) {
        return false;
    }

    // Coba beberapa pendekatan untuk ekstrak dari file DOC
    $content = file_get_contents($filepath);
    if ($content !== false) {
        // Hapus karakter non-printable
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', ' ', $content);

        // Pastikan kita punya konten yang berarti
        $clean_content = trim($content);
        if (!empty($clean_content) && strlen($clean_content) > 10) {
            return $content;
        }
    }

    // Jika ekstraksi gagal, kembalikan false
    return false;
}
?>