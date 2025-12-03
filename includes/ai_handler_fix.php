<?php
// Fixed version of AI handler to ensure proper file-based question creation

class AIHandlerFixed {
    private $apiKey;
    private $baseUrl;
    private $defaultModel;

    public function __construct() {
        if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
            throw new Exception("OPENROUTER_API_KEY tidak ditemukan. Silakan definisikan di file konfigurasi.");
        }

        $this->apiKey = OPENROUTER_API_KEY;
        $this->baseUrl = OPENROUTER_BASE_URL ?? 'https://openrouter.ai/api/v1';
        $this->defaultModel = OPENROUTER_DEFAULT_MODEL ?? 'openai/gpt-3.5-turbo';
    }

    private function sendRequest($prompt, $model = null, $maxTokens = 2048, $temperature = 0.7) {
        $model = $model ?: $this->defaultModel;

        $data = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . '/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'HTTP-Referer: ' . (defined('BASE_URL') ? BASE_URL : 'http://localhost'),
                'X-Title: Sinar Ilmu - Aplikasi Pembelajaran'
            ],
            CURLOPT_TIMEOUT => (defined('OPENROUTER_TIMEOUT') ? OPENROUTER_TIMEOUT : 60),
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'SinarIlmu/1.0'
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception('HTTP Error: ' . $httpCode . '. Response: ' . $response);
        }

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON Decode Error: ' . json_last_error_msg());
        }

        if (!isset($responseData['choices'][0]['message']['content'])) {
            throw new Exception('Invalid response format: ' . $response);
        }

        return $responseData['choices'][0]['message']['content'];
    }

    public function getAnalysisAndExercises($fileContent, $fileName) {
        // Buat prompt yang sangat eksplisit membaca isi file
        $prompt = "ANDA HARUS MEMBACA ISI FILE BERIKUT DENGAN SANGAT TELITI DAN MEMBUAT 10 SOAL BERDASARKAN ISI FILE INI SECARA SPESIFIK.\n\n";
        $prompt .= "NAMA FILE: {$fileName}\n\n";
        $prompt .= "ISI FILE (BACA DENGAN TELITI):\n{$fileContent}\n\n";
        $prompt .= "LANGKAH- LANGKAH:\n";
        $prompt .= "1. BACA ISI FILE SECARA LENGKAP\n";
        $prompt .= "2. IDENTIFIKASI TOPIK UTAMA (Matematika, Fisika, Kimia, Biologi, dll)\n";
        $prompt .= "3. IDENTIFIKASI KONSEP-KONSEP PENTING DALAM FILE\n";
        $prompt .= "4. BUAT 10 SOAL BERDASARKAN ISI LANGSUNG FILE\n";
        $prompt .= "5. BUAT PILIHAN JAWABAN JUGA BERDASARKAN ISI FILE\n\n";
        $prompt .= "CONTOH SPESIFIK (JIKA DALAM FILE ADA: 'Asam adalah zat yang dalam air melepaskan ion H+')\n";
        $prompt .= "MAKA SOAL: 'Menurut teori Arrhenius, asam adalah...'\n";
        $prompt .= "PILIHAN: a) zat yang dalam air melepaskan ion H+, b) zat yang dalam air melepaskan ion OH-, c) senyawa yang memiliki pH > 7, d) senyawa yang berasa pahit\n\n";
        $prompt .= "HASILKAN DALAM FORMAT:\n\n";
        $prompt .= "---ANALYSIS_START---\n";
        $prompt .= "Ringkasan:\n[Tulis ringkasan berdasarkan isi file di atas]\n\n";
        $prompt .= "Penjabaran Materi:\n[Tulis penjabaran berdasarkan konsep dalam isi file di atas]\n";
        $prompt .= "---ANALYSIS_END---\n\n";
        $prompt .= "---QUESTIONS_START---\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan a dari isi file\",\n";
        $prompt .= "      \"b\": \"Pilihan b dari isi file\",\n";
        $prompt .= "      \"c\": \"Pilihan c dari isi file\",\n";
        $prompt .= "      \"d\": \"Pilihan d dari isi file\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  }\n";
        $prompt .= "]\n";
        $prompt .= "---QUESTIONS_END---\n\n";
        $prompt .= "PERINGATAN: SOAL-HARUS BERDASARKAN ISI FILE LANGSUNG. TIDAK BOLEH UMUM.";

        try {
            return $this->sendRequest($prompt, null, 4500, 0.15);
        } catch (Exception $e) {
            // Jika API gagal, buat dari konten file langsung
            return $this->generateQuestionsFromContent($fileContent, $fileName);
        }
    }

    // Fungsi untuk membuat soal dari konten file jika API tidak tersedia
    private function generateQuestionsFromContent($fileContent, $fileName) {
        // Bersihkan konten dari tag dan karakter aneh
        $cleanContent = strip_tags($fileContent);
        $cleanContent = html_entity_decode($cleanContent);
        $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);

        // Pisahkan jadi kalimat
        $sentences = preg_split('/[.!?]+/', $cleanContent);
        $sentences = array_filter($sentences, function($sentence) {
            $sentence = trim($sentence);
            return strlen($sentence) > 30 && 
                   !preg_match('/(©|copyright|all rights reserved|halaman|page|gambar|tabel|source)/i', $sentence);
        });
        $sentences = array_values($sentences);

        // Identifikasi topik berdasarkan kata kunci
        $topik = 'Materi Umum';
        if (preg_match('/(kimia|asam|basa|reaksi|garam|larutan|molekul|atom|ion|elektron|proton|neutron)/i', $cleanContent)) {
            $topik = 'Kimia';
        } elseif (preg_match('/(matematika|fungsi|kuadrat|aljabar|trigonometri|kalkulus|limit|turunan|integral|persamaan|pertidaksamaan)/i', $cleanContent)) {
            $topik = 'Matematika';
        } elseif (preg_match('/(fisika|newton|gaya|usaha|energi|momentum|gelombang|cahaya|listrik|magnet|arus|tekanan|suhu|kalor)/i', $cleanContent)) {
            $topik = 'Fisika';
        } elseif (preg_match('/(biologi|sel|mikroorganisme|dna|rna|metabolisme|respirasi|fotosintesis|evolusi|ekosistem|organ|jaringan)/i', $cleanContent)) {
            $topik = 'Biologi';
        }

        // Buat ringkasan
        $summary = "File {$fileName} berisi materi {$topik} dengan beberapa konsep penting yang dibahas.";
        if (count($sentences) > 0) {
            $summary = "File {$fileName} berisi materi {$topik}: " . substr($sentences[0], 0, 150) . (strlen($sentences[0]) > 150 ? '...' : '');
        }

        // Buat penjabaran
        $penjabaran = "File ini membahas konsep-konsep penting dalam {$topik}.";
        $relevantSentences = array_slice($sentences, 0, min(5, count($sentences)));
        foreach ($relevantSentences as $sentence) {
            if (strlen($sentence) > 40) {
                $penjabaran .= " Di antaranya: " . substr($sentence, 0, 80) . "... ";
            }
        }

        // Buat soal dari isi file
        $questions = [];
        $processedSentences = array_slice($sentences, 0, 10); // Ambil 10 kalimat pertama yang relevan

        foreach ($processedSentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) < 40 || strlen($sentence) > 150) continue;

            // Cek apakah mengandung kata kunci konsep penting
            if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi|rumus|bentuk|cara|langkah|proses|fungsi|manfaat|sifat|ciri|jenis|macam|contoh|prinsip|teori|konsep)/i', $sentence)) {
                $questionText = '';
                
                // Buat soal berdasarkan jenis konsep
                if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi)/i', $sentence)) {
                    $questionText = "Apa yang dimaksud dengan: \"" . substr($sentence, 0, 50) . "..." . "?";
                } elseif (preg_match('/(rumus|bentuk)/i', $sentence)) {
                    $questionText = "Apa rumus/bentuk dari konsep yang disebutkan dalam: \"" . substr($sentence, 0, 40) . "..." . "?";
                } elseif (preg_match('/(cara|langkah|proses)/i', $sentence)) {
                    $questionText = "Apa cara/langkah/proses yang disebutkan dalam: \"" . substr($sentence, 0, 40) . "..." . "?";
                } else {
                    $questionText = "Apa yang dapat dipelajari dari pernyataan: \"" . substr($sentence, 0, 50) . "..." . "?";
                }

                // Buat pilihan jawaban dari konten
                $words = explode(' ', $sentence);
                $wordCount = count($words);
                
                $choiceA = implode(' ', array_slice($words, 0, min(4, $wordCount))) . '...';
                $choiceB = implode(' ', array_slice($words, max(0, $wordCount - 4), 4)) . '...';
                $choiceC = implode(' ', array_slice($words, max(0, intval($wordCount/2)), 4)) . '...';
                $choiceD = 'Konsep yang berkaitan dengan: ' . substr($sentence, 0, 30) . '...';

                $questions[] = [
                    'soal' => $questionText,
                    'pilihan' => [
                        'a' => $choiceA,
                        'b' => $choiceB,
                        'c' => $choiceC,
                        'd' => $choiceD
                    ],
                    'kunci_jawaban' => 'a'
                ];

                if (count($questions) >= 10) break;
            }
        }

        // Jika tidak cukup soal dari konsep langsung, buat soal umum dari isi file
        if (count($questions) < 10) {
            foreach ($sentences as $sentence) {
                if (count($questions) >= 10) break;
                $sentence = trim($sentence);
                if (strlen($sentence) < 50) continue;

                $questionText = "Apa yang dimaksud dengan konsep dalam kalimat: \"" . substr($sentence, 0, 50) . "..." . "?";
                
                // Buat pilihan dari fragmen kalimat
                $fragments = str_split($sentence, max(1, intval(strlen($sentence)/4)));
                $choiceA = isset($fragments[0]) ? substr($fragments[0], 0, 50) . '...' : 'Pilihan A';
                $choiceB = isset($fragments[1]) ? substr($fragments[1], 0, 50) . '...' : 'Pilihan B';
                $choiceC = isset($fragments[2]) ? substr($fragments[2], 0, 50) . '...' : 'Pilihan C';
                $choiceD = 'Jawaban yang benar';

                $questions[] = [
                    'soal' => $questionText,
                    'pilihan' => [
                        'a' => $choiceA,
                        'b' => $choiceB,
                        'c' => $choiceC,
                        'd' => $choiceD
                    ],
                    'kunci_jawaban' => 'a'
                ];
            }
        }

        // Format hasil
        $result = "---ANALYSIS_START---\n";
        $result .= "Ringkasan:\n{$summary}\n\n";
        $result .= "Penjabaran Materi:\n{$penjabaran}\n";
        $result .= "---ANALYSIS_END---\n\n";
        
        $result .= "---QUESTIONS_START---\n";
        $result .= json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        $result .= "---QUESTIONS_END---\n";

        return $result;
    }
}

?>