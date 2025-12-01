<!DOCTYPE html>
<html>
<head>
    <title>Debug Upload</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto p-6">
        <h2 class="text-2xl font-bold mb-6">Debug Upload Form</h2>
        
        <?php
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>";
            echo "<h3>POST Data:</h3>";
            print_r($_POST);
            echo "<h3>FILES Data:</h3>";
            print_r($_FILES);
            echo "</div>";
        }
        ?>
        
        <form action="" method="post" enctype="multipart/form-data" class="space-y-6" onsubmit="console.log('Form submitted');">
            <div>
                <label for="file_upload" class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                <label class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="mb-2 text-sm text-gray-500">
                            <span class="font-semibold">Klik untuk mengunggah</span> atau seret file ke sini
                        </p>
                        <p class="text-xs text-gray-500">
                            PDF, DOCX, JPG, PNG (MAX. 10MB)
                        </p>
                    </div>
                    <input id="file_upload" name="file_upload" type="file" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required onchange="console.log('File selected:', this.files[0])"/>
                </label>
            </div>
            
            <div>
                <label for="file_description" class="block text-sm font-medium text-gray-700">Deskripsi File (Opsional)</label>
                <textarea id="file_description" name="file_description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            
            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="console.log('Submit button clicked')">
                    Upload & Proses dengan AI
                </button>
            </div>
        </form>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Page loaded');
                
                const form = document.querySelector('form');
                form.addEventListener('submit', function(e) {
                    console.log('Form submit event triggered');
                    console.log('Form data will be sent');
                });
            });
        </script>
    </div>
</body>
</html>