<?php
// includes/generate_exercises.php - Endpoint untuk membuat soal latihan dari materi AI

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/ai_handler.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$response = ['success' => false, 'message' => 'Terjadi kesalahan yang tidak diketahui.', 'content' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Pengguna tidak terautentikasi.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    $fileId = filter_var($_POST['file_id'], FILTER_VALIDATE_INT);

    if (!$fileId) {
        $response['message'] = 'ID file tidak valid.';
        echo json_encode($response);
        exit;
    }

    global $pdo;

    try {
        // 1. Ambil hasil analisis AI dari database
        $stmt = $pdo->prepare("SELECT ringkasan, penjabaran_materi FROM analisis_ai WHERE file_id = ? AND user_id = ?");
        $stmt->execute([$fileId, $_SESSION['user_id']]);
        $analysis = $stmt->fetch();

        if (!$analysis) {
            $response['message'] = 'Analisis AI untuk file ini tidak ditemukan.';
            echo json_encode($response);
            exit;
        }

        $materialContent = $analysis['penjabaran_materi'] ?: $analysis['ringkasan'];

        if (empty($materialContent)) {
            $response['message'] = 'Konten materi untuk pembuatan soal tidak ditemukan.';
            echo json_encode($response);
            exit;
        }

        // 2. Panggil AIHandler untuk membuat soal
        $aiHandler = new AIHandler();
        $exercisesJson = $aiHandler->generateExercises($materialContent);

        // 3. Parse respons JSON dari AI
        $exercises = json_decode($exercisesJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error in generate_exercises.php: " . json_last_error_msg() . " - Response: " . $exercisesJson);
            $response['message'] = 'Gagal mengurai respons soal dari AI. Format tidak valid.';
            echo json_encode($response);
            exit;
        }

        if (empty($exercises) || !is_array($exercises)) {
            $response['message'] = 'AI tidak menghasilkan soal latihan.';
            echo json_encode($response);
            exit;
        }

        // 4. Simpan soal ke database
        // Pertama, hapus soal lama jika ada untuk analisis ini
        $stmt = $pdo->prepare("DELETE FROM latihan_soal WHERE analysis_id = (SELECT id FROM analisis_ai WHERE file_id = ? AND user_id = ?)");
        $stmt->execute([$fileId, $_SESSION['user_id']]);

        $analysisIdStmt = $pdo->prepare("SELECT id FROM analisis_ai WHERE file_id = ? AND user_id = ?");
        $analysisIdStmt->execute([$fileId, $_SESSION['user_id']]);
        $analysisIdResult = $analysisIdStmt->fetch();
        $analysisId = $analysisIdResult['id'];

        $insertStmt = $pdo->prepare("INSERT INTO latihan_soal (analysis_id, user_id, question_text, options_json, correct_answer) VALUES (?, ?, ?, ?, ?)");
        foreach ($exercises as $exercise) {
            if (isset($exercise['question']) && isset($exercise['options']) && is_array($exercise['options']) && isset($exercise['correct_answer'])) {
                $insertStmt->execute([
                    $analysisId,
                    $_SESSION['user_id'],
                    $exercise['question'],
                    json_encode($exercise['options']),
                    $exercise['correct_answer']
                ]);
            }
        }

        // 5. Format soal untuk ditampilkan di frontend
        $htmlContent = '<div class="space-y-6">';
        $questionNumber = 1;
        foreach ($exercises as $exercise) {
            if (isset($exercise['question']) && isset($exercise['options']) && is_array($exercise['options']) && isset($exercise['correct_answer'])) {
                $htmlContent .= '<div class="bg-white p-4 rounded-lg shadow">';
                $htmlContent .= '<p class="font-semibold text-lg mb-2">Soal ' . $questionNumber++ . ': ' . escape($exercise['question']) . '</p>';
                $htmlContent .= '<ul class="list-disc list-inside ml-4 space-y-1">';
                foreach ($exercise['options'] as $option) {
                    $htmlContent .= '<li>' . escape($option) . '</li>';
                }
                $htmlContent .= '</ul>';
                $htmlContent .= '<p class="mt-2 text-green-600 font-medium">Jawaban: ' . escape($exercise['correct_answer']) . '</p>';
                $htmlContent .= '</div>';
            }
        }
        $htmlContent .= '</div>';

        $response['success'] = true;
        $response['message'] = 'Soal latihan berhasil dibuat dan disimpan.';
        $response['content'] = $htmlContent;

    } catch (Exception $e) {
        error_log("Error generating exercises: " . $e->getMessage());
        $response['message'] = 'Terjadi kesalahan saat membuat soal latihan: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Permintaan tidak valid.';
}

echo json_encode($response);
?>