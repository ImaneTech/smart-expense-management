<?php
// Fichier: includes/footer.php (CORRIGÉ)

// Inclus config.php pour BASE_URL si ce n'est pas déjà fait
// NOTE: L'inclusion ici est redondante si le fichier principal l'a fait, mais ne cause pas d'erreur.
// include_once(BASE_PATH . '/config.php');
require_once __DIR__ . '/../config.php'; 
?>
</div> </section> 

<script>
    const API_ENDPOINT = "<?= BASE_URL ?>controllers/Notification_api.php";
</script>
<script>
   
    const BASE_URL = "<?= BASE_URL ?>"; 
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?= BASE_URL ?>assets/js/notification.js"></script>

<script src="<?= BASE_URL ?>assets/js/sidebar.js"></script>
</body>
</html>