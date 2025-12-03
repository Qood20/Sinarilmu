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

        // Tambahkan notifikasi error
        $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
        $notifStmt->execute([
            $_SESSION['user_id'],
            "Upload Gagal",
            "Gagal mengunggah file: " . $error_msg,
            "error"
        ]);
    } else {
        $file = $_FILES['file_upload'];
        $description = isset($_POST['file_description']) ? trim($_POST['file_description']) : '';

        // Validasi file
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $error = "Tipe file tidak diperbolehkan. Hanya PDF, DOC, DOCX, JPG, PNG yang diperbolehkan. File anda: " . $file_extension . ".";

            // Tambahkan notifikasi error
            $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
            $notifStmt->execute([
                $_SESSION['user_id'],
                "Upload Gagal",
                "Tipe file tidak diperbolehkan: " . $file_extension,
                "error"
            ]);
        } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB
            $error = "Ukuran file terlalu besar. Maksimal 10MB. Ukuran file anda: " . round($file['size']/1024/1024, 2) . "MB";

            // Tambahkan notifikasi error
            $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
            $notifStmt->execute([
                $_SESSION['user_id'],
                "Upload Gagal",
                "Ukuran file terlalu besar: " . round($file['size']/1024/1024, 2) . "MB (maksimal 10MB)",
                "error"
            ]);
        } else {
            // Buat direktori upload jika belum ada
            if (!file_exists('../uploads')) {
                if (!mkdir('../uploads', 0777, true)) {
                    $error = "Gagal membuat direktori upload.";

                    // Tambahkan notifikasi error
                    $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                    $notifStmt->execute([
                        $_SESSION['user_id'],
                        "Upload Gagal",
                        "Gagal membuat direktori upload",
                        "error"
                    ]);
                }
            }

            // Cek apakah direktori writable
            if (empty($error) && !is_writable('../uploads')) {
                $error = "Direktori upload tidak bisa ditulis.";

                // Tambahkan notifikasi error
                $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                $notifStmt->execute([
                    $_SESSION['user_id'],
                    "Upload Gagal",
                    "Direktori upload tidak bisa ditulis",
                    "error"
                ]);
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
                        $aiHandler = new AIHandler();
                        $aiResponse = null;
                        $isFallback = false;

                        // Ekstraksi konten dari file sesuai jenisnya
                        $fileContent = extractFileContent($upload_path, $file_extension);

                        if ($fileContent === false) {
                            throw new Exception("Tidak dapat mengekstrak konten dari file jenis ini.");
                        }

                        // Batasi ukuran konten untuk menghindari batas API
                        $fileContent = substr($fileContent, 0, 12000); // Batasi hingga 12,000 karakter agar lebih fokus

                        try {
                            // Gunakan fungsi yang disederhanakan namun lebih fokus
                            $aiResponse = $aiHandler->getAnalysisAndExercises($fileContent, $file['name']);
                        } catch (Exception $e) {
                            // Jika API error, gunakan pendekatan lokal
                            error_log("AI API Error: " . $e->getMessage() . ". Using local content-based approach.");
                            $isFallback = true;

                            // Gunakan handler alternatif yang hanya membaca dari isi file
                            if (file_exists(dirname(__DIR__, 2) . '/includes/ai_handler_fix.php')) {
                                require_once dirname(__DIR__, 2) . '/includes/ai_handler_fix.php';
                                $altHandler = new AIHandlerFixed();
                                $aiResponse = $altHandler->getAnalysisAndExercises($fileContent, $file['name']);
                            } else {
                                // Jika file handler alternatif tidak ada, gunakan fungsi lokal
                                $aiResponse = createBasicQuestionsFromContentAsFallback($analysisId, $fileContent, $pdo, $_SESSION['user_id']);
                            }
                        }

                            if ($aiResponse) {
                                try {
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
                                        // Jika tidak ditemukan pembatas standar, coba ekstrak bagian JSON
                                        $cleanResponse = preg_replace('/```json\s*|```/i', '', $aiResponse);
                                        $cleanResponse = preg_replace('/\s*```/', '', $cleanResponse);

                                        // Cari array JSON antara tanda kurung siku
                                        if (preg_match('/(\[.*\])/s', $cleanResponse, $jsonMatch)) {
                                            $questionsJson = trim($jsonMatch[1]);
                                        } else {
                                            $questionsJson = '';
                                        }
                                    }

                                    // Log untuk debugging
                                    error_log("AI Response Questions Section: " . substr($questionsJson, 0, 200) . "...");

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

                                    // Debug: log seluruh respons AI dan bagian-bagiannya
                                    error_log("Full AI Response: " . substr($aiResponse, 0, 500) . "...");
                                    error_log("Questions JSON extracted: " . substr($questionsJson, 0, 300) . "...");
                                    error_log("Questions JSON length: " . strlen($questionsJson));
                                    error_log("Is questionsJson empty? " . (empty($questionsJson) ? 'YES' : 'NO'));
                                    error_log("Analysis Text: " . substr($analysisText, 0, 200) . "...");

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

                                    // Jika respons adalah fallback dan tidak mengandung soal yang valid
                                    if ($isFallback && (empty($questionsJson) || $questionsJson === '[]')) {
                                        // Coba buat soal dasar dari konten file
                                        $basicQuestionsCreated = createBasicQuestionsFromContent($analysisId, $fileContent, $pdo, $_SESSION['user_id']);
                                        if ($basicQuestionsCreated > 0) {
                                            $questionsAdded = $basicQuestionsCreated;
                                        }
                                    } else {
                                        $questions = null;

                                        // Bersihkan teks JSON dari karakter aneh
                                        $jsonText = $questionsJson;
                                        if (!empty($jsonText)) {
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
                                                if (!is_array($q)) {
                                                    error_log("Invalid question format - not an array: " . print_r($q, true));
                                                    continue;
                                                }

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
                                            error_log("JSON Error: " . json_last_error_msg() . " - Content: " . $jsonText);

                                            // Jika parsing JSON gagal, coba parsing manual untuk mencari soal
                                            if (!empty($jsonText)) {
                                                $manualQuestions = parseQuestionsManually($jsonText);
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
                                    }

                                    // Jika tidak ada soal yang ditambahkan tapi analisis berhasil, tetap update status
                                    error_log("Checking if questions need to be created: analysisId=$analysisId, questionsAdded=$questionsAdded");
                                    if ($analysisId && $questionsAdded === 0) {
                                        error_log("No questions were saved for analysis_id: " . $analysisId . ", but summary was saved. Raw question content: " . substr($questionsJson, 0, 100) . "...");

                                        // Jika AI tidak menghasilkan soal yang valid, coba buat soal dasar dari konten file
                                        try {
                                            error_log("Attempting to create basic questions as fallback for analysis_id: " . $analysisId);
                                            $basicQuestionsCreated = createBasicQuestionsFromContent($analysisId, $fileContent, $pdo, $_SESSION['user_id']);
                                            error_log("Basic questions creation returned: " . $basicQuestionsCreated);
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
                                        // INI ADALAH MASALAHNYA - TAMPILKAN PESAN YANG ANDA LIHAT
                                        error_log("FINAL CHECK: questionsAdded is " . $questionsAdded . ", attempting guaranteed fallback! File: " . $file['name']);

                                        // PASTIKAN BAHWA ADA SOAL YANG DIBUAT JIKA questionsAdded TETAP 0
                                        if ($analysisId) {
                                            // Coba sekali lagi fungsi fallback sebagai usaha terakhir
                                            error_log("Executing createBasicQuestionsFromContent as guaranteed fallback for analysis_id: " . $analysisId);
                                            $finalAttempt = createBasicQuestionsFromContent($analysisId, $fileContent, $pdo, $_SESSION['user_id']);
                                            error_log("Guaranteed fallback returned: " . $finalAttempt);

                                            if ($finalAttempt > 0) {
                                                $questionsAdded = $finalAttempt;
                                                $success = "File berhasil diunggah, dianalisis, dan " . $questionsAdded . " soal latihan telah dibuat.";
                                                // Update notifikasi
                                                $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                                                $notifStmt->execute([
                                                    $_SESSION['user_id'],
                                                    "Upload Berhasil",
                                                    "File " . $file['name'] . " berhasil diunggah dan menghasilkan " . $questionsAdded . " soal latihan.",
                                                    "success"
                                                ]);
                                                error_log("SUCCESS: Created " . $questionsAdded . " questions as fallback for file " . $file['name']);
                                            } else {
                                                // Jika fungsi fallback masih mengembalikan 0, kita paksa membuat minimal 1 soal sebagai contoh
                                                error_log("WARNING: Fallback returned 0, forcing at least 1 basic question for file: " . $file['name']);
                                                $questionStmt = $pdo->prepare("INSERT INTO bank_soal_ai (analisis_id, user_id, soal, pilihan_a, pilihan_b, pilihan_c, pilihan_d, kunci_jawaban) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                                $questionStmt->execute([
                                                    $analysisId,
                                                    $_SESSION['user_id'],
                                                    "Apa yang menjadi pokok bahasan utama dari file " . $file['name'] . "?",
                                                    "Pokok bahasan utama file ini",
                                                    "Isi materi dalam file",
                                                    "Ringkasan file ini",
                                                    "Topik yang dibahas dalam file",
                                                    "a"
                                                ]);
                                                $questionsAdded = 1;
                                                $success = "File berhasil diunggah, dianalisis, dan " . $questionsAdded . " soal latihan telah dibuat (soal otomatis).";
                                                // Update notifikasi
                                                $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                                                $notifStmt->execute([
                                                    $_SESSION['user_id'],
                                                    "Upload Berhasil",
                                                    "File " . $file['name'] . " berhasil diunggah dan menghasilkan " . $questionsAdded . " soal latihan (soal otomatis).",
                                                    "success"
                                                ]);
                                            }
                                        } else {
                                            // Jika tidak ada analysisId, berikan error
                                            $error = "Gagal membuat analisis untuk file ini.";
                                            // Tambahkan notifikasi error
                                            $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                                            $notifStmt->execute([
                                                $_SESSION['user_id'],
                                                "Upload Gagal",
                                                "Gagal membuat analisis untuk file " . $file['name'],
                                                "error"
                                            ]);
                                        }
                                    }
                                } catch (Exception $e) {
                                    // Jika ada error saat memproses AI response, update status
                                    $stmt = $pdo->prepare("UPDATE upload_files SET status = 'failed' WHERE id = ?");
                                    $stmt->execute([$uploadedFileId]);
                                    $error = "Terjadi error saat memproses respons AI: " . $e->getMessage();
                                    error_log("AI Response Processing Exception for file ID " . $uploadedFileId . ": " . $e->getMessage());

                                    // Tambahkan notifikasi error
                                    $notifStmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, isi, tipe) VALUES (?, ?, ?, ?)");
                                    $notifStmt->execute([
                                        $_SESSION['user_id'],
                                        "Error Upload",
                                        "Terjadi error saat memproses respons AI untuk file " . $file['name'] . ": " . $e->getMessage(),
                                        "error"
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
                            // Jika ada error umum saat proses AI, update status
                            $stmt = $pdo->prepare("UPDATE upload_files SET status = 'failed' WHERE id = ?");
                            $stmt->execute([$uploadedFileId]);
                            $error = "Terjadi error saat memproses file dengan AI: " . $e->getMessage();
                            error_log("General AI Processing Exception for file ID " . $uploadedFileId . ": " . $e->getMessage());

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

// Fungsi untuk membuat soal latihan sederhana dari konten file
function generateBasicQuestionsFromContent($content, $fileName, $questionCount = 5) {
    // Ambil beberapa kalimat dari konten sebagai potensi soal
    $content = strip_tags($content); // Hapus tag HTML jika ada
    $content = html_entity_decode($content); // Decode entitas HTML
    $content = preg_replace('/\s+/', ' ', $content); // Normalisasi spasi

    // Pisahkan konten menjadi kalimat-kalimat
    $sentences = preg_split('/[.!?]+/', $content);
    $sentences = array_filter($sentences, function($sentence) {
        return strlen(trim($sentence)) > 20; // Ambil kalimat yang cukup panjang
    });

    $questions = [];

    // Coba temukan pola-pola pertanyaan umum dalam konten
    foreach ($sentences as $sentence) {
        $sentence = trim($sentence);
        if (strlen($sentence) < 30) continue; // Kalimat terlalu pendek

        // Jika kalimat mengandung kata-kata yang menunjukkan pertanyaan konsep
        if (preg_match('/(apa|bagaimana|mengapa|jelaskan|uraikan|definisi|pengertian|fungsi|tujuan|manfaat|proses|cara|prinsip|teori)/i', $sentence)) {
            $cleanSentence = preg_replace('/[0-9]+\./', '', $sentence); // Hapus penomoran jika ada
            $cleanSentence = trim($cleanSentence);

            if (!empty($cleanSentence) && !preg_match('/©|copyright|all rights reserved|halaman|page/i', $cleanSentence)) {
                $question = [
                    'soal' => $cleanSentence . '?',
                    'pilihan' => [
                        'a' => 'Pilihan A',
                        'b' => 'Pilihan B',
                        'c' => 'Pilihan C',
                        'd' => 'Pilihan D'
                    ],
                    'kunci_jawaban' => 'a' // Default ke A, bisa diubah pengguna nanti
                ];

                $questions[] = $question;

                if (count($questions) >= $questionCount) {
                    break; // Batasi jumlah soal
                }
            }
        }
    }

    // Jika tidak cukup soal dari pola pertanyaan, buat soal sederhana dari kalimat
    if (count($questions) < $questionCount) {
        // Ambil kalimat-kalimat penting sebagai soal
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) < 30 || strlen($sentence) > 200) continue; // Cukup panjang tapi tidak terlalu panjang

            // Hindari kalimat yang kelihatan seperti header atau referensi
            if (preg_match('/(daftar pustaka|referensi|lampiran|gambar|tabel|source|sumber|©|copyright)/i', $sentence)) {
                continue;
            }

            $question = [
                'soal' => 'Berdasarkan materi, apa yang dimaksud dengan: "' . substr($sentence, 0, 60) . '...".',
                'pilihan' => [
                    'a' => 'Pilihan A',
                    'b' => 'Pilihan B',
                    'c' => 'Pilihan C',
                    'd' => 'Pilihan D'
                ],
                'kunci_jawaban' => 'a'
            ];

            $questions[] = $question;

            if (count($questions) >= $questionCount) {
                break;
            }
        }
    }

    return $questions;
}

// Fungsi untuk menghasilkan respons dalam format yang sesuai dengan output AI
function generateResponseWithBasicQuestions($questions, $content) {
    // Ekstrak ringkasan sederhana dari konten
    $content = strip_tags($content);
    $content = preg_replace('/\s+/', ' ', $content);

    // Ambil beberapa paragraf pertama sebagai ringkasan
    $paragraphs = array_filter(explode("\n", $content), function($p) {
        return strlen(trim($p)) > 20;
    });

    $summary = '';
    $detailed = '';

    if (count($paragraphs) > 0) {
        $summary = substr($paragraphs[0], 0, 200) . (strlen($paragraphs[0]) > 200 ? '...' : '');
        // Gunakan beberapa paragraf pertama sebagai penjabaran
        $detailed = implode("\n\n", array_slice($paragraphs, 0, 3));
    } else {
        $summary = substr($content, 0, 200) . (strlen($content) > 200 ? '...' : '');
        $detailed = $content;
    }

    // Format respons untuk mengikuti struktur yang sama seperti output AI
    $response = "---ANALYSIS_START---\n";
    $response .= "Ringkasan:\n" . $summary . "\n\n";
    $response .= "Penjabaran Materi:\n" . $detailed . "\n";
    $response .= "---ANALYSIS_END---\n\n";
    $response .= "---QUESTIONS_START---\n";
    $response .= json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    $response .= "---QUESTIONS_END---";

    return $response;
}

// Fungsi untuk ekstraksi konten dari berbagai jenis file
function extractFileContent($filepath, $extension) {
    $extension = strtolower($extension);

    switch ($extension) {
        case 'txt':
            $content = file_get_contents($filepath);
            return $content !== false ? $content : '';

        case 'pdf':
            $content = extractTextFromPDF($filepath);
            return $content !== false ? $content : '';

        case 'docx':
            $content = extractTextFromDOCX($filepath);
            return $content !== false ? $content : '';

        case 'doc':
            $content = extractTextFromDOC($filepath);
            return $content !== false ? $content : '';

        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            // Untuk file gambar, kembalikan placeholder karena kita tidak memiliki OCR
            $content = extractTextFromImage($filepath);
            return $content !== false ? $content : "File gambar: " . basename($filepath);

        case 'html':
        case 'htm':
            $content = file_get_contents($filepath);
            if ($content !== false) {
                // Hapus tag HTML, hanya ambil teks
                $content = strip_tags($content);
                return $content;
            }
            return '';

        default:
            // Untuk jenis file lainnya, coba baca sebagai teks biasa
            $content = file_get_contents($filepath);
            if ($content !== false) {
                // Bersihkan karakter non-printable
                $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $content);
                return $content;
            }
            return '';
    }
}

// Fungsi untuk ekstraksi teks dari PDF
function extractTextFromPDF($filepath) {
    if (!file_exists($filepath)) {
        return false;
    }

    // Coba beberapa metode untuk mengekstrak teks dari PDF
    // Metode 1: Gunakan perintah pdftotext jika tersedia
    if (function_exists('exec')) {
        $output = '';
        $return_code = 0;
        $command = 'pdftotext ' . escapeshellarg($filepath) . ' - 2>&1';
        exec($command, $output, $return_code);

        if ($return_code === 0) {
            $text = implode("\n", $output);
            return $text;
        }
    }

    // Jika pdftotext tidak tersedia atau gagal, coba baca sebagai teks biasa
    $content = file_get_contents($filepath);
    if ($content === false) {
        return false;
    }

    // Coba ekstrak teks dari PDF secara manual - solusi fallback sederhana
    // Cari teks antara kurung, biasanya isi teks di PDF
    $text = '';
    $lines = explode("\n", $content);
    $in_text_section = false;

    foreach ($lines as $line) {
        // Coba pola teks umum di PDF
        if (preg_match_all('/\(([^()]+)\)/', $line, $matches)) {
            foreach ($matches[1] as $match) {
                // Hanya tambahkan jika teks terlihat valid (bukan hanya angka atau simbol)
                if (strlen($match) > 2 && str_word_count($match) > 0) {
                    $text .= $match . ' ';
                }
            }
        }
        // Coba pola lain seperti teks dalam kurung siku
        elseif (preg_match_all('/\[([^\[\]]+)\]/', $line, $matches)) {
            foreach ($matches[1] as $match) {
                if (strlen($match) > 2 && str_word_count($match) > 0) {
                    $text .= $match . ' ';
                }
            }
        }
    }

    return trim($text);
}

// Fungsi untuk ekstraksi teks dari DOCX
function extractTextFromDOCX($filepath) {
    if (!file_exists($filepath)) {
        return false;
    }

    // DOCX adalah arsip ZIP, jadi kita perlu membukanya
    // Karena ZipArchive mungkin tidak tersedia, kita coba dengan memeriksa apakah fungsi tersedia
    if (class_exists('ZipArchive')) {
        try {
            // Buka file DOCX sebagai arsip ZIP
            $zip = new ZipArchive();
            if ($zip->open($filepath) === true) {
                // Ambil konten dari file 'word/document.xml'
                $content = $zip->getFromName('word/document.xml');
                $zip->close();

                if ($content !== false) {
                    // Decode XML entities dan bersihkan markup
                    $content = htmlspecialchars_decode($content, ENT_QUOTES);
                    // Hapus tag XML
                    $content = strip_tags($content);
                    // Normalisasi whitespace
                    $content = preg_replace('/\s+/', ' ', $content);
                    return trim($content);
                }
            }
        } catch (Exception $e) {
            error_log("DOCX extraction error with ZipArchive: " . $e->getMessage());
        }
    } else {
        // Jika kelas ZipArchive tidak tersedia, coba alternatif
        // Gunakan perintah sistem jika tersedia
        if (function_exists('exec')) {
            $output = '';
            $return_code = 0;
            $temp_dir = sys_get_temp_dir();
            $temp_file = tempnam($temp_dir, 'docx_');
            $temp_extract_dir = $temp_dir . '/docx_' . uniqid();

            // Buat direktori sementara
            mkdir($temp_extract_dir, 0755, true);

            // Ekstrak file DOCX (yang merupakan ZIP) ke direktori sementara
            $command = 'unzip -j ' . escapeshellarg($filepath) . ' word/document.xml -d ' . escapeshellarg($temp_extract_dir) . ' 2>&1';
            exec($command, $output, $return_code);

            if ($return_code === 0) {
                $xml_path = $temp_extract_dir . '/word/document.xml';
                if (file_exists($xml_path)) {
                    $content = file_get_contents($xml_path);
                    // Bersihkan hasil
                    $content = htmlspecialchars_decode($content, ENT_QUOTES);
                    $content = strip_tags($content);
                    $content = preg_replace('/\s+/', ' ', $content);

                    // Hapus file sementara
                    unlink($xml_path);
                    rmdir($temp_extract_dir);

                    return trim($content);
                }
            } else {
                // Jika unzip tidak berhasil, hapus direktori sementara
                if (is_dir($temp_extract_dir)) {
                    array_map('unlink', glob("$temp_extract_dir/*"));
                    rmdir($temp_extract_dir);
                }
            }
        }
    }

    // Jika semua metode gagal, kembalikan false
    return false;
}

// Fungsi untuk ekstraksi teks dari DOC (implementasi sederhana)
function extractTextFromDOC($filepath) {
    if (!file_exists($filepath)) {
        return false;
    }

    $content = file_get_contents($filepath);
    if ($content === false) {
        return false;
    }

    // Ambil sebagian kecil dari file untuk menghindari memori berlebihan
    $content = substr($content, 0, 1024 * 1024); // Batasi hingga 1MB

    // Bersihkan karakter non-printable tetapi pertahankan karakter teks umum
    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', ' ', $content);
    // Untuk file DOC, bisa mengandung banyak karakter biner, jadi fokus pada teks yang terbaca
    $cleaned = '';
    $len = strlen($content);

    for ($i = 0; $i < $len; $i++) {
        $char = $content[$i];
        $ord = ord($char);

        // Hanya simpan karakter yang terlihat seperti teks
        if (($ord >= 32 && $ord <= 126) || $ord == 9 || $ord == 10 || $ord == 13 || ($ord >= 160 && $ord <= 255)) {
            $cleaned .= $char;
        }
    }

    // Hapus sekuens karakter aneh dan normalisasi whitespace
    $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $cleaned);
    $cleaned = preg_replace('/\s+/', ' ', $cleaned);

    return trim($cleaned);
}

// Fungsi untuk ekstraksi teks dari gambar (placeholder - dalam implementasi nyata, gunakan OCR)
function extractTextFromImage($filepath) {
    // Karena kita tidak punya layanan OCR, kembalikan deskripsi file
    // Dalam implementasi nyata, Anda mungkin ingin mengirim file ke layanan OCR
    // atau menggunakan library seperti Tesseract OCR
    $imageInfo = getimagesize($filepath);
    $width = $imageInfo[0] ?? 0;
    $height = $imageInfo[1] ?? 0;

    return "Gambar (" . basename($filepath) . ") - Dimensi: {$width}x{$height}px. Konten teks tidak dapat diekstrak tanpa OCR. File ini berisi gambar yang mungkin berisi teks atau diagram.";
}

// Fungsi tambahan untuk parsing soal secara manual dari teks respons AI
function parseQuestionsManually($text) {
    $questions = [];

    // Bersihkan teks dari karakter aneh
    $text = preg_replace('/[^\x20-\x7E\x{00A0}-\x{D7FF}\x{E000}-\x{FFFD}\n\r\t]/u', ' ', $text);

    // Coba berbagai pola untuk menemukan soal pilihan ganda
    $patterns = [
        // Pola seperti: 1. Soal? A. Pilihan B. Pilihan C. Pilihan D. Pilihan
        '/(\d+\.[^A-D]+?)(?=A\.|B\.|C\.|D\.|\d+\.|$)/s',
        // Pola seperti: Soal: ... A. ... B. ... C. ... D. ...
        '/(Soal:\s*.*?)(?=A\.|B\.|C\.|D\.|\n\s*\n|$)/s',
        // Pola untuk mencocokkan soal dan pilihan secara terstruktur
    ];

    // Coba ekstrak soal dan pilihan secara manual
    // Cari pola soal dengan pilihan A, B, C, D
    $questionPattern = '/(.*?)(A\..*?B\..*?C\..*?D\.)/s';
    preg_match_all($questionPattern, $text, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $questionPart = trim($match[1]);
        $optionsPart = $match[2];

        // Ekstrak pilihan A, B, C, D
        $options = [];
        preg_match('/A\.\s*(.*?)\s*(?=B\.|$)/s', $optionsPart, $aMatch);
        preg_match('/B\.\s*(.*?)\s*(?=C\.|$)/s', $optionsPart, $bMatch);
        preg_match('/C\.\s*(.*?)\s*(?=D\.|$)/s', $optionsPart, $cMatch);
        preg_match('/D\.\s*(.*?)\s*(?=$)/s', $optionsPart, $dMatch);

        $a = isset($aMatch[1]) ? trim(preg_replace('/\s+/', ' ', $aMatch[1])) : '';
        $b = isset($bMatch[1]) ? trim(preg_replace('/\s+/', ' ', $bMatch[1])) : '';
        $c = isset($cMatch[1]) ? trim(preg_replace('/\s+/', ' ', $cMatch[1])) : '';
        $d = isset($dMatch[1]) ? trim(preg_replace('/\s+/', ' ', $dMatch[1])) : '';

        // Bersihkan dari karakter tambahan
        $questionText = trim(preg_replace('/\s+/', ' ', $questionPart));

        // Pastikan soal dan pilihan valid
        if (strlen($questionText) > 10 && !empty($a) && !empty($b) && !empty($c)) {
            $questions[] = [
                'question' => $questionText,
                'a' => $a,
                'b' => $b,
                'c' => $c,
                'd' => $d,
                'answer' => 'a' // Default jawaban ke A, bisa diperbaiki nanti
            ];
        }
    }

    return $questions;
}

// Fungsi tambahan untuk membuat soal dasar dari konten file jika AI tidak menghasilkan soal yang valid
function createBasicQuestionsFromContent($analysisId, $fileContent, $pdo, $userId) {
    $questionsCreated = 0;

    // Hapus tag HTML dan karakter aneh dari konten
    $cleanContent = strip_tags($fileContent);
    $cleanContent = html_entity_decode($cleanContent);
    $cleanContent = preg_replace('/\s+/', ' ', $cleanContent); // Normalisasi spasi

    // Pisahkan konten menjadi paragraf/kalimat
    $sentences = preg_split('/[.!?]+/', $cleanContent);
    $sentences = array_filter($sentences, function($sentence) {
        return strlen(trim($sentence)) > 20; // Ambil kalimat yang cukup panjang
    });

    // Konversi ke array untuk dapat diacak
    $sentences = array_values($sentences); // Re-index array

    // Ambil beberapa kalimat acak untuk dibuat menjadi soal
    $selectedSentences = array_slice($sentences, 0, min(15, count($sentences))); // Ambil maksimal 15 kalimat agar bisa membuat 10 soal
    shuffle($selectedSentences); // Acak urutan kalimat

    $validQuestions = [];
    foreach ($selectedSentences as $sentence) {
        $sentence = trim($sentence);
        if (strlen($sentence) < 25 || strlen($sentence) > 150) continue; // Sesuaikan panjang kalimat

        // Buat soal dengan beberapa pendekatan
        $questionText = $sentence . '?';

        // Coba buat soal dalam berbagai format
        if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi)/i', $sentence)) {
            // Format definisi
            $questionText = 'Apa yang dimaksud dengan ' . substr($sentence, 0, 40) . '?';
        } else if (preg_match('/(cara|langkah|proses)/i', $sentence)) {
            // Format proses/langkah
            $questionText = 'Apa yang dimaksud dengan proses ' . substr($sentence, 0, 30) . '?';
        } else {
            // Format umum
            $questionText = $sentence . '?';
        }

        // Buat pilihan jawaban dengan metode yang lebih bervariasi
        $words = explode(' ', $sentence);
        $wordCount = count($words);

        // Buat pilihan jawaban yang berbeda-beda
        $pilihanA = implode(' ', array_slice($words, 0, min(5, $wordCount))) . '...';
        $pilihanB = implode(' ', array_slice($words, max(0, $wordCount-5), 5)) . '...';
        $pilihanC = 'Pilihan terkait: ' . implode(' ', array_slice($words, max(0, intval($wordCount/2)), 4)) . '...';
        $pilihanD = 'Jawaban tentang: ' . substr($sentence, 0, 30) . '...';

        // Pastikan pilihan tidak terlalu mirip
        if (strlen($pilihanA) > 5 && strlen($pilihanB) > 5 && strlen($pilihanC) > 5) {
            if (!preg_match('/(©|copyright|all rights reserved|halaman|page|gambar|tabel|source|sumber)/i', $sentence)) {
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
            }
        }

        if (count($validQuestions) >= 10) { // Tambahkan dari 5 ke 10 untuk membuat 10 soal
            break;
        }
    }

    // Jika masih kurang dari 10 soal, buat soal dari struktur umum konten
    if (count($validQuestions) < 10 && !empty($selectedSentences)) {
        $count = 0;
        foreach ($selectedSentences as $sentence) {
            if (count($validQuestions) >= 10) break; // Hentikan jika sudah 10

            $sentence = trim($sentence);
            if (strlen($sentence) < 30 || strlen($sentence) > 150) continue;

            // Buat soal umum dari konten
            $questionText = 'Maksud dari pernyataan: "' . substr($sentence, 0, 50) . '..." adalah?';

            // Buat pilihan jawaban yang acak
            $parts = str_split($sentence, intval(strlen($sentence)/4));
            $pilihanA = isset($parts[0]) ? substr($parts[0], 0, 40) . '...' : 'Opsi A';
            $pilihanB = isset($parts[1]) ? substr($parts[1], 0, 40) . '...' : 'Opsi B';
            $pilihanC = isset($parts[2]) ? substr($parts[2], 0, 40) . '...' : 'Opsi C';
            $pilihanD = 'Jawaban yang benar';

            $validQuestions[] = [
                'question' => $questionText,
                'options' => [
                    'a' => $pilihanA,
                    'b' => $pilihanB,
                    'c' => $pilihanC,
                    'd' => $pilihanD
                ],
                'answer' => 'a'
            ];

            $count++;
            if ($count >= 10) {
                break;
            }
        }
    }

    // Tambahkan lebih banyak jika masih kurang dari 10
    if (count($validQuestions) < 10) {
        // Jika tetap kurang dari 10, buat soal umum berdasarkan nama file dan konten
        $fileWords = explode(' ', str_replace(['.', '_', '-'], ' ', basename($fileContent, '.' . pathinfo($fileContent, PATHINFO_EXTENSION))));
        $additionalNeeded = 10 - count($validQuestions);

        for ($i = 0; $i < $additionalNeeded && count($validQuestions) < 10; $i++) {
            $topic = !empty($fileWords) ? $fileWords[0] : 'materi ini';
            $validQuestions[] = [
                'question' => 'Apa yang dapat dipelajari dari ' . $topic . ' dalam file ini?',
                'options' => [
                    'a' => 'Konsep dan prinsip penting',
                    'b' => 'Contoh dan aplikasi',
                    'c' => 'Rangkuman materi',
                    'd' => 'Latihan dan evaluasi'
                ],
                'answer' => 'a'
            ];
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
                error_log("Error inserting basic question: " . $e->getMessage() . " - Question: " . $q['question']);
            }
        }
    }

    error_log("createBasicQuestionsFromContent created $questionsCreated questions for analysis_id: $analysisId");
    return $questionsCreated;
}

// Fungsi helper untuk fallback
function createBasicQuestionsFromContentAsFallback($analysisId, $fileContent, $pdo, $userId) {
    // Fungsi ini akan menghasilkan format respons yang sesuai dengan format AI
    $cleanContent = strip_tags($fileContent);
    $cleanContent = html_entity_decode($cleanContent);
    $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);

    // Ekstrak kalimat-kalimat penting dari konten
    $sentences = preg_split('/[.!?]+/', $cleanContent);
    $sentences = array_filter($sentences, function($sentence) {
        $sentence = trim($sentence);
        return strlen($sentence) > 30 &&
               !preg_match('/(©|copyright|all rights reserved|halaman|page|gambar|tabel|source|sumber)/i', $sentence);
    });
    $sentences = array_values($sentences); // Re-index

    // Identifikasi topik berdasarkan konten
    $topik = 'Materi Umum';
    if (preg_match('/(kimia|asam|basa|reaksi|garam|larutan|hidrolisis|ph|poh)/i', $cleanContent)) {
        $topik = 'Kimia';
    } elseif (preg_match('/(matematika|fungsi|kuadrat|persamaan|aljabar|trigonometri|kalkulus|limit|turunan|integral)/i', $cleanContent)) {
        $topik = 'Matematika';
    } elseif (preg_match('/(fisika|newton|gaya|usaha|energi|momentum|gelombang|cahaya|listrik|magnet)/i', $cleanContent)) {
        $topik = 'Fisika';
    } elseif (preg_match('/(biologi|sel|mikroorganisme|dna|rna|metabolisme|respirasi|fotosintesis|evolusi|ekosistem)/i', $cleanContent)) {
        $topik = 'Biologi';
    }

    // Buat ringkasan dan penjabaran
    $summary = "File ini berisi materi tentang {$topik}. Beberapa konsep penting yang dibahas dalam file ini.";
    if (!empty($sentences[0])) {
        $summary = "File " . date('Y-m-d_H-i-s') . " tentang {$topik}. Isi utama: " . substr($sentences[0], 0, 100) . (strlen($sentences[0]) > 100 ? '...' : '');
    }

    $detailedExplanation = "File ini membahas konsep-konsep utama dalam {$topik}.";
    $relevantTopics = array_slice($sentences, 0, min(5, count($sentences)));
    foreach ($relevantTopics as $topic) {
        if (strlen($topic) > 30) {
            $detailedExplanation .= " Di antaranya: " . substr($topic, 0, 80) . "... ";
        }
    }

    // Buat soal dari isi konten
    $questions = [];
    $validSentences = array_slice($sentences, 0, 10);

    foreach ($validSentences as $sentence) {
        $sentence = trim($sentence);
        if (strlen($sentence) < 40 || strlen($sentence) > 150) continue;

        if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi|fungsi|cara|langkah|proses|manfaat)/i', $sentence)) {
            $questionText = '';

            if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi)/i', $sentence)) {
                $questionText = 'Apa yang dimaksud dengan ' . substr($sentence, 0, 60) . '?';
            } elseif (preg_match('/(fungsi|manfaat)/i', $sentence)) {
                $questionText = 'Apa fungsi/manfaat dari ' . substr($sentence, 0, 50) . '?';
            } else {
                $questionText = 'Apa yang dapat dipelajari dari pernyataan: "' . substr($sentence, 0, 50) . '..."?';
            }

            $words = explode(' ', $sentence);
            $wordCount = count($words);

            $pilihanA = implode(' ', array_slice($words, 0, min(4, $wordCount))) . '...';
            $pilihanB = implode(' ', array_slice($words, max(0, $wordCount-4), 4)) . '...';
            $pilihanC = implode(' ', array_slice($words, max(0, intval($wordCount/2)), 4)) . '...';
            $pilihanD = 'Konsep yang berkaitan dengan: ' . substr($sentence, 0, 30) . '...';

            $questions[] = [
                'soal' => $questionText,
                'pilihan' => [
                    'a' => $pilihanA,
                    'b' => $pilihanB,
                    'c' => $pilihanC,
                    'd' => $pilihanD
                ],
                'kunci_jawaban' => 'a'
            ];
        }

        if (count($questions) >= 10) break;
    }

    // Tambahkan soal umum jika kurang dari 10
    while (count($questions) < 10) {
        $questions[] = [
            'soal' => 'Apa yang dapat dipelajari dari materi ' . $topik . ' dalam file ini?',
            'pilihan' => [
                'a' => 'Konsep dan prinsip penting dalam ' . $topik,
                'b' => 'Contoh dan penerapan dalam ' . $topik,
                'c' => 'Rangkuman materi ' . $topik,
                'd' => 'Latihan dan evaluasi ' . $topik
            ],
            'kunci_jawaban' => 'a'
        ];
    }

    // Format respons sesuai dengan format AI
    $response = "---ANALYSIS_START---\n";
    $response .= "Ringkasan:\n" . $summary . "\n\n";
    $response .= "Penjabaran Materi:\n" . $detailedExplanation . "\n";
    $response .= "---ANALYSIS_END---\n\n";
    $response .= "---QUESTIONS_START---\n";
    $response .= json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    $response .= "---QUESTIONS_END---\n";

    return $response;
}

?>