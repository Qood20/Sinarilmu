<?php
// includes/generate_exercises.php - Endpoint untuk menghasilkan soal dari materi file

session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit;
}

require_once 'functions.php';
require_once 'ai_handler.php'; // Membutuhkan handler AI untuk membuat soal

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
    
    if ($file_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID file tidak valid.']);
        exit;
    }
    
    try {
        global $pdo;
        
        // Dapatkan data file dan hasil AI
        $stmt = $pdo->prepare("
            SELECT a.ringkasan, a.penjabaran_materi, f.original_name
            FROM analisis_ai a
            JOIN upload_files f ON a.file_id = f.id
            WHERE a.file_id = ? AND f.user_id = ?
        ");
        $stmt->execute([$file_id, $_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Gunakan AI handler yang telah kita buat untuk membuat soal
            $aiHandler = new AIHandler();
            
            // Buat permintaan untuk membuat soal
            $prompt = "Berdasarkan materi berikut: " . $result['ringkasan'] . 
                     "\n\n" . $result['penjabaran_materi'] . 
                     "\n\nBuatkan 5 soal pilihan ganda (multiple choice) dengan 4 pilihan jawaban (A, B, C, D) dan sertakan kunci jawaban serta pembahasan singkat untuk setiap soal.";
            
            $aiResponse = $aiHandler->analyzeText($prompt);
            
            if (!isset($aiResponse['error'])) {
                $soalText = '';

                if (isset($aiResponse['candidates']) && count($aiResponse['candidates']) > 0) {
                    $candidate = $aiResponse['candidates'][0];
                    if (isset($candidate['content']['parts']) && count($candidate['content']['parts']) > 0) {
                        $fullText = '';
                        foreach ($candidate['content']['parts'] as $part) {
                            if (isset($part['text'])) {
                                $fullText .= $part['text'] . ' ';
                            }
                        }
                        $soalText = trim($fullText);
                    } elseif (isset($candidate['output'])) {
                        // Alternatif struktur respons
                        $soalText = $candidate['output'];
                    }
                } else {
                    // Coba struktur respons alternatif
                    if (isset($aiResponse['text'])) {
                        $soalText = $aiResponse['text'];
                    }
                }

                // Simpan soal ke database bank_soal_ai - hubungkan ke file spesifik
                if (!empty($soalText)) {
                    // Ambil informasi file untuk referensi
                    $fileStmt = $pdo->prepare("
                        SELECT uf.original_name, uf.description
                        FROM upload_files uf
                        LEFT JOIN analisis_ai aa ON uf.id = aa.file_id
                        WHERE uf.id = ?
                    ");
                    $fileStmt->execute([$file_id]);
                    $fileInfo = $fileStmt->fetch();

                    $fileName = $fileInfo['original_name'] ?? 'File Tidak Dikenal';

                    // Simpan soal ke database dengan referensi ke file spesifik
                    $stmt = $pdo->prepare("
                        INSERT INTO bank_soal_ai (file_id, user_id, soal, topik_terkait, tingkat_kesulitan, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $file_id, // file_id - ini memastikan soal terkait dengan file yang benar
                        $_SESSION['user_id'],
                        $soalText,
                        $fileName, // topik_terkait - gunakan nama file sebagai referensi topik
                        'sedang' // tingkat_kesulitan default
                    ]);

                    // Logging untuk verifikasi bahwa soal disimpan untuk file yang benar
                    error_log("Soal berhasil dibuat dan disimpan untuk file_id: " . $file_id . " - Nama File: " . $fileName);
                }

                // Format hasil menjadi HTML yang bisa ditampilkan
                $content = '<div class="prose max-w-none">';
                $content .= '<h4 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">';
                $content .= '<svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                $content .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>';
                $content .= '</svg>';
                $content .= 'Soal Latihan dari: ' . escape($result['original_name']);
                $content .= '</h4>';
                $content .= '<div class="bg-purple-50 p-4 rounded-lg border border-purple-200 whitespace-pre-line">';
                $content .= nl2br(escape($soalText));
                $content .= '</div>';
                $content .= '<div class="mt-4 text-sm text-gray-600">Soal ini telah dibuat secara otomatis oleh AI berdasarkan materi yang Anda unggah.</div>';

                // Tambahkan tombol untuk melihat di halaman latihan soal
                $content .= '<div class="mt-4 text-center">';
                $content .= '<a href="?page=exercises" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">';
                $content .= 'Lihat Semua Latihan Soal';
                $content .= '</a>';
                $content .= '</div>';

                $content .= '</div>';

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'content' => $content]);
            } else {
                // Jika AI gagal membuat soal, berikan pesan default
                $content = '<div class="text-center py-4">';
                $content .= '<p class="text-red-600 mb-2">Gagal membuat soal dari materi ini.</p>';
                $content .= '<p class="text-gray-600">Mohon coba lagi nanti atau upload file berbeda yang memiliki konten yang lebih jelas.</p>';
                $content .= '</div>';
                
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'content' => $content, 'message' => 'Gagal membuat soal dari materi ini.']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Data materi tidak ditemukan untuk membuat soal.']);
        }
    } catch (Exception $e) {
        error_log("Error generating exercises: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat membuat soal.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
}