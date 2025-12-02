<?php
namespace Controllers;

use models\Visiteur;
use models\Admin;
use models\Manager;

class AuthController extends Controller {
    
    /**
     * POST /api/login
     */
    public function login() {
        try {
            $data = $this->getJsonInput();
            
            // Validation
            $errors = $this->validateRequired($data, ['email', 'password', 'role']);
            if (!empty($errors)) {
                $this->error(implode(', ', $errors), 400);
            }
            
            $email = $data['email'];
            $password = $data['password'];
            $role = $data['role']; // 'visiteur', 'manager', 'admin'
            
            $user = null;
            
            // Rechercher selon le rôle
            switch ($role) {
                case 'visiteur':
                    $model = new Visiteur();
                    $user = $model->findByEmail($email);
                    break;
                    
                case 'manager':
                    $model = new Manager();
                    $user = $model->findByEmail($email);
                    break;
                    
                case 'admin':
                    $model = new Admin();
                    $user = $model->findByEmail($email);
                    break;
                    
                default:
                    $this->error('Rôle invalide', 400);
            }
            
            if (!$user) {
                $this->error('Email ou mot de passe incorrect', 401);
            }
            
            // Vérifier le mot de passe
            if (!password_verify($password, $user['password'])) {
                $this->error('Email ou mot de passe incorrect', 401);
            }
            
            // Créer la session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_role'] = $role;
            
            unset($user['password']); // Ne pas renvoyer le mot de passe
            
            $this->success([
                'user' => $user,
                'role' => $role
            ], 'Connexion réussie');
            
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/logout
     */
    public function logout() {
        session_start();
        session_destroy();
        $this->success([], 'Déconnexion réussie');
    }
    
    /**
     * GET /api/me
     */
    public function me() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            $this->error('Non authentifié', 401);
        }
        
        $this->success([
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'nom' => $_SESSION['user_nom'],
            'role' => $_SESSION['user_role']
        ]);
    }
}