<?php
// admin/pages/users.php - Kelola pengguna

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Ambil data pengguna
$users = get_all_users();
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Kelola Pengguna</h2>
            <a href="?page=add_user" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Tambah Pengguna
            </a>
        </div>
        
        <div class="mb-4 flex">
            <input type="text" placeholder="Cari pengguna..." class="flex-1 border border-gray-300 rounded-l-lg py-2 px-4 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <button class="bg-blue-600 text-white px-4 rounded-r-lg hover:bg-blue-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <?php if (!empty($users)): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo escape($user['full_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($user['email']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($user['username']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                <?php echo escape($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="?page=edit_user&id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <a href="?page=delete_user&id=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Anda yakin ingin menghapus pengguna ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-500">Tidak ada pengguna ditemukan.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>