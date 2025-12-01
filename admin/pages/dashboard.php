<?php
// admin/pages/dashboard.php - Dashboard admin

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Ambil data statistik dari database
global $pdo;

try {
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_files = $pdo->query("SELECT COUNT(*) FROM upload_files")->fetchColumn();
    $total_exercises = $pdo->query("SELECT COUNT(*) FROM bank_soal_ai")->fetchColumn();

    // Ambil aktivitas terbaru
    $stmt = $pdo->prepare("
        SELECT u.full_name, l.aksi, l.created_at
        FROM log_aktivitas l
        LEFT JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 4
    ");
    $stmt->execute();
    $recent_activity = $stmt->fetchAll();
} catch (Exception $e) {
    // Jika terjadi kesalahan, gunakan data dummy
    $total_users = 0;
    $total_files = 0;
    $total_exercises = 0;
    $recent_activity = [
        ['user' => 'Sistem', 'action' => 'Database error', 'time' => 'Baru saja'],
    ];
}
?>

<div class="max-w-6xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-blue-600"><?php echo $total_users; ?></div>
            <div class="text-gray-600 mt-2">Total Pengguna</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-green-600"><?php echo $total_files; ?></div>
            <div class="text-gray-600 mt-2">File Diunggah</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-yellow-600"><?php echo $total_exercises; ?></div>
            <div class="text-gray-600 mt-2">Soal Dibuat</div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Aktivitas Terbaru -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
            <div class="space-y-4">
                <?php foreach ($recent_activity as $activity): ?>
                    <div class="flex items-start pb-3 border-b border-gray-100">
                        <div class="bg-gray-200 rounded-full h-8 w-8 flex items-center justify-center text-xs mr-3">
                            <?php echo strtoupper(substr($activity['full_name'] ?? 'S', 0, 1)); ?>
                        </div>
                        <div>
                            <div class="font-medium"><?php echo escape($activity['full_name'] ?? 'Tidak Dikenal'); ?></div>
                            <div class="text-sm text-gray-600"><?php echo escape($activity['aksi'] ?? 'Tidak Ada Aksi'); ?></div>
                            <div class="text-xs text-gray-500">
                                <?php
                                if (isset($activity['created_at'])) {
                                    $time_diff = time() - strtotime($activity['created_at']);
                                    $minutes = floor($time_diff / 60);
                                    if ($minutes < 1) {
                                        echo "Baru saja";
                                    } else if ($minutes < 60) {
                                        echo $minutes . " menit yang lalu";
                                    } else {
                                        echo date('d M Y H:i', strtotime($activity['created_at']));
                                    }
                                } else {
                                    echo escape($activity['time'] ?? '');
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Pengguna Baru -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengguna Baru (7 hari terakhir)</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-gray-200 rounded-full h-10 w-10 flex items-center justify-center text-sm mr-3">
                            AB
                        </div>
                        <div>
                            <div class="font-medium">Agus Budiman</div>
                            <div class="text-sm text-gray-600">agus@example.com</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">2 jam yang lalu</div>
                </div>
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-gray-200 rounded-full h-10 w-10 flex items-center justify-center text-sm mr-3">
                            CW
                        </div>
                        <div>
                            <div class="font-medium">Citra Wulandari</div>
                            <div class="text-sm text-gray-600">citra@example.com</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">5 jam yang lalu</div>
                </div>
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-gray-200 rounded-full h-10 w-10 flex items-center justify-center text-sm mr-3">
                            DF
                        </div>
                        <div>
                            <div class="font-medium">Dian Fitriani</div>
                            <div class="text-sm text-gray-600">dian@example.com</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">1 hari yang lalu</div>
                </div>
            </div>
        </div>
    </div>
</div>