<?php
// admin/pages/reports.php - Laporan sistem untuk admin

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Ambil statistik dari database
global $pdo;

try {
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_files = $pdo->query("SELECT COUNT(*) FROM upload_files")->fetchColumn();
    $total_exercises = $pdo->query("SELECT COUNT(*) FROM bank_soal_ai")->fetchColumn();
    $total_activities = $pdo->query("SELECT COUNT(*) FROM log_aktivitas")->fetchColumn();
    
    // Ambil statistik pengguna berdasarkan bulan
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM users 
        GROUP BY DATE(created_at) 
        ORDER BY DATE(created_at) DESC 
        LIMIT 7
    ");
    $user_registrations = $stmt->fetchAll();
    
    // Ambil aktivitas terbaru
    $stmt = $pdo->prepare("
        SELECT u.full_name, l.aksi, l.created_at
        FROM log_aktivitas l
        LEFT JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
} catch (Exception $e) {
    $total_users = 0;
    $total_files = 0;
    $total_exercises = 0;
    $total_activities = 0;
    $user_registrations = [];
    $recent_activities = [];
}
?>

<div class="max-w-6xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-purple-600"><?php echo $total_activities; ?></div>
            <div class="text-gray-600 mt-2">Aktivitas Tercatat</div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Grafik Pendaftaran -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pendaftaran Pengguna 7 Hari Terakhir</h3>
            <div class="space-y-3">
                <?php if (!empty($user_registrations)): ?>
                    <?php foreach ($user_registrations as $reg): ?>
                        <div class="flex justify-between">
                            <span><?php echo htmlspecialchars($reg['date']); ?></span>
                            <span class="font-medium"><?php echo $reg['count']; ?> pengguna</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Data tidak tersedia</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Aktivitas Terbaru -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
            <div class="space-y-4">
                <?php if (!empty($recent_activities)): ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="flex items-start pb-3 border-b border-gray-100">
                            <div class="bg-gray-200 rounded-full h-8 w-8 flex items-center justify-center text-xs mr-3">
                                <?php echo strtoupper(substr($activity['full_name'] ?? 'Sistem', 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-medium"><?php echo htmlspecialchars($activity['full_name'] ?? 'Sistem'); ?></div>
                                <div class="text-sm text-gray-600"><?php echo htmlspecialchars($activity['aksi'] ?? ''); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php 
                                    $time_diff = time() - strtotime($activity['created_at']);
                                    $minutes = floor($time_diff / 60);
                                    if ($minutes < 1) {
                                        echo "Baru saja";
                                    } else if ($minutes < 60) {
                                        echo $minutes . " menit yang lalu";
                                    } else {
                                        echo date('d M Y H:i', strtotime($activity['created_at']));
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Tidak ada aktivitas terbaru</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>