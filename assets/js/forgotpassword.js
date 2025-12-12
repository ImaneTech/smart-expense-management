/* ========================   Gestion mot de passe oublié ======================= */

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("forgotPasswordForm");
    if (!form) return;

    const emailField = form.querySelector("input[name='email']");

    // Regex pour email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Création du div pour feedback **sous l'input**
    const feedback = document.createElement("div");
    feedback.className = "input-feedback text-danger mt-1"; // mt-1 pour un petit espace
    emailField.parentNode.appendChild(feedback);

    // Validation email en temps réel
    function validateEmail() {
        const value = emailField.value.trim();
        if (!emailRegex.test(value)) {
            emailField.classList.add("is-invalid");
            emailField.classList.remove("is-valid");
            feedback.textContent = "Adresse email invalide.";
            return false;
        }
        emailField.classList.remove("is-invalid");
        emailField.classList.add("is-valid");
        feedback.textContent = "";
        return true;
    }

    emailField.addEventListener("input", validateEmail);

    // Validation au submit avec popup
    form.addEventListener("submit", function (e) {
        let valid = validateEmail();

        if (!valid) {
            e.preventDefault();

            // Supprimer popup existante
            const existingAlert = document.querySelector(".js-popup-alert");
            if (existingAlert) existingAlert.remove();

            // Créer popup Bootstrap
            const alertDiv = document.createElement("div");
            alertDiv.className = "alert alert-danger js-popup-alert position-fixed top-0 start-50 translate-middle-x mt-3";
            alertDiv.style.zIndex = "1050";
            //  alertDiv.textContent = "Erreur : Veuillez entrer une adresse email valide.";
            document.body.appendChild(alertDiv);

            // Disparaît après 3 secondes
            setTimeout(() => alertDiv.remove(), 3000);
        }
    });
});
