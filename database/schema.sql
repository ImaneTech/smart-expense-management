-- ============================================
-- SCRIPT SQL COMPLET ET CORRIGÉ (Avec USE DB)
-- ============================================

-- Suppression de l'ancienne BD si elle existe
DROP DATABASE IF EXISTS gestion_frais_db;

-- 1. Création de la base de données
CREATE DATABASE gestion_frais_db;

USE gestion_frais_db;
-- ============================================
-- 2. Création de la table "users" 
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('employe', 'manager', 'admin') NOT NULL,
    department VARCHAR(50) NOT NULL,
    
    -- CHAMP AJOUTÉ POUR LA GESTION MANAGER
    manager_id INT NULL, 
    
    reset_token VARCHAR(255) NULL,
    reset_expires DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- CLÉ ÉTRANGÈRE AJOUTÉE POUR LA GESTION MANAGER
    CONSTRAINT fk_user_manager FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------
-- INSERTION DE DONNEES   "USERS" -------
-----------------------------------------
USE gestion_frais_db;
INSERT INTO users (id, first_name, last_name, email, password, role, department, phone, manager_id) VALUES 
( 2,'Sarah', 'Manager', 'manager@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employe', 'HR', '0600000002', NULL);

USE gestion_frais_db;
INSERT INTO users (id, first_name, last_name, email, password, role, department, phone, manager_id) VALUES 
( 3,'Jean', 'Employé', 'employe@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employe', 'IT', '0600000001', 2),
( 4,'Ali', 'Admin', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employe', 'Finance', '0600000003', NULL);


-------------------------------------------
USE gestion_frais_db;
ALTER TABLE users AUTO_INCREMENT = 1;


---

USE gestion_frais_db;
-- ============================================
-- 3. Table des CATÉGORIES DE FRAIS
-- ============================================
CREATE TABLE categories_frais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE categories_frais
ADD COLUMN actif BOOLEAN NOT NULL DEFAULT 1;

USE gestion_frais_db;
-- Insertion des CATÉGORIES
INSERT INTO categories_frais (nom) VALUES 
('Transport'), ('Hébergement'), ('Restauration'), ('Carburant'), ('Divers');

---

USE gestion_frais_db;
-- ============================================
-- 4. Table des DEMANDES (Dossier global de mission)
-- ============================================
CREATE TABLE demande_frais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    objet_mission VARCHAR(255) NOT NULL,
    lieu_deplacement VARCHAR(150),
    date_depart DATE NOT NULL,
    date_retour DATE NOT NULL,
    statut ENUM('En attente', 'Validée Manager', 'Rejetée Manager', 'Approuvée Compta', 'Payée') DEFAULT 'En attente',
    manager_id_validation INT NULL,
    date_traitement DATETIME NULL,
    commentaire_manager TEXT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_demande_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_demande_manager FOREIGN KEY (manager_id_validation) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

---

USE gestion_frais_db;
-- ============================================
-- 5. Table des DÉTAILS (Lignes de dépenses)
-- ============================================
CREATE TABLE details_frais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    demande_id INT NOT NULL,
    categorie_id INT NOT NULL,
    date_depense DATE NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    description TEXT,
    justificatif_path VARCHAR(255),
    FOREIGN KEY (demande_id) REFERENCES demande_frais(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories_frais(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

---

USE gestion_frais_db;
-- ============================================
-- 6. Table HISTORIQUE (Traçabilité)
-- ============================================
CREATE TABLE historique_statuts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    demande_id INT NOT NULL,
    user_id INT NOT NULL, -- Qui a fait l'action
    ancien_statut VARCHAR(50),
    nouveau_statut VARCHAR(50),
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    commentaire TEXT,
    FOREIGN KEY (demande_id) REFERENCES demande_frais(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

---


CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- À qui s'adresse la notification : Employé, Manager ou Admin
    user_id_cible INT NOT NULL, 
    -- Lien vers la demande de frais concernée
    demande_id INT, 
    
    message VARCHAR(255) NOT NULL,
    -- URL de redirection pour cliquer sur la notification (ex: '/employe/demande_detail.php?id=123')
    lien_url VARCHAR(255), 
    
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- Statut : 0 = Non lue (unread), 1 = Lue (read)
    lue BOOLEAN NOT NULL DEFAULT 0,
    
    -- Clé étrangère vers la table users
    FOREIGN KEY (user_id_cible) REFERENCES users(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----------------------------------------------------------------------------------------------------------

USE gestion_frais_db;
-- ============================================
-- 7. Insertion des DEMANDES DE TEST
-- ============================================

-- Demande 1 (Employé ID 1: Jean Employé) - EN ATTENTE de Sarah (ID 2)
INSERT INTO demande_frais (user_id, objet_mission, lieu_deplacement, date_depart, date_retour, statut,manager_id)
VALUES (3, 'Réunion fournisseur A', 'Marseille', '2025-10-15', '2025-10-16', 'En attente',1);

USE gestion_frais_db;
-- Demande 2 (Employé ID 1: Jean Employé) - VALIDÉE par Sarah (ID 2)
INSERT INTO demande_frais (user_id, objet_mission, lieu_deplacement, date_depart, date_retour, statut, manager_id_validation, date_traitement)
VALUES (2, 'Visite Usine B', 'Lille', '2025-09-01', '2025-09-03', 'Validée Manager', 4, NOW());

USE gestion_frais_db;
-- Lignes de frais pour Demande 1
INSERT INTO details_frais (demande_id, categorie_id, date_depense, montant, description, justificatif_path)
VALUES 
    (1, 1, '2025-10-15', 150.00, 'Billet TGV A/R', '/justificatifs/d1_train.pdf'),
    (1, 2, '2025-10-15', 95.00, 'Nuitée hôtel', '/justificatifs/d1_hotel.pdf');

USE gestion_frais_db;
-- Lignes de frais pour Demande 2
INSERT INTO details_frais (demande_id, categorie_id, date_depense, montant, description, justificatif_path)
VALUES 
    (2, 3, '2025-09-02', 40.50, 'Déjeuner client', '/justificatifs/d2_repas.pdf');
    
ALTER TABLE demande_frais AUTO_INCREMENT = 3;


