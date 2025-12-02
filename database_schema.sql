-- Database: dbsinarilmu

-- Membuat database jika belum ada
CREATE DATABASE IF NOT EXISTS dbsinarilmu;
USE dbsinarilmu;

-- Tabel users: menyimpan informasi pengguna dan admin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel upload_files: menyimpan metadata file yang diunggah oleh pengguna
CREATE TABLE upload_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    description TEXT,
    status ENUM('uploaded', 'processing', 'completed', 'failed') DEFAULT 'uploaded',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel analisis_ai: menyimpan hasil analisis AI dari file yang diunggah
CREATE TABLE analisis_ai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    user_id INT NOT NULL,
    ringkasan TEXT,
    penjabaran_materi LONGTEXT,
    topik_terkait JSON,
    tingkat_kesulitan ENUM('mudah', 'sedang', 'sulit'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES upload_files(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel bank_soal_ai: menyimpan soal-soal yang di-generate oleh AI
CREATE TABLE bank_soal_ai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    analisis_id INT,
    user_id INT NOT NULL,
    soal TEXT NOT NULL,
    pilihan_a TEXT,
    pilihan_b TEXT,
    pilihan_c TEXT,
    pilihan_d TEXT,
    kunci_jawaban CHAR(1),
    pembahasan TEXT,
    tingkat_kesulitan ENUM('mudah', 'sedang', 'sulit'),
    topik VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (analisis_id) REFERENCES analisis_ai(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel hasil_soal_user: menyimpan hasil pengerjaan soal oleh pengguna
CREATE TABLE hasil_soal_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    soal_id INT NOT NULL,
    jawaban_user CHAR(1),
    status_jawaban ENUM('benar', 'salah'),
    nilai DECIMAL(5,2),
    waktu_pengerjaan INT, -- dalam detik
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (soal_id) REFERENCES bank_soal_ai(id) ON DELETE CASCADE
);

-- Tabel chat_ai: menyimpan riwayat percakapan dengan AI
CREATE TABLE chat_ai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pesan_pengguna TEXT NOT NULL,
    pesan_ai TEXT NOT NULL,
    topik_terkait VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel notifikasi: menyimpan notifikasi untuk pengguna
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    tipe ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    sudah_dibaca BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel log_aktivitas: menyimpan log aktivitas pengguna
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    aksi VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel materi_pelajaran: menyimpan materi pelajaran yang diunggah oleh admin
CREATE TABLE materi_pelajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    kelas ENUM('10', '11', '12') NOT NULL,
    mata_pelajaran ENUM('matematika', 'fisika', 'kimia', 'biologi', 'bahasa_indonesia', 'bahasa_inggris', 'sejarah', 'geografi', 'ekonomi', 'sosiologi', 'lainnya') NOT NULL,
    sub_topik VARCHAR(255),
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    created_by INT NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Membuat indeks untuk optimasi query
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_upload_files_user_id ON upload_files(user_id);
CREATE INDEX idx_upload_files_created_at ON upload_files(created_at);
CREATE INDEX idx_analisis_ai_file_id ON analisis_ai(file_id);
CREATE INDEX idx_bank_soal_ai_analisis_id ON bank_soal_ai(analisis_id);
CREATE INDEX idx_hasil_soal_user_user_id ON hasil_soal_user(user_id);
CREATE INDEX idx_chat_ai_user_id ON chat_ai(user_id);
CREATE INDEX idx_notifikasi_user_id ON notifikasi(user_id);
CREATE INDEX idx_log_aktivitas_user_id ON log_aktivitas(user_id);
CREATE INDEX idx_materi_pelajaran_kelas ON materi_pelajaran(kelas);
CREATE INDEX idx_materi_pelajaran_mata_pelajaran ON materi_pelajaran(mata_pelajaran);
CREATE INDEX idx_materi_pelajaran_created_by ON materi_pelajaran(created_by);

-- Membuat user admin default
INSERT INTO users (full_name, email, username, password, role) VALUES 
('Admin Sinar Ilmu', 'admin@sinarilmu.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password default: password (hanya untuk keperluan development)

-- Membuat beberapa contoh data pengguna
INSERT INTO users (full_name, email, username, password, role) VALUES 
('Budi Santoso', 'budi@example.com', 'budi123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Ani Lestari', 'ani@example.com', 'ani456', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Siti Nurhaliza', 'siti@example.com', 'siti789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');