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