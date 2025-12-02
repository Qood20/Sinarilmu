-- Create the materi_pelajaran table
CREATE TABLE IF NOT EXISTS materi_pelajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    kelas ENUM('10', '11', '12') NOT NULL,
    mata_pelajaran ENUM('matematika', 'fisika', 'kimia', 'biologi', 'bahasa_indonesia', 'bahasa_inggris', 'sejarah', 'geografi', 'ekonomi', 'sosiologi', 'lainnya') NOT NULL,
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

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_materi_pelajaran_kelas ON materi_pelajaran(kelas);
CREATE INDEX IF NOT EXISTS idx_materi_pelajaran_mata_pelajaran ON materi_pelajaran(mata_pelajaran);
CREATE INDEX IF NOT EXISTS idx_materi_pelajaran_created_by ON materi_pelajaran(created_by);