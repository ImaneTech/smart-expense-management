-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2025 at 02:48 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestion_frais_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories_frais`
--

CREATE TABLE `categories_frais` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `demande_frais`
--

CREATE TABLE `demande_frais` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `objet_mission` varchar(255) NOT NULL,
  `lieu_deplacement` varchar(150) DEFAULT NULL,
  `date_depart` date NOT NULL,
  `date_retour` date NOT NULL,
  `montant_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `statut` varchar(20) DEFAULT NULL,
  `manager_id_validation` int(11) DEFAULT NULL,
  `date_traitement` datetime DEFAULT NULL,
  `commentaire_manager` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `details_frais`
--

CREATE TABLE `details_frais` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `date_depense` date NOT NULL,
  `montant` decimal(12,2) NOT NULL,
  `description` text,
  `justificatif_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `historique_statuts`
--

CREATE TABLE `historique_statuts` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ancien_statut` varchar(50) DEFAULT NULL,
  `nouveau_statut` varchar(50) DEFAULT NULL,
  `date_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commentaire` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `manager_team`
--

CREATE TABLE `manager_team` (
  `id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id_cible` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `lien_url` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `lue` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('employe','manager','admin') NOT NULL,
  `department` varchar(50) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `theme` enum('light','dark') DEFAULT 'light',
  `preferred_currency` varchar(3) DEFAULT 'MAD'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories_frais`
--
ALTER TABLE `categories_frais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Indexes for table `demande_frais`
--
ALTER TABLE `demande_frais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_demande_user` (`user_id`),
  ADD KEY `fk_demande_manager` (`manager_id_validation`);

--
-- Indexes for table `details_frais`
--
ALTER TABLE `details_frais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Indexes for table `historique_statuts`
--
ALTER TABLE `historique_statuts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `manager_team`
--
ALTER TABLE `manager_team`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_member` (`manager_id`,`member_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_cible` (`user_id_cible`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_manager` (`manager_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories_frais`
--
ALTER TABLE `categories_frais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `demande_frais`
--
ALTER TABLE `demande_frais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `details_frais`
--
ALTER TABLE `details_frais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historique_statuts`
--
ALTER TABLE `historique_statuts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manager_team`
--
ALTER TABLE `manager_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `demande_frais`
--
ALTER TABLE `demande_frais`
  ADD CONSTRAINT `fk_demande_manager` FOREIGN KEY (`manager_id_validation`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_demande_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `details_frais`
--
ALTER TABLE `details_frais`
  ADD CONSTRAINT `details_frais_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demande_frais` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `details_frais_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories_frais` (`id`);

--
-- Constraints for table `historique_statuts`
--
ALTER TABLE `historique_statuts`
  ADD CONSTRAINT `historique_statuts_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demande_frais` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historique_statuts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `manager_team`
--
ALTER TABLE `manager_team`
  ADD CONSTRAINT `manager_team_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `manager_team_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id_cible`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
