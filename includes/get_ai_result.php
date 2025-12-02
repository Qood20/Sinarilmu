<?php
// includes/get_ai_result.php - Endpoint untuk mengambil dan menampilkan hasil analisis AI

session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/database.php';
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
        $stmt = $pdo->prepare("SELECT ringkasan, penjabaran_materi FROM analisis_ai WHERE file_id = ? AND user_id = ?");
        $stmt->execute([$fileId, $_SESSION['user_id']]);
        $analysis = $stmt->fetch();

        if ($analysis) {
            $htmlContent = '<div class="space-y-6">';
            $htmlContent .= '<h4 class="text-xl font-bold text-blue-700">Ringkasan:</h4>';
            $htmlContent .= '<p class="text-gray-700">' . nl2br(escape($analysis['ringkasan'])) . '</p>';
            $htmlContent .= '<h4 class="text-xl font-bold text-blue-700 mt-4">Penjabaran Materi:</h4>';
            $htmlContent .= '<div class="prose max-w-none">' . nl2br(escape($analysis['penjabaran_materi'])) . '</div>';
            $htmlContent .= '</div>';

            $response['success'] = true;
            $response['message'] = 'Hasil analisis AI berhasil dimuat.';
            $response['content'] = $htmlContent;
        } else {
            $response['message'] = 'Hasil analisis AI untuk file ini tidak ditemukan.';
        }
    } catch (Exception $e) {
        error_log("Error getting AI analysis result: " . $e->getMessage());
        $response['message'] = 'Terjadi kesalahan saat mengambil hasil analisis AI: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Permintaan tidak valid.';
}

echo json_encode($response);
?>