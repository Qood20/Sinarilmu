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

        // Check if API key is valid before making the request
        if (empty($this->apiKey) || strlen($this->apiKey) < 20) {
            error_log("Invalid API key provided. Using fallback response.");
            throw new Exception("Invalid or missing API key. API key length: " . strlen($this->apiKey ?? ''));
        }

        $data = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ];

        // Retry mekanisme untuk permintaan API
        $maxRetries = 2; // Reduce retries to speed up fallback
        $retryCount = 0;
        $lastException = null;

        while ($retryCount < $maxRetries) {
            $curl = curl_init();

            // Pastikan BASE_URL sudah diinisialisasi
            $baseUrlValue = defined('BASE_URL') ? BASE_URL : (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'localhost');

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->baseUrl . '/chat/completions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey,
                    'HTTP-Referer: ' . $baseUrlValue,
                    'X-Title: Sinar Ilmu - Aplikasi Belajar Berbasis AI',
                    'User-Agent: SinarIlmu/1.0 (https://github.com/your-app)'
                ],
                CURLOPT_TIMEOUT => (defined('OPENROUTER_TIMEOUT') ? OPENROUTER_TIMEOUT : 60), // Reduce timeout to prevent long waits
                CURLOPT_CONNECTTIMEOUT => 15, // Reduce connection timeout
                CURLOPT_SSL_VERIFYPEER => true, // Pastikan SSL valid
                CURLOPT_FOLLOWLOCATION => true,  // Ikuti redirect jika ada
                CURLOPT_USERAGENT => 'SinarIlmu/1.0' // Tambahkan user agent
            ]);

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            $error_no = curl_errno($curl);
            $response_time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);

            curl_close($curl);

            if ($error) {
                error_log("cURL error ({$error_no}) after {$response_time}s: " . $error);

                // Deteksi apakah error karena timeout
                if ($error_no === CURLE_OPERATION_TIMEDOUT) {
                    $lastException = new Exception('Koneksi API timeout: Permintaan terlalu lama (' . $response_time . 's). Silakan coba lagi.');
                } else {
                    $lastException = new Exception('Koneksi API gagal (' . $error_no . '): ' . $error . ' Setelah ' . $response_time . ' detik.');
                }
            } elseif ($http_code !== 200) {
                error_log("HTTP error {$http_code} after {$response_time}s: Response - " . $response);

                // Periksa apakah ini error 401 (otentikasi) dan jangan retry
                if ($http_code == 401) {
                    error_log("Authentication failed (401). API key might be invalid. Using fallback immediately.");
                    throw new Exception('Authentication failed (401): API key invalid. Using fallback response.');
                }
                // Beberapa status HTTP error yang mungkin bisa di-retry
                elseif ($http_code == 429 || $http_code >= 500) {
                    // Tunggu sebelum retry untuk error 429 (rate limit) atau server error (5xx)
                    sleep(2); // Tunggu 2 detik sebelum retry
                    $retryCount++;
                    continue;
                } else {
                    $lastException = new Exception('HTTP error: ' . $http_code . ' Setelah ' . $response_time . ' detik. Response: ' . $response);
                    // Tidak retry untuk error selain 429 dan 5xx
                    break;
                }
            } else {
                $responseData = json_decode($response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("JSON decode error: " . json_last_error_msg() . " - Response: " . $response);
                    $lastException = new Exception('Format respons API tidak valid: ' . json_last_error_msg() . '. Response: ' . $response);
                } elseif (isset($responseData['choices'][0]['message']['content'])) {
                    return $responseData['choices'][0]['message']['content'];
                } else {
                    error_log("Respons tidak memiliki content yang valid: " . $response);
                    $lastException = new Exception('AI tidak memberikan jawaban yang valid. Response: ' . $response);
                }
                // Jika berhasil, keluar dari loop
                break;
            }

            // Jika kita sampai ke sini dan tidak return, coba lagi
            $retryCount++;
            if ($retryCount < $maxRetries) {
                sleep(1); // Tunggu 1 detik sebelum retry
            }
        }

        // Jika semua retry gagal, lempar exception terakhir
        if ($lastException) {
            throw $lastException;
        } else {
            throw new Exception('Permintaan ke API gagal setelah ' . $maxRetries . ' percobaan.');
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
        // Cek apakah API key terdefinisi sebelum mencoba mengakses API
        if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
            error_log("API Key tidak ditemukan. Menggunakan fallback untuk analisis dan soal.");
            return $this->generateResponseFromContent($fileContent, $fileName);
        }

        // Cek apakah API key valid (hanya format dasar) sebelum dikirim
        if (strlen(OPENROUTER_API_KEY) < 20) {
            error_log("API Key terlihat tidak valid (terlalu pendek). Menggunakan fallback.");
            return $this->generateResponseFromContent($fileContent, $fileName);
        }

        // Batasi panjang konten untuk menghindari batas token
        $truncatedContent = substr($fileContent, 0, 10000); // Batasi hingga 10,000 karakter

        // Buat prompt yang sangat spesifik dan tegas membaca isi file
        $prompt = "Kamu adalah AI spesialis pendidikan. ANALISIS DAN BUAT SOAL SECARA SPESIFIK BERDASARKAN ISI FILE BERIKUT.\n\n";
        $prompt .= "NAMA FILE: " . htmlspecialchars($fileName) . "\n";
        $prompt .= "ISI FILE LENGKAP (BACA DENGAN SANGAT TELITI):\n";
        $prompt .= $truncatedContent . "\n\n";
        $prompt .= "PERINTAH SANGAT TEGAS:\n";
        $prompt .= "1. BACA ISI FILE INI DENGAN SANGAT TELITI DAN MENDALAM\n";
        $prompt .= "2. ANALISIS ISI SEBENARNYA DARI FILE INI\n";
        $prompt .= "3. IDENTIFIKASI TOPIK UTAMA (misal: Matematika-Kuadrat, Fisika-Newton, Kimia-AsamBasa, dll)\n";
        $prompt .= "4. IDENTIFIKASI KONSEP-KONSEP PENTING, DEFINISI, RUMUS, ATAU PRINSIP UTAMA DALAM FILE\n";
        $prompt .= "5. BUAT TEPAT 10 SOAL PILIHAN GANDA BERDASARKAN ISI LANGSUNG FILE INI\n";
        $prompt .= "6. SETIAP SOAL HARUS MENGACU PADA ISI SPESIFIK DALAM FILE\n";
        $prompt .= "7. PILIHAN JAWABAN JUGA HARUS BERDASARKAN ISI FILE, BUKAN UMUM\n";
        $prompt .= "8. KUNCI JAWABAN HARUS BENAR BERDASARKAN ISI FILE\n\n";
        $prompt .= "GARIS BAWAH: JANGAN BUAT SOAL UMUM. SEMUA HARUS BERDASARKAN ISI FILE LANGSUNG.\n\n";

        // Tambahkan contoh spesifik agar AI lebih mengerti
        $prompt .= "CONTOH JIKA ISI FILE BERISI: 'Fungsi kuadrat adalah fungsi yang memiliki bentuk umum f(x) = ax² + bx + c, di mana a ≠ 0. Grafik fungsi kuadrat berbentuk parabola.'\n";
        $prompt .= "MAKA SOAL YANG DIBUAT: 'Apa bentuk umum dari fungsi kuadrat?'\n";
        $prompt .= "PILIHAN JAWABAN: a) f(x) = ax² + bx + c, b) f(x) = ax + b, c) f(x) = ax³ + bx² + c, d) f(x) = aˣ\n";
        $prompt .= "KUNCI JAWABAN: a\n\n";

        $prompt .= "CONTOH LAIN: Jika isi file menyebutkan 'Hukum Ohm menyatakan bahwa V = I × R', maka soal harus: 'Apa yang dinyatakan dalam Hukum Ohm?'\n";
        $prompt .= "BUKAN soal umum seperti 'Apa itu arus listrik?'\n\n";

        $prompt .= "HASILKAN DALAM FORMAT JSON SBB:\n\n";
        $prompt .= "---ANALYSIS_START---\n";
        $prompt .= "Ringkasan:\n[Tulis ringkasan spesifik berdasarkan isi file sebenarnya]\n\n";
        $prompt .= "Penjabaran Materi:\n[Jelaskan konsep-konsep utama yang ditemukan dalam isi file sebenarnya]\n";
        $prompt .= "---ANALYSIS_END---\n\n";
        $prompt .= "---QUESTIONS_START---\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 1\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 2\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 3\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 4\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 5\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 6\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 7\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 8\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 9\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal spesifik berdasarkan isi file sebenarnya nomor 10\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban berdasarkan isi file sebenarnya a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban berdasarkan isi file sebenarnya b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban berdasarkan isi file sebenarnya c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban berdasarkan isi file sebenarnya d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  }\n";
        $prompt .= "]\n";
        $prompt .= "---QUESTIONS_END---\n\n";
        $prompt .= "PENTING: SEMUA ISI ANALISIS, SOAL, DAN PILIHAN JAWABAN HARUS 100% BERDASARKAN ISI FILE DI ATAS, BUKAN PENGETAHUAN UMUM.";

        // Try API first, fall back to content-based generation if it fails
        try {
            return $this->sendRequest($prompt, null, 5000, 0.1);
        } catch (Exception $e) {
            error_log("API failed for file analysis, using fallback: " . $e->getMessage());
            return $this->generateResponseFromContent($fileContent, $fileName);
        }
    }

    /**
     * Method untuk menguji koneksi API sebelum digunakan
     */
    public function testApiConnection() {
        try {
            // Check first if API key is valid format
            if (empty($this->apiKey) || strlen($this->apiKey) < 20) {
                error_log("API key is invalid format - too short or missing");
                return false;
            }

            $testPrompt = "Hanya uji koneksi. Balas dengan 'TERHUBUNG' jika API berfungsi baik.";
            $result = $this->sendRequest($testPrompt, null, 100, 0.1);

            // Periksa apakah respons berisi kata kunci yang menunjukkan koneksi berhasil
            $lowerResult = strtolower($result);
            if (strpos($lowerResult, 'terhubung') !== false ||
                strpos($lowerResult, 'berhasil') !== false ||
                strpos($lowerResult, 'connected') !== false ||
                strpos($lowerResult, 'api') !== false) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("API Connection Test Failed: " . $e->getMessage());
            // Don't return false immediately, instead check if it's a 401 error which we handle differently
            if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), 'Authentication failed') !== false) {
                error_log("Authentication error detected - API key is invalid");
            }
            return false;
        }
    }

    /**
     * Method untuk mengecek apakah API dapat digunakan tanpa mengirim permintaan lengkap
     */
    public function isApiFunctional() {
        // Cek apakah API key dan URL sudah terdefinisi
        if (!defined('OPENROUTER_API_KEY') || !defined('OPENROUTER_BASE_URL')) {
            error_log("API configuration not found");
            return false;
        }

        if (empty(OPENROUTER_API_KEY) || empty(OPENROUTER_BASE_URL)) {
            error_log("API key or base URL not set");
            return false;
        }

        // Jika API key valid, lakukan test connection
        return $this->testApiConnection();
    }

    /**
     * Method untuk mengirim pesan ke AI dan mendapatkan respons
     */
    public function sendMessage($prompt, $model = null, $maxTokens = 2048, $temperature = 0.7) {
        try {
            // Validasi input
            if (empty($prompt)) {
                throw new Exception("Prompt tidak boleh kosong");
            }

            // Coba test koneksi jika belum diinisialisasi
            static $apiConnected = null;
            if ($apiConnected === null) {
                $apiConnected = $this->testApiConnection();
            }

            // Cek koneksi API sebelum mengirim permintaan
            if (!$apiConnected) {
                error_log("API tidak dapat diakses. Menggunakan fallback response.");
                return $this->getFallbackResponse($prompt);
            }

            return $this->sendRequest($prompt, $model, $maxTokens, $temperature);
        } catch (Exception $e) {
            error_log("AIHandler sendMessage Error: " . $e->getMessage());
            // Log lebih detail tentang error
            error_log("Prompt yang dikirim (sebagian): " . substr($prompt, 0, 200) . "...");
            error_log("Error trace: " . $e->getTraceAsString());

            // Log informasi API untuk debugging
            error_log("OpenRouter API Configuration Check:");
            error_log("- API Key set: " . (defined('OPENROUTER_API_KEY') ? 'YES' : 'NO'));
            error_log("- API Key length: " . (defined('OPENROUTER_API_KEY') ? strlen(OPENROUTER_API_KEY) : 'NOT SET'));
            error_log("- API Base URL: " . (defined('OPENROUTER_BASE_URL') ? OPENROUTER_BASE_URL : 'NOT SET'));
            error_log("- Default Model: " . (defined('OPENROUTER_DEFAULT_MODEL') ? OPENROUTER_DEFAULT_MODEL : 'NOT SET'));

            // Jika error adalah otentikasi (401), gunakan fallback daripada melempar exception
            if (strpos($e->getMessage(), 'HTTP error: 401') !== false ||
                strpos($e->getMessage(), '401') !== false ||
                strpos($e->getMessage(), 'Authentication failed') !== false) {
                error_log("Otentikasi API gagal. API Key mungkin tidak valid atau kadaluarsa. Menggunakan fallback.");
                return $this->getFallbackResponse($prompt);
            } elseif (strpos($e->getMessage(), 'timeout') !== false || stripos($e->getMessage(), 'connection') !== false) {
                error_log("Koneksi timeout atau terputus. Cek koneksi internet Anda. Menggunakan fallback.");
                return $this->getFallbackResponse($prompt);
            } elseif (strpos($e->getMessage(), '429') !== false) {
                error_log("Rate limit exceeded. Menggunakan fallback.");
                return $this->getFallbackResponse($prompt);
            } elseif (strpos($e->getMessage(), '404') !== false) {
                error_log("Endpoint API tidak ditemukan. Menggunakan fallback.");
                return $this->getFallbackResponse($prompt);
            } elseif (strpos($e->getMessage(), 'cURL error') !== false) {
                error_log("cURL error ditemukan. Menggunakan fallback.");
                return $this->getFallbackResponse($prompt);
            }

            // Untuk error lainnya, kembalikan fallback juga daripada melempar exception
            error_log("Kesalahan umum terjadi, mengembalikan fallback response.");
            return $this->getFallbackResponse($prompt);
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
            error_log("AIHandler analyzeText Error: " . $e->getMessage());

            // Jika error adalah otentikasi (401), tambahkan log khusus
            if (strpos($e->getMessage(), 'HTTP error: 401') !== false) {
                error_log("Otentikasi API gagal di analyzeText. API Key mungkin tidak valid.");
            }

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

    /**
     * Method untuk memberikan respons fallback ketika API tidak tersedia
     */
    public function getFallbackResponse($prompt) {
        // Tanggapi pertanyaan pendidikan dengan informasi yang lebih bermanfaat dan terstruktur
        $prompt_lower = strtolower($prompt);

        // Klasifikasikan jenis pertanyaan
        if (preg_match('/(matematika|fungsi|kuadrat|aljabar|trigonometri|kalkulus|limit|turunan|integral|persamaan|pertidaksamaan|logaritma|eksponen|statistika|peluang|vektor|matriks|barisan|deret)/', $prompt_lower)) {
            // Jika pertanyaan spesifik tentang matematika
            if (preg_match('/(fungsi kuadrat|parabola)/', $prompt_lower)) {
                return "Fungsi kuadrat adalah fungsi matematika yang memiliki bentuk umum f(x) = ax² + bx + c, di mana a ≠ 0. Grafik fungsi kuadrat berupa parabola yang terbuka ke atas (jika a > 0) atau ke bawah (jika a < 0). Titik puncak parabola dapat ditemukan dengan rumus x = -b/2a.";
            } elseif (preg_match('/(persamaan kuadrat|rumus abc|quadratic)/', $prompt_lower)) {
                return "Persamaan kuadrat memiliki bentuk umum ax² + bx + c = 0. Solusi persamaan ini dapat dicari menggunakan rumus kuadrat: x = (-b ± √(b² - 4ac)) / 2a. Diskriminan (b² - 4ac) menentukan jenis akar-akar persamaan.";
            } elseif (preg_match('/(turunan|diferensial)/', $prompt_lower)) {
                return "Turunan adalah konsep dalam kalkulus yang menggambarkan laju perubahan suatu fungsi terhadap variabelnya. Turunan dari fungsi f(x) dinyatakan sebagai f'(x) atau df/dx. Contoh: turunan dari x² adalah 2x.";
            } else {
                return "Saya membantu Anda dengan materi Matematika. Fungsi kuadrat, misalnya, memiliki bentuk umum f(x) = ax² + bx + c. Silakan beri tahu saya topik matematika spesifik yang ingin Anda pelajari agar saya bisa memberikan penjelasan yang lebih detail.";
            }
        }
        elseif (preg_match('/(fisika|newton|gaya|usaha|energi|momentum|gelombang|cahaya|listrik|magnet|arus|tekanan|suhu|kalor|termal|optik|elektromagnetik|kinematika|dinamika|thermodynamics)/', $prompt_lower)) {
            // Jika pertanyaan spesifik tentang fisika
            if (preg_match('/(hukum newton|newton.s laws)/', $prompt_lower)) {
                return "Hukum Newton tentang gerak terdiri dari tiga hukum: (1) Hukum Inersia - benda tetap diam atau bergerak lurus beraturan jika resultan gaya nol; (2) Hukum Percepatan - F = ma; (3) Hukum Aksi-Reaksi - setiap aksi ada reaksi yang sama besar dan berlawanan arah.";
            } elseif (preg_match('/(energi|usaha|work)/', $prompt_lower)) {
                return "Energi adalah kemampuan untuk melakukan usaha. Energi kinetik (Ek = ½mv²) adalah energi benda bergerak, sedangkan energi potensial (Ep = mgh) adalah energi akibat posisi. Usaha (W = F·s) adalah hasil kali gaya dan perpindahan.";
            } else {
                return "Saya siap membantu Anda memahami konsep-konsep Fisika. Hukum Newton, misalnya, menjelaskan hubungan antara gaya yang bekerja pada benda dan geraknya. Silakan ajukan pertanyaan spesifik Anda.";
            }
        }
        elseif (preg_match('/(kimia|asam|basa|reaksi|garam|larutan|molekul|atom|ion|elektron|proton|neutron|stoikiometri|redoks|elektrokimia)/', $prompt_lower)) {
            // Jika pertanyaan spesifik tentang kimia
            if (preg_match('/(asam basa|ph|ph scale)/', $prompt_lower)) {
                return "Menurut teori Arrhenius, asam adalah zat yang menghasilkan ion H⁺ dalam larutan, sedangkan basa menghasilkan ion OH⁻. Skala pH (0-14) mengukur keasaman/kebasaan larutan: pH < 7 (asam), pH = 7 (netral), pH > 7 (basa).";
            } elseif (preg_match('/(reaksi kimia|chemical reaction)/', $prompt_lower)) {
                return "Reaksi kimia adalah proses perubahan zat-zat pereaksi menjadi zat-zat hasil reaksi. Contoh: 2H₂ + O₂ → 2H₂O. Reaksi harus setara, artinya jumlah atom di ruas kiri dan kanan harus sama.";
            } else {
                return "Saya dapat membantu Anda memahami konsep-konsep Kimia. Seperti pH yang mengukur keasaman larutan, atau struktur atom yang terdiri dari proton, neutron, dan elektron. Apa yang ingin Anda pelajari lebih lanjut?";
            }
        }
        elseif (preg_match('/(biologi|sel|mikroorganisme|dna|rna|metabolisme|respirasi|fotosintesis|evolusi|ekosistem|organ|jaringan|sistem|tubuh|genetika|mikroba|virus|bakteri)/', $prompt_lower)) {
            // Jika pertanyaan spesifik tentang biologi
            if (preg_match('/(fotosintesis|photosynthesis)/', $prompt_lower)) {
                return "Fotosintesis adalah proses pembuatan makanan pada tumbuhan dengan menggunakan energi cahaya matahari. Reaksi umum: 6CO₂ + 6H₂O + cahaya → C₆H₁₂O₆ + 6O₂. Terjadi di kloroplas, menghasilkan glukosa dan oksigen.";
            } elseif (preg_match('/(sel|cell)/', $prompt_lower)) {
                return "Sel adalah unit struktural dan fungsional terkecil makhluk hidup. Ada dua jenis utama: sel prokariotik (tidak memiliki membran inti) dan sel eukariotik (memiliki membran inti). Sel terdiri dari membran plasma, sitoplasma, dan inti sel.";
            } else {
                return "Saya membantu Anda memahami konsep Biologi. Sel sebagai unit dasar kehidupan, fotosintesis sebagai proses pembuatan makanan tumbuhan, atau sistem organ tubuh manusia. Apa yang ingin Anda pelajari?";
            }
        }
        elseif (preg_match('/(berapa|berapa nilai|berapa hasil|hitung|tentukan|carilah|berapa jumlah|berapa besar|berapa banyak|berapa tinggi|berapa jauh|berapa cepat)/', $prompt_lower)) {
            // Jika pertanyaan hitungan matematika
            if (preg_match('/(2 \+ 2|dua ditambah dua|2 ditambah 2)/', $prompt_lower)) {
                return "2 + 2 = 4. Dalam matematika, penjumlahan adalah operasi dasar yang menggabungkan dua atau lebih bilangan untuk menghasilkan jumlah totalnya.";
            } elseif (preg_match('/(luas lingkaran|area of circle)/', $prompt_lower)) {
                return "Luas lingkaran dihitung dengan rumus L = πr², di mana r adalah jari-jari lingkaran dan π (pi) ≈ 3.14159. Contoh: jika jari-jari 7 cm, maka L = π × 7² = 49π ≈ 153.94 cm².";
            } else {
                return "Saya bisa membantu Anda menyelesaikan soal hitungan. Untuk soal matematika atau sains, biasanya perlu mengidentifikasi: (1) apa yang diketahui, (2) apa yang ditanyakan, (3) rumus atau prinsip yang digunakan, (4) langkah-langkah penyelesaian. Silakan beri tahu soal lengkapnya agar saya bisa bantu dengan tepat.";
            }
        }
        elseif (preg_match('/(apa itu|jelaskan|definisi|pengertian|artinya|makna|penjelasan|apa pengertian)/', $prompt_lower)) {
            // Jika permintaan definisi
            $cleanQuestion = preg_replace('/(apa itu|jelaskan|definisi|pengertian|artinya|makna|penjelasan|apa pengertian)\s*/i', '', $prompt);
            if (!empty($cleanQuestion)) {
                return "Terima kasih atas pertanyaan Anda tentang '{$cleanQuestion}'. Dalam konteks pendidikan, '{$cleanQuestion}' adalah konsep penting yang perlu dipahami dengan baik. Konsep ini memiliki beberapa aspek utama, yaitu: (1) definisi dasar, (2) karakteristik utama, (3) aplikasi atau contoh nyata, dan (4) hubungan dengan konsep lain. Apakah Anda ingin saya jelaskan lebih rinci tentang aspek-aspek tersebut?";
            } else {
                return "Saya siap menjelaskan konsep pendidikan yang Anda tanyakan. Setiap konsep penting memiliki definisi, karakteristik, contoh, dan aplikasi dalam kehidupan sehari-hari. Silakan ajukan pertanyaan spesifik tentang materi yang ingin Anda pelajari.";
            }
        }
        elseif (preg_match('/(halo|hai|hello|selamat pagi|selamat siang|selamat sore|siapa kamu|perkenalan)/', $prompt_lower)) {
            return "Halo! Saya Sinar Ilmu, asisten pendidikan AI yang siap membantu Anda belajar. Saya dapat memberikan penjelasan materi pelajaran, membantu menyelesaikan soal, dan memberikan contoh konsep dari berbagai mata pelajaran. Silakan ajukan pertanyaan Anda!";
        }
        else {
            // Untuk pertanyaan umum
            return "Halo! Saya Sinar Ilmu, asisten pendidikan AI. Saat ini sistem koneksi ke server AI sedang tidak aktif, tetapi saya tetap siap membantu Anda belajar. Saya dapat menjelaskan konsep-konsep penting dalam berbagai mata pelajaran seperti Matematika, Fisika, Kimia, dan Biologi. Silakan ajukan pertanyaan Anda secara spesifik, dan saya akan berikan penjelasan yang bermanfaat.";
        }
    }

    /**
     * Method untuk mengirim pesan ke AI dan mendapatkan respons
     */
    public function getResponse($prompt) {
        return $this->sendRequest($prompt, null, 3000, 0.3);
    }

    /**
     * Method untuk menghasilkan respons dari isi konten file jika API tidak tersedia
     */
    public function generateResponseFromContent($fileContent, $fileName) {
        // Jika API tidak bisa diakses, buat respons berdasarkan isi konten file
        $cleanContent = strip_tags($fileContent);
        $cleanContent = html_entity_decode($cleanContent);
        $cleanContent = preg_replace('/\s+/', ' ', $cleanContent); // Normalisasi spasi

        // Pisahkan konten menjadi kalimat untuk diekstrak konsepnya
        $sentences = preg_split('/[.!?]+/', $cleanContent);
        $sentences = array_filter($sentences, function($sentence) {
            $sentence = trim($sentence);
            return strlen($sentence) > 25 && !preg_match('/(©|copyright|all rights reserved|halaman|page|sumber|source|gambar|tabel|pustaka|referensi)/i', $sentence);
        });

        $sentences = array_values($sentences); // Re-index array

        // Buat ringkasan berdasarkan isi file
        $summary = "File " . htmlspecialchars($fileName) . " berisi materi pendidikan tentang berbagai konsep penting dalam bidang terkait.";
        if (count($sentences) > 0) {
            $summary = substr($sentences[0], 0, 150) . (strlen($sentences[0]) > 150 ? '...' : '');
        }

        // Buat penjabaran materi
        $detailedExplanation = "File ini membahas berbagai konsep penting.";
        $validTopics = array_slice($sentences, 0, min(5, count($sentences)));
        foreach ($validTopics as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) > 30) {
                $detailedExplanation .= " Di antaranya: " . substr($sentence, 0, 80) . "... ";
            }
        }

        // Buat soal dari isi konten file - dengan fokus pada isi spesifik
        $questions = [];
        $validSentences = array_slice($sentences, 0, 15); // Ambil lebih banyak kalimat untuk variasi soal

        // Identifikasi topik dari konten file
        $topik = 'Materi Umum';
        $topik_keywords = [
            'matematika' => ['fungsi', 'kuadrat', 'aljabar', 'geometri', 'trigonometri', 'kalkulus', 'persamaan', 'rumus'],
            'fisika' => ['newton', 'gaya', 'energi', 'kecepatan', 'percepatan', 'listrik', 'magnet', 'gelombang', 'cahaya'],
            'kimia' => ['atom', 'molekul', 'reaksi', 'asam', 'basa', 'larutan', 'ion', 'elektron', 'proton'],
            'biologi' => ['sel', 'dna', 'metabolisme', 'fotosintesis', 'ekosistem', 'organ', 'jaringan', 'enzim']
        ];

        foreach ($topik_keywords as $nama_topik => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($fileContent, $keyword) !== false) {
                    $topik = $nama_topik;
                    break 2;
                }
            }
        }

        // Buat soal dari kalimat-kalimat penting
        foreach ($validSentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) < 40 || strlen($sentence) > 300) continue; // Batasi panjang maksimal

            // Bersihkan karakter aneh sebelum proses
            $cleanSentence = preg_replace('/[^\w\s\p{L}\p{N}\p{P}\p{M}().,:;?!-]/u', ' ', $sentence);
            $cleanSentence = preg_replace('/\s+/', ' ', $cleanSentence); // Normalisasi spasi
            $cleanSentence = trim($cleanSentence);

            // Pastikan hanya proses kalimat yang memiliki makna
            if (strlen($cleanSentence) < 40) continue;

            // Cek apakah mengandung kata kunci untuk membuat soal spesifik dari file
            if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi|rumus|bentuk|contoh\s*tentang|karakteristik|ciri|sifat|fungsi|manfaat|proses|langkah|prinsip|teori|konsep)/i', $cleanSentence)) {
                $questionText = '';

                // Buat soal spesifik berdasarkan isi file
                if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi)/i', $cleanSentence) && preg_match('/(.+?)\s+(adalah|merupakan|yaitu)/i', $cleanSentence, $matches)) {
                    // Jika kalimat mengandung definisi spesifik
                    $konsep = trim($matches[1]);
                    $questionText = "Apa yang dimaksud dengan {$konsep} menurut isi file ini?";
                } elseif (preg_match('/(rumus|bentuk)\s+(.+?)\s+adalah/i', $cleanSentence, $matches)) {
                    // Jika berisi rumus
                    $konsep = trim($matches[2]);
                    $questionText = "Apa rumus dari {$konsep} menurut isi file ini?";
                } elseif (preg_match('/(ciri|sifat|karakteristik)/i', $cleanSentence)) {
                    // Jika berisi ciri/sifat
                    $questionText = "Apa saja ciri-ciri atau sifat dari konsep dalam kalimat: '{$cleanSentence}'?";
                } elseif (preg_match('/(langkah|proses)/i', $cleanSentence)) {
                    // Jika berisi proses/langkah
                    $questionText = "Apa langkah atau proses yang disebutkan dalam: '{$cleanSentence}'?";
                } else {
                    // Format umum spesifik dari file
                    $shortSentence = preg_replace('/\s+/', ' ', substr($cleanSentence, 0, 60));
                    $questionText = "Apa yang dinyatakan tentang '{$topik}' dalam kalimat: '{$shortSentence}...'?";
                }

                // Buat pilihan jawaban spesifik dari isi file - pastikan relevan
                $words = explode(' ', $cleanSentence);
                $wordCount = count($words);

                // Pilihan A: Kalimat asli atau bagian awal kalimat
                $pilihanA = trim(implode(' ', array_slice($words, 0, min(8, $wordCount))));

                // Pilihan B: Bagian akhir kalimat
                $pilihanB = trim(implode(' ', array_slice($words, max(0, $wordCount-8), 8)));

                // Pilihan C: Bagian tengah kalimat
                $midStart = max(0, intval($wordCount/3));
                $midEnd = min($wordCount, $midStart + 8);
                $pilihanC = trim(implode(' ', array_slice($words, $midStart, $midEnd - $midStart)));

                // Pilihan D: Buat pilihan yang terkait dengan topik tapi berbeda
                $pilihanD = "Konsep lain dalam bidang {$topik} yang tidak disebutkan dalam file";

                // Validasi dan perbaiki pilihan jawaban
                $pilihanA = !empty($pilihanA) ? $pilihanA . '...' : 'Pilihan jawaban dari isi file';
                $pilihanB = !empty($pilihanB) ? $pilihanB . '...' : 'Opsi terkait dari file';
                $pilihanC = !empty($pilihanC) ? $pilihanC . '...' : 'Alternatif dari isi file';
                $pilihanD = !empty($pilihanD) ? $pilihanD : 'Jawaban umum terkait topik';

                $questions[] = [
                    'soal' => trim($questionText),
                    'pilihan' => [
                        'a' => trim($pilihanA),
                        'b' => trim($pilihanB),
                        'c' => trim($pilihanC),
                        'd' => trim($pilihanD)
                    ],
                    'kunci_jawaban' => 'a' // Jawaban utama dari isi file sebenarnya
                ];
            }

            if (count($questions) >= 10) {
                break;
            }
        }

        // Jika tidak cukup soal dari pola spesifik di atas, buat soal umum tapi tetap dari file
        if (count($questions) < 10) {
            foreach ($validSentences as $sentence) {
                if (count($questions) >= 10) break;

                // Bersihkan kalimat sebelum diproses
                $cleanSentence = preg_replace('/[^\w\s\p{L}\p{N}\p{P}\p{M}().,:;?!-]/u', ' ', $sentence);
                $cleanSentence = preg_replace('/\s+/', ' ', $cleanSentence);
                $cleanSentence = trim($cleanSentence);

                if (strlen($cleanSentence) < 50) continue;

                // Buat soal dengan konteks dari file
                $questionText = "Apa isi pokok dari kalimat berikut yang diambil dari materi {$topik}: '{$cleanSentence}'?";

                // Buat pilihan jawaban yang relevan dengan konteks file
                $words = explode(' ', $cleanSentence);
                $wordCount = count($words);

                // Pilihan A: Inti dari kalimat
                $pilihanA = trim(implode(' ', array_slice($words, 0, min(6, $wordCount))));

                // Pilihan B: Inti dari bagian akhir
                $pilihanB = trim(implode(' ', array_slice($words, max(0, $wordCount-6), 6)));

                // Pilihan C: Gabungan awal dan akhir
                $awal = implode(' ', array_slice($words, 0, min(3, intval($wordCount/2))));
                $akhir = implode(' ', array_slice($words, max(0, $wordCount-3), 3));
                $pilihanC = trim("{$awal}... {$akhir}");

                // Pilihan D: Jawaban umum terkait topik
                $pilihanD = "Konsep umum dalam bidang {$topik}";

                // Validasi pilihan
                $pilihanA = !empty($pilihanA) ? $pilihanA . '...' : 'Pilihan dari isi file';
                $pilihanB = !empty($pilihanB) ? $pilihanB . '...' : 'Opsi relevan dari file';
                $pilihanC = !empty($pilihanC) ? $pilihanC . '...' : 'Gabungan isi file';
                $pilihanD = !empty($pilihanD) ? $pilihanD : 'Konsep umum';

                $questions[] = [
                    'soal' => $questionText,
                    'pilihan' => [
                        'a' => trim($pilihanA),
                        'b' => trim($pilihanB),
                        'c' => trim($pilihanC),
                        'd' => trim($pilihanD)
                    ],
                    'kunci_jawaban' => 'a'
                ];
            }
        }

        // Format respons sesuai dengan format standar
        $response = "---ANALYSIS_START---\n";
        $response .= "Ringkasan:\n" . $summary . "\n\n";
        $response .= "Penjabaran Materi:\n" . $detailedExplanation . "\n";
        $response .= "---ANALYSIS_END---\n\n";
        $response .= "---QUESTIONS_START---\n";
        $response .= json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        $response .= "---QUESTIONS_END---\n";

        return $response;
    }
}
?>
