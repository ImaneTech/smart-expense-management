<?php
require_once __DIR__.'/../../models/DemandeFrais.php';

class DashboardController {
    private $demandeModel;

    public function __construct($pdo){
        $this->demandeModel = new DemandeFrais($pdo);
    }

    public function index(){
        $stats = $this->demandeModel->getAllStats();
        require_once __DIR__.'/../../views/admin/dashboard.php';
    }
}
