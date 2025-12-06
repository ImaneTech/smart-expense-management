

$(document).ready(function() {
    
    // Chemin du Contrôleur API (Assurez-vous qu'il est correct depuis la racine)
   // const apiEndpoint = BASE_URL + 'controllers/Notification_api.php'; 
const apiEndpoint = API_ENDPOINT;
    // ----------------------------------------------------
    // Fonction 1: Mettre à jour le Compteur (Badge)
    // ----------------------------------------------------
    function updateNotificationCount() {
        $.ajax({
            url: apiEndpoint + '?action=count',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const count = response.total || 0;
                const $notifCount = $('#notif-count');
                
                if (count > 0) {
                    $notifCount.text(count).show();
                } else {
                    $notifCount.hide();
                }
            }
        });
    }
// Fonction 2: Charger et Afficher la Liste dans le Modal
function loadNotificationList() {
    const $modalBody = $('#notif-modal-body');
    $modalBody.html('<p class="text-center text-muted p-4">Chargement en cours...</p>');

    $.ajax({
        url: apiEndpoint + '?action=list',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $modalBody.empty(); // Vider le contenu

            if (response.notifications && response.notifications.length > 0) {
                // Utiliser une liste non ordonnée simple pour la structure
                let htmlContent = '<ul style="list-style: none; padding: 0; margin: 0;">';
                
                response.notifications.forEach(function(notif) {
                    
                    // Style pour notification NON LUE (Faire ressortir avec une couleur du thème)
                    // Utilisez un fond très clair dans le mode clair et un fond légèrement plus clair 
                    // que le fond de carte dans le mode sombre.
                    const notReadStyle = `background-color: var(--primary-color); background-color: rgba(var(--primary-color-rgb), 0.1); font-weight: bold; border-left: 4px solid var(--primary-color);`;
                    
                    // Style pour notification LUE
                    const readStyle = `background-color: transparent; color: var(--text-muted); border-left: 4px solid transparent;`;

                    const liStyle = notif.lue == 0 ? notReadStyle : readStyle;
                    const textClass = notif.lue == 0 ? 'text-color' : 'text-muted';

                    htmlContent += `
                        <li style="${liStyle} padding: 12px 15px; border-bottom: 1px solid var(--table-border); cursor: pointer;">
                            <a href="${notif.lien_url}" class="text-decoration-none d-block ${textClass}" style="color: inherit;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="flex-grow: 1;">${notif.message}</span>
                                    <small style="flex-shrink: 0; margin-left: 10px; font-weight: normal;">${notif.date_creation.substring(0, 10)}</small>
                                </div>
                            </a>
                        </li>`;
                });
                
                htmlContent += '</ul>';
                $modalBody.html(htmlContent);
                
                $('#notif-count').hide().text('');
                
            } else {
                $modalBody.html('<p class="text-center text-info p-3">Aucune notification récente.</p>');
            }
        }
    });
}

    // ----------------------------------------------------
    // 3. Déclencheurs et Initialisation
    // ----------------------------------------------------
    
    // Charger la liste lorsque l'utilisateur ouvre le Modal
    $('#notificationModal').on('show.bs.modal', function() {
        loadNotificationList();
    });

    // Initialisation : Lancer le compte au chargement de la page
    updateNotificationCount(); 
    
    // Répéter le compte toutes les minutes (polling)
    setInterval(updateNotificationCount, 60000); 
});