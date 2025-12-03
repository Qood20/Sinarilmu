<?php
// dashboard/pages/chat.php - Halaman chat AI

require_once dirname(__DIR__, 2) . '/includes/functions.php';

global $pdo;

// Ambil riwayat chat pengguna
try {
    $stmt = $pdo->prepare("
        SELECT id, pesan_pengguna, created_at
        FROM chat_ai
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $chat_history = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error getting chat history: " . $e->getMessage());
    $chat_history = [];
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Tanya Sinar</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo escape($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo escape($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">
                Chat dengan AI untuk bertanya tentang materi pelajaran atau file yang telah kamu unggah sebelumnya.
            </p>
            <form action="process_chat.php" method="post" style="display:inline;" onsubmit="return confirm('Anda yakin ingin menghapus semua riwayat chat?')">
                <input type="hidden" name="action" value="delete_chat">
                <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200">Hapus Chat</button>
            </form>
        </div>
        
        <!-- Area Chat -->
        <div class="border border-gray-200 rounded-lg h-96 flex flex-col">
            <div class="border-b border-gray-200 p-4 flex-1 overflow-y-auto">
                <div class="space-y-4">
                    <div class="flex justify-start">
                        <div class="bg-gray-200 rounded-lg p-3 max-w-xs md:max-w-md">
                            <p class="text-gray-800">Halo! Saya Sinar, asisten belajarmu. Ada yang bisa saya bantu?</p>
                        </div>
                    </div>

                    <!-- Tampilkan seluruh riwayat percakapan -->
                    <?php
                    // Ambil semua percakapan terbaru
                    try {
                        $stmt = $pdo->prepare("
                            SELECT id, pesan_pengguna, pesan_ai, created_at
                            FROM chat_ai
                            WHERE user_id = ?
                            ORDER BY created_at DESC
                            LIMIT 10
                        ");
                        $stmt->execute([$_SESSION['user_id']]);
                        $all_chats = $stmt->fetchAll();

                        // Tampilkan pesan dalam urutan terbalik (terlama dulu, terbaru terakhir)
                        foreach (array_reverse($all_chats) as $chat) {
                            ?>
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-blue-500 text-white rounded-lg p-3 max-w-xs md:max-w-md">
                                    <p><?php echo escape($chat['pesan_pengguna']); ?></p>
                                </div>
                                <form action="process_chat.php" method="post" style="display:inline;" class="ml-2">
                                    <input type="hidden" name="action" value="delete_single_chat">
                                    <input type="hidden" name="chat_id" value="<?php echo $chat['id']; ?>">
                                    <button type="submit" class="text-gray-500 hover:text-red-500" onclick="return confirm('Anda yakin ingin menghapus percakapan ini?')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <div class="flex justify-start mb-4">
                                <div class="bg-gray-200 rounded-lg p-3 max-w-xs md:max-w-md">
                                    <?php if (!empty($chat['pesan_ai'])): ?>
                                        <p class="text-gray-800"><?php echo escape($chat['pesan_ai']); ?></p>
                                    <?php else: ?>
                                        <p class="text-gray-500 italic">[Balasan AI sedang tidak tersedia]</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    } catch (Exception $e) {
                        error_log("Error getting chat messages: " . $e->getMessage());
                        echo "<p class='text-red-500'>Terjadi kesalahan saat memuat percakapan.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Input Chat -->
            <div class="border-t border-gray-200 p-4">
                <form action="process_chat.php" method="post" class="flex" id="chatForm">
                    <input type="hidden" name="action" value="send">
                    <input type="text" name="pesan" id="pesanInput" placeholder="Ketik pertanyaanmu di sini..." class="flex-1 border border-gray-300 rounded-l-lg py-2 px-4 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" required>
                    <button type="submit" class="bg-blue-600 text-white px-4 rounded-r-lg hover:bg-blue-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </form>
                <div class="mt-2 flex justify-end">
                    <button type="button" onclick="clearChatInput()" class="text-sm text-gray-600 hover:text-gray-900">
                        Batalkan
                    </button>
                </div>
            </div>

            <script>
                function clearChatInput() {
                    document.getElementById('pesanInput').value = '';
                    document.getElementById('pesanInput').focus();
                }

                document.getElementById('pesanInput').addEventListener('keydown', function(event) {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        document.getElementById('chatForm').submit();
                    }
                });
            </script>
        </div>
    </div>
    
    <!-- Riwayat Chat -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Chat</h3>
        <div class="space-y-3">
            <?php if (!empty($chat_history)): ?>
                <?php foreach ($chat_history as $chat): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-medium text-blue-600"><?php echo substr(escape($chat['pesan_pengguna']), 0, 30); ?>...</div>
                            <div class="text-xs text-gray-500 mt-1"><?php echo escape($chat['topik_terkait'] ?? 'Umum'); ?></div>
                        </div>
                        <div class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($chat['created_at'])); ?></div>
                    </div>
                    <div class="text-sm text-gray-600 truncate mt-2"><?php echo escape($chat['pesan_pengguna']); ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-gray-500">
                    Belum ada riwayat chat
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>