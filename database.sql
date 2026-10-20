
CREATE DATABASE IF NOT EXISTS sima
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sima;

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    program_studi VARCHAR(100) NOT NULL,
    semester TINYINT UNSIGNED NOT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO mahasiswa (nim, nama, program_studi, semester)
VALUES
('2301001', 'Andi Pratama', 'Sistem Informasi', 3),
('2301002', 'Siti Rahma', 'Manajemen Informatika', 5),
('2301003', 'Budi Santoso', 'Sistem Informasi', 1);
