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

        // Retry mekanisme untuk permintaan API
        $maxRetries = 3;
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
                CURLOPT_TIMEOUT => (defined('OPENROUTER_TIMEOUT') ? OPENROUTER_TIMEOUT : 120), // Tingkatkan default timeout
                CURLOPT_CONNECTTIMEOUT => 30, // Timeout untuk koneksi
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
                    $lastException = new Exception('HTTP error: ' . $http_code . ' (Otentikasi gagal). Response: ' . $response);
                    break; // Jangan retry untuk error 401
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

        // Buat prompt yang sangat fokus pada isi file
        $prompt = "Anda adalah AI analisis dan pembuat soal spesifik. BACA SECARA SANGAT TELITI isi file berikut dan buat 10 soal pilihan ganda BERDASARKAN ISI FILE INI.\n\n";

        $prompt .= "NAMA FILE: " . htmlspecialchars($fileName) . "\n\n";

        $prompt .= "ISI FILE UNTUK ANDA BACA SECARA LENGKAP:\n";
        $prompt .= $fileContent . "\n\n";

        $prompt .= "LANGKAH PEMROSESAN WAJIB:\n";
        $prompt .= "1. BACA dan PAHAMI isi file secara menyeluruh\n";
        $prompt .= "2. IDENTIFIKASI topik utama: mata pelajaran dan subtopiknya\n";
        $prompt .= "3. TEMUKAN konsep-konsep penting, definisi, rumus, atau kalimat kunci dalam isi file\n";
        $prompt .= "4. BUAT soal berdasarkan konteks LANGSUNG dari isi file\n";
        $prompt .= "5. BUAT pilihan jawaban juga berdasarkan isi file\n\n";

        $prompt .= "BERIKUT CONTOH PROSES YANG TEPAT:\n";
        $prompt .= "Jika file berisi: 'Asam adalah senyawa yang dalam air melepaskan ion H+'\n";
        $prompt .= "Maka soalnya: 'Apa yang dimaksud dengan asam menurut definisi Arrhenius?'\n";
        $prompt .= "Pilihan: a) Senyawa yang melepaskan ion H+ dalam air, b) Senyawa yang menerima ion H+, c) Senyawa yang mengandung atom hidrogen, d) Senyawa yang berasa asam\n\n";

        $prompt .= "Jika file berisi: 'Fungsi kuadrat memiliki bentuk umum f(x) = ax² + bx + c'\n";
        $prompt .= "Maka soalnya: 'Apa bentuk umum dari fungsi kuadrat?'\n";
        $prompt .= "Pilihan: a) f(x) = ax² + bx + c, b) f(x) = ax + b, c) f(x) = ax³ + bx² + cx + d, d) f(x) = aˣ\n\n";

        $prompt .= "PERATURAN SANGAT TEGAS:\n";
        $prompt .= "- SOAL HARUS MEMILIKI SUMBER LANGSUNG DARI ISI FILE\n";
        $prompt .= "- JANGAN BUAT SOAL UMUM, HARUS SPESIFIK DARI ISI FILE\n";
        $prompt .= "- GUNAKAN KATA-KATA ATAU KALIMAT LANGSUNG DARI FILE\n";
        $prompt .= "- PILIHAN JAWABAN JUGA HARUS BERDASARKAN ISI FILE\n";
        $prompt .= "- TOPIK SOAL SESUAI DENGAN MATA PELAJARAN DALAM FILE\n";
        $prompt .= "- BUAT TEPAT 10 SOAL BERKUALITAS TINGGI\n\n";

        $prompt .= "HASILKAN DALAM FORMAT INI SAJA:\n\n";

        $prompt .= "---ANALYSIS_START---\n";
        $prompt .= "Ringkasan:\n[Ringkasan isi file berdasarkan topik utama dalam file]\n\n";
        $prompt .= "Penjabaran Materi:\n[Penjelasan konsep-konsep utama dalam file berdasarkan isi sebenarnya file]\n";
        $prompt .= "---ANALYSIS_END---\n\n";

        $prompt .= "---QUESTIONS_START---\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 1\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 2\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 3\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 4\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 5\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 6\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 7\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 8\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 9\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  },\n";
        $prompt .= "  {\n";
        $prompt .= "    \"soal\": \"Soal berdasarkan isi file spesifik nomor 10\",\n";
        $prompt .= "    \"pilihan\": {\n";
        $prompt .= "      \"a\": \"Pilihan jawaban dari isi file a\",\n";
        $prompt .= "      \"b\": \"Pilihan jawaban dari isi file b\",\n";
        $prompt .= "      \"c\": \"Pilihan jawaban dari isi file c\",\n";
        $prompt .= "      \"d\": \"Pilihan jawaban dari isi file d\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"kunci_jawaban\": \"a\"\n";
        $prompt .= "  }\n";
        $prompt .= "]\n";
        $prompt .= "---QUESTIONS_END---\n\n";

        $prompt .= "CATATAN: ISI FILE DI ATAS ADALAH SUMBER UTAMA PEMBUATAN SOAL. FOKUSLAH PADA KONTEKS LANGSUNG ISI FILE.";

        return $this->sendRequest($prompt, null, 5000, 0.1);
    }

    /**
     * Method untuk menguji koneksi API sebelum digunakan
     */
    public function testApiConnection() {
        try {
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
        // Coba test koneksi jika belum diinisialisasi
        static $apiConnected = null;
        if ($apiConnected === null) {
            $apiConnected = $this->testApiConnection();
            if (!$apiConnected) {
                error_log("API tidak dapat diakses. Menggunakan fallback response.");
                return $this->getFallbackResponse($prompt);
            }
        }

        try {
            // Validasi input
            if (empty($prompt)) {
                throw new Exception("Prompt tidak boleh kosong");
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
            error_log("- API Base URL: " . (defined('OPENROUTER_BASE_URL') ? OPENROUTER_BASE_URL : 'NOT SET'));
            error_log("- Default Model: " . (defined('OPENROUTER_DEFAULT_MODEL') ? OPENROUTER_DEFAULT_MODEL : 'NOT SET'));

            // Jika error adalah otentikasi (401), kita tetap lempar exception
            if (strpos($e->getMessage(), 'HTTP error: 401') !== false || strpos($e->getMessage(), '401') !== false) {
                error_log("Otentikasi API gagal. API Key mungkin tidak valid atau kadaluarsa.");
            } elseif (strpos($e->getMessage(), 'timeout') !== false || stripos($e->getMessage(), 'connection') !== false) {
                error_log("Koneksi timeout atau terputus. Cek koneksi internet Anda.");
            } elseif (strpos($e->getMessage(), '429') !== false) {
                error_log("Rate limit exceeded - terlalu banyak permintaan ke API dalam waktu singkat.");
            } elseif (strpos($e->getMessage(), '404') !== false) {
                error_log("Endpoint API tidak ditemukan. Cek URL base API.");
            } elseif (strpos($e->getMessage(), 'cURL error') !== false) {
                error_log("cURL error ditemukan. Cek konfigurasi koneksi dan firewall.");
            }

            // Lempar kembali exception untuk ditangani di level aplikasi
            throw $e;
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
        // Untuk prompt yang berkaitan dengan pelajaran/pendidikan, berikan respons yang sesuai
        if (stripos($prompt, 'matematika') !== false ||
            stripos($prompt, 'fisika') !== false ||
            stripos($prompt, 'kimia') !== false ||
            stripos($prompt, 'biologi') !== false ||
            stripos($prompt, 'pelajaran') !== false ||
            stripos($prompt, 'belajar') !== false ||
            stripos($prompt, 'materi') !== false ||
            stripos($prompt, 'soal') !== false ||
            stripos($prompt, 'latihan') !== false) {
            return "Saat ini sistem AI sedang tidak dapat diakses. Silakan coba unggah file materi terlebih dahulu agar sistem bisa memberikan penjelasan berdasarkan materi yang telah diunggah.";
        } else {
            return "Saat ini sistem AI sedang tidak dapat diakses. Silakan coba lagi nanti.";
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

        // Buat soal dari isi konten file
        $questions = [];
        $validSentences = array_slice($sentences, 0, 10); // Ambil maksimal 10 kalimat untuk dibuat soal

        foreach ($validSentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) < 40 || strlen($sentence) > 150) continue;

            // Cek apakah mengandung kata kunci untuk membuat soal
            if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi|fungsi|cara|langkah|proses|manfaat|tujuan|hasil|bentuk|rumus|contoh)/i', $sentence)) {
                $questionText = '';
                if (preg_match('/(adalah|merupakan|yaitu|pengertian|definisi)/i', $sentence)) {
                    // Format soal definisi
                    $shortSentence = substr($sentence, 0, 60);
                    $questionText = 'Apa yang dimaksud dengan ' . $shortSentence . '?';
                } else if (preg_match('/(fungsi|manfaat|tujuan)/i', $sentence)) {
                    // Format soal fungsi/manfaat
                    $shortSentence = substr($sentence, 0, 50);
                    $questionText = 'Apa fungsi/manfaat dari ' . $shortSentence . '?';
                } else if (preg_match('/(cara|langkah|proses)/i', $sentence)) {
                    // Format soal proses/langkah
                    $shortSentence = substr($sentence, 0, 40);
                    $questionText = 'Apa langkah/proses dari ' . $shortSentence . '?';
                } else {
                    // Format umum
                    $questionText = 'Apa yang dapat dipelajari dari pernyataan: "' . substr($sentence, 0, 50) . '..."?';
                }

                // Buat pilihan jawaban dari konten yang relevan
                $words = explode(' ', $sentence);
                $wordCount = count($words);

                $pilihanA = implode(' ', array_slice($words, 0, min(4, $wordCount))) . '...';
                $pilihanB = implode(' ', array_slice($words, max(0, $wordCount-4), 4)) . '...';
                $pilihanC = implode(' ', array_slice($words, max(0, intval($wordCount/2)), 4)) . '...';
                $pilihanD = 'Konsep yang berkaitan dengan: ' . substr($sentence, 0, 30) . '...';

                $questions[] = [
                    'soal' => $questionText,
                    'pilihan' => [
                        'a' => $pilihanA,
                        'b' => $pilihanB,
                        'c' => $pilihanC,
                        'd' => $pilihanD
                    ],
                    'kunci_jawaban' => 'a' // Jawaban default
                ];
            }

            if (count($questions) >= 10) {
                break;
            }
        }

        // Jika tidak cukup soal dari pola di atas, buat soal umum
        if (count($questions) < 10) {
            foreach ($validSentences as $sentence) {
                if (count($questions) >= 10) break;

                $sentence = trim($sentence);
                if (strlen($sentence) < 50) continue;

                $questionText = 'Apa yang dimaksud dengan konsep dalam kalimat: "' . substr($sentence, 0, 40) . '..."?';

                // Buat pilihan jawaban dari fragmen kalimat
                $fragments = str_split($sentence, max(1, intval(strlen($sentence)/4)));
                $pilihanA = isset($fragments[0]) ? substr($fragments[0], 0, 50) . '...' : 'Opsi A';
                $pilihanB = isset($fragments[1]) ? substr($fragments[1], 0, 50) . '...' : 'Opsi B';
                $pilihanC = isset($fragments[2]) ? substr($fragments[2], 0, 50) . '...' : 'Opsi C';
                $pilihanD = 'Jawaban yang benar';

                $questions[] = [
                    'soal' => $questionText,
                    'pilihan' => [
                        'a' => $pilihanA,
                        'b' => $pilihanB,
                        'c' => $pilihanC,
                        'd' => $pilihanD
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
