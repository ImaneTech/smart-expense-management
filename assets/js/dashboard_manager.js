

document.addEventListener('DOMContentLoaded', function() {
    
    console.log('Dashboard Manager chargé.');

    // 1. Animation des chiffres (Compteur)
    // Sélectionne tous les éléments avec la classe .counter
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target'); // La valeur finale (ex: 1500)
        const duration = 1000; // Durée en ms (1 seconde)
        const increment = target / (duration / 16); // Calcul du pas d'incrémentation
        
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            
            if (current < target) {
                // Si c'est un montant (avec virgule), on formate, sinon entier
                if(counter.classList.contains('is-amount')) {
                    counter.innerText = current.toFixed(2);
                } else {
                    counter.innerText = Math.ceil(current);
                }
                requestAnimationFrame(updateCounter);
            } else {
                // Fin de l'animation, on met le chiffre exact
                if(counter.classList.contains('is-amount')) {
                    counter.innerText = target.toLocaleString('fr-FR', {minimumFractionDigits: 2}); 
                } else {
                    counter.innerText = target;
                }
            }
        };
        
        updateCounter();
    });

    // 2. Initialisation des Tooltips Bootstrap (si tu en utilises)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

});