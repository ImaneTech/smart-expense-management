<?php
// =============================================
// ======= CONTROLLER CATEGORIE ================
// Fichier : controllers/CategorieController.php
// =============================================

require_once __DIR__ . '/../models/CategorieModel.php'; 
// Assurez-vous que BASE_URL et BASE_PATH sont définis dans config.php

// =============================================
// ======= DEFINITION DE LA CLASSE =============
// =============================================
class CategorieController {

    private $model;

    // =============================================
    // ======= CONSTRUCTEUR =======================
    // Initialise le modèle et vérifie l'authentification admin
    // =============================================
    public function __construct($db) {
        $this->model = new CategorieModel($db);
        $this->checkAuth();
    }

    // =============================================
    // ======= VERIFICATION AUTH ==================
    // Vérifie si l'utilisateur est connecté et a le rôle 'admin'
    // =============================================
    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            $redirectUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'views/auth/login.php';
            header('Location: ' . $redirectUrl); 
            exit;
        }
    }

    // =============================================
    // ======= LISTE DES CATEGORIES ===============
    // Récupère toutes les catégories pour l'affichage
    // =============================================
    public function index(): array {
        return $this->model->getAllCategories();
    }

    // =============================================
    // ======= AJOUT D'UNE CATEGORIE ==============
    // Traite le formulaire d'ajout via POST
    // =============================================
    public function addCategorieAction(array $postData) {
        if (empty(trim($postData['nom'] ?? ''))) {
            $_SESSION['error_message'] = "Le nom de la catégorie est obligatoire.";
            return;
        }

        $nom = htmlspecialchars(trim($postData['nom']));

        if ($this->model->addCategorie($nom)) {
            $_SESSION['message'] = "Catégorie '{$nom}' ajoutée avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur: Le nom '{$nom}' existe déjà ou la base de données a rencontré une erreur.";
        }

        // PRG (Post-Redirect-Get) pattern
        header('Location: ' . BASE_URL . 'views/admin/gerer_categories.php');
        exit;
    }

    // =============================================
    // ======= SUPPRESSION D'UNE CATEGORIE ========
    // Traite la suppression via POST
    // =============================================
    public function deleteCategorieAction(array $postData) {
        $id = (int)($postData['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error_message'] = "ID de catégorie invalide.";
            return;
        }

        if ($this->model->deleteCategorie($id)) {
            $_SESSION['message'] = "Catégorie (ID: {$id}) supprimée avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur: Impossible de supprimer la catégorie (elle est peut-être utilisée par des demandes de frais existantes).";
        }

        // PRG pattern
        header('Location: ' . BASE_URL . 'views/admin/gerer_categories.php');
        exit;
    }
}
