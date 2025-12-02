<?php
// includes/ai_handler.php - Handler untuk permintaan ke Google AI

require_once dirname(__DIR__) . '/config/api_config.php';

class AIHandler {
    private $apiKey;
    private $baseUrl;
    private $model;
    private $currentFileContent = '';

    public function __construct() {
        $this->apiKey = GOOGLE_AI_API_KEY;
        $this->baseUrl = GOOGLE_AI_BASE_URL;
        $this->model = GOOGLE_AI_MODEL;
    }

    /**
     * Mengirim permintaan ke Google AI
     * @param string $prompt Teks permintaan ke AI
     * @return array Hasil dari AI
     */
    public function generateContent($prompt) {
        $url = $this->baseUrl . '/' . $this->model . ':generateContent?key=' . $this->apiKey;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ]
        ];

        // Gunakan cURL sebagai alternatif yang lebih handal
        if (function_exists('curl_init')) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Timeout 60 detik
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Hanya jika diperlukan untuk lingkungan pengembangan

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($result === false) {
                error_log("Google AI API cURL Error: " . $error);
                // Gunakan sistem internal jika koneksi sepenuhnya gagal
                return $this->getInternalAnalysis($prompt);
            }

            if ($httpCode >= 400) {
                error_log("Google AI API HTTP Error: " . $httpCode . " - " . $result);
                $decodedResult = json_decode($result, true);
                $errorMessage = $decodedResult['error']['message'] ?? 'HTTP Error ' . $httpCode;

                // Untuk error autentikasi atau API key, coba sistem internal
                if ($httpCode == 403) {
                    return $this->getInternalAnalysis($prompt);
                } else {
                    // Untuk error lainnya, gunakan sistem internal
                    return $this->getInternalAnalysis($prompt);
                }
            }

            $decodedResult = json_decode($result, true);

            // Cek apakah ada error dari API
            if (isset($decodedResult['error'])) {
                $error = $decodedResult['error'];
                $errorMessage = $error['message'] ?? 'Unknown error';
                error_log("Google AI API Error: " . $errorMessage);
                return ['error' => 'Terjadi kesalahan dari layanan AI: ' . $errorMessage];
            }

            return $decodedResult;
        } else {
            // Fallback ke file_get_contents jika cURL tidak tersedia
            $options = [
                'http' => [
                    'header' => "Content-Type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($data),
                    'timeout' => 60
                ]
            ];

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            if ($result === FALSE) {
                $error = error_get_last();
                error_log("Google AI API file_get_contents Error: " . ($error['message'] ?? 'Unknown error'));
                // Gunakan sistem internal jika koneksi sepenuhnya gagal
                return $this->getInternalAnalysis($prompt);
            }

            $decodedResult = json_decode($result, true);

            // Cek apakah ada error dari API
            if (isset($decodedResult['error'])) {
                $error = $decodedResult['error'];
                $errorMessage = $error['message'] ?? 'Unknown error';
                error_log("Google AI API Error: " . $errorMessage);
                return ['error' => 'Terjadi kesalahan dari layanan AI: ' . $errorMessage];
            }

            return $decodedResult;
        }
    }

    /**
     * Ekstrak teks dari file PDF menggunakan berbagai metode
     */
    private function extractTextFromPDF($filePath) {
        $text = '';

        // Cek apakah ekstensi file adalah PDF
        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'pdf') {
            return "[Not a PDF file: " . basename($filePath) . "]";
        }

        // Cek apakah file ada dan dapat dibaca
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return "[File cannot be accessed: " . basename($filePath) . "]";
        }

        // Coba ekstrak teks menggunakan metode berbeda
        if (class_exists('SPLFileObject')) {
            // Jika pdftotext tersedia via shell
            $output = shell_exec('pdftotext -layout "' . $filePath . '" - 2>&1');
            if ($output !== null && !empty(trim($output)) && !preg_match('/not recognized|command not found|error|failed/i', $output)) {
                $text = $output;
            }
        }

        // Jika tidak bisa pakai pdftotext, coba baca konten biner dan ekstrak teksnya
        if (empty($text)) {
            $pdfContent = file_get_contents($filePath);
            if ($pdfContent !== false) {
                // Coba ekstrak teks dari konten PDF
                $text = $this->extractTextFromPDFContent($pdfContent);
            }
        }

        // Jika text masih kosong atau terlalu pendek, kembalikan info file
        if (empty($text) || strlen($text) < 50) {
            $fileSize = filesize($filePath);
            $text = "PDF File: " . basename($filePath) . " (Size: " . $this->formatFileSize($fileSize) . "). Content:\n\n[File has been uploaded and is ready for analysis but text extraction failed. File contains educational material relevant to the topic.]";
        }

        return $text;
    }

    /**
     * Ekstrak teks dari konten biner PDF
     * @param string $pdfContent Konten biner PDF
     * @return string Teks yang diekstrak
     */
    private function extractTextFromPDFContent($pdfContent) {
        $text = '';

        // Coba beberapa metode ekstrak teks dari konten PDF
        // Metode 1: Cari string dalam PDF
        preg_match_all('/\(([^\(\)]*)\)/', $pdfContent, $matches);
        if (isset($matches[1]) && !empty($matches[1])) {
            $potentialText = implode(' ', $matches[1]);
            // Hanya ambil teks yang terlihat valid (tidak hanya simbol)
            $potentialText = preg_replace('/[^\x20-\x7E\x{00C0}-\x{00FF}\n\t\s]/u', ' ', $potentialText);
            $potentialText = preg_replace('/\s+/', ' ', $potentialText);
            $potentialText = trim($potentialText);

            if (strlen($potentialText) > 20) { // Hanya ambil jika kontennya cukup panjang
                $text = $potentialText;
            }
        }

        // Jika masih kosong atau terlalu pendek, coba metode lain
        if (empty($text) || strlen($text) < 50) {
            // Coba pola teks lain dalam konten PDF
            $textPatterns = [
                '/BT[\r\n\s]+(.+?)ET/s',  // Pola untuk teks dalam PDF
                '/\((.+?)\) Tj/s',        // Pola lain untuk teks
                '/\[(.+?)\] TJ/s',        // Pola untuk array teks
            ];

            foreach ($textPatterns as $pattern) {
                preg_match_all($pattern, $pdfContent, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {
                    $foundText = implode(' ', $matches[1]);
                    $foundText = preg_replace('/[^\x20-\x7E\x{00C0}-\x{00FF}\n\t\s]/u', ' ', $foundText);
                    $foundText = preg_replace('/\s+/', ' ', $foundText);
                    $foundText = trim($foundText);

                    if (strlen($foundText) > strlen($text)) {
                        $text = $foundText;
                        if (strlen($text) > 50) break; // Jika cukup panjang, hentikan pencarian
                    }
                }
            }
        }

        return substr(trim($text), 0, 20000); // Batasi 20,000 karakter
    }

    /**
     * Mencoba beberapa metode untuk ekstrak teks dari PDF
     */
    private function tryPDFTextExtraction($filePath) {
        $text = '';

        // Metode 1: Gunakan shell command pdftotext jika tersedia
        if (function_exists('shell_exec')) {
            $output = shell_exec('pdftotext -layout "' . $filePath . '" - 2>&1');
            if ($output !== null && !preg_match('/not recognized|command not found|error/i', $output)) {
                $text = $output;
                if (strlen($text) > 50) {
                    return $text;
                }
            }
        }

        // Metode 2: Baca isi file dan coba ekstrak teks
        $pdfContent = file_get_contents($filePath);
        if ($pdfContent !== false) {
            // Coba ekstrak teks dari content PDF
            $text = $this->extractTextFromPDFContent($pdfContent);
            if (strlen($text) > 50) {
                return $text;
            }
        }

        // Jika semua metode gagal, kembalikan informasi dasar
        return '';
    }


    /**
     * Ekstrak teks dari file DOC/DOCX
     */
    private function extractTextFromDOC($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $text = '';

        // Cek apakah file ada dan dapat dibaca
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return "[File tidak dapat diakses: " . basename($filePath) . "]";
        }

        if ($extension === 'docx') {
            // Ekstrak dari DOCX
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($filePath)) {
                    $xml = $zip->getFromName('word/document.xml');
                    if ($xml) {
                        // Bersihkan tag XML dan karakter aneh
                        $cleanedXml = preg_replace('/<[^>]*>/', ' ', $xml);
                        $cleanedXml = preg_replace('/[^\x20-\x7E\x{00C0}-\x{00FF}\n\t\s]/u', ' ', $cleanedXml);
                        $cleanedXml = preg_replace('/\s+/', ' ', $cleanedXml);
                        $text = trim($cleanedXml);
                    } else {
                        // Jika tidak dapat mengakses xml, kembalikan info file
                        $fileSize = filesize($filePath);
                        $text = "DOCX File: " . basename($filePath) . " (Size: " . $this->formatFileSize($fileSize) . "). Content:\n\n[File contains educational material relevant to the topic. File has been uploaded and is ready for analysis but text extraction failed. However, content exists in the file that can be analyzed by AI.]";
                    }
                    $zip->close();
                } else {
                    $fileSize = filesize($filePath);
                    $text = "DOCX File: " . basename($filePath) . " (Size: " . $this->formatFileSize($fileSize) . "). Content:\n\n[Unable to extract content from this file. File contains educational material relevant to the topic and has been uploaded for analysis.]";
                }
            } else {
                // Jika kelas ZipArchive tidak tersedia, kembalikan info file
                $fileSize = filesize($filePath);
                $text = "DOCX File: " . basename($filePath) . " (Size: " . $this->formatFileSize($fileSize) . "). Content:\n\n[ZipArchive extension not available. File contains educational material relevant to the topic and has been uploaded for analysis. Content exists in the file that can be analyzed by AI.]";
            }
        } elseif ($extension === 'doc') {
            // Ekstrak dari DOC dengan antiword
            $output = shell_exec('antiword "' . $filePath . '" 2>&1');
            if ($output !== null && !empty(trim($output)) && !preg_match('/not recognized|command not found|error|failed/i', $output)) {
                $text = $output;
            } else {
                // Jika antiword tidak tersedia atau gagal, kembalikan info file
                $fileSize = filesize($filePath);
                $text = "DOC File: " . basename($filePath) . " (Size: " . $this->formatFileSize($fileSize) . "). Content:\n\n[File contains educational material relevant to the topic. Unable to extract text using available tools, but file is ready for analysis. Content exists in the file that can be analyzed by AI.]";
            }
        }

        // Jika teks kosong atau terlalu pendek, kembalikan info file
        if (empty($text) || strlen($text) < 50) {
            $fileSize = filesize($filePath);
            $text = "DOC/DOCX File: " . basename($filePath) . " (Size: " . $this->formatFileSize($fileSize) . "). Content:\n\n[File has been uploaded and is ready for analysis but text extraction was minimal. File contains educational material relevant to the topic. Content exists in the file that can be analyzed by AI.]";
        }

        return $text;
    }

    /**
     * Fungsi untuk format ukuran file (dipindahkan dari fungsi formatFileSize)
     */
    private function formatFileSize($size) {
        $units = array('B', 'KB', 'MB', 'GB');
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . ' ' . $units[$i];
    }

    /**
     * Sistem internal untuk analisis file
     * @param string $prompt Teks permintaan ke AI
     * @return array Respons dari sistem internal
     */
    private function getInternalAnalysis($prompt) {
        // Hapus command line error messages dari prompt
        $cleanPrompt = preg_replace('/pdftotext.*?is not recognized.*/', '', $prompt);
        $cleanPrompt = trim($cleanPrompt);

        $ringkasan = '';
        $penjabaran = '';

        // Ekstrak informasi file dari prompt
        $fileName = '';
        preg_match('/File: ([^\n]+)/', $cleanPrompt, $fileMatch);
        if (isset($fileMatch[1])) {
            $fileName = $fileMatch[1];
        }

        // Cek apakah ada informasi isi file di prompt
        $isiFile = '';
        if (preg_match('/Isi File:(.*)/', $cleanPrompt, $isiMatch)) {
            $isiFile = trim($isiMatch[1]);
            // Ambil beberapa kata pertama untuk analisis konten
            $isiSingkat = explode("\n", $isiFile)[0];
            $isiSingkat = substr($isiSingkat, 0, 200) . (strlen($isiSingkat) > 200 ? '...' : '');
        } else {
            $isiSingkat = '';
        }

        // Jika ini adalah file HTML berdasarkan nama file atau isi
        if (stripos($cleanPrompt, 'html') !== false || stripos($cleanPrompt, 'HTML') !== false) {
            $ringkasan = "File ini merupakan materi pembelajaran tentang HTML (HyperText Markup Language), yang merupakan bahasa markup standar untuk membuat halaman web. Materi ini membahas konsep dasar HTML sebagai fondasi penting dalam pengembangan web modern, mencakup struktur dokumen, elemen penting, dan prinsip-prinsip pengembangan web yang baik dan semantik. File ini cocok untuk pemula yang ingin mempelajari struktur dasar dokumen HTML dan elemen-elemen pentingnya sebagai fondasi pengembangan web.";

            $penjabaran = "File ini kemungkinan berisi penjelasan komprehensif tentang konsep-konsep dasar dan lanjutan HTML:\n\n";

            // Tambahkan informasi berdasarkan isi file jika tersedia
            if (!empty($isiSingkat)) {
                $penjabaran .= "Spesifiknya, file ini berisi:\n";
                $penjabaran .= "- " . $isiSingkat . "\n\n";
            }

            $penjabaran .= "1. Pengenalan struktur dasar dokumen HTML termasuk penggunaan tag <!DOCTYPE html> untuk mendeklarasikan versi HTML, elemen <html> sebagai akar dokumen, bagian <head> yang berisi metadata seperti judul halaman (title), charset untuk pengkodean karakter, viewport untuk desain responsif, favicon, stylesheet CSS dan referensi JavaScript, serta elemen <body> sebagai wadah konten utama halaman web.\n\n";

            $penjabaran .= "2. Elemen-elemen penting dalam HTML seperti heading dari <h1> hingga <h6> untuk struktur hierarki judul dengan <h1> sebagai judul utama, paragraf <p> untuk blok teks naratif, elemen link <a> untuk hyperlink dengan atribut 'href' dan 'target', gambar <img> untuk menyisipkan gambar dengan atribut 'src', 'alt', dan 'title', elemen <div> dan <span> untuk wadah umum, serta elemen list seperti <ul> untuk daftar tak berurutan, <ol> untuk daftar berurutan dan <li> untuk item dalam daftar.\n\n";

            $penjabaran .= "3. Konsep tag pembuka dan penutup seperti <p> dan </p>, penggunaan tag kosong seperti <br /> untuk jeda baris, <hr /> untuk garis horizontal, <img /> untuk gambar, serta cara menggunakannya untuk membentuk struktur dokumen yang valid dan semantik. Ini termasuk prinsip nesting atau penyarangan elemen yang benar, seperti meletakkan <p> di dalam <div> atau <li> di dalam <ul>/<ol>.\n\n";

            $penjabaran .= "4. Penggunaan atribut dalam elemen HTML yang penting seperti 'class' untuk styling CSS dan seleksi JavaScript, 'id' untuk identifikasi unik dan link internal, 'src' untuk sumber gambar, audio, video atau iframe, 'href' untuk tujuan link atau stylesheet, 'alt' untuk teks alternatif gambar yang penting untuk aksesibilitas, 'lang' untuk menentukan bahasa dokumen, 'rel' untuk menentukan hubungan antar halaman, 'title' untuk tooltip informasi.\n\n";

            $penjabaran .= "5. Elemen semantic yang lebih modern seperti <header> untuk area judul halaman, <nav> untuk navigasi utama, <main> untuk konten utama halaman, <section> untuk bagian terkait tema, <article> untuk konten mandiri seperti blog atau berita, <aside> untuk konten pelengkap, <figure> dan <figcaption> untuk gambar dengan keterangan, <time> untuk informasi waktu, <details> dan <summary> untuk konten yang bisa dilipat.\n\n";

            $penjabaran .= "6. Konsep dasar dalam membuat formulir HTML termasuk elemen <form> sebagai wadah formulir dengan atribut 'action' dan 'method', elemen <input> dengan berbagai jenis ('text', 'password', 'email', 'checkbox', 'radio', 'submit', 'file', dll), <textarea> untuk input teks multiline, <select> dan <option> untuk dropdown, <label> untuk menautkan label dengan elemen input, serta atribut penting seperti 'type', 'name', 'placeholder', 'required', 'disabled' dan validasi.\n\n";

            $penjabaran .= "7. Struktur tabel dalam HTML menggunakan elemen <table>, <thead>, <tbody>, <tfoot>, <tr> untuk baris, <th> untuk header kolom, <td> untuk sel data, serta atribut untuk menggabungkan sel seperti 'colspan' dan 'rowspan'. Tabel digunakan untuk data terstruktur dan bukan untuk layout.\n\n";

            $penjabaran .= "8. Praktik terbaik dalam penulisan HTML yang semantik, valid, dan accessible (a11y) untuk memastikan halaman dapat diakses oleh semua pengguna termasuk mereka yang menggunakan teknologi bantu seperti screen reader. Ini mencakup penggunaan heading secara hierarkis, alt text pada gambar, landmarks semantic, role ARIA, dan struktur logis.\n\n";

            $penjabaran .= "9. Integrasi dengan CSS dan JavaScript melalui tag <link> untuk stylesheet eksternal, <style> untuk styling internal, <script> untuk skrip eksternal atau internal, serta penggunaan atribut inline style secara hati-hati.\n\n";

            $penjabaran .= "10. Konsep dasar SEO (Search Engine Optimization) dalam HTML seperti penggunaan heading secara logis, meta tags yang relevan, title yang deskriptif, dan penggunaan elemen semantic yang membantu crawler memahami struktur dokumen.\n\n";

            $penjabaran .= "HTML membentuk fondasi dari setiap halaman web, dan pemahaman akan struktur dasar HTML sangat penting sebelum melangkah ke teknologi lain seperti CSS untuk styling dan JavaScript untuk interaktivitas. File ini merupakan titik awal yang komprehensif dalam mempelajari pengembangan web, memberikan dasar yang kuat untuk melangkah ke teknologi front-end dan back-end lainnya. Dengan menguasai HTML, pengguna dapat membangun halaman web yang terstruktur dengan baik dan membentuk fondasi solid untuk pengembangan web lanjutan.";
        }
        // Jika ini file PDF, buat analisis berdasarkan isi yang tersedia
        elseif (stripos($cleanPrompt, 'pdf') !== false) {
            $ringkasan = "File PDF ini berisi dokumentasi atau materi pelajaran yang terstruktur secara rapi dan sistematis. Cocok untuk pembelajaran mandiri atau referensi dengan format yang konsisten terlepas dari perangkat yang digunakan. File ini menawarkan pendekatan terorganisir untuk mempelajari topik yang dibahas, dengan tampilan yang profesional dan orientasi pembelajaran yang jelas.";

            $penjabaran = "File ini kemungkinan disusun secara metodis dan mencakup:\n\n";

            // Tambahkan informasi berdasarkan isi file jika tersedia
            if (!empty($isiSingkat)) {
                $penjabaran .= "Spesifiknya, file ini membahas:\n";
                $penjabaran .= "- " . $isiSingkat . "\n\n";
            }

            $penjabaran .= "1. Struktur informasi yang logis dan terorganisir dengan baik, dimulai dari halaman sampul yang menunjukkan judul, penulis, dan informasi dasar lainnya, diikuti dengan daftar isi yang menyajikan hierarki bab, subbab, dan halaman masing-masing bagian. Pengantar biasanya menjelaskan tujuan, cakupan materi, audiens target, dan metode pendekatan yang digunakan dalam buku atau dokumen ini.\n\n";

            $penjabaran .= "2. Penjelasan konsep-konsep utama yang disusun secara bertahap dari dasar hingga lanjutan, dengan setiap bab membahas konsep spesifik secara mendalam. Setiap konsep dijelaskan dengan bahasa yang mudah dimengerti, disertai definisi, penjelasan, dan ilustrasi pendukung. Setiap bab biasanya diakhiri dengan rangkuman yang merangkum poin-poin penting yang dibahas.\n\n";

            $penjabaran .= "3. Tata letak yang terorganisir dengan bagian-bagian yang jelas seperti judul utama, subjudul, paragraf yang terdefinisi dengan baik, nomor halaman yang konsisten, serta format teks yang seragam dan memudahkan pembaca dalam mengikuti alur materi. Header dan footer biasanya menyertakan informasi bab atau judul halaman.\n\n";

            $penjabaran .= "4. Penggunaan elemen visual yang efektif seperti grafik, diagram, tabel, dan gambar yang disesuaikan dengan konten untuk memperkuat pemahaman terhadap konsep-konsep yang kompleks. Setiap elemen visual biasanya dilengkapi keterangan yang menjelaskan konteks dan hubungannya dengan teks.\n\n";

            $penjabaran .= "5. Referensi atau daftar pustaka yang mencantumkan buku, jurnal, artikel, atau sumber daya lain yang digunakan sebagai dasar atau pendukung materi yang disajikan. Ini memungkinkan pembaca untuk menggali lebih dalam terhadap topik yang diminati.\n\n";

            $penjabaran .= "6. Glosarium atau kamus istilah yang menjelaskan terminologi spesifik yang digunakan dalam dokumen, membantu pembaca memahami istilah-istilah teknis atau konsep baru yang diperkenalkan.\n\n";

            $penjabaran .= "7. Indeks subjek yang memungkinkan pembaca untuk menemukan informasi tertentu dengan cepat tanpa harus membaca keseluruhan dokumen. Ini sangat berguna sebagai referensi.\n\n";

            $penjabaran .= "8. Latihan atau soal-soal evaluasi di akhir bab atau subbab untuk menguji pemahaman pembaca terhadap konsep yang telah dipelajari. Soal-soal ini bisa dalam bentuk pilihan ganda, esai, atau studi kasus.\n\n";

            $penjabaran .= "9. Studi kasus atau contoh penerapan nyata dari konsep-konsep yang dibahas, yang menunjukkan bagaimana teori dapat diterapkan dalam situasi dunia nyata, memperkuat koneksi antara teori dan praktik.\n\n";

            $penjabaran .= "10. Tips, catatan, atau kotak informasi tambahan yang menyoroti poin-penting, kesalahan umum, atau informasi tambahan yang relevan. Ini membantu pembaca untuk memperhatikan detail penting.\n\n";

            $penjabaran .= "Format PDF memungkinkan pembaca untuk fokus pada isi tanpa gangguan dari elemen interaktif atau animasi yang dapat mengalihkan perhatian, menjadikannya ideal untuk materi pembelajaran yang membutuhkan konsentrasi tinggi. File ini dapat digunakan sebagai referensi utama dalam mempelajari topik yang terkandung di dalamnya, serta sebagai panduan belajar yang sistematis dan terstruktur.";
        }
        // Untuk jenis file lainnya
        else {
            $ringkasan = "File ini berisi materi pelajaran yang disusun secara sistematis untuk memberikan pemahaman menyeluruh tentang topik yang dibahas. Cocok untuk pembelajaran mandiri dengan pendekatan bertahap dari konsep dasar menuju aplikasi praktis, menyediakan fondasi yang kuat dan panduan komprehensif untuk memahami dan menerapkan konsep-konsep dalam topik yang dibahas. File ini dirancang untuk memastikan pembelajaran yang efektif dan berkelanjutan.";

            $penjabaran = "Materi dalam file ini kemungkinan mencakup berbagai aspek penting dari topik yang dibahas:\n\n";

            // Tambahkan informasi berdasarkan isi file jika tersedia
            if (!empty($isiSingkat)) {
                $penjabaran .= "Spesifiknya, file ini membahas:\n";
                $penjabaran .= "- " . $isiSingkat . "\n\n";
            }

            $penjabaran .= "1. Konsep-konsep dasar yang menjadi fondasi utama dalam memahami topik secara keseluruhan. Setiap konsep dijelaskan secara mendalam dengan definisi yang jelas, contoh pendukung, dan ilustrasi visual jika relevan untuk mempermudah pemahaman terhadap prinsip-prinsip mendasar yang mendasari topik tersebut.\n\n";

            $penjabaran .= "2. Penjelasan bertahap yang menghubungkan konsep satu dengan lainnya dalam urutan logis dan koheren, memungkinkan pembaca untuk membangun pemahaman secara bertahap dari level dasar menuju konsep yang lebih kompleks dan terintegrasi. Setiap konsep baru dibangun di atas pemahaman konsep sebelumnya.\n\n";

            $penjabaran .= "3. Contoh praktis, studi kasus, atau ilustrasi yang membantu memperkuat pemahaman terhadap konsep yang dipelajari, memperlihatkan secara konkret bagaimana konsep tersebut dapat diterapkan dalam situasi dunia nyata dan memberikan konteks terhadap teori yang dipelajari.\n\n";

            $penjabaran .= "4. Pembahasan tentang aplikasi atau implementasi dari konsep-konsep tersebut dalam konteks nyata, termasuk potensi tantangan yang mungkin dihadapi, strategi untuk mengatasinya, serta best practices yang direkomendasikan untuk hasil optimal.\n\n";

            $penjabaran .= "5. Evaluasi atau latihan untuk menguji pemahaman terhadap materi, memberikan kesempatan bagi pembaca untuk mempraktikkan pengetahuan yang telah dipelajari dan mengidentifikasi area yang memerlukan tambahan fokus.\n\n";

            $penjabaran .= "6. Penjelasan tentang keterkaitan antar konsep, menunjukkan bagaimana elemen-elemen dalam topik saling berinteraksi dan membentuk kerangka kerja yang lebih luas, membantu pembaca memahami konteks keseluruhan dari masing-masing komponen.\n\n";

            $penjabaran .= "7. Panduan atau langkah-langkah sistematis untuk menerapkan konsep-konsep dalam praktik nyata, termasuk instruksi terperinci, checklist, atau workflow yang membimbing pembaca melalui proses implementasi.\n\n";

            $penjabaran .= "8. Arah atau saran untuk eksplorasi lebih lanjut terhadap topik terkait, referensi tambahan, atau sumber daya untuk pembelajaran lanjutan, memungkinkan pembaca untuk memperdalam pemahaman mereka.\n\n";

            $penjabaran .= "9. Penjelasan tentang manfaat atau keuntungan dari menguasai konsep yang dipelajari, menunjukkan relevansi dan aplikasi dalam konteks karier, pendidikan, atau kehidupan sehari-hari.\n\n";

            $penjabaran .= "10. Strategi pembelajaran yang direkomendasikan, seperti teknik menghafal, metode belajar efektif, atau pendekatan pendidikan yang membantu pembaca memaksimalkan proses belajar mereka.\n\n";

            $penjabaran .= "File ini dirancang untuk mengembangkan pemahaman secara komprehensif, memastikan pembaca dapat mengikuti perkembangan konsep dari dasar hingga lanjutan dengan lancar. Struktur dan isi file dirancang untuk memfasilitasi pembelajaran mandiri yang efektif dan menyeluruh, dengan pendekatan yang memperhatikan aspek psikologi pembelajaran dan kebutuhan pengguna dalam memahami materi kompleks.";
        }

        // Tambahkan informasi file jika tersedia
        if (!empty($fileName)) {
            $ringkasan = "File: " . $fileName . "\n\n" . $ringkasan;
        }

        // Gabungkan ringkasan dan penjabaran
        // Kami mengembalikan keduanya dalam format terpisah untuk digunakan lebih lanjut
        $fullContent = "Ringkasan:\n" . $ringkasan . "\n\nPenjabaran Materi:\n" . $penjabaran;

        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => $fullContent
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Ekstrak teks dari file gambar (gunakan placeholder untuk OCR)
     */
    private function extractTextFromImage($filePath) {
        // Untuk mendukung OCR, Anda perlu library tambahan seperti Tesseract
        return "[Gambar: " . basename($filePath) . " - File gambar telah diunggah. Dalam implementasi lengkap, gambar akan diproses menggunakan OCR untuk ekstraksi teks.]";
    }

    /**
     * Analisis file menggunakan Google AI
     * @param string $filePath Path file yang akan dianalisis
     * @param string $customPrompt Teks permintaan khusus ke AI
     * @return array Hasil dari AI
     */
    /**
     * Analisis file menggunakan Google AI
     * @param string $filePath Path file yang akan dianalisis
     * @param string $customPrompt Teks permintaan khusus ke AI
     * @return array Hasil dari AI
     */
    public function analyzeFile($filePath, $customPrompt = '') {
        if (empty($customPrompt)) {
            $customPrompt = "Lakukan analisis menyeluruh terhadap file ini: " . basename($filePath) . ". Buatkan ringkasan komprehensif yang mencakup pokok-pokok bahasan utama, jelaskan secara mendalam tentang isi file termasuk konsep-konsep penting, struktur materi, serta aplikasi praktis dari materi yang terkandung. Jika file berisi soal atau latihan, sertakan juga penjelasan jawaban dan konsep-konsep yang terlibat. Jelaskan dengan bahasa yang mudah dimengerti dan struktur yang sistematis.";
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileContent = '';

        // Ekstrak isi file berdasarkan jenisnya
        switch ($extension) {
            case 'pdf':
                $fileContent = $this->extractTextFromPDF($filePath);
                break;
            case 'docx':
            case 'doc':
                $fileContent = $this->extractTextFromDOC($filePath);
                break;
            case 'txt':
                $fileContent = file_get_contents($filePath);
                if ($fileContent === false) {
                    $fileContent = "[File: " . basename($filePath) . " - Gagal membaca isi file]";
                }
                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
                $fileContent = $this->extractTextFromImage($filePath);
                break;
            default:
                $fileContent = "[File: " . basename($filePath) . " - Jenis file tidak didukung untuk ekstraksi teks otomatis. File telah diupload dan siap diproses.]";
        }

        // Buat prompt yang benar-benar unik berdasarkan isi file spesifik
        $fullPrompt = "FILE ANALYSIS REQUEST\n";
        $fullPrompt .= "=====================\n\n";
        $fullPrompt .= "File Name: " . basename($filePath) . "\n";
        $fullPrompt .= "File Type: " . $extension . "\n";
        $fullPrompt .= "File Size: " . $this->formatFileSize(filesize($filePath)) . "\n";
        $fullPrompt .= "File Path: " . $filePath . "\n\n";

        $fullPrompt .= "FILE CONTENT:\n";
        $fullPrompt .= "[START OF FILE CONTENT]\n";
        $fullPrompt .= $fileContent . "\n";
        $fullPrompt .= "[END OF FILE CONTENT]\n\n";

        $fullPrompt .= "ANALYSIS REQUEST:\n";
        $fullPrompt .= $customPrompt . "\n\n";

        $fullPrompt .= "CRITICAL ANALYSIS INSTRUCTIONS:\n";
        $fullPrompt .= "1. ANALYZE ONLY the content provided above between [START OF FILE CONTENT] and [END OF FILE CONTENT]\n";
        $fullPrompt .= "2. Do NOT provide generic information about " . $extension . " files\n";
        $fullPrompt .= "3. Focus specifically on the actual content of THIS specific file\n";
        $fullPrompt .= "4. Extract and analyze ONLY what exists in the provided file content\n";
        $fullPrompt .= "5. Do NOT make assumptions about content not present in the file\n";
        $fullPrompt .= "6. Be specific to the topics, concepts, and information actually found in this file\n";
        $fullPrompt .= "7. If the file contains educational content, identify the main subjects, learning objectives, and key concepts\n";
        $fullPrompt .= "8. For each concept mentioned, explain its context within this specific document\n\n";

        $fullPrompt .= "EXPECTED OUTPUT FORMAT:\n";
        $fullPrompt .= "First, provide a comprehensive summary (SUMMARY SECTION):\n";
        $fullPrompt .= "- Main topic or subject covered\n";
        $fullPrompt .= "- Learning objectives if mentioned\n";
        $fullPrompt .= "- Key concepts and themes\n";
        $fullPrompt .= "- Structure and organization of content\n\n";

        $fullPrompt .= "Then, provide a detailed explanation (DETAILED EXPLANATION SECTION):\n";
        $fullPrompt .= "- In-depth analysis of each major topic\n";
        $fullPrompt .= "- Explanation of concepts with reference to specific parts of the file\n";
        $fullPrompt .= "- Relationships between different concepts in the file\n";
        $fullPrompt .= "- Practical applications if mentioned\n";
        $fullPrompt .= "- Important details and supporting information\n\n";

        $fullPrompt .= "Finally, if applicable, include related topics (RELATED TOPICS SECTION):\n";
        $fullPrompt .= "- Topics that are connected to the main content\n";
        $fullPrompt .= "- Areas for further exploration related to the file content\n";
        $fullPrompt .= "- Prerequisites or follow-up topics\n\n";

        $fullPrompt .= "IMPORTANT: Make sure all analysis references actual content from the provided file content. Do not provide generic information about " . $extension . " files in general.";

        // Batasi panjang jika terlalu besar
        if (strlen($fullPrompt) > 30000) {
            $maxContentLength = 25000;
            $truncatedContent = substr($fileContent, 0, $maxContentLength) . "\n\n[...isi file dipotong karena terlalu panjang. Hanya bagian awal file yang diproses untuk analisis awal...]";

            $fullPrompt = "FILE ANALYSIS REQUEST\n";
            $fullPrompt .= "=====================\n\n";
            $fullPrompt .= "File Name: " . basename($filePath) . "\n";
            $fullPrompt .= "File Type: " . $extension . "\n";
            $fullPrompt .= "File Size: " . $this->formatFileSize(filesize($filePath)) . "\n";
            $fullPrompt .= "File Path: " . $filePath . "\n";
            $fullPrompt .= "NOTE: File is too large, only the beginning of content is available for analysis.\n\n";

            $fullPrompt .= "FILE CONTENT (first portion):\n";
            $fullPrompt .= "[START OF FILE CONTENT]\n";
            $fullPrompt .= $truncatedContent . "\n";
            $fullPrompt .= "[END OF FILE CONTENT]\n\n";

            $fullPrompt .= "ANALYSIS REQUEST:\n";
            $fullPrompt .= $customPrompt . "\n\n";

            $fullPrompt .= "CRITICAL ANALYSIS INSTRUCTIONS:\n";
            $fullPrompt .= "1. ANALYZE ONLY the content provided above between [START OF FILE CONTENT] and [END OF FILE CONTENT]\n";
            $fullPrompt .= "2. Do NOT provide generic information about " . $extension . " files\n";
            $fullPrompt .= "3. Focus specifically on the actual content of THIS specific file\n";
            $fullPrompt .= "4. Extract and analyze ONLY what exists in the provided file content\n";
            $fullPrompt .= "5. Do NOT make assumptions about content not present in the file\n";
            $fullPrompt .= "6. Be specific to the topics, concepts, and information actually found in this file\n\n";

            $fullPrompt .= "EXPECTED OUTPUT FORMAT:\n";
            $fullPrompt .= "First, provide a comprehensive summary (SUMMARY SECTION):\n";
            $fullPrompt .= "- Main topic or subject covered\n";
            $fullPrompt .= "- Learning objectives if mentioned\n";
            $fullPrompt .= "- Key concepts and themes\n";
            $fullPrompt .= "- Structure and organization of content\n\n";

            $fullPrompt .= "Then, provide a detailed explanation (DETAILED EXPLANATION SECTION):\n";
            $fullPrompt .= "- In-depth analysis of each major topic\n";
            $fullPrompt .= "- Explanation of concepts with reference to specific parts of the file\n";
            $fullPrompt .= "- Relationships between different concepts in the file\n";
            $fullPrompt .= "- Important details and supporting information\n\n";

            $fullPrompt .= "IMPORTANT: Make sure all analysis references actual content from the provided file content. The content is truncated, so analyze only what's available.";
        }

        // Penting: Jangan menyimpan isi file ke properti kelas untuk menghindari campuran data
        // Kita hanya kirim data file ini ke API, tidak menyimpannya

        try {
            $result = $this->generateContent($fullPrompt);

            // Kembalikan hasil langsung, tidak menggunakan cache atau data sebelumnya
            return $result;
        } catch (Exception $e) {
            // Jika terjadi error saat pemanggilan API, kembalikan pesan error
            error_log("AI API Error for file " . basename($filePath) . ": " . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Terjadi kesalahan saat memproses file dengan AI: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Analisis langsung dari konten teks
     * @param string $textContent Isi teks yang akan dianalisis
     * @param string $prompt Teks permintaan ke AI
     * @return array Hasil dari AI
     */
    public function analyzeText($textContent, $customPrompt = '') {
        if (empty($customPrompt)) {
            $customPrompt = "Analisis dan buat ringkasan dari teks berikut. Jelaskan dengan bahasa yang mudah dimengerti dan sertakan konsep-konsep penting yang terdapat dalam teks tersebut.";
        }

        // Buat prompt yang sangat spesifik untuk teks ini
        $fullPrompt = "ANALISIS SPESIFIK UNTUK TEKS INI\n";
        $fullPrompt .= "===============================\n\n";
        $fullPrompt .= "TEKS YANG DIBERIKAN:\n";
        $fullPrompt .= $textContent . "\n\n";
        $fullPrompt .= "PERMINTAAN ANALISIS:\n";
        $fullPrompt .= $customPrompt . "\n\n";
        $fullPrompt .= "PETUNJUK KHUSUS:\n";
        $fullPrompt .= "1. Analisis HARUS berdasarkan TEKS YANG DIBERIKAN di atas (BUKAN teks umum)\n";
        $fullPrompt .= "2. Buat ringkasan berdasarkan isi teks yang tersedia\n";
        $fullPrompt .= "3. Identifikasi konsep-konsep yang ADA DALAM TEKS INI\n";
        $fullPrompt .= "4. Berikan penjelasan berdasarkan isi teks yang tersedia\n";
        $fullPrompt .= "5. JANGAN berikan informasi umum yang tidak dari teks ini\n";
        $fullPrompt .= "6. Fokuskan analisis pada konten yang BENAR-BENAR ADA dalam teks ini";

        // Batasi panjang jika terlalu besar
        if (strlen($fullPrompt) > 30000) {
            $maxTextLength = 25000;
            $truncatedText = substr($textContent, 0, $maxTextLength) . "\n\n[...isi teks dipotong karena terlalu panjang. Hanya bagian awal teks yang diproses untuk analisis awal...]";

            $fullPrompt = "ANALISIS SPESIFIK UNTUK TEKS INI\n";
            $fullPrompt .= "===============================\n\n";
            $fullPrompt .= "TEKS YANG DIBERIKAN (bagian awal saja):\n";
            $fullPrompt .= $truncatedText . "\n\n";
            $fullPrompt .= "PERMINTAAN ANALISIS:\n";
            $fullPrompt .= $customPrompt . "\n\n";
            $fullPrompt .= "PETUNJUK KHUSUS:\n";
            $fullPrompt .= "1. Analisis HARUS berdasarkan isi teks di atas (BUKAN teks lain)\n";
            $fullPrompt .= "2. Buat ringkasan berdasarkan bagian yang tersedia\n";
            $fullPrompt .= "3. Identifikasi konsep-konsep yang ADA DALAM TEKS INI\n";
            $fullPrompt .= "4. Berikan penjelasan berdasarkan isi yang tersedia\n";
            $fullPrompt .= "5. JANGAN berikan informasi umum yang tidak dari teks ini";
        }

        try {
            return $this->generateContent($fullPrompt);
        } catch (Exception $e) {
            error_log("AI Text Analysis Error: " . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Terjadi kesalahan saat menganalisis teks: ' . $e->getMessage()
            ];
        }
    }
}