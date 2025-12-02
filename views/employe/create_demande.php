<?php
// views/employe/demandes_creation.php

// Les catégories de frais sont fournies par le contrôleur (e.g., $categories)
// Simulation des données si la vue est testée seule:
if (!isset($categories)) {
    $categories = [
        ['id' => 1, 'nom' => 'Transport'], 
        ['id' => 2, 'nom' => 'Hébergement'], 
        ['id' => 3, 'nom' => 'Restauration'], 
        ['id' => 4, 'nom' => 'Carburant'], 
        ['id' => 5, 'nom' => 'Divers']
    ];
}
?>

<div class="details-page-manager">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h2 class="text-theme-secondary">Créer une Nouvelle Demande de Frais</h2>
    </div>

    <form action="/employe/demandes" method="POST" enctype="multipart/form-data">
        
        <div class="detail-card p-4 mb-4">
            <h5 class="section-title-custom mb-4"><i class="fas fa-clipboard-list"></i> Détails de la Mission</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="objet_mission" class="form-label">Objet de la Mission *</label>
                    <input type="text" class="form-control" id="objet_mission" name="objet_mission" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="lieu_deplacement" class="form-label">Lieu du Déplacement</label>
                    <input type="text" class="form-control" id="lieu_deplacement" name="lieu_deplacement">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="date_depart" class="form-label">Date de Départ *</label>
                    <input type="date" class="form-control" id="date_depart" name="date_depart" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="date_retour" class="form-label">Date de Retour *</label>
                    <input type="date" class="form-control" id="date_retour" name="date_retour" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label for="description_mission" class="form-label">Description / Contexte (Optionnel)</label>
                    <textarea class="form-control" id="description_mission" name="description_mission" rows="2"></textarea>
                </div>
            </div>
        </div>
        
        <div class="detail-card p-4 mb-4">
            <h5 class="section-title-custom mb-4"><i class="fas fa-receipt"></i> Lignes de Dépenses</h5>
            
            <div class="alert alert-info small py-2" role="alert">
                <i class="fas fa-info-circle"></i> Chaque dépense nécessite une date, un montant, une catégorie et un justificatif.
            </div>
            
            <div id="expense-lines-container">
                </div>
            
            <div class="col-12 mt-3 text-center">
                <button type="button" id="add-expense-line-btn" class="btn btn-outline-primary">
                    <i class="fas fa-plus-circle"></i> Ajouter une ligne de frais
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-4">
            <button type="submit" name="action" value="brouillon" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-save"></i> Sauvegarder Brouillon
            </button>
            <button type="submit" name="action" value="soumettre" class="btn btn-success btn-action">
                <i class="fas fa-paper-plane"></i> Soumettre pour validation
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('expense-lines-container');
        const addBtn = document.getElementById('add-expense-line-btn');
        let lineIndex = 0; // Index pour les noms de champs (important pour PHP: lignes_frais[0][...]

        // Générer les options de catégorie une seule fois en JS
        // Les données PHP ($categories) sont nécessaires ici
        const categoryOptions = `
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
            <?php endforeach; ?>
        `;
        
        // Fonction pour ajouter une ligne de frais
        function addExpenseLine() {
            const newLine = document.createElement('div');
            newLine.className = 'expense-line row border-bottom pb-3 mb-3 align-items-center';
            newLine.setAttribute('data-index', lineIndex);
            
            // Le template HTML utilise lineIndex pour créer des tableaux PHP correctement nommés
            newLine.innerHTML = `
                <div class="col-md-2 mb-2">
                    <label class="form-label visually-hidden">Date</label>
                    <input type="date" class="form-control form-control-sm" name="lignes_frais[${lineIndex}][date]" placeholder="Date *" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label visually-hidden">Type</label>
                    <select class="form-select form-select-sm" name="lignes_frais[${lineIndex}][categorie_id]" required>
                        <option value="">-- Catégorie * --</option>
                        ${categoryOptions}
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label visually-hidden">Montant</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="lignes_frais[${lineIndex}][montant]" placeholder="Montant (€) *" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label visually-hidden">Justificatif</label>
                    <input type="file" class="form-control form-control-sm" name="lignes_frais[${lineIndex}][justificatif]" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label visually-hidden">Description</label>
                    <input type="text" class="form-control form-control-sm" name="lignes_frais[${lineIndex}][description]" placeholder="Description (optionnel)">
                </div>
                <div class="col-md-1 mb-2 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-danger remove-line-btn" title="Supprimer la ligne">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
            container.appendChild(newLine);
            lineIndex++;
        }
        
        // Gérer le clic sur le bouton d'ajout
        addBtn.addEventListener('click', addExpenseLine);

        // Gérer le clic sur les boutons de suppression (via délégation d'événement)
        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-line-btn')) {
                e.target.closest('.expense-line').remove();
            }
        });

        // Ajouter la première ligne au chargement de la page
        addExpenseLine();
    });
</script>