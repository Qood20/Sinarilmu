<?php
// File: includes/ai_handler.php

class AIHandler {
    private $apiKey;
    private $baseUrl;
    private $defaultModel;

    public function __construct() {
        if (!defined('OPENROUTER_API_KEY')) {
            throw new Exception('OPENROUTER_API_KEY tidak ditemukan. Silakan definisikan di file konfigurasi.');
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
                'HTTP-Referer: ' . (defined('BASE_URL') ? BASE_URL : 'localhost'),
                'X-Title: Sinarilmu AI Integration'
            ],
            CURLOPT_TIMEOUT => (defined('OPENROUTER_TIMEOUT') ? OPENROUTER_TIMEOUT : 90), // Tingkatkan default timeout
            CURLOPT_CONNECTTIMEOUT => 30, // Timeout untuk koneksi
            CURLOPT_SSL_VERIFYPEER => true, // Pastikan SSL valid
            CURLOPT_FOLLOWLOCATION => true,  // Ikuti redirect jika ada
            CURLOPT_USERAGENT => 'SinarIlmu/1.0' // Tambahkan user agent
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        $error_no = curl_errno($curl);

        curl_close($curl);

        if ($error) {
            error_log("cURL error ({$error_no}): " . $error);
            // Deteksi apakah error karena timeout
            if ($error_no === CURLE_OPERATION_TIMEDOUT) {
                throw new Exception('Koneksi API timeout: Permintaan terlalu lama. Silakan coba lagi.');
            } else {
                throw new Exception('Koneksi API gagal: ' . $error);
            }
        }

        if ($http_code !== 200) {
            error_log("HTTP error {$http_code}: Response - " . $response);
            throw new Exception('HTTP error: ' . $http_code . ', Response: ' . $response);
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg() . " - Response: " . $response);
            throw new Exception('Format respons API tidak valid: ' . json_last_error_msg());
        }

        if (isset($responseData['choices'][0]['message']['content'])) {
            return $responseData['choices'][0]['message']['content'];
        } else {
            error_log("Respons tidak memiliki content yang valid: " . $response);
            throw new Exception('AI tidak memberikan jawaban yang valid. Response: ' . $response);
        }
    }

    public function getAnalysis($fileContent, $fileName) {
        $prompt = "Anda adalah AI analisis materi canggih. Analisis konten file berikut dan berikan ringkasan serta penjabaran materi yang mendalam.\n\n";
        $prompt .= "Nama File: " . htmlspecialchars($fileName) . "\n";
        $prompt .= "Konten File:\n" . substr($fileContent, 0, 25000) . "\n\n";
        $prompt .= "FORMAT JAWABAN (gunakan pemisah ini):\n";
        $prompt .= "Ringkasan:\n[Tulis ringkasan komprehensif dari materi dalam file ini]\n\n";
        $prompt .= "Penjabaran Materi:\n[Tulis penjelasan yang detail, terstruktur, dan mendalam tentang setiap konsep utama yang ditemukan dalam file. Jika ini adalah materi pelajaran, jelaskan seolah-olah Anda adalah seorang guru.]";

        return $this->sendRequest($prompt);
    }

    public function generateExercises($materialContent) {
        $prompt = "Anda adalah AI pembuat soal. Berdasarkan materi berikut, buatlah 5 soal latihan pilihan ganda beserta jawabannya untuk menguji pemahaman.\n\n";
        $prompt .= "Materi:\n" . htmlspecialchars($materialContent) . "\n\n";
        $prompt .= "FORMAT JAWABAN (JSON):\n";
        $prompt .= "Berikan jawaban dalam format JSON array. Setiap objek dalam array harus memiliki kunci: 'question', 'options' (sebuah array dari 4 string), dan 'correct_answer' (string yang cocok dengan salah satu dari options).\n";
        $prompt .= "Contoh: \n";
        $prompt .= "[{\"question\": \"Ibukota Indonesia adalah?\", \"options\": [\"Jakarta\", \"Bandung\", \"Surabaya\", \"Medan\"], \"correct_answer\": \"Jakarta\"}]";

        return $this->sendRequest($prompt, null, 1500, 0.5);
    }

    public function getAnalysisAndExercises($fileContent, $fileName) {
        $prompt = "Anda adalah AI analisis materi dan pembuat soal canggih. Analisis konten file berikut dan berikan ringkasan, penjabaran materi, dan 12 soal latihan pilihan ganda.\n\n";
        $prompt .= "Nama File: " . htmlspecialchars($fileName) . "\n";
        $prompt .= "Konten File:\n" . substr($fileContent, 0, 20000) . "\n\n";
        $prompt .= "FORMAT JAWABAN (HARUS MENGGUNAKAN PEMISAH INI):\n\n";
        $prompt .= "---ANALYSIS_START---\n";
        $prompt .= "Ringkasan:\n[Tulis ringkasan komprehensif dari materi dalam file ini]\n\n";
        $prompt .= "Penjabaran Materi:\n[Tulis penjelasan yang detail, terstruktur, dan mendalam tentang setiap konsep utama yang ditemukan dalam file. Jelaskan seolah-olah Anda adalah seorang guru.]\n";
        $prompt .= "---ANALYSIS_END---\n\n";
        $prompt .= "---QUESTIONS_START---\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Contoh soal pilihan ganda\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan A\",\n";
        $prompt .= "      \"b\": \"Pilihan B\",\n";
        $prompt .= "      \"c\": \"Pilihan C\",\n";
        $prompt .= "      \"d\": \"Pilihan D\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  }\n";
        $prompt .= "] // Kembalikan array JSON 12 soal dengan format ini. Hanya kembalikan JSON, tanpa penjelasan tambahan di luar pembatas.\n";
        $prompt .= "---QUESTIONS_END---";

        // Increase max tokens to accommodate both analysis and questions
        return $this->sendRequest($prompt, null, 3500, 0.6);
    }

    /**
     * Method untuk mengirim pesan ke AI dan mendapatkan respons
     */
    public function sendMessage($prompt, $model = null, $maxTokens = 2048, $temperature = 0.7) {
        try {
            return $this->sendRequest($prompt, $model, $maxTokens, $temperature);
        } catch (Exception $e) {
            error_log("AIHandler sendMessage Error: " . $e->getMessage());
            // Jangan melempar exception, tapi kembalikan pesan error untuk ditangani di level aplikasi
            throw $e; // Tetap lempar untuk ditangani di aplikasi
        }
    }

    /**
     * Method untuk menganalisis teks dan mendapatkan respons dari AI
     */
    public function analyzeText($prompt, $model = null, $maxTokens = 2048, $temperature = 0.7) {
        try {
            $response = $this->sendRequest($prompt, $model, $maxTokens, $temperature);

            // Format respons untuk kompatibilitas dengan sistem yang mengharapkan format Google AI
            return [
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => $response
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Method untuk generate konten (kompatibilitas dengan test file)
     */
    public function generateContent($prompt) {
        return $this->analyzeText($prompt);
    }
}
?>
