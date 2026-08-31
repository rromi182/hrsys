/*
Template Name: StarCode & Dashboard Template
File: auth Register init Js File
*/

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerForm");
    
    // Si no estamos en la página de register, salir sin error
    if (!form) return;

    const emailField = document.getElementById("email-field");
    const usernameField = document.getElementById("username-field");
    const passwordField = document.getElementById("password");
    const emailError = document.getElementById("email-error");
    const usernameError = document.getElementById("username-error");
    const passwordError = document.getElementById("password-error");
    const passwordSuggestion = document.getElementById("password-suggestion");

    form.addEventListener("submit", function (event) {
        let valid = true;

        // Reset errors (solo si el elemento existe)
        if (emailField) emailField.classList.remove("error");
        if (usernameField) usernameField.classList.remove("error");
        if (passwordField) passwordField.classList.remove("error");
        if (emailError) emailError.classList.add("hidden");
        if (usernameError) usernameError.classList.add("hidden");
        if (passwordError) passwordError.classList.add("hidden");
        if (passwordSuggestion) passwordSuggestion.classList.add("hidden");

        // Validar email
        if (emailField && !validateEmail(emailField.value)) {
            emailField.classList.add("error");
            if (emailError) emailError.classList.remove("hidden");
            valid = false;
        }

        // Validar username/name
        if (usernameField && !usernameField.value.trim()) {
            usernameField.classList.add("error");
            if (usernameError) usernameError.classList.remove("hidden");
            valid = false;
        }

        // Validar password
        if (passwordField && (passwordField.value.length < 8 || !containsLettersAndNumbers(passwordField.value))) {
            passwordField.classList.add("error");
            if (passwordError) passwordError.classList.remove("hidden");
            if (passwordSuggestion) passwordSuggestion.classList.remove("hidden");
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
        }
        // Si es válido, el formulario se envía normalmente al servidor
    });

    function validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function containsLettersAndNumbers(str) {
        return /[a-zA-Z]/.test(str) && /\d/.test(str);
    }
});