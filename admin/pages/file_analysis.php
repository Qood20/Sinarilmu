<?php
// admin/pages/file_analysis.php - Halaman hasil analisis file untuk admin

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Cek apakah pengguna adalah admin
if (!is_admin()) {
    header('Location: ../?page=login');
    ob_end_clean();
    exit;
}

global $pdo;

// Ambil ID file dari parameter
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($file_id <= 0) {
    $_SESSION['error'] = "ID file tidak valid.";
    header('Location: ?page=files');
    exit;
}

// Ambil data analisis terkait file
$stmt = $pdo->prepare("
    SELECT a.*, u.full_name as user_name, f.original_name as file_name
    FROM analisis_ai a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN upload_files f ON a.file_id = f.id
    WHERE a.file_id = ?
");
$stmt->execute([$file_id]);
$analysis = $stmt->fetch();

if (!$analysis) {
    $_SESSION['error'] = "Tidak ada hasil analisis untuk file ini.";
    header('Location: ?page=file_detail&id=' . $file_id);
    exit;
}

// Ambil soal-soal terkait
$stmt = $pdo->prepare("SELECT * FROM bank_soal_ai WHERE analisis_id = ?");
$stmt->execute([$analysis['id']]);
$questions = $stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Hasil Analisis File</h2>
            <a href="?page=file_detail&id=<?php echo $file_id; ?>" class="text-blue-600 hover:text-blue-900">
                ← Kembali ke detail file
            </a>
        </div>
        
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900"><?php echo escape($analysis['file_name']); ?></h3>
            <p class="text-gray-600">Dianalisis untuk: <?php echo escape($analysis['user_name']); ?></p>
            <p class="text-sm text-gray-500">Tanggal: <?php echo date('d M Y H:i:s', strtotime($analysis['created_at'])); ?></p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Ringkasan</h3>
                <div class="mt-1 p-4 bg-gray-50 rounded-md">
                    <p class="text-gray-900 whitespace-pre-wrap"><?php echo !empty($analysis['ringkasan']) ? escape($analysis['ringkasan']) : 'Tidak ada ringkasan'; ?></p>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500">Tingkat Kesulitan</h3>
                <div class="mt-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        <?php echo $analysis['tingkat_kesulitan'] === 'mudah' ? 'bg-green-100 text-green-800' : 
                           ($analysis['tingkat_kesulitan'] === 'sedang' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                        <?php echo escape($analysis['tingkat_kesulitan']); ?>
                    </span>
                </div>
                
                <h3 class="text-sm font-medium text-gray-500 mt-4">Topik Terkait</h3>
                <div class="mt-1">
                    <?php if (!empty($analysis['topik_terkait'])): ?>
                        <?php $topics = json_decode($analysis['topik_terkait'], true); ?>
                        <?php if (is_array($topics) && !empty($topics)): ?>
                            <?php foreach ($topics as $topic): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-2">
                                    <?php echo escape($topic); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-gray-900">Tidak ada topik terkait</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-gray-900">Tidak ada topik terkait</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900">Penjabaran Materi</h3>
            <div class="mt-2 p-4 bg-gray-50 rounded-md">
                <p class="text-gray-900 whitespace-pre-wrap"><?php echo !empty($analysis['penjabaran_materi']) ? escape($analysis['penjabaran_materi']) : 'Tidak ada penjabaran materi'; ?></p>
            </div>
        </div>
        
        <?php if (!empty($questions)): ?>
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900">Soal Terkait</h3>
            <div class="mt-4 space-y-6">
                <?php foreach ($questions as $index => $question): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <h4 class="font-medium text-gray-900">Soal #<?php echo $index + 1; ?></h4>
                        <span class="text-xs font-medium px-2 py-1 rounded-full
                            <?php echo $question['tingkat_kesulitan'] === 'mudah' ? 'bg-green-100 text-green-800' : 
                               ($question['tingkat_kesulitan'] === 'sedang' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo escape($question['tingkat_kesulitan']); ?>
                        </span>
                    </div>
                    <div class="mt-2">
                        <p class="text-gray-800"><?php echo escape($question['soal']); ?></p>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-4">
                        <div class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-700 mr-2">A</span>
                            <p class="text-gray-700"><?php echo escape($question['pilihan_a']); ?></p>
                        </div>
                        <div class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-700 mr-2">B</span>
                            <p class="text-gray-700"><?php echo escape($question['pilihan_b']); ?></p>
                        </div>
                        <div class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-700 mr-2">C</span>
                            <p class="text-gray-700"><?php echo escape($question['pilihan_c']); ?></p>
                        </div>
                        <div class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-700 mr-2">D</span>
                            <p class="text-gray-700"><?php echo escape($question['pilihan_d']); ?></p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-700">Kunci Jawaban: <span class="font-bold"><?php echo escape($question['kunci_jawaban']); ?></span></p>
                        <?php if (!empty($question['pembahasan'])): ?>
                        <p class="mt-1 text-sm text-gray-600"><span class="font-medium">Pembahasan:</span> <?php echo escape($question['pembahasan']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>