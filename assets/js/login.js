/* ========================   Afficher / Masquer mot de passe   ======================= */
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/* ========================  Validation en temps réel ======================= */
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    if (!form) return;

    const emailField = form.querySelector("input[name='email']");
    const passwordField = form.querySelector("input[name='password']");

    // Regex email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Création des div de feedback
    [emailField, passwordField].forEach(field => {
        const feedback = document.createElement("div");
        feedback.className = "input-feedback text-danger mt-1"; // message rouge
        // Vérifier si l'input est dans un input-group
        if (field.parentNode.classList.contains("input-group")) {
            field.parentNode.parentNode.insertBefore(feedback, field.parentNode.nextSibling);
        } else {
            field.parentNode.appendChild(feedback);
        }
    });

    const emailFeedback = emailField.parentNode.querySelector(".input-feedback");
    const passwordFeedback = passwordField.parentNode.parentNode.querySelector(".input-feedback");

    // Fonctions de validation
    function validateEmail() {
        const value = emailField.value.trim();
        if (!emailRegex.test(value)) {
            emailField.classList.add("is-invalid");
            emailField.classList.remove("is-valid");
            emailFeedback.textContent = "Email invalide.";
            return false;
        }
        emailField.classList.remove("is-invalid");
        emailField.classList.add("is-valid");
        emailFeedback.textContent = "";
        return true;
    }

    function validatePassword() {
        const value = passwordField.value;
        if (value.length < 8) {
            passwordField.classList.add("is-invalid");
            passwordField.classList.remove("is-valid");
            passwordFeedback.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
            return false;
        }
        passwordField.classList.remove("is-invalid");
        passwordField.classList.add("is-valid");
        passwordFeedback.textContent = "";
        return true;
    }

    // Validation en temps réel
    emailField.addEventListener("input", validateEmail);
    passwordField.addEventListener("input", validatePassword);
});
