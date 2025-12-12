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

/* ========================  Animations au chargement  ======================= */
window.addEventListener('load', function () {
    const leftSection = document.querySelector('.left-section');
    leftSection.style.opacity = '0';
    leftSection.style.transform = 'translateX(-30px)';
    setTimeout(() => {
        leftSection.style.transition = 'all 0.8s ease-out';
        leftSection.style.opacity = '1';
        leftSection.style.transform = 'translateX(0)';
    }, 100);

    const rightSection = document.querySelector('.right-section');
    rightSection.style.transform = 'translateX(100%)';
    setTimeout(() => {
        rightSection.style.transition = 'transform 1s cubic-bezier(0.68,-0.55,0.265,1.55)';
        rightSection.style.transform = 'translateX(0)';
    }, 200);

    const illustration = document.querySelector('.illustration-image');
    if (illustration) {
        illustration.style.opacity = '0';
        illustration.style.transform = 'scale(0.8)';
        setTimeout(() => {
            illustration.style.transition = 'all 0.8s ease-out';
            illustration.style.opacity = '1';
            illustration.style.transform = 'scale(1)';
        }, 1000);
    }

    const formElements = document.querySelectorAll('.form-group, .checkbox-group, .btn-signup, .login-link');
    formElements.forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        setTimeout(() => {
            el.style.transition = 'all 0.5s ease-out';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 400 + (i * 80));
    });
});

/* ========================  Validation formulaire  ======================== */
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("signupForm");
    if (!form) return;

    const fields = {
        first_name: form.querySelector("input[name='first_name']"),
        last_name: form.querySelector("input[name='last_name']"),
        email: form.querySelector("input[name='email']"),
        phone: form.querySelector("input[name='phone']"),
        password: form.querySelector("input[name='password']"),
        confirm_password: form.querySelector("input[name='confirm_password']"),
        role: form.querySelector("select[name='role']"),
        department: form.querySelector("select[name='department']"),
        terms: form.querySelector("input[name='terms']")
    };

    // Regex pour validations communes des champs
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^0[67][0-9]{8}$/;
    const nameRegex = /^[A-Za-zÀ-ÖØ-öø-ÿ\-\s]+$/;

    /* ========================   Ajouter div pour message sous input ======================== */
    Object.values(fields).forEach(field => {
        if (field && field.tagName === "INPUT" || field.tagName === "SELECT") {
            let feedback = document.createElement("div");
            feedback.className = "invalid-feedback";
            field.parentNode.appendChild(feedback);
        }
    });
    /* ========================     Fonctions de validation  ======================== */
    function markInvalid(input, message) {
        input.classList.add("is-invalid");
        input.classList.remove("is-valid");
        input.parentNode.querySelector(".invalid-feedback").textContent = message;
    }

    function markValid(input) {
        input.classList.remove("is-invalid");
        input.classList.add("is-valid");
        input.parentNode.querySelector(".invalid-feedback").textContent = "";
    }

    function validateName(input) {
        if (!nameRegex.test(input.value.trim())) {
            markInvalid(input, "Nom ou prénom invalide.");
            return false;
        }
        markValid(input);
        return true;
    }

    function validateEmail() {
        if (!emailRegex.test(fields.email.value.trim())) {
            markInvalid(fields.email, "Email invalide.");
            return false;
        }
        markValid(fields.email);
        return true;
    }

    function validatePhone() {
        if (!phoneRegex.test(fields.phone.value.trim())) {
            markInvalid(fields.phone, "Téléphone invalide.");
            return false;
        }
        markValid(fields.phone);
        return true;
    }

    function validatePassword() {
        const pwd = fields.password.value;
        const valid = pwd.length >= 8 && /[A-Z]/.test(pwd) && /[^A-Za-z0-9]/.test(pwd);
        if (!valid) {
            markInvalid(fields.password, "Mot de passe doit contenir 8 caractères, une majuscule et un caractère spécial.");
            return false;
        }
        markValid(fields.password);
        return true;
    }

    function validateConfirmPassword() {
        if (fields.confirm_password.value !== fields.password.value) {
            markInvalid(fields.confirm_password, "Les mots de passe ne correspondent pas.");
            return false;
        }
        markValid(fields.confirm_password);
        return true;
    }

    function validateSelect(select) {
        if (!select.value) {
            markInvalid(select, "Veuillez sélectionner une option.");
            return false;
        }
        markValid(select);
        return true;
    }

    /* ========================   Validation en temps réel ======================== */
    fields.first_name.addEventListener("input", () => validateName(fields.first_name));
    fields.last_name.addEventListener("input", () => validateName(fields.last_name));
    fields.email.addEventListener("input", validateEmail);
    fields.phone.addEventListener("input", validatePhone);
    fields.password.addEventListener("input", validatePassword);
    fields.confirm_password.addEventListener("input", validateConfirmPassword);
    fields.role.addEventListener("change", () => validateSelect(fields.role));
    fields.department.addEventListener("change", () => validateSelect(fields.department));

    /* ========================   Validation finale submit ======================== */
    form.addEventListener("submit", function (e) {
        let valid =
            validateName(fields.first_name) &&
            validateName(fields.last_name) &&
            validateEmail() &&
            validatePhone() &&
            validatePassword() &&
            validateConfirmPassword() &&
            validateSelect(fields.role) &&
            validateSelect(fields.department) &&
            fields.terms.checked;

        if (!valid) {
            e.preventDefault();
        }
    });
});
