# 💼 Système de Gestion des Frais de Déplacement

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue?style=for-the-badge&logo=php)](https://www.php.net/) 
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-green?style=for-the-badge&logo=mysql)](https://www.mysql.com/) 
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple?style=for-the-badge&logo=bootstrap)](https://getbootstrap.com/) 
[![Licence](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

---

## 🚀 Démo
**Visionnez la démonstration complète de l'application en cliquant sur l'image ci-dessous !**

[![Système de Gestion des Frais de Déplacement – Démo Fonctionnelle](https://img.youtube.com/vi/VOTRE_VIDEO_ID/hqdefault.jpg)](https://www.youtube.com/watch?v=VOTRE_VIDEO_ID)

---

## 🔹 Description
Cette application web permet de **digitaliser et centraliser la gestion des frais de déplacement** au sein d'une organisation (entreprise ou administration).  
Elle assure un flux de travail clair et rapide, de la soumission de la demande par l'employé jusqu'à sa validation finale par l'administrateur.

---

## 🛠️ Technologies utilisées
* **Front-end :** HTML5, CSS3, **Bootstrap 5**, JavaScript
* **Back-end :** **PHP 7/8** (Logique métier, sessions, contrôle)
* **Base de données :** **MySQL**
* **Outils complémentaires :** phpMyAdmin, Git/GitHub

---

## 👥 Fonctionnalités Détaillées

### 🧑‍💼 Espace Employé
* 🔑 **Authentification Sécurisée**
* 📝 **Création et Soumission** de notes de frais (avec objectif de mission)
* 📄 **Ajout de Justificatifs** (fichiers PDF, images)
* 📊 **Suivi des Statuts** en temps réel (En cours, Validée, Rejetée)
* 📁 Consultation de l’historique des remboursements

### 👔 Espace Manager (Validateur)
* 📋 **Visualisation** des demandes soumises par son équipe
* 📑 Consultation des **détails de frais et des justificatifs**
* ✅ **Validation** ou ❌ **Rejet** motivé des demandes
* 🔍 Filtres et recherche par employé, date ou statut

### 🛠️ Espace Administrateur
* 📂 Consultation et gestion de **toutes les demandes** validées et en attente
* ⚙️ **Mise à Jour du Statut** final (ex: Remboursée)
* 👤 **Gestion des Utilisateurs** (CRUD - Création, Modification, Suppression, Rôles)
* 💳 **Gestion des Catégories de Frais** (mise à jour des types de dépenses)

---

## 🏗️ Architecture Technique

### 🌐 Structure (Exemple de tables MySQL)
* `users` : id, nom, email, rôle, mot_de_passe
* `demandes_frais` : id, user_id, objectif, date_mission, statut, ...
* `details_frais` : id, demande_id, type, date, montant, description, justificatif_path
* `categories_frais`
* `historique_statuts`

### 🔄 Workflow de Validation
1.  👤 L'Employé soumet la demande de frais.
2.  👔 Le Manager reçoit la notification et procède à la **validation/rejet initial**.
3.  🛠️ Si la demande est validée, l'Administrateur reçoit la demande pour **vérification finale et approbation**.
4.  👤 L'Employé est notifié du statut final (Approuvée / Rejetée).

---

## ⚙️ Installation et Démarrage
Pour exécuter ce projet localement, suivez ces étapes :

1.  **Clonage du dépôt :**
    ```bash
    git clone [https://github.com/votre-utilisateur/gestion-frais.git](https://github.com/votre-utilisateur/gestion-frais.git)
    cd gestion-frais
    ```
2.  **Configuration de la Base de Données :**
    * Créez une base de données MySQL nommée `gestion_frais`.
    * Importez le fichier `gestion_frais.sql` (fourni dans le projet) via phpMyAdmin ou votre outil préféré.
    * Modifiez les informations de connexion à la base de données dans le fichier `config.php` (ou équivalent) pour qu'elles correspondent à votre environnement local (nom d'utilisateur, mot de passe).
3.  **Lancement :**
    * Déplacez le dossier du projet dans le répertoire de votre serveur web (ex: `htdocs` pour XAMPP).
    * Démarrez votre serveur web local (Apache) et MySQL.
    * Accédez à l'application via votre navigateur : `http://localhost/gestion-frais`

---

## 🤝 Contribution
Les contributions sont les **bienvenues** et fortement encouragées.

1.  *Fork* le projet
2.  Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3.  *Commit* vos changements (`git commit -m 'Ajout d'une fonctionnalité'` )
4.  *Push* vers la branche (`git push origin feature/AmazingFeature`)
5.  Ouvrez une **Pull Request**

---

## 📜 Licence
Ce projet est distribué sous la **Licence MIT**. Voir le fichier `LICENSE` pour plus d'informations.
