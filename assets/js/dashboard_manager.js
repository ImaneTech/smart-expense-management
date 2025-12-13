

// Durée de l'animation par défaut en millisecondes
const ANIMATION_DURATION = 2000;

/**
 * Fonction de formatage pour afficher les nombres (avec ou sans décimales).
 * @param {number} number - Le nombre à formater.
 * @param {boolean} isAmount - Indique si c'est un montant (doit avoir 2 décimales).
 * @returns {string} Le nombre formaté.
 */
function formatNumber(number, isAmount) {
    if (isAmount) {
        // Utilise toLocaleString pour le formatage FR (virgule décimale) et le séparateur de milliers
        // Fixe à 2 décimales
        return number.toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' ' + (typeof CURRENCY_SYMBOL !== 'undefined' ? CURRENCY_SYMBOL : '€') + ' est.';
    }
    // Pour les nombres entiers (arrondi au supérieur pour l'animation)
    return Math.ceil(number).toString();
}

/**
 * Anime un compteur unique en utilisant requestAnimationFrame pour une performance optimale.
 * @param {HTMLElement} element - L'élément HTML du compteur.
 */
function animateCounter(element) {
    // Récupère la valeur cible (nombre)
    const target = parseFloat(element.getAttribute('data-target'));
    const isAmount = element.classList.contains('is-amount');

    let startTimestamp = null;
    let initialValue = 0; // Commence toujours à 0

    // Définir la valeur de départ à 0 avant de commencer l'animation
    element.textContent = formatNumber(initialValue, isAmount);

    const step = (timestamp) => {
        if (!startTimestamp) {
            startTimestamp = timestamp;
        }

        const elapsed = timestamp - startTimestamp;
        // La progression va de 0 à 1
        const progress = Math.min(elapsed / ANIMATION_DURATION, 1);

        // Calcul de la valeur actuelle basée sur la progression
        const currentValue = progress * target;

        // Mise à jour de l'affichage
        element.textContent = formatNumber(currentValue, isAmount);

        if (progress < 1) {
            // Continuer l'animation
            window.requestAnimationFrame(step);
        } else {
            // S'assurer que la valeur finale est la valeur cible exacte
            element.textContent = formatNumber(target, isAmount);
        }
    };

    // Démarrer l'animation uniquement si la cible est un nombre positif
    if (target > 0) {
        window.requestAnimationFrame(step);
    } else {
        element.textContent = formatNumber(target, isAmount);
    }
}

/**
 * Initialise l'animation des compteurs.
 * Utilise l'IntersectionObserver pour démarrer l'animation
 * uniquement lorsque le compteur entre dans le viewport (optimisation).
 */
function initCounters() {
    const counters = document.querySelectorAll('.counter');

    // Si IntersectionObserver n'est pas supporté, lancer immédiatement
    if (!('IntersectionObserver' in window)) {
        counters.forEach(animateCounter);
        return;
    }

    // Observer pour démarrer l'animation quand le compteur est visible
    const observerOptions = {
        root: null, // Le viewport est la racine
        rootMargin: '0px',
        threshold: 0.5 // Déclencher quand 50% de l'élément est visible
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Lancer l'animation
                animateCounter(entry.target);
                // Arrêter d'observer cet élément
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    counters.forEach(counter => {
        observer.observe(counter);
    });
}

/**
 * Fonction principale exécutée après le chargement du DOM.
 */
document.addEventListener('DOMContentLoaded', function () {
    console.log('Dashboard Manager chargé. Démarrage des animations...');

    // 1. Démarrer l'initialisation des compteurs
    initCounters();

    // 2. Initialisation des Tooltips Bootstrap (nécessite l'objet global 'bootstrap')
    // Vérifier si 'bootstrap' est défini pour éviter les erreurs
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});