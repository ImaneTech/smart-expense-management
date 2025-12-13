# Système de Gestion des Frais de Déplacement

## Description
Ce projet est une **application web** permettant de digitaliser et automatiser la gestion des frais de déplacement au sein d'une entreprise ou d'une administration.  
Elle permet aux employés de soumettre des demandes de remboursement, aux managers de valider ou rejeter ces demandes, et aux administrateurs de gérer l'ensemble des utilisateurs et des catégories de frais.

---

## Technologies utilisées
- **Front-end :** HTML5, CSS3, Bootstrap 5, JavaScript  
- **Back-end :** PHP 7/8  
- **Base de données :** MySQL  
- **Outils complémentaires :** phpMyAdmin, Git/GitHub

---

## Fonctionnalités

### 1. Espace Employé
- Authentification sécurisée
- Création et soumission de demandes de frais
- Ajout de justificatifs (PDF, images)
- Suivi des statuts des demandes (En cours, Validée, Rejetée)
- Consultation de l’historique des remboursements

### 2. Espace Manager (Validateur)
- Visualisation des demandes de son équipe
- Consultation des justificatifs
- Validation ou rejet des demandes
- Filtres par employé, date ou statut

### 3. Espace Administrateur
- Consultation de toutes les demandes validées
- Mise à jour du statut (En cours, Approuvée, Rejetée)
- Gestion des utilisateurs (création, modification, suppression)
- Gestion des catégories de frais
- Interface ergonomique et responsive (Bootstrap 5)

---

## Architecture du projet

### Front-end
- Composants Bootstrap : tables, cards, modals, badges, alerts
- Responsive design pour ordinateurs, tablettes et mobiles

### Back-end
- PHP pour la logique métier et le traitement des formulaires
- Validation et contrôle des données

### Base de données (MySQL)
- **users** : id, nom, email, rôle, mot_de_passe  
- **demande_frais** : id, user_id, objectif, date_mission, statut  
- **details_frais** : id, note_id, type, date, montant, description  
- **categories_frais**  
- **historique_statuts**

---

## Workflow
1. L’employé prépare une demande de frais et la soumet.  
2. Le manager valide ou rejette la demande.  
3. Si validée, la demande est envoyée à l’administrateur.  
4. L’administrateur vérifie et valide le remboursement.  
5. L’employé reçoit la notification finale.

---

## Installation
1. Cloner le dépôt :
   ```bash
   git clone https://github.com/ImaneTech/gestion-frais.git
