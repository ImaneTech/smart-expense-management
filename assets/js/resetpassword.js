/* ========================   Gestion Reset Password ======================= */

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    if (!form) return;

    const passwordField = form.querySelector("input[name='password']");
    const confirmField = form.querySelector("input[name='password_confirm']");

    // Création des div pour feedback sous les inputs
    [passwordField, confirmField].forEach(field => {
        const feedback = document.createElement("div");
        feedback.className = "input-feedback text-danger mt-1";
        // Placer sous l'input (après input-group)
        if (field.parentNode.classList.contains("input-group")) {
            field.parentNode.parentNode.appendChild(feedback);
        } else {
            field.parentNode.appendChild(feedback);
        }
    });

    const passwordFeedback = passwordField.parentNode.parentNode.querySelector(".input-feedback");
    const confirmFeedback = confirmField.parentNode.parentNode.querySelector(".input-feedback");

    // Validation mot de passe
    function validatePassword() {
        const pwd = passwordField.value;
        // Minimum 8 caractères, au moins une majuscule et un caractère spécial
        const valid = pwd.length >= 8 && /[A-Z]/.test(pwd) && /[^A-Za-z0-9]/.test(pwd);
        if (!valid) {
            passwordField.classList.add("is-invalid");
            passwordField.classList.remove("is-valid");
            passwordFeedback.textContent = "Mot de passe doit contenir 8 caractères, une majuscule et un caractère spécial.";
            return false;
        }
        passwordField.classList.remove("is-invalid");
        passwordField.classList.add("is-valid");
        passwordFeedback.textContent = "";
        return true;
    }

    // Validation confirmation
    function validateConfirm() {
        if (confirmField.value !== passwordField.value) {
            confirmField.classList.add("is-invalid");
            confirmField.classList.remove("is-valid");
            confirmFeedback.textContent = "Les mots de passe ne correspondent pas.";
            return false;
        }
        confirmField.classList.remove("is-invalid");
        confirmField.classList.add("is-valid");
        confirmFeedback.textContent = "";
        return true;
    }

    // Validation en temps réel
    passwordField.addEventListener("input", () => { validatePassword(); validateConfirm(); });
    confirmField.addEventListener("input", validateConfirm);

    /* ======================== Gestion Reset Password / Forgot Password ======================= */

    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("form");
        if (!form) return;

        const passwordField = form.querySelector("input[name='password']");
        const confirmField = form.querySelector("input[name='password_confirm']");

        // Création des div pour feedback sous les inputs
        const feedbackMap = new Map();
        [passwordField, confirmField].forEach(field => {
            const feedback = document.createElement("div");
            feedback.className = "input-feedback text-danger mt-1";
            if (field.parentNode.classList.contains("input-group")) {
                field.parentNode.parentNode.appendChild(feedback);
            } else {
                field.parentNode.appendChild(feedback);
            }
            feedbackMap.set(field, feedback);
        });

        const passwordFeedback = feedbackMap.get(passwordField);
        const confirmFeedback = feedbackMap.get(confirmField);

        // Validation mot de passe
        function validatePassword() {
            const pwd = passwordField.value;
            const valid = pwd.length >= 8 && /[A-Z]/.test(pwd) && /[^A-Za-z0-9]/.test(pwd);
            if (!valid) {
                passwordField.classList.add("is-invalid");
                passwordField.classList.remove("is-valid");
                passwordFeedback.textContent = "Mot de passe doit contenir 8 caractères, une majuscule et un caractère spécial.";
                return false;
            }
            passwordField.classList.remove("is-invalid");
            passwordField.classList.add("is-valid");
            passwordFeedback.textContent = "";
            return true;
        }

        // Validation confirmation
        function validateConfirm() {
            if (confirmField.value !== passwordField.value) {
                confirmField.classList.add("is-invalid");
                confirmField.classList.remove("is-valid");
                confirmFeedback.textContent = "Les mots de passe ne correspondent pas.";
                return false;
            }
            confirmField.classList.remove("is-invalid");
            confirmField.classList.add("is-valid");
            confirmFeedback.textContent = "";
            return true;
        }

        // Validation en temps réel
        passwordField.addEventListener("input", () => { validatePassword(); validateConfirm(); });
        confirmField.addEventListener("input", validateConfirm);

        // Fonction pour créer popup toast
        function showToast(message, type = "error") {
            // Vérifier si container existe déjà
            let container = form.querySelector(".toast-container");
            if (!container) {
                container = document.createElement("div");
                container.className = "toast-container";
                container.style.position = "absolute";
                container.style.top = "10px";
                container.style.left = "10px";
                container.style.zIndex = "1050";
                container.style.display = "flex";
                container.style.flexDirection = "column";
                container.style.gap = "10px";
                form.style.position = "relative"; // nécessaire pour position absolute
                form.appendChild(container);
            }

            // Créer le toast
            const toast = document.createElement("div");
            toast.className = `toast-alert ${type}`;
            toast.style.display = "flex";
            toast.style.alignItems = "center";
            toast.style.justifyContent = "space-between";
            toast.style.padding = "10px 15px";
            toast.style.borderRadius = "5px";
            toast.style.minWidth = "250px";
            toast.style.boxShadow = "0 2px 8px rgba(0,0,0,0.2)";
            toast.style.color = "#000";
            toast.style.fontSize = "0.9rem";

            // Fond selon type
            switch (type) {
                case "info": toast.style.backgroundColor = "#d0f0fd"; break;
                case "warning": toast.style.backgroundColor = "#fff4d6"; break;
                case "success": toast.style.backgroundColor = "#d6f5d6"; break;
                case "error": toast.style.backgroundColor = "#fdd6d6"; break;
                default: toast.style.backgroundColor = "#fdd6d6";
            }

            // Texte message
            const span = document.createElement("span");
            span.textContent = message;

            // Bouton fermeture
            const btn = document.createElement("button");
            btn.innerHTML = "&times;";
            btn.style.border = "none";
            btn.style.background = "transparent";
            btn.style.cursor = "pointer";
            btn.addEventListener("click", () => toast.remove());

            toast.appendChild(span);
            toast.appendChild(btn);
            container.appendChild(toast);

            // Supprime automatiquement après 5s
            setTimeout(() => {
                if (container.contains(toast)) toast.remove();
            }, 5000);
        }

        // Validation au submit
        form.addEventListener("submit", function (e) {
            let valid = validatePassword() && validateConfirm();
            if (!valid) {
                e.preventDefault();
                showToast("Erreur : Veuillez corriger les champs avant de soumettre.", "error");
            }
        });
    });

});
