<?php
// admin/pages/exercise_detail.php - Halaman detail hasil latihan untuk admin

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil ID hasil latihan dari parameter
$result_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($result_id <= 0) {
    $_SESSION['error'] = "ID hasil latihan tidak valid.";
    header('Location: ?page=exercises');
    exit;
}

// Ambil data hasil latihan
$stmt = $pdo->prepare("
    SELECT hs.*, bs.soal, bs.pilihan_a, bs.pilihan_b, bs.pilihan_c, bs.pilihan_d, bs.kunci_jawaban, bs.pembahasan, u.full_name as user_name
    FROM hasil_soal_user hs
    JOIN bank_soal_ai bs ON hs.soal_id = bs.id
    JOIN users u ON hs.user_id = u.id
    WHERE hs.id = ?
");
$stmt->execute([$result_id]);
$result = $stmt->fetch();

if (!$result) {
    $_SESSION['error'] = "Hasil latihan tidak ditemukan.";
    header('Location: ?page=exercises');
    exit;
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Detail Hasil Latihan</h2>
            <a href="?page=exercises" class="text-blue-600 hover:text-blue-900">
                ← Kembali ke daftar hasil latihan
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Pengguna</h3>
                <p class="mt-1 text-gray-900"><?php echo escape($result['user_name']); ?></p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500">Nilai</h3>
                <p class="mt-1 text-gray-900 text-lg font-semibold">
                    <span class="<?php echo $result['nilai'] >= 75 ? 'text-green-600' : ($result['nilai'] >= 50 ? 'text-yellow-600' : 'text-red-600'); ?>">
                        <?php echo $result['nilai']; ?>
                    </span>
                </p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500">Status Jawaban</h3>
                <p class="mt-1 text-gray-900">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        <?php echo $result['status_jawaban'] === 'benar' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo escape($result['status_jawaban']); ?>
                    </span>
                </p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500">Tanggal Pengerjaan</h3>
                <p class="mt-1 text-gray-900"><?php echo date('d M Y H:i:s', strtotime($result['created_at'])); ?></p>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900">Soal</h3>
            <div class="mt-4 p-4 bg-gray-50 rounded-md">
                <p class="text-gray-800"><?php echo escape($result['soal']); ?></p>
            </div>
            
            <h3 class="text-lg font-medium text-gray-900 mt-6">Pilihan Jawaban</h3>
            <div class="mt-4 space-y-2">
                <div class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-700 mr-2">A</span>
                    <p class="text-gray-700"><?php echo escape($result['pilihan_a']); ?></p>
                </div>
                <div class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-700 mr-2">B</span>
                    <p class="text-gray-700"><?php echo escape($result['pilihan_b']); ?></p>
                </div>
                <div class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-700 mr-2">C</span>
                    <p class="text-gray-700"><?php echo escape($result['pilihan_c']); ?></p>
                </div>
                <div class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-700 mr-2">D</span>
                    <p class="text-gray-700"><?php echo escape($result['pilihan_d']); ?></p>
                </div>
            </div>
            
            <h3 class="text-lg font-medium text-gray-900 mt-6">Jawaban Pengguna</h3>
            <div class="mt-2 p-4 bg-blue-50 rounded-md">
                <p class="text-gray-800">Jawaban: <span class="font-semibold"><?php echo escape($result['jawaban_user']); ?></span></p>
            </div>
            
            <h3 class="text-lg font-medium text-gray-900 mt-6">Kunci Jawaban</h3>
            <div class="mt-2 p-4 bg-green-50 rounded-md">
                <p class="text-gray-800">Kunci Jawaban: <span class="font-semibold"><?php echo escape($result['kunci_jawaban']); ?></span></p>
            </div>
            
            <h3 class="text-lg font-medium text-gray-900 mt-6">Pembahasan</h3>
            <div class="mt-2 p-4 bg-yellow-50 rounded-md">
                <p class="text-gray-800"><?php echo !empty($result['pembahasan']) ? escape($result['pembahasan']) : 'Tidak ada pembahasan'; ?></p>
            </div>
        </div>
        
        <div class="mt-8 flex space-x-4">
            <a href="?page=exercises" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Kembali
            </a>
            <a href="?page=delete_exercise_result&id=<?php echo $result['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Anda yakin ingin menghapus hasil latihan ini?')">
                Hapus Hasil
            </a>
        </div>
    </div>
</div>