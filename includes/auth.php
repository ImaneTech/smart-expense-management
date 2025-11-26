<?php

 // Fonctions simples d'authentification  -- Gestion basique des sessions utilisateurs

 // Vérifie si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}


 // Récupère l'ID de l'utilisateur
 
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}


 // Récupère le rôle de l'utilisateur
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Redirige si l'utilisateur n'est pas connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Déconnecte l'utilisateur
function logout() {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
