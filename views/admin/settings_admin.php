<?php
// Fichier: views/admin/settings_admin.php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';

// ---------------------------------------------------------------------
// 1. CHARGEMENT DES DÉPENDANCES ET CLASSE PDO
// ---------------------------------------------------------------------

// Les contrôleurs et modèles sont chargés AVANT le header pour garantir la disponibilité de $pdo
require_once BASE_PATH . 'controllers/SettingsController.php';
require_once BASE_PATH . 'models/NotificationModel.php'; 

// Header et Utilitaires
require_once BASE_PATH . 'includes/header.php';
require_once BASE_PATH . 'includes/flash.php';


// Vérification Rôle Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'views/settings.php'); 
    exit;
}

// ---------------------------------------------------------------------
// 2. LOGIQUE DE LA PAGE
// ---------------------------------------------------------------------

// Chargement des données de l'utilisateur
$settingsController = new SettingsController($pdo);
$userSettings = $settingsController->getSettings($_SESSION['user_id']);
$currentTheme = $userSettings['theme'] ?? 'light';
$currentCurrency = $userSettings['preferred_currency'] ?? 'MAD'; 

// Gestion des onglets
$activeTab = $_GET['tab'] ?? 'display';

// --- TRAITEMENT POST --- (Pour les thèmes et préférences)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. THEME
    if (isset($_POST['type']) && $_POST['type'] === 'display') {
        $theme = $_POST['theme'] ?? 'light';
        $settingsController->updateDisplaySettings($_SESSION['user_id'], $theme);
        setFlash('success', 'Thème mis à jour.');
        header('Location: settings_admin.php?tab=display'); exit;
    }

    // 2. DEVISE
    if (isset($_POST['type']) && $_POST['type'] === 'preferences') {
        $currency = $_POST['currency'] ?? 'MAD';
        $settingsController->updateInputPreferences($_SESSION['user_id'], $currency);
        setFlash('success', 'Devise enregistrée.');
        header('Location: settings_admin.php?tab=prefs'); exit;
    }
}
?>

<style>
    /* Styles généraux */
    .page-header-title { color: var(--text-color); font-weight: 700; margin-bottom: 1.5rem; }
    .settings-card { background-color: var(--card-bg); border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
    
    /* Tabs */
    .nav-tabs-custom { border-bottom: 2px solid #eaecf4; padding: 0 1rem; background-color: var(--card-bg); }
    .nav-tabs-custom .nav-link { border: none; color: var(--text-color); font-weight: 600; padding: 1rem 1.5rem; transition: all 0.3s ease; background: transparent; position: relative; }
    .nav-tabs-custom .nav-link:hover { color: var(--primary-color); background-color: rgba(var(--primary-color-rgb), 0.05); }
    .nav-tabs-custom .nav-link.active { color: var(--primary-color); }
    .nav-tabs-custom .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background-color: var(--primary-color); border-top-left-radius: 3px; border-top-right-radius: 3px; }
    
    /* Theme buttons */
    .theme-selector-label { cursor: pointer; border: 2px solid #e4e9f7; border-radius: 12px; padding: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 600; color: var(--text-color); transition: all 0.3s; }
    .btn-check:checked + .theme-selector-label { border-color: var(--primary-color); background-color: rgba(var(--primary-color-rgb), 0.05); color: var(--primary-color); }
    .btn-theme { background-color: var(--primary-color); color: #fff; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; transition: transform 0.2s, background-color 0.2s; }
    .btn-theme:hover { background-color: #65a63b; color: #fff; transform: translateY(-2px); }
    .tab-content-area { padding: 2rem; }
    .form-select-lg { font-size: 1rem; }
</style>

<div class="container mt-4 mb-5">
    <div class="d-flex align-items-center mb-4">
        <h2 class="page-header-title mb-0"><i class="bi bi-gear-fill text-theme-primary me-2"></i> Paramètres Administrateur</h2>
    </div>
    
    <div class="card settings-card">
        
        <ul class="nav nav-tabs nav-tabs-custom" id="settingsTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link <?= $activeTab=='display'?'active':'' ?>" id="display-tab" data-bs-toggle="tab" data-bs-target="#display" type="button"><i class="fas fa-palette me-2"></i> Affichage</button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab=='prefs'?'active':'' ?>" id="prefs-tab" data-bs-toggle="tab" data-bs-target="#prefs" type="button"><i class="fas fa-sliders-h me-2"></i> Préférences</button>
            </li>
            <li class="nav-item">
                <a href="settings_admin.php?tab=categories" class="nav-link <?= $activeTab=='categories'?'active':'' ?>" id="categories-tab"><i class="fas fa-tags me-2"></i> Gestion Catégories</a>
            </li>
            <li class="nav-item">
                <a href="settings_admin.php?tab=notifications" class="nav-link <?= $activeTab=='notifications'?'active':'' ?>" id="notifications-tab"><i class="fas fa-bell me-2"></i> Notifications</a>
            </li>
        </ul>

        <div class="tab-content tab-content-area" id="settingsTabContent">
            
            <div class="tab-pane fade <?= $activeTab=='display'?'show active':'' ?>" id="display" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="type" value="display">
                    <h5 class="fw-bold mb-4" style="color: var(--text-color);">Thème de l'interface</h5>
                    <div class="row mb-4">
                        <div class="col-md-9 offset-md-1">
                            <div class="row g-3">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="theme" id="themeLight" value="light" <?= $currentTheme == 'light' ? 'checked' : '' ?>>
                                    <label class="theme-selector-label shadow-sm" for="themeLight"><i class="fas fa-sun fs-4"></i> Clair</label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="theme" id="themeDark" value="dark" <?= $currentTheme == 'dark' ? 'checked' : '' ?>>
                                    <label class="theme-selector-label shadow-sm" for="themeDark"><i class="fas fa-moon fs-4"></i> Sombre</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit" class="btn btn-theme shadow-sm"><i class="fas fa-check-lg me-2"></i> Appliquer</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade <?= $activeTab=='prefs'?'show active':'' ?>" id="prefs" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="type" value="preferences">
                    <h5 class="fw-bold mb-4" style="color: var(--text-color);">Préférences Générales</h5>
                    <div class="row mb-4 align-items-center">
                        <label class="col-md-3 col-form-label text-muted fw-bold">Devise par défaut</label>
                        <div class="col-md-6">
                            <select class="form-select form-select-lg" name="currency">
                                <option value="MAD" <?= $currentCurrency == 'MAD' ? 'selected' : '' ?>>MAD (Dirham)</option>
                                <option value="EUR" <?= $currentCurrency == 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                                <option value="USD" <?= $currentCurrency == 'USD' ? 'selected' : '' ?>>USD (Dollar)</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit" class="btn btn-theme shadow-sm"><i class="fas fa-save me-2"></i> Enregistrer</button>
                    </div>
                </form>
            </div>

           

            <div class="tab-pane fade <?= $activeTab=='notifications'?'show active':'' ?>" id="notifications" role="tabpanel">
                 <?php 
                    // Inclusion du contenu de la page d'historique de notification
                    // Assurez-vous que historique_notif.php est à la racine de /views/
                    require_once BASE_PATH . 'views/historique_notif.php';
                ?>
            </div>

        </div>
    </div>
</div>

<?php displayFlash(); require_once BASE_PATH . 'includes/footer.php'; ?>