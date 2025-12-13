# 💼 Système de Gestion des Frais de Déplacement

[![PHP](https://img.shields.io/badge/PHP-7.4-blue)](https://www.php.net/) 
[![MySQL](https://img.shields.io/badge/MySQL-5.7-green)](https://www.mysql.com/) 
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple)](https://getbootstrap.com/) 
[![Licence](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 🔹 Description
Cette application web permet de **digitaliser la gestion des frais de déplacement** dans une entreprise ou administration.  
Elle assure :  
- La soumission des demandes par les employés  
- La validation ou le rejet par les managers  
- Le suivi et la gestion par les administrateurs  

---

## 🛠️ Technologies utilisées
- **Front-end :** HTML5, CSS3, Bootstrap 5, JavaScript  
- **Back-end :** PHP 7/8  
- **Base de données :** MySQL  
- **Outils complémentaires :** phpMyAdmin, Git/GitHub

---

## 👥 Fonctionnalités

### 🧑‍💼 Espace Employé
- 🔑 Authentification sécurisée  
- 📝 Création et soumission de demandes de frais  
- 📄 Ajout de justificatifs (PDF, images)  
- 📊 Suivi des statuts : En cours, Validée, Rejetée  
- 📁 Consultation de l’historique des remboursements  

### 👔 Espace Manager (Validateur)
- 📋 Visualisation des demandes de l’équipe  
- 📑 Consultation des justificatifs  
- ✅ Validation ou ❌ rejet des demandes  
- 🔍 Filtres par employé, date ou statut  

### 🛠️ Espace Administrateur
- 📂 Consultation de toutes les demandes validées  
- ⚙️ Mise à jour du statut : En cours, Approuvée, Rejetée  
- 👤 Gestion des utilisateurs (création, modification, suppression)  
- 💳 Gestion des catégories de frais  
- 🎨 Interface ergonomique et responsive (Bootstrap 5)

---

## 🏗️ Architecture du projet

### Front-end
- Bootstrap 5 : tables, cards, modals, badges, alerts  
- Responsive design : ordinateur, tablette, mobile  

### Back-end
- PHP : logique métier, traitement formulaires  
- Validation et contrôle des données  

### Base de données (MySQL)
- **users** : id, nom, email, rôle, mot_de_passe  
- **demande_frais** : id, user_id, objectif, date_mission, statut  
- **details_frais** : id, note_id, type, date, montant, description  
- **categories_frais**  
- **historique_statuts**

---

## 🔄 Workflow
1. 👤 L’employé prépare et soumet une demande  
2. 👔 Le manager valide ou rejette  
3. 🛠️ Si validée, l’administrateur vérifie et valide  
4. 👤 L’employé reçoit la notification finale  

---

## 🚀 Installation
1. Cloner le dépôt :  
```bash
git clone https://github.com/votre-utilisateur/gestion-frais.git
