<?php
// dashboard/pages/profile.php - Halaman profil pengguna

require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Ambil data pengguna
$user = get_user_by_id($_SESSION['user_id']);
?>

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Profil Saya</h2>
        
        <form action="process_profile.php" method="post" class="space-y-6">
            <div class="flex items-center justify-center">
                <div class="relative">
                    <img class="h-24 w-24 rounded-full border-4 border-white shadow" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=0D8ABC&color=fff" alt="Avatar">
                    <button type="button" class="absolute bottom-0 right-0 bg-white rounded-full p-1 shadow">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo escape($user['full_name']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="<?php echo escape($user['email']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" value="<?php echo escape($user['username']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- Tombol untuk menuju ke halaman ganti kata sandi -->
            <div>
                <a href="?page=change_password" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Ganti Kata Sandi
                </a>
            </div>
            
            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Perbarui Profil
                </button>
            </div>
        </form>
    </div>
    
    <!-- Statistik Pengguna -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Belajar</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">5</div>
                <div class="text-sm text-gray-600">File Diunggah</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">12</div>
                <div class="text-sm text-gray-600">Soal Dikerjakan</div>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">8.2</div>
                <div class="text-sm text-gray-600">Nilai Rata-rata</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">24</div>
                <div class="text-sm text-gray-600">Jam Belajar</div>
            </div>
        </div>
    </div>
    
    <!-- File yang Diunggah -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">File yang Diunggah</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Matematika_Aljabar.pdf</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">25 Nov 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="#" class="text-blue-600 hover:text-blue-900">Lihat</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Fisika_Gerak_Lurus.docx</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">24 Nov 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="#" class="text-blue-600 hover:text-blue-900">Lihat</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>