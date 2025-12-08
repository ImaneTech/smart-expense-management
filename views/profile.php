<?php
if (session_status() === PHP_SESSION_NONE) session_start();



// --- Initialisation PHP ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 1. INCLUSIONS
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'models/UserModel.php';
require_once BASE_PATH . 'controllers/ProfileController.php';
require_once BASE_PATH . 'includes/header.php';

// 2. INCLUSION DES FONCTIONS FLASH (Essentiel pour les popups)
require_once BASE_PATH . 'includes/flash.php'; 

// CDN Icones & SweetAlert
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">';
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

// 3. SECURITE
if (!isset($_SESSION['user_id'])) { header('Location: /login.php'); exit; }
if (!isset($pdo)) { die("Erreur critique : Connexion base de données (\$pdo) introuvable."); }

$controller = new ProfileController($pdo);

// --- TRAITEMENT DES FORMULAIRES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Mise à jour Infos
    if (isset($_POST['update_info'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $phone = $_POST['phone'];
        $dept = $_POST['department'];

        if ($controller->updateInfo($_SESSION['user_id'], $nom, $prenom, $phone, $dept)) {
            // Message de succès en Français
            setFlash('success', 'Profil mis à jour avec succès.');
            
            // Mise à jour session
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_prenom'] = $prenom;
        } else {
            setFlash('danger', 'Erreur lors de la mise à jour.');
        }
    }

    // 2. Mise à jour Mot de passe
    if (isset($_POST['update_password'])) {
        $currentPwd = $_POST['current_password'];
        $newPwd     = $_POST['new_password'];
        $confirmPwd = $_POST['confirm_password'];
        $regex = '/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/';

        if ($newPwd !== $confirmPwd) {
            setFlash('danger', 'Les mots de passe ne correspondent pas.');
        } elseif (!preg_match($regex, $newPwd)) {
             setFlash('danger', 'Le mot de passe doit contenir 8 caractères, 1 majuscule, 1 symbole.');
        } else {
            $result = $controller->updatePassword($_SESSION['user_id'], $currentPwd, $newPwd);
            if ($result === true) {
                setFlash('success', 'Mot de passe modifié avec succès.');
            } else {
                setFlash('danger', is_string($result) ? $result : "Erreur inconnue.");
            }
        }
    }
}

// Récupération des données
$user = $controller->getUser($_SESSION['user_id']);
if (!$user) { echo "Erreur chargement profil."; exit; }

$prenomInit = isset($user['prenom']) ? substr($user['prenom'], 0, 1) : '';
$nomInit = isset($user['nom']) ? substr($user['nom'], 0, 1) : '';
$initials = strtoupper($prenomInit . $nomInit);
?>

<style>
    .section-title { color: #344767; font-weight: 700; font-size: 1.2rem; margin-bottom: 1rem; display: flex; align-items: center; }
    .profile-card { border: none; border-radius: 16px; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .avatar-circle { width: 120px; height: 120px; background-color: #e8f5e9; color: #43a047; border: 2px solid #ffffff; box-shadow: 0 0 0 1px #c8e6c9; font-size: 2.8rem; font-weight: 600; margin: 0 auto; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
    .btn-primary-theme { background-color: #76BD46; border-color: #76BD46; color: white; }
    .btn-primary-theme:hover { background-color: #65a63b; color: white; }
    .form-control:focus, .form-select:focus { border-color: #76BD46; box-shadow: 0 0 0 0.2rem rgba(118, 189, 70, 0.25); }
    .input-group-text.toggle-password { cursor: pointer; background-color: #fff; border-left: none; color: #6c757d; }
    .input-group-text.toggle-password:hover { color: #76BD46; }
    .password-input { border-right: none; }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            
            <h3 class="section-title">
                <i class="bi bi-person-circle me-2 text-success"></i> Informations Personnelles
            </h3>

            <div class="card profile-card mb-4"> 
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="row align-items-center">
                            
                            <div class="col-md-3 text-center border-end-md">
                                <div class="avatar-circle mb-3"><?= $initials ?></div>
                                <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h5>
                                <span class="badge px-3 py-2 border" style="background-color: #e8f5e9; color: #2e7d32; font-weight: 600; font-size: 0.9rem;">
                                    <?= htmlspecialchars($user['role'] ?? 'Employé') ?>
                                </span>
                            </div>

                            <div class="col-md-9 ps-md-5 pt-3 pt-md-0">
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Prénom</label>
                                        <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Nom</label>
                                        <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Téléphone</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                            <input type="text" class="form-control border-start-0" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="06 00 00 00 00">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Département</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-building text-muted"></i></span>
                                            <select class="form-select border-start-0" name="department">
                                                <option value="" disabled <?= empty($user['department']) ? 'selected' : '' ?>>Choisir...</option>
                                                <option value="Informatique" <?= ($user['department'] ?? '') == 'Informatique' ? 'selected' : '' ?>>Informatique</option>
                                                <option value="Ressources Humaines" <?= ($user['department'] ?? '') == 'Ressources Humaines' ? 'selected' : '' ?>>Ressources Humaines</option>
                                                <option value="Finance" <?= ($user['department'] ?? '') == 'Finance' ? 'selected' : '' ?>>Finance</option>
                                                <option value="Marketing" <?= ($user['department'] ?? '') == 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                                                <option value="Commercial" <?= ($user['department'] ?? '') == 'Commercial' ? 'selected' : '' ?>>Commercial</option>
                                                <option value="Direction" <?= ($user['department'] ?? '') == 'Direction' ? 'selected' : '' ?>>Direction</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted small">Email professionnel</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope-fill text-muted"></i></span>
                                        <input type="email" class="form-control bg-light border-start-0" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="update_info" class="btn btn-primary-theme px-4 rounded-pill fw-bold">
                                        Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <h3 class="section-title mt-5">
                <i class="bi bi-shield-lock-fill me-2 text-success"></i> Sécurité du compte
            </h3>
            
            <div class="card profile-card">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small">Mot de passe actuel</label>
                            <div class="input-group">
                                <input type="password" class="form-control password-input" name="current_password" id="current_password" required>
                                <span class="input-group-text toggle-password" onclick="togglePassword('current_password', 'eye_curr')">
                                    <i class="bi bi-eye-slash-fill" id="eye_curr"></i>
                                </span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Nouveau mot de passe</label>
                                <div class="input-group">
                                    <input type="password" class="form-control password-input" name="new_password" id="new_password" required 
                                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}" title="Min 8 car., 1 Maj, 1 Spécial">
                                    <span class="input-group-text toggle-password" onclick="togglePassword('new_password', 'eye_new')">
                                        <i class="bi bi-eye-slash-fill" id="eye_new"></i>
                                    </span>
                                </div>
                                
                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                    <span class="badge border fw-normal" style="background-color: #e8f5e9; color: #2e7d32; font-size: 0.75rem;">8+ Caractères</span>
                                    <span class="badge border fw-normal" style="background-color: #e8f5e9; color: #2e7d32; font-size: 0.75rem;">1 Majuscule</span>
                                    <span class="badge border fw-normal" style="background-color: #e8f5e9; color: #2e7d32; font-size: 0.75rem;">1 Symbole</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Confirmer le mot de passe</label>
                                <div class="input-group">
                                    <input type="password" class="form-control password-input" name="confirm_password" id="confirm_password" required>
                                    <span class="input-group-text toggle-password" onclick="togglePassword('confirm_password', 'eye_conf')">
                                        <i class="bi bi-eye-slash-fill" id="eye_conf"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" name="update_password" class="btn btn-outline-danger px-4 rounded-pill fw-bold">
                                Mettre à jour le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div> 
    </div> 
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye-slash-fill");
        icon.classList.add("bi-eye-fill");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-fill");
        icon.classList.add("bi-eye-slash-fill");
    }
}
</script>

<?php 
// 4. AFFICHAGE POPUP
displayFlash(); 

require_once BASE_PATH . 'includes/footer.php'; 
?>