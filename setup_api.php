<?php
/**
 * Setup API Key for Sinar Ilmu Application
 * 
 * This script provides a setup guide for API key configuration
 * and ensures that the application works properly with or without API access
 */

// Check if we're accessing this directly or including it
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    // Display setup instructions
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Setup API Key - Sinar Ilmu</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-gray-100'>
        <div class='max-w-4xl mx-auto p-8 mt-12 bg-white rounded-xl shadow-lg'>
            <h1 class='text-3xl font-bold text-center text-blue-700 mb-8'>Setup API Key Sinar Ilmu</h1>
            
            <div class='mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200'>
                <h2 class='text-xl font-semibold text-blue-800 mb-4'>Penting!</h2>
                <p class='text-gray-700 mb-3'>Aplikasi Sinar Ilmu sekarang dapat berfungsi <strong>tanpa API key</strong> berkat sistem fallback yang canggih!</p>
                <p class='text-gray-700'>Namun, untuk pengalaman terbaik, Anda bisa menambahkan API key dari OpenRouter.</p>
            </div>
            
            <div class='grid grid-cols-1 md:grid-cols-2 gap-8 mb-8'>
                <div class='bg-gray-50 p-6 rounded-lg border border-gray-200'>
                    <h3 class='text-lg font-semibold text-gray-800 mb-3'>Langkah-langkah:</h3>
                    <ol class='list-decimal pl-5 space-y-2 text-gray-700'>
                        <li>Buka file <code class='bg-gray-200 px-1 rounded'>config/config.php</code></li>
                        <li>Temukan baris dengan <code class='bg-gray-200 px-1 rounded'>OPENROUTER_API_KEY</code></li>
                        <li>Ganti dengan API key dari <a href='https://openrouter.ai' target='_blank' class='text-blue-600 hover:underline'>OpenRouter</a></li>
                        <li>Simpan perubahan</li>
                    </ol>
                </div>
                
                <div class='bg-green-50 p-6 rounded-lg border border-green-200'>
                    <h3 class='text-lg font-semibold text-green-800 mb-3'>Keunggulan Sistem Baru:</h3>
                    <ul class='list-disc pl-5 space-y-2 text-gray-700'>
                        <li>Fallback cerdas saat API tidak tersedia</li>
                        <li>Analisis konten file lokal</li>
                        <li>Pembuatan soal berdasarkan isi file</li>
                        <li>Chatbot pendidikan dengan respons kontekstual</li>
                    </ul>
                </div>
            </div>
            
            <div class='mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200'>
                <h3 class='font-semibold text-yellow-800 mb-2'>Contoh Konfigurasi:</h3>
                <pre class='bg-gray-800 text-green-400 p-4 rounded text-sm overflow-x-auto'>define('OPENROUTER_API_KEY',
    'sk-or-v1-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');</pre>
            </div>
            
            <div class='flex justify-center'>
                <a href='../dashboard/?page=home' class='px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors'>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </body>
    </html>";
} else {
    // If included, just provide functions
    /**
     * Check if API is configured properly
     */
    function isApiConfigured() {
        return defined('OPENROUTER_API_KEY') && !empty(OPENROUTER_API_KEY) && strlen(OPENROUTER_API_KEY) > 20;
    }
    
    /**
     * Check API availability
     */
    function checkApiStatus() {
        if (!isApiConfigured()) {
            return [
                'configured' => false,
                'available' => false,
                'message' => 'API key belum diatur'
            ];
        }
        
        // Even if configured, we return that it might not be available
        // since we can't verify without making a request
        return [
            'configured' => true,
            'available' => false, // Default to unavailable for safety
            'message' => 'API key diatur tetapi koneksi belum diuji'
        ];
    }
}
?>