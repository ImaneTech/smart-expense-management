<?php
require_once "../config.php";
require_once "../models/Demande.php";

$demandes = Demande::getAll($pdo);
?>

<tbody>
<?php foreach ($demandes as $d): ?>

<tr>
    <td><?= $d['id']; ?></td>
    <td><?= $d['visiteur_nom']; ?></td>
    <td><?= $d['objectif']; ?></td>
    <td><?= $d['date_mission']; ?></td>
    <td><?= $d['montant_total']; ?></td>

    <td class="
        <?php
        if ($d['statut_actuel'] === 'validé') echo 'status-valid';
        elseif ($d['statut_actuel'] === 'rejeté') echo 'status-rejet';
        else echo 'status-attente';
        ?>
    ">
        <?= ucfirst($d['statut_actuel']); ?>
    </td>
</tr>

<?php endforeach; ?>
</tbody>

<td>
    <form method="POST" action="../controllers/demandesController.php?action=updateStatus">
        <input type="hidden" name="demande_id" value="<?= $d['id']; ?>">
        <input type="hidden" name="user_type" value="admin">
        <input type="hidden" name="user_id" value="1">

        <button name="statut" value="validé" class="btn btn-success btn-sm">Valider</button>
        <button name="statut" value="rejeté" class="btn btn-danger btn-sm">Rejeter</button>
    </form>
</td>

