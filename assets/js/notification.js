// Fichier: JavaScript
$(document).ready(function () {


    const apiEndpoint = API_ENDPOINT;
    console.log("DEBUG: API_ENDPOINT défini comme:", apiEndpoint); 

    // Fonction 1: Mettre à jour le Compteur (Badge)
    function updateNotificationCount() {
        const url_count = apiEndpoint + '?action=count';
        console.log("DEBUG: Appel AJAX 'count' URL:", url_count); 

        $.ajax({
            url: url_count,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log("DEBUG: Réponse 'count' reçue:", response); 

                const count = response.total || 0;
                const $notifCount = $('#notif-count');

                if (count > 0) {
                    $notifCount.text(count).show();
                } else {
                    $notifCount.hide();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Gestion d'erreur spécifique pour le compteur
                console.error("ERREUR AJAX 'count' (le badge):", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    }

    // Fonction 2: Charger et Afficher la Liste dans le Modal
    function loadNotificationList() {
        const $modalBody = $('#notif-modal-body');
        $modalBody.html('<p class="text-center text-muted p-4">Chargement des notifications...</p>');

        const url_list = apiEndpoint + '?action=list';
        console.log("DEBUG: Appel AJAX 'list' URL:", url_list); 

        $.ajax({
            url: url_list,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log("DEBUG: Réponse 'list' reçue:", response); 

                $modalBody.empty(); // Vider le contenu

                if (response.notifications && response.notifications.length > 0) {
                    let htmlContent = '<ul style="list-style: none; padding: 0; margin: 0;">';

                    response.notifications.forEach(function (notif) {

                        const displayDate = notif.date_creation ? notif.date_creation.substring(0, 10) : 'N/A';
                        const isUnread = notif.lue == 0;
                        const liStyle = isUnread ?
                            `background-color: var(--bs-light); font-weight: bold; border-left: 4px solid var(--primary-color);` :
                            `background-color: transparent; border-left: 4px solid transparent;`;

                        const textClass = isUnread ? 'text-dark' : 'text-muted';
                        const dateTextClass = isUnread ? 'text-secondary' : 'text-muted';

                        htmlContent += `
                            <li style="${liStyle} padding: 12px 15px; border-bottom: 1px solid var(--table-border); cursor: pointer;">
                                <a href="${notif.lien_url}" class="text-decoration-none d-block ${textClass}" style="color: inherit;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <span style="flex-grow: 1;">${notif.message}</span>
                                        <small class="${dateTextClass}" style="flex-shrink: 0; margin-left: 10px; font-weight: normal;">${displayDate}</small>
                                    </div>
                                </a>
                            </li>`;
                    });

                    htmlContent += '</ul>';


                    $modalBody.html(htmlContent);

                    // Après avoir chargé la liste, on la marque comme lue
                    markNotificationsAsRead();

                } else {
                    console.log("DEBUG: 'list' est vide ou ne contient pas 'notifications'."); 
                    $modalBody.html('<p class="text-center p-3" style="color: #2566A1;">Aucune notification récente.</p>');
                    updateNotificationCount();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
              
                $modalBody.html('<p class="text-center text-danger p-4">Erreur lors du chargement des notifications. (Code: ' + jqXHR.status + ')</p>');
                console.error("ERREUR AJAX 'list':", textStatus, errorThrown, jqXHR.responseText); 
            }
        });
    }

    function markNotificationsAsRead() {
        const url_mark = apiEndpoint + '?action=mark_as_read';
        console.log("DEBUG: Appel AJAX 'mark_as_read' URL:", url_mark); 

        $.ajax({
            url: url_mark,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                console.log("DEBUG: Réponse 'mark_as_read' reçue:", response);
                const $notifCount = $('#notif-count');
                $notifCount.hide().text('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Gestion d'erreur spécifique pour le marquage
                console.error("ERREUR AJAX 'mark_as_read':", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    }

    // ----------------------------------------------------
    // 4. Déclencheurs et Initialisation
    // ----------------------------------------------------

    $('#notificationModal').on('show.bs.modal', function () {
        console.log("INFO: Modal ouvert, appel de loadNotificationList."); 
        loadNotificationList();
    });

    updateNotificationCount();

    setInterval(updateNotificationCount, 60000);
});