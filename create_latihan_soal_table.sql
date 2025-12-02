CREATE TABLE latihan_soal (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    analysis_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    question_text TEXT NOT NULL,
    options_json JSON NOT NULL,
    correct_answer VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (analysis_id) REFERENCES analisis_ai(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);