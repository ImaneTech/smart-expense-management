-- ============================================
-- 1. Création de la base de données
-- ============================================
CREATE DATABASE IF NOT EXISTS gestion_frais_db
USE gestion_frais_db;

-- ============================================
-- 2. Création de la table "users"
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('employe', 'manager', 'admin') NOT NULL,
    department VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

USE gestion_frais_db;
ALTER TABLE users 
ADD COLUMN reset_token VARCHAR(255) NULL,
ADD COLUMN reset_expires DATETIME NULL;

USE gestion_frais_db;
DESCRIBE users;


-- Mot de passe "123456" haché pour tous : $2y$10$abcdefghijklmnopqrstuv
-- Remplacez le hash par un vrai hash généré par votre signup si besoin

INSERT INTO users (first_name, last_name, email, password, role, department, phone) VALUES 
('Jean', 'Employé', 'employe@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employe', 'IT', '0600000001'),
('Sarah', 'Manager', 'manager@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'HR', '0600000002'),
('Ali', 'Admin', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Finance', '0600000003');